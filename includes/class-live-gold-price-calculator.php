<?php
/**
 * Live Gold Price Calculator
 *
 * @package Persian_Live_Gold_Price_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Live_Gold_Price_Calculator {

	/**
	 * Calculates the final live price for a given product or variation ID.
	 *
	 * @param int $product_id Product or Variation ID.
	 * @return float|false The calculated price, or false if not a live gold product.
	 */
	public static function calculate_price( $product_id ) {
		$product_id = absint( $product_id );
		if ( ! $product_id ) {
			return false;
		}

		$is_enabled = get_post_meta( $product_id, '_live_gold_price_enabled', true );
		if ( '' === $is_enabled ) {
			// Backward compatibility: fallback to legacy meta key.
			$is_enabled = get_post_meta( $product_id, '_lgp_enabled', true );
		}

		if ( 'yes' !== $is_enabled ) {
			return false; // Not enabled for this product/variation.
		}

		$purity = get_post_meta( $product_id, '_live_gold_price_purity', true );
		if ( '' === $purity ) {
			$purity = get_post_meta( $product_id, '_lgp_purity', true );
		}

		if ( empty( $purity ) ) {
			return false; // No purity configured.
		}

		$prices = Live_Gold_Price_API_Handler::get_prices();
		if ( empty( $prices ) || ! isset( $prices[ $purity ] ) ) {
			return false; // Price not available in API data for this purity.
		}

		$live_price_per_unit = floatval( $prices[ $purity ] );

		$weight_meta = get_post_meta( $product_id, '_live_gold_price_weight', true );
		if ( '' === $weight_meta ) {
			$weight_meta = get_post_meta( $product_id, '_lgp_weight', true );
		}
		$weight = floatval( $weight_meta );
		if ( $weight <= 0 ) {
			// Default to 1 unit (for coins or unweighted items).
			$weight = 1.0;
		}

		$wage_type = get_post_meta( $product_id, '_live_gold_price_wage_type', true );
		if ( '' === $wage_type ) {
			$wage_type = get_post_meta( $product_id, '_lgp_wage_type', true );
		}

		$wage_meta = get_post_meta( $product_id, '_live_gold_price_wage', true );
		if ( '' === $wage_meta ) {
			$wage_meta = get_post_meta( $product_id, '_lgp_wage', true );
		}
		$wage_val = floatval( $wage_meta );

		$profit_val = get_post_meta( $product_id, '_live_gold_price_profit', true );
		if ( '' === $profit_val ) {
			$profit_val = get_post_meta( $product_id, '_lgp_profit', true );
		}
		if ( '' === $profit_val ) {
			$profit_val = get_option( 'live_gold_price_global_profit', '' );
			if ( '' === $profit_val ) {
				$profit_val = get_option( 'lgp_global_profit', '7' );
			}
		}
		$profit_percent = floatval( $profit_val );

		$tax_val = get_post_meta( $product_id, '_live_gold_price_tax', true );
		if ( '' === $tax_val ) {
			$tax_val = get_post_meta( $product_id, '_lgp_tax', true );
		}
		if ( '' === $tax_val ) {
			$tax_val = get_option( 'live_gold_price_global_tax', '' );
			if ( '' === $tax_val ) {
				$tax_val = get_option( 'lgp_global_tax', '2' );
			}
		}
		$tax_percent = floatval( $tax_val );

		// Step 1: Base Gold Value.
		$base_gold_value = $weight * $live_price_per_unit;

		// Step 2: Wage (Ojrat).
		$wage_amount = 0.0;
		if ( 'percent' === $wage_type ) {
			$wage_amount = $base_gold_value * ( $wage_val / 100.0 );
		} elseif ( 'per_gram' === $wage_type ) {
			$wage_amount = $weight * $wage_val;
		} elseif ( 'fixed' === $wage_type ) {
			$wage_amount = $wage_val;
		}

		$value_with_wage = $base_gold_value + $wage_amount;

		// Step 3: Seller Profit.
		$profit_amount = $value_with_wage * ( $profit_percent / 100.0 );

		// Step 4: Tax (VAT on base + wage + profit).
		$value_with_profit = $value_with_wage + $profit_amount;
		$tax_amount        = $value_with_profit * ( $tax_percent / 100.0 );

		// Final Price (rounded to nearest whole currency unit).
		$final_price = round( $value_with_profit + $tax_amount );

		/**
		 * Filter the calculated live price.
		 *
		 * @param float $final_price       Final price.
		 * @param int   $product_id        Product or Variation ID.
		 * @param float $base_gold_value   Base raw gold value.
		 * @param float $wage_amount       Wage calculated.
		 * @param float $profit_amount     Profit calculated.
		 * @param float $tax_amount        Tax calculated.
		 */
		$final_price = apply_filters(
			'live_gold_price_calculated_price',
			$final_price,
			$product_id,
			$base_gold_value,
			$wage_amount,
			$profit_amount,
			$tax_amount
		);

		return floatval( $final_price );
	}

	/**
	 * Formats the price for display.
	 *
	 * @param float $price Price value.
	 * @return string Formatted price HTML.
	 */
	public static function format_price( $price ) {
		if ( function_exists( 'wc_price' ) ) {
			return wc_price( $price );
		}
		return (string) $price;
	}
}

// Backward compatibility alias.
if ( ! class_exists( 'LGP_Calculator', false ) ) {
	class_alias( 'Live_Gold_Price_Calculator', 'LGP_Calculator' );
}
