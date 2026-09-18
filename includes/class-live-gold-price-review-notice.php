<?php
/**
 * Live Gold Price Review Notice Handler
 *
 * An ethical, non-intrusive review request notice for administrators
 * triggered only after verified successful usage.
 *
 * @package Persian_Live_Gold_Price_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Live_Gold_Price_Review_Notice {

	/**
	 * Initialize review notice hooks.
	 */
	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'render_notice' ) );
		add_action( 'wp_ajax_live_gold_price_dismiss_review', array( __CLASS__, 'ajax_dismiss' ) );
		add_action( 'admin_footer', array( __CLASS__, 'render_scripts' ) );
	}

	/**
	 * Render notice if all qualification criteria are satisfied.
	 */
	public static function render_notice() {
		// Strict capability requirement: Only administrators can review plugins.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$user_id = get_current_user_id();

		// Check permanent dismissal state.
		if ( 'yes' === get_user_meta( $user_id, 'live_gold_price_review_dismissed', true ) ) {
			return;
		}

		// Check snooze state.
		$snooze_until = (int) get_user_meta( $user_id, 'live_gold_price_review_snooze_until', true );
		if ( $snooze_until && time() < $snooze_until ) {
			return;
		}

		// 1. Check activation age (minimum 7 full days).
		$activated_time = (int) get_option( 'live_gold_price_activated_time', 0 );
		if ( ! $activated_time ) {
			// Initialize if missing.
			update_option( 'live_gold_price_activated_time', time() );
			return;
		}

		if ( ( time() - $activated_time ) < ( 7 * DAY_IN_SECONDS ) ) {
			return;
		}

		// 2. Check successful API update count (minimum 50 successful updates).
		$success_count = (int) get_option( 'live_gold_price_successful_fetches_count', 0 );
		if ( $success_count < 50 ) {
			return;
		}

		$review_url = 'https://wordpress.org/support/plugin/live-gold-price/reviews/#new-post';
		?>
		<div id="live-gold-price-review-notice" class="notice notice-info is-dismissible" style="padding: 12px 16px; border-right-width: 4px; border-right-color: #f0b840;">
			<div style="display: flex; align-items: flex-start; justify-content: space-between;">
				<div>
					<h3 style="margin: 0 0 6px 0; font-size: 14px; font-weight: 600; color: #1d2327;">
						<?php esc_html_e( 'از کار با افزونه قیمت لحظه‌ای طلا رضایت دارید؟', 'live-gold-price' ); ?>
					</h3>
					<p style="margin: 0 0 10px 0; color: #50575e; font-size: 13px; line-height: 1.6;">
						<?php esc_html_e( 'افزونه قیمت لحظه‌ای طلا اکنون بیش از ۵۰ بار نرخ‌های طلا و سکه فروشگاه شما را با موفقیت به‌روزرسانی کرده است. با ثبت یک امتیاز ۵ ستاره و نظر ارزشمند خود در مخزن وردپرس، به توسعه و تداوم این پروژه رایگان کمک کنید.', 'live-gold-price' ); ?>
					</p>
					<div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
						<a href="<?php echo esc_url( $review_url ); ?>" target="_blank" rel="noopener noreferrer" class="button button-primary live-gold-price-review-btn" data-action-type="permanent">
							⭐ <?php esc_html_e( 'ثبت نظر در مخزن وردپرس', 'live-gold-price' ); ?>
						</a>
						<button type="button" class="button button-secondary live-gold-price-review-btn" data-action-type="snooze">
							<?php esc_html_e( 'بعداً یادآوری کن (۱۴ روز)', 'live-gold-price' ); ?>
						</button>
						<button type="button" class="button-link live-gold-price-review-btn" data-action-type="permanent" style="color: #646970; text-decoration: none; padding: 4px 8px;">
							<?php esc_html_e( 'قبلاً نظر داده‌ام / نمایش نده', 'live-gold-price' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * AJAX handler for dismissing or snoozing the review notice.
	 */
	public static function ajax_dismiss() {
		check_ajax_referer( 'live_gold_price_review_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
		}

		$user_id     = get_current_user_id();
		$action_type = isset( $_POST['action_type'] ) ? sanitize_key( wp_unslash( $_POST['action_type'] ) ) : 'permanent';

		if ( 'snooze' === $action_type ) {
			// Snooze for 14 days.
			update_user_meta( $user_id, 'live_gold_price_review_snooze_until', time() + ( 14 * DAY_IN_SECONDS ) );
		} else {
			// Dismiss permanently.
			update_user_meta( $user_id, 'live_gold_price_review_dismissed', 'yes' );
		}

		wp_send_json_success();
	}

	/**
	 * Output minimal inline JS to handle dismissal asynchronously.
	 */
	public static function render_scripts() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<script type="text/javascript">
		(function($) {
			$(document).on('click', '.live-gold-price-review-btn, #live-gold-price-review-notice .notice-dismiss', function(e) {
				var $notice = $('#live-gold-price-review-notice');
				var actionType = $(this).data('action-type') || 'permanent';

				if (!$(this).hasClass('notice-dismiss') && $(this).attr('target') !== '_blank') {
					e.preventDefault();
				}

				$.post(ajaxurl, {
					action: 'live_gold_price_dismiss_review',
					action_type: actionType,
					nonce: '<?php echo esc_js( wp_create_nonce( 'live_gold_price_review_nonce' ) ); ?>'
				});

				$notice.fadeTo(100, 0, function() {
					$notice.slideUp(150, function() {
						$notice.remove();
					});
				});
			});
		})(jQuery);
		</script>
		<?php
	}
}

// Backward compatibility alias.
if ( ! class_exists( 'Live_Gold_Price_Review_Notice', false ) ) {
	class_alias( 'Live_Gold_Price_Review_Notice', 'LGP_Review_Notice' );
}
