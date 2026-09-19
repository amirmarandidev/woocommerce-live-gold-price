<?php
/**
 * Live Gold Price Admin Settings
 *
 * @package Persian_Live_Gold_Price_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Live_Gold_Price_Admin_Settings {

	/**
	 * Initialize admin hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_post_live_gold_price_manual_fetch', array( __CLASS__, 'manual_fetch_prices' ) );
		// Backward compatibility action hook.
		add_action( 'admin_post_lgp_manual_fetch', array( __CLASS__, 'manual_fetch_prices' ) );
	}

	/**
	 * Manual trigger to fetch fresh prices from API.
	 */
	public static function manual_fetch_prices() {
		if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'live-gold-price' ), 403 );
		}

		if ( isset( $_GET['live_gold_price_nonce'] ) ) {
			check_admin_referer( 'live_gold_price_manual_fetch_action', 'live_gold_price_nonce' );
		} else {
			check_admin_referer( 'lgp_manual_fetch_nonce' );
		}

		// Clear transients to force fresh fetch.
		delete_transient( 'live_gold_price_gold_prices' );
		delete_transient( 'lgp_gold_prices' );

		$result = Live_Gold_Price_API_Handler::fetch_prices_from_api();

		$param = ( false !== $result ) ? 'updated=1' : 'fetch_error=1';
		wp_safe_redirect( admin_url( 'admin.php?page=live-gold-price-settings&' . $param ) );
		exit;
	}

	/**
	 * Register admin menu entry.
	 */
	public static function add_admin_menu() {
		add_menu_page(
			__( 'تنظیمات قیمت زنده طلا', 'live-gold-price' ),
			__( 'قیمت زنده طلا', 'live-gold-price' ),
			'manage_woocommerce',
			'live-gold-price-settings',
			array( __CLASS__, 'settings_page_html' ),
			'dashicons-chart-line',
			55
		);
	}

	/**
	 * Register plugin settings and fields.
	 */
	public static function register_settings() {
		register_setting(
			'live_gold_price_settings_group',
			'live_gold_price_api_url',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
				'default'           => 'https://api.brsapi.ir/Market/Gold_Currency.php',
			)
		);

		register_setting(
			'live_gold_price_settings_group',
			'live_gold_price_api_key',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'BpPAcAtIbRzRMrUTRN18BePUdbIBQiNr',
			)
		);

		register_setting(
			'live_gold_price_settings_group',
			'live_gold_price_refresh_interval',
			array(
				'type'              => 'integer',
				'sanitize_callback' => array( __CLASS__, 'sanitize_refresh_interval' ),
				'default'           => 60,
			)
		);

		register_setting(
			'live_gold_price_settings_group',
			'live_gold_price_global_profit',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( __CLASS__, 'sanitize_float_or_empty' ),
				'default'           => '7',
			)
		);

		register_setting(
			'live_gold_price_settings_group',
			'live_gold_price_global_tax',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( __CLASS__, 'sanitize_float_or_empty' ),
				'default'           => '2',
			)
		);

		register_setting(
			'live_gold_price_settings_group',
			'live_gold_price_dark_mode_fix',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( __CLASS__, 'sanitize_checkbox' ),
				'default'           => 'no',
			)
		);

		register_setting(
			'live_gold_price_settings_group',
			'live_gold_price_dropdown_dark_mode_fix',
			array(
				'type'              => 'string',
				'sanitize_callback' => array( __CLASS__, 'sanitize_checkbox' ),
				'default'           => 'no',
			)
		);

		add_settings_section(
			'live_gold_price_main_section',
			__( 'تنظیمات API و فرمول پایه', 'live-gold-price' ),
			'__return_null',
			'live-gold-price-settings'
		);

		add_settings_field(
			'live_gold_price_api_url',
			__( 'لینک وب‌سرویس (API Endpoint)', 'live-gold-price' ),
			array( __CLASS__, 'render_api_url_field' ),
			'live-gold-price-settings',
			'live_gold_price_main_section'
		);

		add_settings_field(
			'live_gold_price_api_key',
			__( 'کلید وب‌سرویس (API Key)', 'live-gold-price' ),
			array( __CLASS__, 'render_api_key_field' ),
			'live-gold-price-settings',
			'live_gold_price_main_section'
		);

		add_settings_field(
			'live_gold_price_refresh_interval',
			__( 'زمان رفرش قیمت‌ها (ثانیه)', 'live-gold-price' ),
			array( __CLASS__, 'render_refresh_interval_field' ),
			'live-gold-price-settings',
			'live_gold_price_main_section'
		);

		add_settings_field(
			'live_gold_price_global_profit',
			__( 'سود پیش‌فرض فروشنده (%)', 'live-gold-price' ),
			array( __CLASS__, 'render_global_profit_field' ),
			'live-gold-price-settings',
			'live_gold_price_main_section'
		);

		add_settings_field(
			'live_gold_price_global_tax',
			__( 'مالیات پیش‌فرض (%)', 'live-gold-price' ),
			array( __CLASS__, 'render_global_tax_field' ),
			'live-gold-price-settings',
			'live_gold_price_main_section'
		);

		add_settings_field(
			'live_gold_price_dark_mode_fix',
			__( 'اصلاح رنگ تیره قالب (Dark Mode)', 'live-gold-price' ),
			array( __CLASS__, 'render_dark_mode_fix_field' ),
			'live-gold-price-settings',
			'live_gold_price_main_section'
		);

		add_settings_field(
			'live_gold_price_dropdown_dark_mode_fix',
			__( 'اصلاح رنگ گزینه‌ها (لیست متغیرها)', 'live-gold-price' ),
			array( __CLASS__, 'render_dropdown_dark_mode_fix_field' ),
			'live-gold-price-settings',
			'live_gold_price_main_section'
		);
	}

	/**
	 * Sanitize refresh interval in seconds with strict minimum of 10 seconds.
	 *
	 * @param mixed $value Input value.
	 * @return int Sanitized interval (>= 10).
	 */
	public static function sanitize_refresh_interval( $value ) {
		$interval = absint( $value );
		if ( $interval < 10 ) {
			$interval = 10;
		}
		return $interval;
	}

	/**
	 * Sanitize float value or keep empty.
	 *
	 * @param mixed $value Input value.
	 * @return string Sanitized string.
	 */
	public static function sanitize_float_or_empty( $value ) {
		$trimmed = trim( (string) $value );
		if ( '' === $trimmed ) {
			return '';
		}
		return (string) floatval( $trimmed );
	}

	/**
	 * Sanitize checkbox value.
	 *
	 * @param mixed $value Input value.
	 * @return string 'yes' or 'no'.
	 */
	public static function sanitize_checkbox( $value ) {
		return 'yes' === $value ? 'yes' : 'no';
	}

	/**
	 * Render API URL field.
	 */
	public static function render_api_url_field() {
		$value = get_option( 'live_gold_price_api_url', '' );
		if ( '' === $value ) {
			$value = get_option( 'lgp_api_url', 'https://api.brsapi.ir/Market/Gold_Currency.php' );
		}
		?>
		<input type="text" name="live_gold_price_api_url" value="<?php echo esc_attr( $value ); ?>" class="regular-text" style="width: 100%; max-width: 600px;">
		<p class="description">
			<?php esc_html_e( 'آدرس دریافت قیمت لحظه‌ای طلا. برای دریافت کلید وب‌سرویس به وب‌سایت ارائه‌دهنده مراجعه کنید:', 'live-gold-price' ); ?>
			<a href="https://brsapi.ir/free-api-gold-currency-webservice/" target="_blank" rel="noopener noreferrer">https://brsapi.ir/free-api-gold-currency-webservice/</a>
		</p>
		<?php
	}

	/**
	 * Render API Key field.
	 */
	public static function render_api_key_field() {
		$value = get_option( 'live_gold_price_api_key', '' );
		if ( '' === $value ) {
			$value = get_option( 'lgp_api_key', '' );
		}
		if ( '' === $value ) {
			$value = 'BpPAcAtIbRzRMrUTRN18BePUdbIBQiNr';
		}
		?>
		<input type="password" name="live_gold_price_api_key" value="<?php echo esc_attr( $value ); ?>" class="regular-text" style="width: 100%; max-width: 600px;" autocomplete="off">
		<p class="description"><?php esc_html_e( 'کلید تایید هویت برای وب‌سرویس (به صورت پیش‌فرض کلید رایگان عمومی وب‌سرویس درج شده است)', 'live-gold-price' ); ?></p>
		<?php
	}

	/**
	 * Render Refresh Interval field with rate limit advisory and performance notices.
	 */
	public static function render_refresh_interval_field() {
		$value = Live_Gold_Price_API_Handler::get_refresh_interval();
		?>
		<div style="display: flex; align-items: center; gap: 8px;">
			<input type="number" name="live_gold_price_refresh_interval" value="<?php echo esc_attr( $value ); ?>" min="10" step="1" class="small-text" style="width: 90px; text-align: center; font-weight: bold; font-size: 14px;">
			<span style="font-weight: 500;"><?php esc_html_e( 'ثانیه', 'live-gold-price' ); ?></span>
		</div>
		<p class="description" style="margin-top: 6px;">
			<?php esc_html_e( 'تعیین بازه زمانی خودکار برای استعلام قیمت جدید از وب‌سرویس و به‌روزرسانی زنده قیمت در صفحه محصولات و فروشگاه (به طور پیش‌فرض ۶۰ ثانیه). حداقل مقدار مجاز ۱۰ ثانیه است.', 'live-gold-price' ); ?>
		</p>
		<div style="margin-top: 10px; padding: 12px 16px; background: #fff8e5; border-right: 4px solid #ffb900; border-radius: 4px; max-width: 680px; font-size: 13px; line-height: 1.7; color: #444;">
			<strong style="color: #b35f00; display: flex; align-items: center; gap: 6px; margin-bottom: 6px; font-size: 13.5px;">
				⚠️ <?php esc_html_e( 'هشدار مهم در خصوص سقف مصرف و لیمیت وب‌سرویس (API Rate Limit):', 'live-gold-price' ); ?>
			</strong>
			<p style="margin: 0 0 6px 0;">
				<?php esc_html_e( 'لطفاً این رقم را متناسب با سقف مجاز اشتراک وب‌سرویس خود تنظیم فرمایید. برای نمونه در وب‌سرویس brsapi، اشتراک‌های پولی دارای سقف مصرف روزانه مشخص (مثلاً حداکثر ۱۵,۰۰۰ درخواست در روز) هستند. تنظیم این فیلد روی اعداد بسیار پایین (مثلاً ۱۰ یا ۱۵ ثانیه) در سایت‌های پرترافیک و پربازدید می‌تواند سهمیه روزانه شما را به سرعت به اتمام رسانده و وب‌سرویس موقتاً قطع شود.', 'live-gold-price' ); ?>
			</p>
			<p style="margin: 0; color: #666; font-size: 12px;">
				<?php esc_html_e( '💡 سیستم کش هوشمند و پایداری سرعت سایت: حتی با تعیین بازه‌های کوتاه، معماری کش افزونه طوری طراحی شده است که فرآیند استعلام در پس‌زمینه انجام شده و به هیچ عنوان سرعت سایت کاهش پیدا نمی‌کند و قیمت‌ها روی حالت لودینگ معطل نمی‌مانند.', 'live-gold-price' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render Global Profit field.
	 */
	public static function render_global_profit_field() {
		$value = get_option( 'live_gold_price_global_profit', '' );
		if ( '' === $value ) {
			$value = get_option( 'lgp_global_profit', '7' );
		}
		?>
		<input type="number" step="any" name="live_gold_price_global_profit" value="<?php echo esc_attr( $value ); ?>" class="small-text">
		<p class="description"><?php esc_html_e( 'درصد سود پیش‌فرض برای محصولاتی که سود اختصاصی ندارند.', 'live-gold-price' ); ?></p>
		<?php
	}

	/**
	 * Render Global Tax field.
	 */
	public static function render_global_tax_field() {
		$value = get_option( 'live_gold_price_global_tax', '' );
		if ( '' === $value ) {
			$value = get_option( 'lgp_global_tax', '2' );
		}
		?>
		<input type="number" step="any" name="live_gold_price_global_tax" value="<?php echo esc_attr( $value ); ?>" class="small-text">
		<p class="description"><?php esc_html_e( 'درصد مالیات پیش‌فرض (ارزش افزوده).', 'live-gold-price' ); ?></p>
		<?php
	}

	/**
	 * Render Dark Mode Fix field.
	 */
	public static function render_dark_mode_fix_field() {
		$value = get_option( 'live_gold_price_dark_mode_fix', '' );
		if ( '' === $value ) {
			$value = get_option( 'lgp_dark_mode_fix', 'no' );
		}
		?>
		<label>
			<input type="checkbox" name="live_gold_price_dark_mode_fix" value="yes" <?php checked( $value, 'yes' ); ?>>
			<?php esc_html_e( 'تبدیل خودکار متن‌ها و عناوین با رنگ تیره (#222222) به رنگ سفید (#fff)', 'live-gold-price' ); ?>
		</label>
		<p class="description"><?php esc_html_e( 'اگر از قالب تیره استفاده می‌کنید و برخی متون سایت به دلیل رنگ تیره ناخوانا هستند، این گزینه را فعال کنید.', 'live-gold-price' ); ?></p>
		<?php
	}

	/**
	 * Render Dropdown Dark Mode Fix field.
	 */
	public static function render_dropdown_dark_mode_fix_field() {
		$value = get_option( 'live_gold_price_dropdown_dark_mode_fix', '' );
		if ( '' === $value ) {
			$value = get_option( 'lgp_dropdown_dark_mode_fix', 'no' );
		}
		?>
		<label>
			<input type="checkbox" name="live_gold_price_dropdown_dark_mode_fix" value="yes" <?php checked( $value, 'yes' ); ?>>
			<?php esc_html_e( 'اصلاح رنگ لیست‌های بازشونده (Select/Dropdown) در قالب تیره', 'live-gold-price' ); ?>
		</label>
		<p class="description"><?php esc_html_e( 'در صورتی که در صفحه محصول، متن گزینه‌های منوی بازشونده (مثل اندازه انگشتر) قابل خواندن نیستند، این گزینه را فعال کنید تا پس‌زمینه تیره و متن سفید شود.', 'live-gold-price' ); ?></p>
		<?php
	}

	/**
	 * Render settings page.
	 */
	public static function settings_page_html() {
		if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized access.', 'live-gold-price' ), 403 );
		}

		$fetch_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=live_gold_price_manual_fetch' ),
			'live_gold_price_manual_fetch_action',
			'live_gold_price_nonce'
		);
		?>
		<div class="wrap">
			<div style="display: flex; justify-content: space-between; align-items: center; margin: 15px 0 20px 0; flex-wrap: wrap; gap: 10px;">
				<h1 style="margin: 0;"><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<div style="display: inline-flex; align-items: center; gap: 8px; flex-wrap: wrap;">
					<a href="https://github.com/amirmarandidev/woocommerce-live-gold-price" target="_blank" rel="noopener noreferrer" class="button button-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 500; padding: 4px 12px; height: auto;">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="vertical-align: middle;"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/></svg>
						<?php esc_html_e( 'مشاهده در گیت‌هاب', 'live-gold-price' ); ?>
					</a>
					<button type="button" id="live-gold-price-donate-trigger" class="button button-primary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 500; padding: 4px 14px; height: auto; background: #e0245e; border-color: #c81e51;">
						<span class="dashicons dashicons-heart" style="vertical-align: middle; font-size: 18px; width: 18px; height: 18px; color: #fff;"></span>
						<?php esc_html_e( 'حمایت از پروژه', 'live-gold-price' ); ?>
					</button>
					<a href="https://t.me/amirmarandidev" target="_blank" rel="noopener noreferrer" class="button button-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 500; padding: 4px 12px; height: auto;">
						<span class="dashicons dashicons-format-chat" style="vertical-align: middle; font-size: 18px; width: 18px; height: 18px;"></span>
						<?php esc_html_e( 'ارتباط با توسعه‌دهنده', 'live-gold-price' ); ?>
					</a>
				</div>
			</div>
			<?php if ( isset( $_GET['fetch_error'] ) ) : ?>
				<div class="notice notice-error is-dismissible">
					<p><?php esc_html_e( 'خطا در ارتباط با وب‌سرویس یا دریافت قیمت‌ها. لطفاً اتصال اینترنت سرور یا کلید وب‌سرویس را بررسی نمایید.', 'live-gold-price' ); ?></p>
				</div>
			<?php elseif ( isset( $_GET['updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'قیمت‌های لحظه‌ای با موفقیت از وب‌سرویس دریافت و به‌روزرسانی شدند.', 'live-gold-price' ); ?></p>
				</div>
			<?php endif; ?>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'live_gold_price_settings_group' );
				do_settings_sections( 'live-gold-price-settings' );
				submit_button( esc_html__( 'ذخیره تنظیمات', 'live-gold-price' ) );
				?>
			</form>

			<hr>

			<div style="margin-top: 20px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3><?php esc_html_e( 'وضعیت قیمت‌های زنده', 'live-gold-price' ); ?></h3>
				<?php
				$prices = get_transient( 'live_gold_price_gold_prices' );
				if ( ! $prices ) {
					$prices = get_transient( 'lgp_gold_prices' );
				}
				if ( ! $prices ) {
					$prices = get_option( 'live_gold_price_gold_prices_backup' );
				}
				if ( ! $prices ) {
					$prices = get_option( 'lgp_gold_prices_backup' );
				}
				?>
				<?php if ( ! empty( $prices ) && is_array( $prices ) ) : ?>
					<p style="color: #46b450; font-weight: bold;"><?php esc_html_e( 'قیمت‌های زنده با موفقیت دریافت شده و در حافظه موقت (کش) موجود هستند.', 'live-gold-price' ); ?></p>
					<ul style="background: #f9f9f9; padding: 15px; border: 1px solid #eee;">
						<?php foreach ( array_slice( $prices, 0, 5 ) as $name => $price ) : ?>
							<li><strong><?php echo esc_html( $name ); ?>:</strong> <?php echo esc_html( number_format( (float) $price ) ); ?> <?php esc_html_e( 'تومان', 'live-gold-price' ); ?></li>
						<?php endforeach; ?>
						<li>...</li>
					</ul>
				<?php else : ?>
					<p style="color: #dc3232; font-weight: bold;"><?php esc_html_e( 'در حال حاضر قیمت زنده‌ای در کش موجود نیست (ممکن است منقضی شده باشد یا API پاسخ نداده باشد).', 'live-gold-price' ); ?></p>
				<?php endif; ?>
				<a href="<?php echo esc_url( $fetch_url ); ?>" class="button button-secondary"><?php esc_html_e( 'دریافت دستی قیمت‌ها همین الان', 'live-gold-price' ); ?></a>
			</div>

			<div style="margin-top: 20px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h3><?php esc_html_e( 'اطلاعات وضعیت سیستم و عیب‌یابی (System Diagnostic Report)', 'live-gold-price' ); ?></h3>
				<p class="description"><?php esc_html_e( 'در صورت نیاز به پشتیبانی فنی یا گزارش اشکال، متن زیر را کپی کرده و ارسال نمایید:', 'live-gold-price' ); ?></p>
				<textarea id="live-gold-price-diagnostics" readonly="readonly" style="width: 100%; height: 180px; font-family: monospace; font-size: 12px; background: #f6f7f7; direction: ltr; margin-top: 8px;"><?php echo esc_textarea( self::get_system_diagnostics() ); ?></textarea>
				<p style="margin-top: 10px; margin-bottom: 0;">
					<button type="button" class="button button-secondary" onclick="navigator.clipboard.writeText(document.getElementById('live-gold-price-diagnostics').value); alert('<?php echo esc_js( __( 'گزارش سیستم با موفقیت کپی شد.', 'live-gold-price' ) ); ?>');">
						📋 <?php esc_html_e( 'کپی گزارش در کلیپ‌بورد', 'live-gold-price' ); ?>
					</button>
				</p>
			</div>

			<div style="margin-top: 30px; text-align: center; color: #777;">
				<p><?php esc_html_e( 'توسعه‌داده‌شده توسط:', 'live-gold-price' ); ?> <a href="https://kourox.ir" target="_blank" rel="noopener noreferrer" style="text-decoration: none; font-weight: bold; color: #0073aa;">Amir Marandi</a></p>
			</div>

			<!-- Donation Modal -->
			<div id="live-gold-price-donate-modal" style="display: none; position: fixed; z-index: 999999; inset: 0; background: rgba(0, 0, 0, 0.65); align-items: center; justify-content: center; backdrop-filter: blur(2px);">
				<div style="background: #ffffff; border-radius: 12px; width: 90%; max-width: 500px; box-shadow: 0 10px 30px rgba(0,0,0,0.25); border: 1px solid #ccd0d4; overflow: hidden; direction: rtl; text-align: right;">
					<div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; background: #fbfbfb; border-bottom: 1px solid #eee;">
						<h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1d2327;">
							💖 <?php esc_html_e( 'حمایت از پروژه', 'live-gold-price' ); ?>
						</h3>
						<button type="button" id="live-gold-price-donate-close-x" style="background: none; border: none; font-size: 26px; line-height: 1; cursor: pointer; color: #646970; padding: 0;">&times;</button>
					</div>
					<div style="padding: 20px; font-size: 14px; line-height: 1.7; color: #3c434a;">
						<p style="margin-top: 0; font-weight: 500;">
							<?php esc_html_e( 'این ابزار کاملاً رایگان و متن‌بازه. اگه کارتون رو راه انداخت و براتون مفید بود، با زدن یه ⭐ روی گیت‌هاب یا حمایت رمزارزی می‌تونید به ادامه‌ی این مسیر انرژی بدید:', 'live-gold-price' ); ?>
						</p>

						<div style="background: #f9f9f9; border: 1px solid #e2e4e7; border-radius: 8px; padding: 12px 14px; margin-bottom: 10px;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
								<span style="font-weight: 600; color: #007cba;">🔹 USDT (BEP20):</span>
								<button type="button" class="button button-small lgp-copy-btn" data-clipboard="0xd593ae9D32bEA690EC62460C54BF3951aFFF7803"><?php esc_html_e( 'کپی', 'live-gold-price' ); ?></button>
							</div>
							<code style="display: block; direction: ltr; text-align: left; font-size: 12px; background: #fff; padding: 6px 8px; border: 1px solid #ddd; border-radius: 4px; word-break: break-all; color: #2c3338;">0xd593ae9D32bEA690EC62460C54BF3951aFFF7803</code>
						</div>

						<div style="background: #f9f9f9; border: 1px solid #e2e4e7; border-radius: 8px; padding: 12px 14px; margin-bottom: 10px;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
								<span style="font-weight: 600; color: #d63638;">🔸 USDT (TRC20):</span>
								<button type="button" class="button button-small lgp-copy-btn" data-clipboard="THaaHzoTwXfUfcrtYTDXRsMmk9qhnXa56M"><?php esc_html_e( 'کپی', 'live-gold-price' ); ?></button>
							</div>
							<code style="display: block; direction: ltr; text-align: left; font-size: 12px; background: #fff; padding: 6px 8px; border: 1px solid #ddd; border-radius: 4px; word-break: break-all; color: #2c3338;">THaaHzoTwXfUfcrtYTDXRsMmk9qhnXa56M</code>
						</div>

						<div style="background: #f9f9f9; border: 1px solid #e2e4e7; border-radius: 8px; padding: 12px 14px; margin-bottom: 15px;">
							<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
								<span style="font-weight: 600; color: #135e96;">💳 <?php esc_html_e( 'شماره کارت:', 'live-gold-price' ); ?></span>
								<button type="button" class="button button-small lgp-copy-btn" data-clipboard="5022291619360157"><?php esc_html_e( 'کپی', 'live-gold-price' ); ?></button>
							</div>
							<code style="display: block; direction: ltr; text-align: left; font-size: 15px; font-weight: bold; background: #fff; padding: 6px 8px; border: 1px solid #ddd; border-radius: 4px; letter-spacing: 2px; color: #1d2327;">5022291619360157</code>
						</div>

						<div style="text-align: left;">
							<button type="button" id="live-gold-price-donate-close-btn" class="button button-secondary"><?php esc_html_e( 'بستن', 'live-gold-price' ); ?></button>
						</div>
					</div>
				</div>
			</div>

			<script type="text/javascript">
			document.addEventListener('DOMContentLoaded', function() {
				var modal = document.getElementById('live-gold-price-donate-modal');
				var openBtn = document.getElementById('live-gold-price-donate-trigger');
				var closeX = document.getElementById('live-gold-price-donate-close-x');
				var closeBtn = document.getElementById('live-gold-price-donate-close-btn');

				if (openBtn && modal) {
					openBtn.addEventListener('click', function() {
						modal.style.display = 'flex';
					});
				}

				var closeModal = function() {
					if (modal) modal.style.display = 'none';
				};

				if (closeX) closeX.addEventListener('click', closeModal);
				if (closeBtn) closeBtn.addEventListener('click', closeModal);

				if (modal) {
					modal.addEventListener('click', function(e) {
						if (e.target === modal) closeModal();
					});
				}

				document.addEventListener('keydown', function(e) {
					if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
						closeModal();
					}
				});

				var copyBtns = document.querySelectorAll('.lgp-copy-btn');
				copyBtns.forEach(function(btn) {
					btn.addEventListener('click', function() {
						var text = this.getAttribute('data-clipboard');
						if (text && navigator.clipboard) {
							var self = this;
							navigator.clipboard.writeText(text).then(function() {
								var orig = self.textContent;
								self.textContent = '✓ کپی شد!';
								self.style.color = '#46b450';
								setTimeout(function() {
									self.textContent = orig;
									self.style.color = '';
								}, 2000);
							});
						}
					});
				});
			});
			</script>
		</div>
		<?php
	}

	/**
	 * Generate sanitized system diagnostic report for technical support.
	 *
	 * @return string Plain-text system status report.
	 */
	public static function get_system_diagnostics() {
		$hpos_status = 'No';
		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$hpos_status = 'Yes (Enabled)';
		}

		$curl_info = 'Not Available';
		if ( function_exists( 'curl_version' ) ) {
			$curl      = curl_version();
			$curl_info = $curl['version'] . ' (SSL: ' . $curl['ssl_version'] . ')';
		}

		$api_key    = get_option( 'live_gold_price_api_key', '' );
		$masked_key = empty( $api_key ) ? 'Not Configured' : substr( $api_key, 0, 4 ) . '****' . substr( $api_key, -3 );

		$transient_status = 'Empty / Expired';
		$cached_prices    = get_transient( 'live_gold_price_gold_prices' );
		if ( is_array( $cached_prices ) && ! empty( $cached_prices ) ) {
			$transient_status = 'Active (' . count( $cached_prices ) . ' assets cached)';
		}

		$backup_status = 'Empty';
		$backup_prices = get_option( 'live_gold_price_gold_prices_backup', array() );
		if ( is_array( $backup_prices ) && ! empty( $backup_prices ) ) {
			$backup_status = 'Available (' . count( $backup_prices ) . ' assets saved)';
		}

		$last_fetch           = (int) get_option( 'live_gold_price_last_api_fetch', 0 );
		$last_fetch_formatted = $last_fetch ? gmdate( 'Y-m-d H:i:s \U\T\C', $last_fetch ) : 'Never';

		$cron_status    = ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) ? 'Disabled via constant' : 'Enabled';
		$cron_scheduled = wp_next_scheduled( 'live_gold_price_fetch_prices_cron' );
		$cron_next      = $cron_scheduled ? gmdate( 'Y-m-d H:i:s \U\T\C', $cron_scheduled ) : 'Not Scheduled';

		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : 'Unknown';

		$theme_info = 'Unknown';
		if ( function_exists( 'wp_get_theme' ) ) {
			$theme      = wp_get_theme();
			$theme_info = $theme->get( 'Name' ) . ' (' . $theme->get( 'Version' ) . ')';
		}

		$report = array(
			'=== Live Gold Price Diagnostic Report ===',
			'Generated At:           ' . gmdate( 'Y-m-d H:i:s \U\T\C' ),
			'Plugin Version:         ' . LIVE_GOLD_PRICE_VERSION,
			'WordPress Version:      ' . get_bloginfo( 'version' ),
			'Site Language:          ' . get_locale(),
			'WooCommerce Version:    ' . ( defined( 'WC_VERSION' ) ? WC_VERSION : 'Inactive' ),
			'WooCommerce HPOS:       ' . $hpos_status,
			'PHP Version:            ' . PHP_VERSION,
			'Web Server:             ' . $server_software,
			'PHP Memory Limit:       ' . ini_get( 'memory_limit' ),
			'PHP Max Execution Time: ' . ini_get( 'max_execution_time' ) . 's',
			'cURL Version:           ' . $curl_info,
			'WP-Cron Status:         ' . $cron_status,
			'Next Cron Run:          ' . $cron_next,
			'API Endpoint URL:       ' . get_option( 'live_gold_price_api_url', 'Default' ),
			'API Key Status:         ' . $masked_key,
			'Price Refresh Interval: ' . Live_Gold_Price_API_Handler::get_refresh_interval() . ' seconds',
			'Transient Cache:        ' . $transient_status,
			'Persistent Backup:      ' . $backup_status,
			'Last Successful Fetch:  ' . $last_fetch_formatted,
			'Total Successful Syncs: ' . (int) get_option( 'live_gold_price_successful_fetches_count', 0 ),
			'Active Theme:           ' . $theme_info,
			'=== End Diagnostic Report ===',
		);

		return implode( "\n", $report );
	}
}

// Backward compatibility alias.
if ( ! class_exists( 'LGP_Admin_Settings', false ) ) {
	class_alias( 'Live_Gold_Price_Admin_Settings', 'LGP_Admin_Settings' );
}
