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
	 * Retrieve configured refresh interval in seconds (strictly minimum 10 seconds).
	 *
	 * @return int Interval in seconds.
	 */
	public static function get_refresh_interval() {
		$interval = get_option( 'live_gold_price_refresh_interval', '' );
		if ( '' === $interval ) {
			$interval = get_option( 'lgp_refresh_interval', 60 );
		}
		$interval = absint( $interval );
		return max( 10, $interval );
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
			// If transient expired, check when the API was last queried.
			$last_fetch = (int) get_option( 'live_gold_price_last_api_fetch', 0 );
			if ( ! $last_fetch ) {
				$last_fetch = (int) get_option( 'lgp_last_api_fetch', 0 );
			}

			$interval = self::get_refresh_interval();

			// If never fetched or the configured interval has elapsed, fetch live from API.
			if ( 0 === $last_fetch || ( time() - $last_fetch ) >= $interval ) {
				$prices = self::fetch_prices_from_api();
			}

			if ( empty( $prices ) ) {
				// Fallback to persistent backup option to avoid blocking frontend.
				$prices = get_option( 'live_gold_price_gold_prices_backup' );

				if ( empty( $prices ) ) {
					// Backward compatibility: check legacy backup option.
					$prices = get_option( 'lgp_gold_prices_backup' );
				}
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
		$api_key = trim( (string) get_option( 'live_gold_price_api_key', '' ) );
		if ( empty( $api_key ) ) {
			$api_key = trim( (string) get_option( 'lgp_api_key', '' ) );
		}
		// Fallback to public working default key if unconfigured.
		if ( empty( $api_key ) ) {
			$api_key = 'BpPAcAtIbRzRMrUTRN18BePUdbIBQiNr';
		}

		$api_url = get_option( 'live_gold_price_api_url', '' );
		if ( empty( $api_url ) ) {
			$api_url = get_option( 'lgp_api_url', '' );
		}
		if ( empty( $api_url ) ) {
			$api_url = 'https://api.brsapi.ir/Market/Gold_Currency.php';
		}

		// Clean any existing key parameter to avoid query string corruption.
		$clean_url = remove_query_arg( 'key', $api_url );
		$url       = add_query_arg( 'key', $api_key, $clean_url );

		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 15,
				'sslverify'  => false, // Prevent SSL handshake failures on servers with outdated local CA bundles
				'limit'      => 100000,
				'user-agent' => 'LiveGoldPrice/' . LIVE_GOLD_PRICE_VERSION . '; ' . home_url(),
			)
		);

		if ( is_wp_error( $response ) ) {
			return self::handle_api_failure();
		}

		$status_code = (int) wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code && 0 !== $status_code ) {
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

		// Parse gold and coin rates.
		foreach ( $data['gold'] as $item ) {
			if ( is_array( $item ) && isset( $item['name'] ) && isset( $item['price'] ) ) {
				$name = trim( (string) $item['name'] );
				if ( '' !== $name ) {
					$price = floatval( $item['price'] );
					$parsed_prices[ $name ] = $price;

					// Normalize spaces (replace ZWNJ and NBSP with standard space) for robust matching.
					$normalized_name = str_replace( array( "\xE2\x80\x8C", "\xC2\xA0" ), ' ', $name );
					$normalized_name = preg_replace( '/\s+/', ' ', $normalized_name );
					$normalized_name = trim( $normalized_name );
					if ( '' !== $normalized_name && $normalized_name !== $name ) {
						$parsed_prices[ $normalized_name ] = $price;
					}
				}
			}
		}

		if ( empty( $parsed_prices ) ) {
			return self::handle_api_failure();
		}

		$interval = self::get_refresh_interval();
		// Set transient TTL (at least 8s or interval minus buffer)
		$ttl = ( $interval > 15 ) ? ( $interval - 3 ) : $interval;

		// Store in transient aligned with user configured refresh interval.
		set_transient( 'live_gold_price_gold_prices', $parsed_prices, $ttl );
		set_transient( 'lgp_gold_prices', $parsed_prices, $ttl );

		// Store backup that never expires.
		update_option( 'live_gold_price_gold_prices_backup', $parsed_prices );
		update_option( 'lgp_gold_prices_backup', $parsed_prices );
		update_option( 'live_gold_price_last_api_fetch', time() );
		update_option( 'lgp_last_api_fetch', time() );

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
			$interval = self::get_refresh_interval();
			$ttl      = ( $interval > 15 ) ? ( $interval - 3 ) : $interval;

			// Restore transient with old data to avoid spamming a failing external API.
			set_transient( 'live_gold_price_gold_prices', $old_prices, $ttl );
			set_transient( 'lgp_gold_prices', $old_prices, $ttl );
			return $old_prices;
		}

		return false;
	}
}

// Backward compatibility alias for any existing code or third-party extensions.
if ( ! class_exists( 'LGP_API_Handler', false ) ) {
	class_alias( 'Live_Gold_Price_API_Handler', 'LGP_API_Handler' );
}
