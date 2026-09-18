<?php
/**
 * Live Gold Price Product Meta
 *
 * @package Persian_Live_Gold_Price_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Live_Gold_Price_Product_Meta {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'add_gold_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'gold_tab_content' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_gold_meta' ) );
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'variation_gold_content' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'save_variation_gold_meta' ), 10, 2 );
	}

	/**
	 * Add custom Live Gold Price tab to WooCommerce product data meta box.
	 *
	 * @param array $tabs Product tabs.
	 * @return array Modified tabs.
	 */
	public static function add_gold_tab( $tabs ) {
		$tabs['live_gold_price_gold'] = array(
			'label'  => __( 'قیمت زنده طلا', 'live-gold-price' ),
			'target' => 'live_gold_price_gold_product_data',
			'class'  => array( 'show_if_simple' ),
		);
		return $tabs;
	}

	/**
	 * Purity/coin choices supported by the API.
	 *
	 * @return array Purity options.
	 */
	public static function get_purity_options() {
		return array(
			''                 => __( 'محصول طلا نیست (غیرفعال)', 'live-gold-price' ),
			'طلای 18 عیار'      => __( 'طلای 18 عیار', 'live-gold-price' ),
			'سکه امامی'        => __( 'سکه امامی', 'live-gold-price' ),
			'سکه بهار آزادی'    => __( 'سکه بهار آزادی', 'live-gold-price' ),
			'نیم سکه'          => __( 'نیم سکه', 'live-gold-price' ),
			'ربع سکه'          => __( 'ربع سکه', 'live-gold-price' ),
			'سکه گرمی'         => __( 'سکه گرمی', 'live-gold-price' ),
		);
	}

	/**
	 * Render the product data panel for simple products.
	 */
	public static function gold_tab_content() {
		global $post;
		$post_id = $post ? $post->ID : 0;

		if ( ! $post_id ) {
			return;
		}

		echo '<div id="live_gold_price_gold_product_data" class="panel woocommerce_options_panel hidden">';

		wp_nonce_field( 'live_gold_price_save_product_meta_action', 'live_gold_price_product_meta_nonce' );

		$is_enabled = get_post_meta( $post_id, '_live_gold_price_enabled', true );
		if ( '' === $is_enabled ) {
			$is_enabled = get_post_meta( $post_id, '_lgp_enabled', true );
		}

		$purity = get_post_meta( $post_id, '_live_gold_price_purity', true );
		if ( '' === $purity ) {
			$purity = get_post_meta( $post_id, '_lgp_purity', true );
		}

		$weight = get_post_meta( $post_id, '_live_gold_price_weight', true );
		if ( '' === $weight ) {
			$weight = get_post_meta( $post_id, '_lgp_weight', true );
		}

		$wage_type = get_post_meta( $post_id, '_live_gold_price_wage_type', true );
		if ( '' === $wage_type ) {
			$wage_type = get_post_meta( $post_id, '_lgp_wage_type', true );
		}

		$wage = get_post_meta( $post_id, '_live_gold_price_wage', true );
		if ( '' === $wage ) {
			$wage = get_post_meta( $post_id, '_lgp_wage', true );
		}

		$profit = get_post_meta( $post_id, '_live_gold_price_profit', true );
		if ( '' === $profit ) {
			$profit = get_post_meta( $post_id, '_lgp_profit', true );
		}

		$tax = get_post_meta( $post_id, '_live_gold_price_tax', true );
		if ( '' === $tax ) {
			$tax = get_post_meta( $post_id, '_lgp_tax', true );
		}

		woocommerce_wp_checkbox( array(
			'id'          => '_live_gold_price_enabled',
			'value'       => $is_enabled,
			'label'       => __( 'فعال‌سازی قیمت زنده', 'live-gold-price' ),
			'description' => __( 'با تیک زدن این گزینه، قیمت این محصول به صورت زنده و با فرمول طلا محاسبه می‌شود.', 'live-gold-price' ),
			'desc_tip'    => true,
		) );

		woocommerce_wp_select( array(
			'id'          => '_live_gold_price_purity',
			'value'       => $purity,
			'label'       => __( 'نوع طلا / نوع سکه', 'live-gold-price' ),
			'description' => __( 'نوع محصول را برای دریافت قیمت متناظر از API انتخاب کنید.', 'live-gold-price' ),
			'desc_tip'    => true,
			'options'     => self::get_purity_options(),
		) );

		woocommerce_wp_text_input( array(
			'id'                => '_live_gold_price_weight',
			'value'             => $weight,
			'label'             => __( 'وزن (گرم)', 'live-gold-price' ),
			'description'       => __( 'وزن طلا را وارد کنید. برای سکه معمولاً ۱ وارد می‌شود.', 'live-gold-price' ),
			'desc_tip'          => true,
			'type'              => 'number',
			'custom_attributes' => array( 'step' => 'any', 'min' => '0' ),
		) );

		woocommerce_wp_select( array(
			'id'      => '_live_gold_price_wage_type',
			'value'   => $wage_type,
			'label'   => __( 'نوع محاسبه اجرت', 'live-gold-price' ),
			'options' => array(
				'percent'  => __( 'درصدی از قیمت خام طلا', 'live-gold-price' ),
				'per_gram' => __( 'مبلغ ثابت به ازای هر گرم', 'live-gold-price' ),
				'fixed'    => __( 'مبلغ ثابت کل', 'live-gold-price' ),
			),
		) );

		woocommerce_wp_text_input( array(
			'id'                => '_live_gold_price_wage',
			'value'             => $wage,
			'label'             => __( 'مقدار اجرت', 'live-gold-price' ),
			'description'       => __( 'مقدار اجرت را بر اساس نوع انتخاب شده در بالا وارد کنید.', 'live-gold-price' ),
			'desc_tip'          => true,
			'type'              => 'number',
			'custom_attributes' => array( 'step' => 'any', 'min' => '0' ),
		) );

		woocommerce_wp_text_input( array(
			'id'                => '_live_gold_price_profit',
			'value'             => $profit,
			'label'             => __( 'سود فروشنده (%)', 'live-gold-price' ),
			'description'       => __( 'در صورت خالی گذاشتن، از تنظیمات پیش‌فرض افزونه استفاده می‌شود.', 'live-gold-price' ),
			'desc_tip'          => true,
			'type'              => 'number',
			'custom_attributes' => array( 'step' => 'any', 'min' => '0' ),
		) );

		woocommerce_wp_text_input( array(
			'id'                => '_live_gold_price_tax',
			'value'             => $tax,
			'label'             => __( 'مالیات ارزش افزوده (%)', 'live-gold-price' ),
			'description'       => __( 'در صورت خالی گذاشتن، از تنظیمات پیش‌فرض افزونه استفاده می‌شود.', 'live-gold-price' ),
			'desc_tip'          => true,
			'type'              => 'number',
			'custom_attributes' => array( 'step' => 'any', 'min' => '0' ),
		) );

		echo '</div>';
	}

	/**
	 * Save product meta data for simple products.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function save_gold_meta( $post_id ) {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return;
		}

		// Security: verify nonce.
		if ( ! isset( $_POST['live_gold_price_product_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['live_gold_price_product_meta_nonce'] ) ), 'live_gold_price_save_product_meta_action' ) ) {
			return;
		}

		// Security: verify user permission.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Handle enabled state.
		$is_enabled = ( isset( $_POST['_live_gold_price_enabled'] ) || isset( $_POST['_lgp_enabled'] ) ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_live_gold_price_enabled', $is_enabled );
		update_post_meta( $post_id, '_lgp_enabled', $is_enabled ); // Backward compatibility sync

		// Field handlers with strict sanitization.
		$text_fields = array(
			'_live_gold_price_purity'    => '_lgp_purity',
			'_live_gold_price_wage_type' => '_lgp_wage_type',
		);
		foreach ( $text_fields as $field_key => $legacy_key ) {
			$value = '';
			if ( isset( $_POST[ $field_key ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_POST[ $field_key ] ) );
			} elseif ( isset( $_POST[ $legacy_key ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_POST[ $legacy_key ] ) );
			}

			if ( '' !== $value ) {
				update_post_meta( $post_id, $field_key, $value );
				update_post_meta( $post_id, $legacy_key, $value );
			} else {
				delete_post_meta( $post_id, $field_key );
				delete_post_meta( $post_id, $legacy_key );
			}
		}

		$number_fields = array(
			'_live_gold_price_weight' => '_lgp_weight',
			'_live_gold_price_wage'   => '_lgp_wage',
			'_live_gold_price_profit' => '_lgp_profit',
			'_live_gold_price_tax'    => '_lgp_tax',
		);
		foreach ( $number_fields as $field_key => $legacy_key ) {
			$val_present = false;
			$clean_val   = '';
			if ( isset( $_POST[ $field_key ] ) && '' !== trim( (string) wp_unslash( $_POST[ $field_key ] ) ) ) {
				$clean_val   = (string) floatval( wp_unslash( $_POST[ $field_key ] ) );
				$val_present = true;
			} elseif ( isset( $_POST[ $legacy_key ] ) && '' !== trim( (string) wp_unslash( $_POST[ $legacy_key ] ) ) ) {
				$clean_val   = (string) floatval( wp_unslash( $_POST[ $legacy_key ] ) );
				$val_present = true;
			}

			if ( $val_present ) {
				update_post_meta( $post_id, $field_key, $clean_val );
				update_post_meta( $post_id, $legacy_key, $clean_val );
			} else {
				delete_post_meta( $post_id, $field_key );
				delete_post_meta( $post_id, $legacy_key );
			}
		}

		// Ensure WooCommerce marks the product as purchasable.
		if ( 'yes' === $is_enabled ) {
			$reg_price = get_post_meta( $post_id, '_regular_price', true );
			if ( empty( $reg_price ) ) {
				update_post_meta( $post_id, '_regular_price', '1' );
				update_post_meta( $post_id, '_price', '1' );
			}
		}
	}

	/**
	 * Render variation custom live gold fields.
	 *
	 * @param int     $loop           Position in loop.
	 * @param array   $variation_data Variation data array.
	 * @param WP_Post $variation      Variation post object.
	 */
	public static function variation_gold_content( $loop, $variation_data, $variation ) {
		$variation_id = absint( $variation->ID );

		$is_enabled = get_post_meta( $variation_id, '_live_gold_price_enabled', true );
		if ( '' === $is_enabled ) {
			$is_enabled = get_post_meta( $variation_id, '_lgp_enabled', true );
		}

		$purity = get_post_meta( $variation_id, '_live_gold_price_purity', true );
		if ( '' === $purity ) {
			$purity = get_post_meta( $variation_id, '_lgp_purity', true );
		}

		$weight = get_post_meta( $variation_id, '_live_gold_price_weight', true );
		if ( '' === $weight ) {
			$weight = get_post_meta( $variation_id, '_lgp_weight', true );
		}

		$wage_type = get_post_meta( $variation_id, '_live_gold_price_wage_type', true );
		if ( '' === $wage_type ) {
			$wage_type = get_post_meta( $variation_id, '_lgp_wage_type', true );
		}

		$wage = get_post_meta( $variation_id, '_live_gold_price_wage', true );
		if ( '' === $wage ) {
			$wage = get_post_meta( $variation_id, '_lgp_wage', true );
		}

		$profit = get_post_meta( $variation_id, '_live_gold_price_profit', true );
		if ( '' === $profit ) {
			$profit = get_post_meta( $variation_id, '_lgp_profit', true );
		}

		$tax = get_post_meta( $variation_id, '_live_gold_price_tax', true );
		if ( '' === $tax ) {
			$tax = get_post_meta( $variation_id, '_lgp_tax', true );
		}

		echo '<div class="options_group form-row form-row-full live-gold-price-variation-settings lgp-variation-settings">';
		echo '<h4>' . esc_html__( 'قیمت زنده طلا (جایگزین قیمت عادی می‌شود)', 'live-gold-price' ) . '</h4>';

		woocommerce_wp_checkbox( array(
			'id'            => "_live_gold_price_enabled[{$loop}]",
			'name'          => "_live_gold_price_enabled[{$loop}]",
			'value'         => $is_enabled,
			'label'         => __( 'فعال‌سازی قیمت زنده برای این متغیر', 'live-gold-price' ),
			'wrapper_class' => 'form-row form-row-full',
		) );

		woocommerce_wp_select( array(
			'id'            => "_live_gold_price_purity[{$loop}]",
			'name'          => "_live_gold_price_purity[{$loop}]",
			'value'         => $purity,
			'label'         => __( 'نوع طلا / نوع سکه', 'live-gold-price' ),
			'options'       => self::get_purity_options(),
			'wrapper_class' => 'form-row form-row-full',
		) );

		woocommerce_wp_text_input( array(
			'id'                => "_live_gold_price_weight[{$loop}]",
			'name'              => "_live_gold_price_weight[{$loop}]",
			'value'             => $weight,
			'label'             => __( 'وزن (گرم)', 'live-gold-price' ),
			'type'              => 'number',
			'custom_attributes' => array( 'step' => 'any', 'min' => '0' ),
			'wrapper_class'     => 'form-row form-row-first',
		) );

		woocommerce_wp_select( array(
			'id'            => "_live_gold_price_wage_type[{$loop}]",
			'name'          => "_live_gold_price_wage_type[{$loop}]",
			'value'         => $wage_type,
			'label'         => __( 'نوع محاسبه اجرت', 'live-gold-price' ),
			'options'       => array(
				'percent'  => __( 'درصدی', 'live-gold-price' ),
				'per_gram' => __( 'ثابت هر گرم', 'live-gold-price' ),
				'fixed'    => __( 'ثابت کل', 'live-gold-price' ),
			),
			'wrapper_class' => 'form-row form-row-last',
		) );

		woocommerce_wp_text_input( array(
			'id'                => "_live_gold_price_wage[{$loop}]",
			'name'              => "_live_gold_price_wage[{$loop}]",
			'value'             => $wage,
			'label'             => __( 'مقدار اجرت', 'live-gold-price' ),
			'type'              => 'number',
			'custom_attributes' => array( 'step' => 'any', 'min' => '0' ),
			'wrapper_class'     => 'form-row form-row-first',
		) );

		woocommerce_wp_text_input( array(
			'id'                => "_live_gold_price_profit[{$loop}]",
			'name'              => "_live_gold_price_profit[{$loop}]",
			'value'             => $profit,
			'label'             => __( 'سود فروشنده (%)', 'live-gold-price' ),
			'type'              => 'number',
			'custom_attributes' => array( 'step' => 'any', 'min' => '0' ),
			'wrapper_class'     => 'form-row form-row-last',
		) );

		woocommerce_wp_text_input( array(
			'id'                => "_live_gold_price_tax[{$loop}]",
			'name'              => "_live_gold_price_tax[{$loop}]",
			'value'             => $tax,
			'label'             => __( 'مالیات (%)', 'live-gold-price' ),
			'type'              => 'number',
			'custom_attributes' => array( 'step' => 'any', 'min' => '0' ),
			'wrapper_class'     => 'form-row form-row-first',
		) );

		echo '</div>';
	}

	/**
	 * Save product variation meta data.
	 *
	 * @param int $variation_id Variation ID.
	 * @param int $i            Index.
	 */
	public static function save_variation_gold_meta( $variation_id, $i ) {
		$variation_id = absint( $variation_id );
		if ( ! $variation_id ) {
			return;
		}

		// Security: capability check.
		if ( ! current_user_can( 'edit_post', $variation_id ) ) {
			return;
		}

		// Security: check WooCommerce nonce if present.
		if ( isset( $_POST['security'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['security'] ) );
			if ( ! wp_verify_nonce( $nonce, 'save-variations' ) ) {
				return;
			}
		} elseif ( isset( $_POST['woocommerce_meta_nonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) );
			if ( ! wp_verify_nonce( $nonce, 'woocommerce_save_data' ) ) {
				return;
			}
		}

		$is_enabled = 'no';
		if ( isset( $_POST['_live_gold_price_enabled'][ $i ] ) ) {
			$is_enabled = 'yes' === sanitize_text_field( wp_unslash( $_POST['_live_gold_price_enabled'][ $i ] ) ) ? 'yes' : 'no';
		} elseif ( isset( $_POST['_lgp_enabled'][ $i ] ) ) {
			$is_enabled = 'yes' === sanitize_text_field( wp_unslash( $_POST['_lgp_enabled'][ $i ] ) ) ? 'yes' : 'no';
		}

		update_post_meta( $variation_id, '_live_gold_price_enabled', $is_enabled );
		update_post_meta( $variation_id, '_lgp_enabled', $is_enabled );

		$text_fields = array(
			'_live_gold_price_purity'    => '_lgp_purity',
			'_live_gold_price_wage_type' => '_lgp_wage_type',
		);
		foreach ( $text_fields as $field_key => $legacy_key ) {
			$value = '';
			if ( isset( $_POST[ $field_key ][ $i ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_POST[ $field_key ][ $i ] ) );
			} elseif ( isset( $_POST[ $legacy_key ][ $i ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_POST[ $legacy_key ][ $i ] ) );
			}

			if ( '' !== $value ) {
				update_post_meta( $variation_id, $field_key, $value );
				update_post_meta( $variation_id, $legacy_key, $value );
			} else {
				delete_post_meta( $variation_id, $field_key );
				delete_post_meta( $variation_id, $legacy_key );
			}
		}

		$number_fields = array(
			'_live_gold_price_weight' => '_lgp_weight',
			'_live_gold_price_wage'   => '_lgp_wage',
			'_live_gold_price_profit' => '_lgp_profit',
			'_live_gold_price_tax'    => '_lgp_tax',
		);
		foreach ( $number_fields as $field_key => $legacy_key ) {
			$val_present = false;
			$clean_val   = '';
			if ( isset( $_POST[ $field_key ][ $i ] ) && '' !== trim( (string) wp_unslash( $_POST[ $field_key ][ $i ] ) ) ) {
				$clean_val   = (string) floatval( wp_unslash( $_POST[ $field_key ][ $i ] ) );
				$val_present = true;
			} elseif ( isset( $_POST[ $legacy_key ][ $i ] ) && '' !== trim( (string) wp_unslash( $_POST[ $legacy_key ][ $i ] ) ) ) {
				$clean_val   = (string) floatval( wp_unslash( $_POST[ $legacy_key ][ $i ] ) );
				$val_present = true;
			}

			if ( $val_present ) {
				update_post_meta( $variation_id, $field_key, $clean_val );
				update_post_meta( $variation_id, $legacy_key, $clean_val );
			} else {
				delete_post_meta( $variation_id, $field_key );
				delete_post_meta( $variation_id, $legacy_key );
			}
		}

		if ( 'yes' === $is_enabled ) {
			$reg_price = get_post_meta( $variation_id, '_regular_price', true );
			if ( empty( $reg_price ) ) {
				update_post_meta( $variation_id, '_regular_price', '1' );
				update_post_meta( $variation_id, '_price', '1' );
			}
		}
	}
}

// Backward compatibility alias.
if ( ! class_exists( 'LGP_Product_Meta', false ) ) {
	class_alias( 'Live_Gold_Price_Product_Meta', 'LGP_Product_Meta' );
}
