<?php
/**
 * Live Gold Price API Handler
 *
 * @package Persian_Live_Gold_Price_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Live_Gold_Price_API_Handler {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'live_gold_price_fetch_prices_cron', array( __CLASS__, 'fetch_prices_from_api' ) );
		// Legacy cron hook alias for seamless backward compatibility.
		add_action( 'lgp_fetch_prices_cron', array( __CLASS__, 'fetch_prices_from_api' ) );
	}

	/**
	 * Retrieve cached gold prices or fallback to backup option.
	 *
	 * @return array|false Array of prices or false on failure.
	 */
	public static function get_prices() {
		$prices = get_transient( 'live_gold_price_gold_prices' );

		if ( false === $prices ) {
			// Backward compatibility: check legacy transient.
			$prices = get_transient( 'lgp_gold_prices' );
		}

		if ( false === $prices ) {
			// Transient expired. Return persistent backup immediately to prevent frontend blocking.
			$prices = get_option( 'live_gold_price_gold_prices_backup' );

			if ( empty( $prices ) ) {
				// Backward compatibility: check legacy backup option.
				$prices = get_option( 'lgp_gold_prices_backup' );
			}

			if ( empty( $prices ) ) {
				// First run ever without any cache or backup: fetch synchronously.
				$prices = self::fetch_prices_from_api();
			}
		}

		return is_array( $prices ) ? $prices : false;
	}

	/**
	 * Fetch live prices from remote gold API endpoint.
	 *
	 * @return array|false Parsed prices or false on failure.
	 */
	public static function fetch_prices_from_api() {
		$api_key = get_option( 'live_gold_price_api_key', '' );
		if ( empty( $api_key ) ) {
			$api_key = get_option( 'lgp_api_key', '' );
		}

		if ( empty( $api_key ) ) {
			return false;
		}

		$api_url = get_option( 'live_gold_price_api_url', '' );
		if ( empty( $api_url ) ) {
			$api_url = get_option( 'lgp_api_url', 'https://api.brsapi.ir/Market/Gold_Currency.php' );
		}

		$url = add_query_arg( 'key', rawurlencode( $api_key ), $api_url );

		$response = wp_remote_get(
			esc_url_raw( $url ),
			array(
				'timeout' => 15,
				'limit'   => 50000, // Prevent OOM if API returns unexpected large payload
			)
		);

		if ( is_wp_error( $response ) ) {
			return self::handle_api_failure();
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			return self::handle_api_failure();
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return self::handle_api_failure();
		}

		$data = json_decode( $body, true );

		if ( ! is_array( $data ) || ! isset( $data['gold'] ) || ! is_array( $data['gold'] ) ) {
			return self::handle_api_failure();
		}

		$parsed_prices = array();

		// Parse the gold items (e.g. "طلای 18 عیار" and coins).
		foreach ( $data['gold'] as $item ) {
			if ( is_array( $item ) && isset( $item['name'] ) && isset( $item['price'] ) ) {
				$name = sanitize_text_field( trim( (string) $item['name'] ) );
				if ( '' !== $name ) {
					$parsed_prices[ $name ] = floatval( $item['price'] );
				}
			}
		}

		if ( empty( $parsed_prices ) ) {
			return self::handle_api_failure();
		}

		// Store in transient for 55 seconds (to align with 1-minute cron).
		set_transient( 'live_gold_price_gold_prices', $parsed_prices, 55 );
		// Store backup that never expires.
		update_option( 'live_gold_price_gold_prices_backup', $parsed_prices );
		update_option( 'live_gold_price_last_api_fetch', time() );

		// Increment successful fetches count for qualification tracking.
		$fetch_count = (int) get_option( 'live_gold_price_successful_fetches_count', 0 );
		update_option( 'live_gold_price_successful_fetches_count', $fetch_count + 1 );

		return $parsed_prices;
	}

	/**
	 * Handle API failure by falling back to stored backup data.
	 *
	 * @return array|false Backup prices or false.
	 */
	private static function handle_api_failure() {
		$old_prices = get_option( 'live_gold_price_gold_prices_backup' );
		if ( empty( $old_prices ) ) {
			$old_prices = get_option( 'lgp_gold_prices_backup' );
		}

		if ( ! empty( $old_prices ) && is_array( $old_prices ) ) {
			// Restore transient with old data to avoid spamming a failing external API.
			set_transient( 'live_gold_price_gold_prices', $old_prices, 55 );
			return $old_prices;
		}

		return false;
	}
}

// Backward compatibility alias for any existing code or third-party extensions.
if ( ! class_exists( 'LGP_API_Handler', false ) ) {
	class_alias( 'Live_Gold_Price_API_Handler', 'LGP_API_Handler' );
}
