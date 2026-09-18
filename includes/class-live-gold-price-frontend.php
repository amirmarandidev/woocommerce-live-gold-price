<?php
/**
 * Live Gold Price Frontend Handler
 *
 * @package Persian_Live_Gold_Price_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Live_Gold_Price_Frontend {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_filter( 'woocommerce_get_price_html', array( __CLASS__, 'override_price_html' ), 99, 2 );
		add_filter( 'wc_get_price_thousand_separator', array( __CLASS__, 'filter_thousand_separator' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_route' ) );
	}

	/**
	 * Filter thousand separator for currency formatting.
	 *
	 * @param string $separator Existing separator.
	 * @return string Modified separator.
	 */
	public static function filter_thousand_separator( $separator ) {
		return ',';
	}

	/**
	 * Enqueue frontend scripts and styles.
	 */
	public static function enqueue_assets() {
		if ( is_admin() ) {
			return;
		}

		wp_enqueue_style(
			'live-gold-price-frontend',
			LIVE_GOLD_PRICE_PLUGIN_URL . 'assets/css/live-gold-price-frontend.css',
			array(),
			LIVE_GOLD_PRICE_VERSION
		);

		// Dynamic dark mode dropdown styles if enabled.
		$dropdown_fix = get_option( 'live_gold_price_dropdown_dark_mode_fix', '' );
		if ( '' === $dropdown_fix ) {
			$dropdown_fix = get_option( 'lgp_dropdown_dark_mode_fix', 'no' );
		}
		if ( 'yes' === $dropdown_fix ) {
			$dropdown_css = '
			.woocommerce .variations select,
			.woocommerce .variations select option,
			.select2-container--default .select2-results__option,
			.select2-container--default .select2-selection--single {
				background-color: #222222 !important;
				color: #ffffff !important;
			}
			.select2-container--default .select2-selection--single {
				border-color: #444444 !important;
			}
			.select2-container--default .select2-selection--single .select2-selection__rendered {
				color: #ffffff !important;
			}';
			wp_add_inline_style( 'live-gold-price-frontend', $dropdown_css );
		}

		wp_enqueue_script(
			'live-gold-price-frontend',
			LIVE_GOLD_PRICE_PLUGIN_URL . 'assets/js/live-gold-price-frontend.js',
			array(),
			LIVE_GOLD_PRICE_VERSION,
			true
		);

		wp_localize_script(
			'live-gold-price-frontend',
			'live_gold_price_data',
			array(
				'rest_url' => esc_url_raw( rest_url( 'live-gold-price/v1/prices' ) ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
			)
		);

		// Backward compatibility for any external script referencing lgp_data.
		wp_localize_script(
			'live-gold-price-frontend',
			'lgp_data',
			array(
				'rest_url' => esc_url_raw( rest_url( 'live-gold-price/v1/prices' ) ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
			)
		);

		// Dark mode text contrast inline script fix if enabled.
		$dark_mode_fix = get_option( 'live_gold_price_dark_mode_fix', '' );
		if ( '' === $dark_mode_fix ) {
			$dark_mode_fix = get_option( 'lgp_dark_mode_fix', 'no' );
		}
		if ( 'yes' === $dark_mode_fix ) {
			$inline_js = "document.addEventListener('DOMContentLoaded',function(){var f=function(){var e=document.querySelectorAll('h1,h2,h3,h4,h5,h6,p,span,a,label,strong,b,div,li,th,td');for(var i=0;i<e.length;i++){var c=window.getComputedStyle(e[i]).color;if(c==='rgb(34, 34, 34)'||c==='#222222'){e[i].style.setProperty('color','#ffffff','important');}}};f();if(typeof jQuery!=='undefined'){jQuery(document).on('ajaxComplete',f);}setTimeout(f,500);setTimeout(f,2000);});";
			wp_add_inline_script( 'live-gold-price-frontend', $inline_js );
		}
	}

	/**
	 * Wrap price HTML with live indicators for enabled gold products.
	 *
	 * @param string     $price_html Price HTML.
	 * @param WC_Product $product    Product instance.
	 * @return string Modified price HTML.
	 */
	public static function override_price_html( $price_html, $product ) {
		if ( ! $product || ! is_object( $product ) ) {
			return $price_html;
		}

		if ( $product->is_type( 'variable' ) ) {
			$children = $product->get_visible_children();
			$is_gold  = false;
			foreach ( $children as $child_id ) {
				$enabled = get_post_meta( $child_id, '_live_gold_price_enabled', true );
				if ( '' === $enabled ) {
					$enabled = get_post_meta( $child_id, '_lgp_enabled', true );
				}
				if ( 'yes' === $enabled ) {
					$is_gold = true;
					break;
				}
			}
			if ( ! $is_gold ) {
				return $price_html;
			}
		} else {
			$enabled = get_post_meta( $product->get_id(), '_live_gold_price_enabled', true );
			if ( '' === $enabled ) {
				$enabled = get_post_meta( $product->get_id(), '_lgp_enabled', true );
			}
			if ( 'yes' !== $enabled ) {
				return $price_html;
			}
		}

		$current_calculated = self::get_product_price_html( $product );
		if ( $current_calculated ) {
			$price_html = $current_calculated;
		}

		return sprintf(
			'<span class="live-gold-price-wrapper lgp-live-price-wrapper" data-product-id="%1$s"><span class="live-gold-price-icon lgp-live-icon" title="%2$s"></span>%3$s</span>',
			esc_attr( $product->get_id() ),
			esc_attr__( 'قیمت لحظه‌ای', 'live-gold-price' ),
			$price_html
		);
	}

	/**
	 * Generates formatted price HTML dynamically.
	 *
	 * @param WC_Product $product WooCommerce product object.
	 * @return string Price HTML.
	 */
	public static function get_product_price_html( $product ) {
		if ( ! $product || ! is_object( $product ) ) {
			return '';
		}

		if ( $product->is_type( 'variable' ) ) {
			$prices   = array();
			$children = $product->get_visible_children();
			foreach ( $children as $child_id ) {
				$enabled = get_post_meta( $child_id, '_live_gold_price_enabled', true );
				if ( '' === $enabled ) {
					$enabled = get_post_meta( $child_id, '_lgp_enabled', true );
				}
				if ( 'yes' !== $enabled ) {
					continue;
				}

				$calc_price = Live_Gold_Price_Calculator::calculate_price( $child_id );
				if ( false !== $calc_price ) {
					$prices[] = $calc_price;
				} else {
					$variation = wc_get_product( $child_id );
					if ( $variation ) {
						$prices[] = floatval( $variation->get_price( 'edit' ) );
					}
				}
			}

			if ( empty( $prices ) ) {
				return '<span class="live-gold-price-error lgp-price-error">' . esc_html__( 'خطا: اطلاعات قیمت یا تنظیمات محصول ناقص است', 'live-gold-price' ) . '</span>';
			}

			$min_price = min( $prices );
			$max_price = max( $prices );

			if ( $min_price !== $max_price ) {
				return wc_format_price_range( $min_price, $max_price );
			} else {
				return wc_price( $min_price );
			}
		} else {
			$calc_price = Live_Gold_Price_Calculator::calculate_price( $product->get_id() );
			if ( false !== $calc_price ) {
				return wc_price( $calc_price );
			} else {
				return '<span class="live-gold-price-error lgp-price-error">' . esc_html__( 'در حال محاسبه... (اگر تغییر نکرد تنظیمات API یا محصول ناقص است)', 'live-gold-price' ) . '</span>';
			}
		}
	}

	/**
	 * Register REST API route for frontend price fetching.
	 */
	public static function register_rest_route() {
		register_rest_route(
			'live-gold-price/v1',
			'/prices',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'rest_get_prices' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'ids' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function( $param ) {
							return is_string( $param ) && '' !== trim( $param );
						},
					),
				),
			)
		);

		// Backward compatibility REST route.
		register_rest_route(
			'lgp/v1',
			'/prices',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'rest_get_prices' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'ids' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	/**
	 * REST API callback to return calculated live prices.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public static function rest_get_prices( $request ) {
		$ids_param = $request->get_param( 'ids' );
		if ( empty( $ids_param ) ) {
			return new WP_Error( 'missing_ids', esc_html__( 'No product IDs provided.', 'live-gold-price' ), array( 'status' => 400 ) );
		}

		$ids_param = sanitize_text_field( wp_unslash( $ids_param ) );
		$ids       = explode( ',', $ids_param );
		$ids       = array_map( 'absint', $ids );
		$ids       = array_unique( array_filter( $ids ) );

		$response_data = array();

		foreach ( $ids as $id ) {
			$product = wc_get_product( $id );
			if ( ! $product ) {
				continue;
			}

			$html = self::get_product_price_html( $product );
			if ( $html ) {
				$response_data[ $id ] = sprintf(
					'<span class="live-gold-price-icon lgp-live-icon" title="%1$s"></span>%2$s',
					esc_attr__( 'قیمت لحظه‌ای', 'live-gold-price' ),
					$html
				);
			}
		}

		return rest_ensure_response( $response_data );
	}
}

// Backward compatibility alias.
if ( ! class_exists( 'LGP_Frontend', false ) ) {
	class_alias( 'Live_Gold_Price_Frontend', 'LGP_Frontend' );
}
