<?php
/**
 * Live Gold Price Cart Handler
 *
 * @package Persian_Live_Gold_Price_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Live_Gold_Price_Cart {

	/**
	 * Initialize cart pricing hooks.
	 */
	public static function init() {
		// Priority 9999 to ensure this calculation runs after standard pricing filters.
		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'update_cart_prices' ), 9999, 1 );
	}

	/**
	 * Recalculate cart item prices dynamically based on live gold rates.
	 *
	 * @param WC_Cart $cart WooCommerce Cart object.
	 */
	public static function update_cart_prices( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		// Prevent infinite recursion loops.
		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
			return;
		}

		if ( ! $cart || ! is_object( $cart ) || ! method_exists( $cart, 'get_cart' ) ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item ) {
			if ( empty( $cart_item['data'] ) || ! is_object( $cart_item['data'] ) ) {
				continue;
			}

			$product_id = ! empty( $cart_item['variation_id'] ) ? absint( $cart_item['variation_id'] ) : absint( $cart_item['product_id'] );

			$is_enabled = get_post_meta( $product_id, '_live_gold_price_enabled', true );
			if ( '' === $is_enabled ) {
				$is_enabled = get_post_meta( $product_id, '_lgp_enabled', true );
			}

			if ( 'yes' === $is_enabled ) {
				$calc_price = Live_Gold_Price_Calculator::calculate_price( $product_id );

				if ( false !== $calc_price ) {
					$cart_item['data']->set_price( $calc_price );
				}
			}
		}
	}
}

// Backward compatibility alias.
if ( ! class_exists( 'LGP_Cart', false ) ) {
	class_alias( 'Live_Gold_Price_Cart', 'LGP_Cart' );
}
