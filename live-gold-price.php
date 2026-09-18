<?php
/**
 * Plugin Name:       Live Gold Price
 * Plugin URI:        https://github.com/thekourox/live-gold-price
 * Description:       افزونه فارسی اتصال آنلاین به وب‌سرویس‌های قیمت لحظه‌ای طلا و محاسبه قیمت لحظه‌ای محصولات در ووکامرس.
 * Version:           1.5.3
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Amir Marandi
 * Author URI:        https://kourox.ir
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       live-gold-price
 * Domain Path:       /languages
 * WC requires at least: 5.0
 * WC tested up to:   9.3
 *
 * @package Persian_Live_Gold_Price_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin Constants.
define( 'LIVE_GOLD_PRICE_VERSION', '1.5.3' );
define( 'LIVE_GOLD_PRICE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LIVE_GOLD_PRICE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LIVE_GOLD_PRICE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Backward compatibility constants for any legacy third-party code.
if ( ! defined( 'LGP_VERSION' ) ) {
	define( 'LGP_VERSION', LIVE_GOLD_PRICE_VERSION );
}
if ( ! defined( 'LGP_PLUGIN_DIR' ) ) {
	define( 'LGP_PLUGIN_DIR', LIVE_GOLD_PRICE_PLUGIN_DIR );
}
if ( ! defined( 'LGP_PLUGIN_URL' ) ) {
	define( 'LGP_PLUGIN_URL', LIVE_GOLD_PRICE_PLUGIN_URL );
}

/**
 * Declare compatibility with WooCommerce High-Performance Order Storage (HPOS).
 */
add_action( 'before_woocommerce_init', 'live_gold_price_declare_hpos_compatibility' );
function live_gold_price_declare_hpos_compatibility() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}

/**
 * Main Plugin Class.
 */
class Live_Gold_Price {

	/**
	 * Single instance of the class.
	 *
	 * @var Live_Gold_Price|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return Live_Gold_Price
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );
		register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivate' ) );

		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Initialize the plugin after plugins are loaded.
	 */
	public function init() {
		// Load text domain for translations.
		load_plugin_textdomain( 'live-gold-price', false, dirname( LIVE_GOLD_PRICE_PLUGIN_BASENAME ) . '/languages' );

		// Verify WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			return;
		}

		// Run version check and automatic database migration routine.
		$this->check_version();

		$this->includes();
		$this->init_classes();
	}

	/**
	 * Versioning and Migration System.
	 * Compares installed DB version with current plugin constant and runs necessary migrations.
	 */
	public function check_version() {
		$installed_version = get_option( 'live_gold_price_version', '0.0.0' );

		if ( version_compare( $installed_version, LIVE_GOLD_PRICE_VERSION, '<' ) ) {
			$this->run_upgrade( $installed_version );
			update_option( 'live_gold_price_version', LIVE_GOLD_PRICE_VERSION );
		}
	}

	/**
	 * Upgrade routine to migrate legacy settings and data cleanly.
	 *
	 * @param string $from_version Previous version string.
	 */
	private function run_upgrade( $from_version ) {
		// Map legacy option keys to new standardized prefix.
		$legacy_options = array(
			'lgp_api_url'                => 'live_gold_price_api_url',
			'lgp_api_key'                => 'live_gold_price_api_key',
			'lgp_global_profit'          => 'live_gold_price_global_profit',
			'lgp_global_tax'             => 'live_gold_price_global_tax',
			'lgp_dark_mode_fix'          => 'live_gold_price_dark_mode_fix',
			'lgp_dropdown_dark_mode_fix' => 'live_gold_price_dropdown_dark_mode_fix',
			'lgp_gold_prices_backup'     => 'live_gold_price_gold_prices_backup',
			'lgp_last_api_fetch'         => 'live_gold_price_last_api_fetch',
		);

		foreach ( $legacy_options as $old_key => $new_key ) {
			$old_val = get_option( $old_key, null );
			if ( null !== $old_val && false === get_option( $new_key, false ) ) {
				update_option( $new_key, $old_val );
			}
		}

		// Migrate transient cache if present.
		$old_transient = get_transient( 'lgp_gold_prices' );
		if ( false !== $old_transient && false === get_transient( 'live_gold_price_gold_prices' ) ) {
			set_transient( 'live_gold_price_gold_prices', $old_transient, 55 );
		}
	}

	/**
	 * Admin notice displayed if WooCommerce is not active.
	 */
	public function woocommerce_missing_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		?>
		<div class="notice notice-error is-dismissible">
			<p>
				<?php esc_html_e( 'افزونه «قیمت لحظه‌ای طلا» برای کارکرد صحیح نیازمند نصب و فعال‌سازی افزونه ووکامرس (WooCommerce) است.', 'live-gold-price' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Include required class files.
	 */
	private function includes() {
		require_once LIVE_GOLD_PRICE_PLUGIN_DIR . 'includes/class-live-gold-price-api-handler.php';
		require_once LIVE_GOLD_PRICE_PLUGIN_DIR . 'includes/class-live-gold-price-calculator.php';
		require_once LIVE_GOLD_PRICE_PLUGIN_DIR . 'includes/class-live-gold-price-product-meta.php';
		require_once LIVE_GOLD_PRICE_PLUGIN_DIR . 'includes/class-live-gold-price-cart.php';
		require_once LIVE_GOLD_PRICE_PLUGIN_DIR . 'includes/class-live-gold-price-frontend.php';

		if ( is_admin() ) {
			require_once LIVE_GOLD_PRICE_PLUGIN_DIR . 'includes/class-live-gold-price-admin-settings.php';
			require_once LIVE_GOLD_PRICE_PLUGIN_DIR . 'includes/class-live-gold-price-review-notice.php';
		}
	}

	/**
	 * Initialize modular component classes.
	 */
	private function init_classes() {
		Live_Gold_Price_API_Handler::init();
		Live_Gold_Price_Product_Meta::init();
		Live_Gold_Price_Cart::init();
		Live_Gold_Price_Frontend::init();

		if ( is_admin() ) {
			Live_Gold_Price_Admin_Settings::init();
			Live_Gold_Price_Review_Notice::init();
		}
	}

	/**
	 * Plugin activation routine.
	 */
	public static function activate() {
		if ( ! wp_next_scheduled( 'live_gold_price_fetch_prices_cron' ) ) {
			wp_schedule_event( time(), 'live_gold_price_1_min', 'live_gold_price_fetch_prices_cron' );
		}

		// Ensure version is recorded upon initial activation.
		if ( false === get_option( 'live_gold_price_version', false ) ) {
			update_option( 'live_gold_price_version', LIVE_GOLD_PRICE_VERSION );
		}

		// Record initial activation timestamp for review delay qualification.
		if ( false === get_option( 'live_gold_price_activated_time', false ) ) {
			update_option( 'live_gold_price_activated_time', time() );
		}
	}

	/**
	 * Plugin deactivation routine.
	 */
	public static function deactivate() {
		$timestamp = wp_next_scheduled( 'live_gold_price_fetch_prices_cron' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'live_gold_price_fetch_prices_cron' );
		}

		// Clean up legacy scheduled hook if still registered.
		$legacy_timestamp = wp_next_scheduled( 'lgp_fetch_prices_cron' );
		if ( $legacy_timestamp ) {
			wp_unschedule_event( $legacy_timestamp, 'lgp_fetch_prices_cron' );
		}
	}
}

/**
 * Register 1-minute cron interval.
 *
 * @param array $schedules WP cron schedules.
 * @return array
 */
add_filter( 'cron_schedules', 'live_gold_price_add_cron_interval' );
function live_gold_price_add_cron_interval( $schedules ) {
	$schedules['live_gold_price_1_min'] = array(
		'interval' => 60,
		'display'  => __( 'هر ۱ دقیقه (افزونه طلا)', 'live-gold-price' ),
	);
	// Backward compatibility schedule key.
	$schedules['lgp_1_min'] = array(
		'interval' => 60,
		'display'  => __( 'هر ۱ دقیقه (افزونه طلا)', 'live-gold-price' ),
	);
	return $schedules;
}

// Instantiate plugin singleton.
Live_Gold_Price::get_instance();
