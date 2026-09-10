<?php
/**
 * Plugin Name:       CleanVerify for WooCommerce
 * Description:       All-in-one customer verification, anti-fraud protection, and inactive customer cleanup for WooCommerce stores.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * Author:            FahimMurshed
 * Author URI:        https://fahimm.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cleanverify-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'CLEANVERIFY_VERSION', '1.0.0' );

/**
 * Show an admin notice instead of fataling if WooCommerce isn't active.
 */
function cleanverify_woocommerce_missing_notice() {
	echo '<div class="notice notice-error"><p>' .
		esc_html__( 'CleanVerify for WooCommerce requires WooCommerce to be installed and active.', 'cleanverify-for-woocommerce' ) .
		'</p></div>';
}

/**
 * Completely disable WordPress Twemoji remote script and CDN requests.
 */
function cleanverify_disable_emojis() {
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'cleanverify_disable_emojis' );
add_action( 'admin_init', 'cleanverify_disable_emojis' );

/**
 * Boot the plugin once other plugins have loaded, so we can safely check for WooCommerce.
 */
function cleanverify_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'cleanverify_woocommerce_missing_notice' );
		return;
	}

	new CleanVerify_For_WooCommerce();
}
add_action( 'plugins_loaded', 'cleanverify_init' );

class CleanVerify_For_WooCommerce {

	const NONCE_ACTION        = 'cleanverify_delete_customers';
	const EXPORT_NONCE_ACTION = 'cleanverify_export_csv';
	const MENU_SLUG           = 'cleanverify';

	// Never consider or delete users who also hold any of these roles,
	// even if they happen to carry the "customer" role too.
	const EXCLUDED_ROLES = array( 'administrator', 'editor', 'shop_manager' );

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_page' ) );
		add_action( 'admin_head', array( $this, 'admin_menu_custom_styles' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_action_links' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Inject minimal CSS in admin head to style the PRO badge in the WordPress admin sidebar menu.
	 */
	public function admin_menu_custom_styles() {
		if ( defined( 'CLEANVERIFY_PRO_VERSION' ) ) {
			echo '<style id="cleanverify-menu-badge-css">
				#adminmenu .fahim-menu-pro-badge,
				#adminmenu .fahim-title-pro-badge {
					background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%) !important;
					color: #ffffff !important;
					font-size: 8px !important;
					font-weight: 800 !important;
					letter-spacing: 0.05em !important;
					padding: 1.5px 4.5px !important;
					border-radius: 3px !important;
					text-transform: uppercase !important;
					line-height: 1.1 !important;
					display: inline-block !important;
					margin-left: 4px !important;
					vertical-align: 1px !important;
					box-shadow: 0 1px 3px rgba(239, 68, 68, 0.4) !important;
				}
			</style>';
		}
	}

	/**
	 * Enqueue admin assets (styles and scripts) for plugin settings pages.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Page query parameter is read-only for asset enqueue screen detection.
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ( isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '' );
		$is_cv_page = ( self::MENU_SLUG === $page )
			|| ( 'toplevel_page_' . self::MENU_SLUG === $hook )
			|| ( 'users_page_' . self::MENU_SLUG === $hook )
			|| ( false !== strpos( $hook, self::MENU_SLUG ) )
			|| ( isset( $GLOBALS['hook_suffix'] ) && false !== strpos( $GLOBALS['hook_suffix'], self::MENU_SLUG ) );

		if ( ! $is_cv_page ) {
			return;
		}

		// Prevent WordPress emoji scripts from making remote CDN requests on plugin pages.
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		add_filter( 'emoji_svg_url', '__return_false' );

		$css_path = plugin_dir_path( __FILE__ ) . 'assets/css/admin.css';
		$js_path  = plugin_dir_path( __FILE__ ) . 'assets/js/admin.js';

		$css_ver = file_exists( $css_path ) ? filemtime( $css_path ) : CLEANVERIFY_VERSION;
		$js_ver  = file_exists( $js_path ) ? filemtime( $js_path ) : CLEANVERIFY_VERSION;

		wp_enqueue_style(
			'cleanverify-admin-style',
			plugins_url( 'assets/css/admin.css', __FILE__ ),
			array(),
			$css_ver
		);

		wp_enqueue_script(
			'cleanverify-admin-script',
			plugins_url( 'assets/js/admin.js', __FILE__ ),
			array(),
			$js_ver,
			true
		);
	}

	/**
	 * Add shortcut settings link on Plugins management screen.
	 *
	 * @param array $links Existing plugin action links.
	 * @return array
	 */
	public function add_plugin_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG ) ),
			esc_html__( 'Settings', 'cleanverify-for-woocommerce' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

	public function add_admin_page() {
		$is_pro_active = defined( 'CLEANVERIFY_PRO_VERSION' );
		$page_title    = $is_pro_active ? __( 'CleanVerify Pro for WooCommerce', 'cleanverify-for-woocommerce' ) : __( 'CleanVerify for WooCommerce', 'cleanverify-for-woocommerce' );
		$menu_title    = $is_pro_active 
			? 'CleanVerify <span class="fahim-title-pro-badge fahim-menu-pro-badge">PRO</span>' 
			: __( 'CleanVerify', 'cleanverify-for-woocommerce' );

		$hook = add_menu_page(
			$page_title,
			$menu_title,
			'delete_users',
			self::MENU_SLUG,
			array( $this, 'render_admin_page' ),
			'dashicons-shield-alt',
			55.6 // Positioned immediately after "WooCommerce" (position 55.5)
		);

		// Runs before any HTML is output, so headers for the CSV download can be sent safely.
		add_action( 'load-' . $hook, array( $this, 'maybe_export_csv' ) );
	}

	/**
	 * Stream candidate list as a CSV download and exit, if requested.
	 * Supports exporting all filtered candidates or only selected candidates.
	 */
	public function maybe_export_csv() {
		if ( ! isset( $_POST['cv_action'] ) || 'export_csv' !== $_POST['cv_action'] ) {
			return;
		}

		if ( ! current_user_can( 'delete_users' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'cleanverify-for-woocommerce' ) );
		}

		// Verify nonce from either the export form or the table form.
		if ( isset( $_POST['cv_export_nonce'] ) ) {
			check_admin_referer( self::EXPORT_NONCE_ACTION, 'cv_export_nonce' );
		} elseif ( isset( $_POST['cv_nonce'] ) ) {
			check_admin_referer( self::NONCE_ACTION, 'cv_nonce' );
		} else {
			wp_die( esc_html__( 'Security check failed.', 'cleanverify-for-woocommerce' ) );
		}

		$min_age    = isset( $_POST['cv_min_age'] ) ? absint( $_POST['cv_min_age'] ) : 30;
		$candidates = $this->get_customers_without_orders( $min_age );

		// If user selected specific customer checkboxes, export only those.
		if ( ! empty( $_POST['cv_user_ids'] ) ) {
			$selected_ids = array_map( 'absint', (array) $_POST['cv_user_ids'] );
			$candidates   = array_filter(
				$candidates,
				function( $c ) use ( $selected_ids ) {
					return in_array( (int) $c->ID, $selected_ids, true );
				}
			);
		}

		if ( ob_get_level() ) {
			ob_end_clean();
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=cleanverify-customers-' . gmdate( 'Y-m-d' ) . '.csv' );

		$output = fopen( 'php://output', 'w' );

		// Send UTF-8 BOM for Microsoft Excel compatibility across Windows and Mac.
		fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		fputcsv( $output, array( 'Customer ID', 'Username', 'Display Name', 'Email', 'Registered Date' ), ',', '"', '\\' );

		foreach ( $candidates as $c ) {
			fputcsv(
				$output,
				array(
					$c->ID,
					isset( $c->user_login ) ? $c->user_login : '',
					$c->display_name,
					$c->user_email,
					$c->user_registered,
				),
				',',
				'"',
				'\\'
			);
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Direct output stream for CSV download.
		fclose( $output );
		exit;
	}

	/**
	 * Get all "customer" role users with zero orders (checked by user ID and billing email).
	 *
	 * @param int $min_account_age_days Skip accounts newer than this (safety buffer).
	 * @return array
	 */
	private function get_customers_without_orders( $min_account_age_days ) {
		$results = array();
		$cutoff  = time() - ( absint( $min_account_age_days ) * DAY_IN_SECONDS );

		$customers = get_users( array(
			'role'         => 'customer',
			'role__not_in' => self::EXCLUDED_ROLES,
			'fields'       => array( 'ID', 'user_login', 'user_email', 'user_registered', 'display_name' ),
		) );

		foreach ( $customers as $customer ) {
			$registered_time = strtotime( $customer->user_registered );

			// Safety: don't touch accounts younger than the cutoff.
			if ( $registered_time > $cutoff ) {
				continue;
			}

			$order_ids = wc_get_orders( array(
				'customer' => $customer->ID,
				'limit'    => 1,
				'return'   => 'ids',
			) );

			// Also check by billing email in case orders were placed as a guest
			// before or after registering with the same email.
			if ( empty( $order_ids ) ) {
				$order_ids = wc_get_orders( array(
					'billing_email' => $customer->user_email,
					'limit'         => 1,
					'return'        => 'ids',
				) );
			}

			if ( empty( $order_ids ) ) {
				$results[] = $customer;
			}
		}

		return $results;
	}

	/**
	 * Render the main plugin admin interface with tab navigation and modern unified theme.
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'delete_users' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'cleanverify-for-woocommerce' ) );
		}

		$is_pro_active = defined( 'CLEANVERIFY_PRO_VERSION' );
		$valid_tabs    = $is_pro_active ? array( 'cleanup', 'settings', 'trash', 'logs', 'about' ) : array( 'cleanup', 'pro', 'about' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab parameter is used for view switching only.
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'cleanup';
		if ( ! in_array( $active_tab, $valid_tabs, true ) ) {
			$active_tab = 'cleanup';
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Filter parameter is used for read-only candidate display.
		$min_age          = isset( $_REQUEST['cv_min_age'] ) ? absint( wp_unslash( $_REQUEST['cv_min_age'] ) ) : 30;
		$candidates_count = count( $this->get_customers_without_orders( $min_age ) );
		?>
		<div class="fahim-page-wrap wp-exclude-emoji" id="fahim-page-wrap">
			<!-- Header Bar -->
			<div class="fahim-header-bar">
				<div class="fahim-header-left">
					<?php if ( $is_pro_active ) : ?>
						<span class="fahim-header-badge fahim-tab-badge-pro"><svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor" style="vertical-align:-1px; margin-right:3px;"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg><?php esc_html_e( 'CleanVerify Pro Active', 'cleanverify-for-woocommerce' ); ?></span>
					<?php else : ?>
						<span class="fahim-header-badge"><?php esc_html_e( 'CleanVerify Suite', 'cleanverify-for-woocommerce' ); ?></span>
					<?php endif; ?>
					<h1 class="fahim-header-title">
						Clean<span class="highlight">Verify</span> <?php esc_html_e( 'for WooCommerce', 'cleanverify-for-woocommerce' ); ?>
						<?php if ( $is_pro_active ) : ?>
							<span class="fahim-title-pro-badge">PRO</span>
						<?php endif; ?>
					</h1>
					<p class="fahim-header-subtitle">
						<?php
						if ( $is_pro_active ) {
							esc_html_e( 'Enterprise customer verification, anti-fraud protection, automated scheduling, and inactive customer cleanup.', 'cleanverify-for-woocommerce' );
						} else {
							esc_html_e( 'Safely verify accounts, block fake registrations, and clean customers with zero orders.', 'cleanverify-for-woocommerce' );
						}
						?>
					</p>
				</div>
				<div class="fahim-header-actions">
					<?php
					if ( $is_pro_active ) {
						/* translators: 1: Core version number, 2: Pro version number */
						$version_tooltip = sprintf( esc_html__( 'CleanVerify Core v%1$s | Pro v%2$s', 'cleanverify-for-woocommerce' ), CLEANVERIFY_VERSION, CLEANVERIFY_PRO_VERSION );
					} else {
						/* translators: %s: Core version number */
						$version_tooltip = sprintf( esc_html__( 'CleanVerify Core v%s', 'cleanverify-for-woocommerce' ), CLEANVERIFY_VERSION );
					}
					?>
					<div class="fahim-version-badge" title="<?php echo esc_attr( $version_tooltip ); ?>">
						<span class="fahim-version-dot"></span>
						<span class="fahim-version-label"><?php esc_html_e( 'v', 'cleanverify-for-woocommerce' ); ?><?php echo esc_html( CLEANVERIFY_VERSION ); ?></span>
						<?php if ( $is_pro_active ) : ?>
							<span class="fahim-version-pro-tag"><?php esc_html_e( 'PRO', 'cleanverify-for-woocommerce' ); ?></span>
						<?php endif; ?>
					</div>
					<button type="button" class="fahim-header-btn" data-cv-modal="cv-modal-roadmap" title="<?php esc_attr_e( 'View Product Roadmap', 'cleanverify-for-woocommerce' ); ?>">
						<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
						<span><?php esc_html_e( 'Roadmap', 'cleanverify-for-woocommerce' ); ?></span>
						<?php if ( $is_pro_active ) : ?>
							<span class="fahim-header-btn-badge"><?php esc_html_e( 'PRO + FREE', 'cleanverify-for-woocommerce' ); ?></span>
						<?php else : ?>
							<span class="fahim-header-btn-badge fahim-header-btn-badge-free"><?php esc_html_e( 'FREE', 'cleanverify-for-woocommerce' ); ?></span>
						<?php endif; ?>
					</button>
					<button type="button" class="fahim-header-btn" data-cv-modal="cv-modal-changelog" title="<?php esc_attr_e( 'View Version Changelog', 'cleanverify-for-woocommerce' ); ?>">
						<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
						<span><?php esc_html_e( 'Changelog', 'cleanverify-for-woocommerce' ); ?></span>
					</button>
				</div>
			</div>

			<!-- Navigation Tabs -->
			<div class="fahim-nav-tabs">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG . '&tab=cleanup' ) ); ?>" class="fahim-nav-tab <?php echo 'cleanup' === $active_tab ? 'is-active' : ''; ?>" data-tab="cleanup">
					<span><?php esc_html_e( 'Customer Cleanup', 'cleanverify-for-woocommerce' ); ?></span>
					<span class="fahim-tab-badge"><?php echo esc_html( $candidates_count ); ?></span>
				</a>
				<?php if ( $is_pro_active ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG . '&tab=settings' ) ); ?>" class="fahim-nav-tab <?php echo 'settings' === $active_tab ? 'is-active' : ''; ?>" data-tab="settings">
						<span><?php esc_html_e( 'Anti-Spam & OTP', 'cleanverify-for-woocommerce' ); ?></span>
						<span class="fahim-tab-badge fahim-tab-badge-pro"><?php esc_html_e( 'PRO', 'cleanverify-for-woocommerce' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG . '&tab=trash' ) ); ?>" class="fahim-nav-tab <?php echo 'trash' === $active_tab ? 'is-active' : ''; ?>" data-tab="trash">
						<span><?php esc_html_e( 'Trash Bin & Restore', 'cleanverify-for-woocommerce' ); ?></span>
						<span class="fahim-tab-badge fahim-tab-badge-pro"><?php esc_html_e( 'PRO', 'cleanverify-for-woocommerce' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG . '&tab=logs' ) ); ?>" class="fahim-nav-tab <?php echo 'logs' === $active_tab ? 'is-active' : ''; ?>" data-tab="logs">
						<span><?php esc_html_e( 'Audit Trail', 'cleanverify-for-woocommerce' ); ?></span>
						<span class="fahim-tab-badge fahim-tab-badge-pro"><?php esc_html_e( 'PRO', 'cleanverify-for-woocommerce' ); ?></span>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG . '&tab=pro' ) ); ?>" class="fahim-nav-tab <?php echo 'pro' === $active_tab ? 'is-active' : ''; ?>" data-tab="pro">
						<span><svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor" style="vertical-align:-1px; margin-right:4px;"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg><?php esc_html_e( 'Free vs Pro', 'cleanverify-for-woocommerce' ); ?></span>
						<span class="fahim-tab-badge fahim-tab-badge-pro"><?php esc_html_e( 'PRO', 'cleanverify-for-woocommerce' ); ?></span>
					</a>
				<?php endif; ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG . '&tab=about' ) ); ?>" class="fahim-nav-tab <?php echo 'about' === $active_tab ? 'is-active' : ''; ?>" data-tab="about">
					<span><?php esc_html_e( 'About Developer', 'cleanverify-for-woocommerce' ); ?></span>
				</a>
			</div>

			<!-- Tab Content Body Panels -->
			<div class="fahim-tab-panels">
				<div id="fahim-tab-cleanup" class="fahim-tab-pane <?php echo 'cleanup' === $active_tab ? 'is-active' : ''; ?>">
					<?php $this->render_cleanup_tab( $min_age ); ?>
				</div>

				<?php if ( $is_pro_active ) : ?>
					<div id="fahim-tab-settings" class="fahim-tab-pane <?php echo 'settings' === $active_tab ? 'is-active' : ''; ?>">
						<?php do_action( 'cleanverify_render_pro_tab', 'settings' ); ?>
					</div>
					<div id="fahim-tab-trash" class="fahim-tab-pane <?php echo 'trash' === $active_tab ? 'is-active' : ''; ?>">
						<?php do_action( 'cleanverify_render_pro_tab', 'trash' ); ?>
					</div>
					<div id="fahim-tab-logs" class="fahim-tab-pane <?php echo 'logs' === $active_tab ? 'is-active' : ''; ?>">
						<?php do_action( 'cleanverify_render_pro_tab', 'logs' ); ?>
					</div>
				<?php else : ?>
					<div id="fahim-tab-pro" class="fahim-tab-pane <?php echo 'pro' === $active_tab ? 'is-active' : ''; ?>">
						<?php $this->render_free_vs_pro_tab(); ?>
					</div>
				<?php endif; ?>

				<div id="fahim-tab-about" class="fahim-tab-pane <?php echo 'about' === $active_tab ? 'is-active' : ''; ?>">
					<?php $this->render_about_developer_tab(); ?>
				</div>
			</div>

			<!-- Roadmap Modal -->
			<div class="fahim-modal-backdrop" id="cv-modal-roadmap" role="dialog" aria-modal="true" aria-hidden="true">
				<div class="fahim-modal-dialog">
					<div class="fahim-modal-header">
						<div class="fahim-modal-header-left">
							<span class="fahim-modal-icon">🗺️</span>
							<div>
								<h3 class="fahim-modal-title"><?php esc_html_e( 'CleanVerify Product Roadmap', 'cleanverify-for-woocommerce' ); ?></h3>
								<?php if ( $is_pro_active ) : ?>
									<span class="fahim-modal-subtitle"><?php esc_html_e( 'Upcoming Pro integrations and Core Free milestone releases', 'cleanverify-for-woocommerce' ); ?></span>
								<?php else : ?>
									<span class="fahim-modal-subtitle"><?php esc_html_e( 'Upcoming open-source enhancements & core milestone releases', 'cleanverify-for-woocommerce' ); ?></span>
								<?php endif; ?>
							</div>
						</div>
						<button type="button" class="fahim-modal-close" data-cv-modal-close aria-label="<?php esc_attr_e( 'Close modal', 'cleanverify-for-woocommerce' ); ?>">&times;</button>
					</div>
					<div class="fahim-modal-body">
						<?php if ( $is_pro_active ) : ?>
							<!-- ================= CleanVerify Pro Milestones ================= -->
							<div class="fahim-roadmap-section-title">
								<span class="fahim-section-tag fahim-section-tag-pro"><?php esc_html_e( 'PRO EDITION', 'cleanverify-for-woocommerce' ); ?></span>
								<span><?php esc_html_e( 'CleanVerify Pro Milestones', 'cleanverify-for-woocommerce' ); ?></span>
							</div>

							<div class="fahim-roadmap-milestone">
								<div class="fahim-roadmap-milestone-header">
									<span class="fahim-status-badge is-progress"><?php esc_html_e( 'In Progress', 'cleanverify-for-woocommerce' ); ?></span>
									<h4 class="fahim-roadmap-milestone-title"><?php esc_html_e( 'v1.1.0 - Multi-Channel Instant Verification', 'cleanverify-for-woocommerce' ); ?></h4>
								</div>
								<div class="fahim-roadmap-items">
									<div class="fahim-roadmap-item">
										<div class="fahim-roadmap-item-icon">💬</div>
										<div class="fahim-roadmap-item-content">
											<div class="fahim-roadmap-item-title-row">
												<strong><?php esc_html_e( 'WhatsApp Business OTP Verification', 'cleanverify-for-woocommerce' ); ?></strong>
												<span class="fahim-tag-pill fahim-tag-pill-pro"><?php esc_html_e( 'PRO FEATURE', 'cleanverify-for-woocommerce' ); ?></span>
											</div>
											<p><?php esc_html_e( 'Deliver 6-digit verification codes directly to customer WhatsApp numbers via WhatsApp Cloud API & Twilio with 1-click verification links.', 'cleanverify-for-woocommerce' ); ?></p>
										</div>
									</div>
									<div class="fahim-roadmap-item">
										<div class="fahim-roadmap-item-icon">✈️</div>
										<div class="fahim-roadmap-item-content">
											<div class="fahim-roadmap-item-title-row">
												<strong><?php esc_html_e( 'Telegram Bot Instant Verification', 'cleanverify-for-woocommerce' ); ?></strong>
												<span class="fahim-tag-pill fahim-tag-pill-pro"><?php esc_html_e( 'PRO FEATURE', 'cleanverify-for-woocommerce' ); ?></span>
											</div>
											<p><?php esc_html_e( 'Instant OTP codes and 1-tap deep-link authentication via dedicated Telegram Bot with zero SMS costs.', 'cleanverify-for-woocommerce' ); ?></p>
										</div>
									</div>
									<div class="fahim-roadmap-item">
										<div class="fahim-roadmap-item-icon">📲</div>
										<div class="fahim-roadmap-item-content">
											<div class="fahim-roadmap-item-title-row">
												<strong><?php esc_html_e( 'SMS Gateway Failover', 'cleanverify-for-woocommerce' ); ?></strong>
												<span class="fahim-tag-pill fahim-tag-pill-pro"><?php esc_html_e( 'PRO FEATURE', 'cleanverify-for-woocommerce' ); ?></span>
											</div>
											<p><?php esc_html_e( 'Multi-carrier SMS routing (Twilio, Vonage, AWS SNS) with automatic failover if WhatsApp or Telegram is undelivered.', 'cleanverify-for-woocommerce' ); ?></p>
										</div>
									</div>
								</div>
							</div>

							<div class="fahim-roadmap-milestone">
								<div class="fahim-roadmap-milestone-header">
									<span class="fahim-status-badge is-planned"><?php esc_html_e( 'Planned', 'cleanverify-for-woocommerce' ); ?></span>
									<h4 class="fahim-roadmap-milestone-title"><?php esc_html_e( 'v1.2.0 - Advanced Anti-Fraud & Risk Scoring', 'cleanverify-for-woocommerce' ); ?></h4>
								</div>
								<div class="fahim-roadmap-items">
									<div class="fahim-roadmap-item">
										<div class="fahim-roadmap-item-icon">🌐</div>
										<div class="fahim-roadmap-item-content">
											<div class="fahim-roadmap-item-title-row">
												<strong><?php esc_html_e( 'VPN & Datacenter Proxy Blocker', 'cleanverify-for-woocommerce' ); ?></strong>
												<span class="fahim-tag-pill fahim-tag-pill-pro"><?php esc_html_e( 'SECURITY', 'cleanverify-for-woocommerce' ); ?></span>
											</div>
											<p><?php esc_html_e( 'Detect and challenge registrations originating from commercial VPNs (NordVPN, ExpressVPN), datacenter proxies, and TOR exit nodes.', 'cleanverify-for-woocommerce' ); ?></p>
										</div>
									</div>
									<div class="fahim-roadmap-item">
										<div class="fahim-roadmap-item-icon">🤖</div>
										<div class="fahim-roadmap-item-content">
											<div class="fahim-roadmap-item-title-row">
												<strong><?php esc_html_e( 'Multi-Provider CAPTCHA Suite', 'cleanverify-for-woocommerce' ); ?></strong>
												<span class="fahim-tag-pill fahim-tag-pill-pro"><?php esc_html_e( 'BOT DEFENSE', 'cleanverify-for-woocommerce' ); ?></span>
											</div>
											<p><?php esc_html_e( 'Support for Google reCAPTCHA v3 invisible score-based challenges and hCaptcha alongside Cloudflare Turnstile.', 'cleanverify-for-woocommerce' ); ?></p>
										</div>
									</div>
									<div class="fahim-roadmap-item">
										<div class="fahim-roadmap-item-icon">💳</div>
										<div class="fahim-roadmap-item-content">
											<div class="fahim-roadmap-item-title-row">
												<strong><?php esc_html_e( 'Checkout Velocity Rate-Limiter', 'cleanverify-for-woocommerce' ); ?></strong>
												<span class="fahim-tag-pill fahim-tag-pill-pro"><?php esc_html_e( 'FRAUD SHIELD', 'cleanverify-for-woocommerce' ); ?></span>
											</div>
											<p><?php esc_html_e( 'Prevent card-testing bot attacks by rate-limiting checkout attempts and registration submissions per IP.', 'cleanverify-for-woocommerce' ); ?></p>
										</div>
									</div>
								</div>
							</div>

							<div class="fahim-roadmap-milestone">
								<div class="fahim-roadmap-milestone-header">
									<span class="fahim-status-badge is-planned"><?php esc_html_e( 'Planned', 'cleanverify-for-woocommerce' ); ?></span>
									<h4 class="fahim-roadmap-milestone-title"><?php esc_html_e( 'v1.3.0 - Visual Storage & Multisite Sync', 'cleanverify-for-woocommerce' ); ?></h4>
								</div>
								<div class="fahim-roadmap-items">
									<div class="fahim-roadmap-item">
										<div class="fahim-roadmap-item-icon">📊</div>
										<div class="fahim-roadmap-item-content">
											<div class="fahim-roadmap-item-title-row">
												<strong><?php esc_html_e( 'Live Database Storage Savings Visualizer', 'cleanverify-for-woocommerce' ); ?></strong>
												<span class="fahim-tag-pill fahim-tag-pill-pro"><?php esc_html_e( 'ANALYTICS', 'cleanverify-for-woocommerce' ); ?></span>
											</div>
											<p><?php esc_html_e( 'Visual analytics displaying exact MB/GB database space saved, index table reduction, and SQL query performance improvements.', 'cleanverify-for-woocommerce' ); ?></p>
										</div>
									</div>
									<div class="fahim-roadmap-item">
										<div class="fahim-roadmap-item-icon">🗄️</div>
										<div class="fahim-roadmap-item-content">
											<div class="fahim-roadmap-item-title-row">
												<strong><?php esc_html_e( 'WordPress Multisite Network Sync', 'cleanverify-for-woocommerce' ); ?></strong>
												<span class="fahim-tag-pill fahim-tag-pill-pro"><?php esc_html_e( 'ENTERPRISE', 'cleanverify-for-woocommerce' ); ?></span>
											</div>
											<p><?php esc_html_e( 'Network-wide super-admin controls to run customer cleanup across all multisite child stores with synced blacklists.', 'cleanverify-for-woocommerce' ); ?></p>
										</div>
									</div>
								</div>
							</div>

							<!-- ================= CleanVerify Core Milestones ================= -->
							<div class="fahim-roadmap-section-title" style="margin-top: 28px;">
								<span class="fahim-section-tag fahim-section-tag-free"><?php esc_html_e( 'CORE EDITION', 'cleanverify-for-woocommerce' ); ?></span>
								<span><?php esc_html_e( 'CleanVerify Core (Free) Milestones', 'cleanverify-for-woocommerce' ); ?></span>
							</div>
						<?php endif; ?>

						<div class="fahim-roadmap-milestone">
							<div class="fahim-roadmap-milestone-header">
								<span class="fahim-status-badge is-progress"><?php esc_html_e( 'In Progress', 'cleanverify-for-woocommerce' ); ?></span>
								<h4 class="fahim-roadmap-milestone-title"><?php esc_html_e( 'v1.1.0 - Advanced Filtering & CSV Export Suite', 'cleanverify-for-woocommerce' ); ?></h4>
							</div>
							<div class="fahim-roadmap-items">
								<div class="fahim-roadmap-item">
									<div class="fahim-roadmap-item-icon">🔍</div>
									<div class="fahim-roadmap-item-content">
										<div class="fahim-roadmap-item-title-row">
											<strong><?php esc_html_e( 'Advanced Date & Inactivity Range Builder', 'cleanverify-for-woocommerce' ); ?></strong>
											<span class="fahim-tag-pill fahim-tag-pill-free"><?php esc_html_e( 'CORE FEATURE', 'cleanverify-for-woocommerce' ); ?></span>
										</div>
										<p><?php esc_html_e( 'Filter inactive customers by custom registration date ranges and precise days-since-last-login thresholds.', 'cleanverify-for-woocommerce' ); ?></p>
									</div>
								</div>
								<div class="fahim-roadmap-item">
									<div class="fahim-roadmap-item-icon">📥</div>
									<div class="fahim-roadmap-item-content">
										<div class="fahim-roadmap-item-title-row">
											<strong><?php esc_html_e( 'Custom CSV Export Column Builder', 'cleanverify-for-woocommerce' ); ?></strong>
											<span class="fahim-tag-pill fahim-tag-pill-free"><?php esc_html_e( 'CORE FEATURE', 'cleanverify-for-woocommerce' ); ?></span>
										</div>
										<p><?php esc_html_e( 'Customize export columns (User ID, Email, Registered Date, Last Login, Order Count, Spend) before exporting.', 'cleanverify-for-woocommerce' ); ?></p>
									</div>
								</div>
								<div class="fahim-roadmap-item">
									<div class="fahim-roadmap-item-icon">📊</div>
									<div class="fahim-roadmap-item-content">
										<div class="fahim-roadmap-item-title-row">
											<strong><?php esc_html_e( 'Pre-Deletion Summary Breakdown', 'cleanverify-for-woocommerce' ); ?></strong>
											<span class="fahim-tag-pill fahim-tag-pill-free"><?php esc_html_e( 'CORE FEATURE', 'cleanverify-for-woocommerce' ); ?></span>
										</div>
										<p><?php esc_html_e( 'Detailed preview showing estimated database storage to be reclaimed and customer role distribution before executing deletion.', 'cleanverify-for-woocommerce' ); ?></p>
									</div>
								</div>
							</div>
						</div>

						<div class="fahim-roadmap-milestone">
							<div class="fahim-roadmap-milestone-header">
								<span class="fahim-status-badge is-planned"><?php esc_html_e( 'Planned', 'cleanverify-for-woocommerce' ); ?></span>
								<h4 class="fahim-roadmap-milestone-title"><?php esc_html_e( 'v1.2.0 - Performance & High-Scale Core Engine', 'cleanverify-for-woocommerce' ); ?></h4>
							</div>
							<div class="fahim-roadmap-items">
								<div class="fahim-roadmap-item">
									<div class="fahim-roadmap-item-icon">⚡</div>
									<div class="fahim-roadmap-item-content">
										<div class="fahim-roadmap-item-title-row">
											<strong><?php esc_html_e( 'High-Volume Asynchronous Batch Scanner', 'cleanverify-for-woocommerce' ); ?></strong>
											<span class="fahim-tag-pill fahim-tag-pill-free"><?php esc_html_e( 'CORE FEATURE', 'cleanverify-for-woocommerce' ); ?></span>
										</div>
										<p><?php esc_html_e( 'Chunk-based background scanner optimized for stores with 100,000+ customer records without PHP timeout limits.', 'cleanverify-for-woocommerce' ); ?></p>
									</div>
								</div>
								<div class="fahim-roadmap-item">
									<div class="fahim-roadmap-item-icon">🛡️</div>
									<div class="fahim-roadmap-item-content">
										<div class="fahim-roadmap-item-title-row">
											<strong><?php esc_html_e( 'Custom User Role Exclusion Whitelist', 'cleanverify-for-woocommerce' ); ?></strong>
											<span class="fahim-tag-pill fahim-tag-pill-free"><?php esc_html_e( 'CORE FEATURE', 'cleanverify-for-woocommerce' ); ?></span>
										</div>
										<p><?php esc_html_e( 'Granular protection checklist ensuring custom WordPress roles (Wholesalers, B2B, Affiliates) are never deleted.', 'cleanverify-for-woocommerce' ); ?></p>
									</div>
								</div>
								<div class="fahim-roadmap-item">
									<div class="fahim-roadmap-item-icon">🔍</div>
									<div class="fahim-roadmap-item-content">
										<div class="fahim-roadmap-item-title-row">
											<strong><?php esc_html_e( 'Email Domain Instant Search & Filter', 'cleanverify-for-woocommerce' ); ?></strong>
											<span class="fahim-tag-pill fahim-tag-pill-free"><?php esc_html_e( 'CORE FEATURE', 'cleanverify-for-woocommerce' ); ?></span>
										</div>
										<p><?php esc_html_e( 'Real-time client-side domain search box in candidate table to isolate specific email providers.', 'cleanverify-for-woocommerce' ); ?></p>
									</div>
								</div>
							</div>
						</div>

						<div class="fahim-roadmap-milestone">
							<div class="fahim-roadmap-milestone-header">
								<span class="fahim-status-badge is-planned"><?php esc_html_e( 'Planned', 'cleanverify-for-woocommerce' ); ?></span>
								<h4 class="fahim-roadmap-milestone-title"><?php esc_html_e( 'v1.3.0 - Developer & CLI Extensibility', 'cleanverify-for-woocommerce' ); ?></h4>
							</div>
							<div class="fahim-roadmap-items">
								<div class="fahim-roadmap-item">
									<div class="fahim-roadmap-item-icon">💻</div>
									<div class="fahim-roadmap-item-content">
										<div class="fahim-roadmap-item-title-row">
											<strong><?php esc_html_e( 'Native WP-CLI Automation Suite', 'cleanverify-for-woocommerce' ); ?></strong>
											<span class="fahim-tag-pill"><?php esc_html_e( 'DEV TOOLS', 'cleanverify-for-woocommerce' ); ?></span>
										</div>
										<p><?php esc_html_e( 'Headless command line tools for running scans, exports, and scheduled cleanup batches directly in server crons.', 'cleanverify-for-woocommerce' ); ?></p>
									</div>
								</div>
								<div class="fahim-roadmap-item">
									<div class="fahim-roadmap-item-icon">🪝</div>
									<div class="fahim-roadmap-item-content">
										<div class="fahim-roadmap-item-title-row">
											<strong><?php esc_html_e( 'Extended Action Hooks & Filter Matrix', 'cleanverify-for-woocommerce' ); ?></strong>
											<span class="fahim-tag-pill"><?php esc_html_e( 'DEV TOOLS', 'cleanverify-for-woocommerce' ); ?></span>
										</div>
										<p><?php esc_html_e( 'Granular WordPress developer hooks for third-party CRM syncing and custom pre-deletion events.', 'cleanverify-for-woocommerce' ); ?></p>
									</div>
								</div>
							</div>
						</div>

						<?php if ( ! $is_pro_active ) : ?>
							<!-- Pro Features Roadmap Teaser Banner in Free Mode -->
							<div class="fahim-roadmap-pro-teaser">
								<div class="fahim-roadmap-pro-teaser-content">
									<strong>⚡ <?php esc_html_e( 'Looking for Multi-Channel OTP & Anti-Fraud Features?', 'cleanverify-for-woocommerce' ); ?></strong>
									<p><?php esc_html_e( 'CleanVerify Pro introduces WhatsApp Business OTP, Telegram Bot verification, SMS failover, and VPN detection in v1.1.0.', 'cleanverify-for-woocommerce' ); ?></p>
								</div>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG . '&tab=pro' ) ); ?>" class="fahim-btn-primary fahim-btn-sm" style="flex-shrink:0;">
									<?php esc_html_e( 'Explore Pro Features', 'cleanverify-for-woocommerce' ); ?> &rarr;
								</a>
							</div>
						<?php endif; ?>
					</div>
					<div class="fahim-modal-footer">
						<span class="fahim-modal-footer-hint"><?php esc_html_e( 'Have a feature suggestion? Contact via fahimm.com', 'cleanverify-for-woocommerce' ); ?></span>
						<button type="button" class="fahim-btn-secondary fahim-btn-sm" data-cv-modal-close><?php esc_html_e( 'Close', 'cleanverify-for-woocommerce' ); ?></button>
					</div>
				</div>
			</div>

			<!-- Changelog Modal -->
			<div class="fahim-modal-backdrop" id="cv-modal-changelog" role="dialog" aria-modal="true" aria-hidden="true">
				<div class="fahim-modal-dialog">
					<div class="fahim-modal-header">
						<div class="fahim-modal-header-left">
							<span class="fahim-modal-icon">📋</span>
							<div>
								<h3 class="fahim-modal-title"><?php esc_html_e( 'Version Changelog', 'cleanverify-for-woocommerce' ); ?></h3>
								<span class="fahim-modal-subtitle"><?php esc_html_e( 'Release history and feature updates', 'cleanverify-for-woocommerce' ); ?></span>
							</div>
						</div>
						<button type="button" class="fahim-modal-close" data-cv-modal-close aria-label="<?php esc_attr_e( 'Close modal', 'cleanverify-for-woocommerce' ); ?>">&times;</button>
					</div>
					<div class="fahim-modal-body">
						<div class="fahim-changelog-release">
							<div class="fahim-changelog-release-header">
								<span class="fahim-status-badge is-released"><?php esc_html_e( 'Current Release', 'cleanverify-for-woocommerce' ); ?></span>
								<h4 class="fahim-changelog-version"><?php esc_html_e( 'Version 1.0.0', 'cleanverify-for-woocommerce' ); ?></h4>
							</div>
							<ul class="fahim-changelog-list">
								<li><strong><?php esc_html_e( 'Core Engine:', 'cleanverify-for-woocommerce' ); ?></strong> <?php esc_html_e( 'Safe zero-order inactive customer detection cross-referenced with order history and guest billing emails.', 'cleanverify-for-woocommerce' ); ?></li>
								<li><strong><?php esc_html_e( 'CSV Export:', 'cleanverify-for-woocommerce' ); ?></strong> <?php esc_html_e( 'Instant export of filtered or selected customer data with Excel UTF-8 BOM encoding.', 'cleanverify-for-woocommerce' ); ?></li>
								<li><strong><?php esc_html_e( 'Modern Dashboard:', 'cleanverify-for-woocommerce' ); ?></strong> <?php esc_html_e( 'Unified Light Theme design system with instant tab switching and zero page reloads.', 'cleanverify-for-woocommerce' ); ?></li>
								<li><strong><?php esc_html_e( 'Role Shield:', 'cleanverify-for-woocommerce' ); ?></strong> <?php esc_html_e( 'Hardcoded exclusion protecting Administrators, Editors, Authors, and Shop Managers.', 'cleanverify-for-woocommerce' ); ?></li>
								<li><strong><?php esc_html_e( 'Turnstile Anti-Spam (Pro):', 'cleanverify-for-woocommerce' ); ?></strong> <?php esc_html_e( 'Privacy-first Cloudflare Turnstile bot protection for checkout, registration, and login.', 'cleanverify-for-woocommerce' ); ?></li>
								<li><strong><?php esc_html_e( 'Disposable Email Shield (Pro):', 'cleanverify-for-woocommerce' ); ?></strong> <?php esc_html_e( 'Blacklist blocking 500+ disposable email domains with custom domain support.', 'cleanverify-for-woocommerce' ); ?></li>
								<li><strong><?php esc_html_e( 'Email OTP Verification (Pro):', 'cleanverify-for-woocommerce' ); ?></strong> <?php esc_html_e( '6-digit numeric email passcode verification with live AJAX verification and resend cooldown.', 'cleanverify-for-woocommerce' ); ?></li>
								<li><strong><?php esc_html_e( 'Quarantine Trash Bin (Pro):', 'cleanverify-for-woocommerce' ); ?></strong> <?php esc_html_e( '30-day soft-delete holding bin with 1-click full user account and meta restoration.', 'cleanverify-for-woocommerce' ); ?></li>
								<li><strong><?php esc_html_e( 'Automated Cron Pruning (Pro):', 'cleanverify-for-woocommerce' ); ?></strong> <?php esc_html_e( 'Scheduled daily, weekly, or monthly background inactive account cleanups.', 'cleanverify-for-woocommerce' ); ?></li>
								<li><strong><?php esc_html_e( 'GDPR Anonymization (Pro):', 'cleanverify-for-woocommerce' ); ?></strong> <?php esc_html_e( 'Pseudonymize customer personal data while maintaining historical sales reporting integrity.', 'cleanverify-for-woocommerce' ); ?></li>
								<li><strong><?php esc_html_e( 'Audit Trail & WP-CLI (Pro):', 'cleanverify-for-woocommerce' ); ?></strong> <?php esc_html_e( 'Activity logging and command-line automation for server administrators.', 'cleanverify-for-woocommerce' ); ?></li>
							</ul>
						</div>

						<div class="fahim-changelog-release">
							<div class="fahim-changelog-release-header">
								<span class="fahim-status-badge is-progress"><?php esc_html_e( 'In Development', 'cleanverify-for-woocommerce' ); ?></span>
								<h4 class="fahim-changelog-version"><?php esc_html_e( 'Version 1.1.0', 'cleanverify-for-woocommerce' ); ?></h4>
							</div>
							<ul class="fahim-changelog-list">
								<li><strong><?php esc_html_e( 'WhatsApp Verification (Pro):', 'cleanverify-for-woocommerce' ); ?></strong> <?php esc_html_e( 'WhatsApp Business Cloud API and Twilio integration for instant 6-digit OTP delivery.', 'cleanverify-for-woocommerce' ); ?></li>
								<li><strong><?php esc_html_e( 'Telegram Verification (Pro):', 'cleanverify-for-woocommerce' ); ?></strong> <?php esc_html_e( 'Instant bot verification and deep-linked 1-tap authentication with zero SMS fees.', 'cleanverify-for-woocommerce' ); ?></li>
								<li><strong><?php esc_html_e( 'SMS Failover Routing (Pro):', 'cleanverify-for-woocommerce' ); ?></strong> <?php esc_html_e( 'Multi-carrier SMS routing (Twilio, Vonage, AWS SNS) as backup channel.', 'cleanverify-for-woocommerce' ); ?></li>
								<li><strong><?php esc_html_e( 'Advanced Date & Inactivity Builder (Core):', 'cleanverify-for-woocommerce' ); ?></strong> <?php esc_html_e( 'Custom date range selectors and days-since-last-login isolation.', 'cleanverify-for-woocommerce' ); ?></li>
								<li><strong><?php esc_html_e( 'Custom CSV Column Builder (Core):', 'cleanverify-for-woocommerce' ); ?></strong> <?php esc_html_e( 'Choose specific user fields and export columns before downloading.', 'cleanverify-for-woocommerce' ); ?></li>
							</ul>
						</div>
					</div>
					<div class="fahim-modal-footer">
						<span class="fahim-modal-footer-hint"><?php esc_html_e( 'CleanVerify Core & Pro Release Notes', 'cleanverify-for-woocommerce' ); ?></span>
						<button type="button" class="fahim-btn-secondary fahim-btn-sm" data-cv-modal-close><?php esc_html_e( 'Close', 'cleanverify-for-woocommerce' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		<?php
	}

	/**
	 * Render the Customers Cleanup tab with modern cards, metrics, and presets.
	 *
	 * @param int $min_age Current filter threshold in days.
	 */
	private function render_cleanup_tab( $min_age ) {
		$deleted = array();
		$errors  = array();

		// Handle deletion submission.
		if (
			isset( $_POST['cv_action'] ) &&
			'delete' === $_POST['cv_action'] &&
			check_admin_referer( self::NONCE_ACTION, 'cv_nonce' ) &&
			! empty( $_POST['cv_confirm'] ) &&
			! empty( $_POST['cv_user_ids'] )
		) {
			require_once ABSPATH . 'wp-admin/includes/user.php';

			$user_ids = array_map( 'absint', (array) $_POST['cv_user_ids'] );

			foreach ( $user_ids as $user_id ) {
				$user = get_userdata( $user_id );

				if ( ! $user || ! in_array( 'customer', (array) $user->roles, true ) ) {
					continue;
				}

				// Second safety net: skip if the account also holds an excluded role,
				// even if the submitted list was somehow tampered with.
				if ( array_intersect( self::EXCLUDED_ROLES, (array) $user->roles ) ) {
					$errors[] = $user->user_email . ' ' . esc_html__( '(skipped - protected role)', 'cleanverify-for-woocommerce' );
					continue;
				}

				if ( wp_delete_user( $user_id ) ) {
					$deleted[] = $user->user_email;
				} else {
					$errors[] = $user->user_email;
				}
			}
		}

		$candidates = $this->get_customers_without_orders( $min_age );
		?>
		<div class="fahim-page">
			<?php if ( ! empty( $deleted ) ) : ?>
				<div class="fahim-alert fahim-alert-success">
					<span>✅</span>
					<span><strong><?php esc_html_e( 'Successfully deleted:', 'cleanverify-for-woocommerce' ); ?></strong> <?php echo esc_html( implode( ', ', $deleted ) ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $errors ) ) : ?>
				<div class="fahim-alert fahim-alert-error">
					<span>⚠️</span>
					<span><strong><?php esc_html_e( 'Could not delete:', 'cleanverify-for-woocommerce' ); ?></strong> <?php echo esc_html( implode( ', ', $errors ) ); ?></span>
				</div>
			<?php endif; ?>

			<!-- Quick Metric Stat Cards -->
			<div class="fahim-metric-grid">
				<div class="fahim-metric-card">
					<div class="fahim-metric-icon-wrap fahim-metric-icon-blue">👥</div>
					<div class="fahim-metric-details">
						<strong><?php echo esc_html( count( $candidates ) ); ?></strong>
						<span><?php esc_html_e( 'Candidates Found', 'cleanverify-for-woocommerce' ); ?></span>
					</div>
				</div>
				<div class="fahim-metric-card">
					<div class="fahim-metric-icon-wrap fahim-metric-icon-purple">⏱️</div>
					<div class="fahim-metric-details">
						<strong><?php echo esc_html( $min_age ); ?>+ Days</strong>
						<span><?php esc_html_e( 'Inactivity Threshold', 'cleanverify-for-woocommerce' ); ?></span>
					</div>
				</div>
				<div class="fahim-metric-card">
					<div class="fahim-metric-icon-wrap fahim-metric-icon-green">🛡️</div>
					<div class="fahim-metric-details">
						<strong>Protected</strong>
						<span><?php esc_html_e( 'Staff Roles Safe', 'cleanverify-for-woocommerce' ); ?></span>
					</div>
				</div>
			</div>

			<!-- Filter & Actions Card -->
			<div class="fahim-section-card">
				<h3 class="fahim-section-title">🔍 <?php esc_html_e( 'Filter Candidates & Export', 'cleanverify-for-woocommerce' ); ?></h3>
				<p class="fahim-section-desc">
					<?php esc_html_e( 'Lists customer accounts registered prior to the cutoff date with zero order history (checked by user ID and guest billing email).', 'cleanverify-for-woocommerce' ); ?>
				</p>
				<div class="fahim-filter-controls">
					<div class="fahim-preset-pills">
						<span class="fahim-preset-label"><?php esc_html_e( 'Presets:', 'cleanverify-for-woocommerce' ); ?></span>
						<?php
						$presets = array( 30, 60, 90, 180, 0 );
						foreach ( $presets as $p ) {
							if ( 0 === $p ) {
								$label = __( 'All Time (0d)', 'cleanverify-for-woocommerce' );
							} else {
								/* translators: %d: Number of days. */
								$label = sprintf( __( '%d Days', 'cleanverify-for-woocommerce' ), $p );
							}
							$is_selected   = (int) $min_age === $p ? 'is-selected' : '';
							$url           = add_query_arg(
								array(
									'page'       => self::MENU_SLUG,
									'tab'        => 'cleanup',
									'cv_min_age' => $p,
								),
								admin_url( 'admin.php' )
							);
							echo '<a href="' . esc_url( $url ) . '" class="fahim-preset-btn ' . esc_attr( $is_selected ) . '">' . esc_html( $label ) . '</a> ';
						}
						?>
					</div>
					<div class="fahim-filter-actions">
						<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="fahim-filter-form">
							<input type="hidden" name="page" value="<?php echo esc_attr( self::MENU_SLUG ); ?>">
							<input type="hidden" name="tab" value="cleanup">
							<div class="fahim-input-combo">
								<span class="fahim-input-combo-label">⏱️ <?php esc_html_e( 'Older than', 'cleanverify-for-woocommerce' ); ?></span>
								<input type="number" id="cv_min_age" name="cv_min_age" value="<?php echo esc_attr( $min_age ); ?>" min="0" class="fahim-number-input" aria-label="<?php esc_attr_e( 'Minimum account age in days', 'cleanverify-for-woocommerce' ); ?>">
								<span class="fahim-input-combo-unit"><?php esc_html_e( 'days', 'cleanverify-for-woocommerce' ); ?></span>
							</div>
							<button type="submit" class="fahim-btn-primary">
								<span>🔍</span> <?php esc_html_e( 'Filter', 'cleanverify-for-woocommerce' ); ?>
							</button>
						</form>
						<?php if ( ! empty( $candidates ) ) : ?>
							<form method="post" style="display:inline-block;">
								<?php wp_nonce_field( self::EXPORT_NONCE_ACTION, 'cv_export_nonce' ); ?>
								<input type="hidden" name="cv_min_age" value="<?php echo esc_attr( $min_age ); ?>">
								<input type="hidden" name="cv_action" value="export_csv">
								<button type="submit" class="fahim-btn-secondary">
									<span>📥</span> <?php esc_html_e( 'Export All as CSV', 'cleanverify-for-woocommerce' ); ?> (<?php echo count( $candidates ); ?>)
								</button>
							</form>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- Customer Candidates List Table -->
			<?php if ( empty( $candidates ) ) : ?>
				<div class="fahim-empty-card">
					<div class="fahim-empty-icon">✨</div>
					<h4 class="fahim-empty-title"><?php esc_html_e( 'Customer Database is Clean!', 'cleanverify-for-woocommerce' ); ?></h4>
					<p class="fahim-empty-desc"><?php esc_html_e( 'No customer accounts match the current inactivity threshold. All accounts have placed at least one order or are newer than the selected filter buffer.', 'cleanverify-for-woocommerce' ); ?></p>
					<a href="<?php echo esc_url( add_query_arg( array( 'page' => self::MENU_SLUG, 'tab' => 'cleanup', 'cv_min_age' => 0 ), admin_url( 'admin.php' ) ) ); ?>" class="fahim-btn-secondary">
						<span>⚡</span> <?php esc_html_e( 'Check All Accounts (0 Days)', 'cleanverify-for-woocommerce' ); ?>
					</a>
				</div>
			<?php else : ?>
				<div class="fahim-section-card">
					<h3 class="fahim-section-title">
						👥 <?php esc_html_e( 'Review & Remove Candidate Customers', 'cleanverify-for-woocommerce' ); ?>
					</h3>
					<p class="fahim-section-desc">
						<?php esc_html_e( 'Tick the accounts you wish to export or delete. A CSV backup is recommended before permanently deleting any customer data.', 'cleanverify-for-woocommerce' ); ?>
					</p>

					<form method="post">
						<?php wp_nonce_field( self::NONCE_ACTION, 'cv_nonce' ); ?>
						<input type="hidden" name="cv_min_age" value="<?php echo esc_attr( $min_age ); ?>">

						<div class="fahim-table-wrapper">
							<table class="fahim-table">
								<thead>
									<tr>
										<th style="width:40px; text-align:center;">
											<input type="checkbox" onclick="document.querySelectorAll('.cv-cb').forEach(cb=>cb.checked=this.checked);" aria-label="<?php esc_attr_e( 'Select all', 'cleanverify-for-woocommerce' ); ?>">
										</th>
										<th><?php esc_html_e( 'Customer', 'cleanverify-for-woocommerce' ); ?></th>
										<th><?php esc_html_e( 'Email Address', 'cleanverify-for-woocommerce' ); ?></th>
										<th><?php esc_html_e( 'Registered On', 'cleanverify-for-woocommerce' ); ?></th>
										<th><?php esc_html_e( 'Order History', 'cleanverify-for-woocommerce' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ( $candidates as $c ) :
										$initial              = strtoupper( substr( $c->display_name ? $c->display_name : $c->user_login, 0, 1 ) );
										$registered_timestamp = strtotime( $c->user_registered );
										$time_diff            = human_time_diff( $registered_timestamp, time() );
										?>
										<tr>
											<td style="text-align:center;">
												<input class="cv-cb" type="checkbox" name="cv_user_ids[]" value="<?php echo esc_attr( $c->ID ); ?>">
											</td>
											<td>
												<div class="fahim-user-cell">
													<span class="fahim-user-avatar"><?php echo esc_html( $initial ); ?></span>
													<div class="fahim-user-info">
														<span class="fahim-user-name"><?php echo esc_html( $c->display_name ); ?></span>
														<span class="fahim-user-username">@<?php echo esc_html( isset( $c->user_login ) ? $c->user_login : '' ); ?> <span class="fahim-id-badge">#<?php echo esc_html( $c->ID ); ?></span></span>
													</div>
												</div>
											</td>
											<td>
												<span>✉️ <?php echo esc_html( $c->user_email ); ?></span>
											</td>
											<td>
												<strong><?php echo esc_html( gmdate( 'M j, Y', $registered_timestamp ) ); ?></strong>
												<br><span style="font-size:11.5px; color:var(--muted);">
													<?php
													/* translators: %s: Human-readable relative time difference (e.g. 2 days). */
													printf( esc_html__( '%s ago', 'cleanverify-for-woocommerce' ), esc_html( $time_diff ) );
													?>
												</span>
											</td>
											<td>
												<span class="fahim-order-badge">🚫 <?php esc_html_e( '0 Orders', 'cleanverify-for-woocommerce' ); ?></span>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>

						<!-- Deletion Safety & Confirmation Box -->
						<div class="fahim-danger-box">
							<h4 class="fahim-danger-title">⚠️ <?php esc_html_e( 'Permanent Deletion Warning', 'cleanverify-for-woocommerce' ); ?></h4>
							<p class="fahim-danger-desc">
								<?php esc_html_e( 'Account deletion is permanent and cannot be reversed. Always export a CSV backup first. Accounts carrying Administrator, Editor, or Shop Manager roles are automatically protected and will never be touched.', 'cleanverify-for-woocommerce' ); ?>
							</p>
							<label class="fahim-confirm-label">
								<input type="checkbox" name="cv_confirm" value="1" required>
								<span><?php esc_html_e( 'I understand this will permanently delete the selected customer accounts and cannot be undone.', 'cleanverify-for-woocommerce' ); ?></span>
							</label>
							<div class="fahim-confirm-actions">
								<button type="submit" name="cv_action" value="delete" class="fahim-btn-danger" onclick="return confirm('<?php echo esc_js( __( 'Permanently delete the selected customers? This cannot be undone.', 'cleanverify-for-woocommerce' ) ); ?>');">
									<span>🗑️</span> <?php esc_html_e( 'Delete Selected Customers', 'cleanverify-for-woocommerce' ); ?>
								</button>
								<button type="submit" name="cv_action" value="export_csv" formnovalidate class="fahim-btn-secondary">
									<span>📥</span> <?php esc_html_e( 'Export Selected to CSV', 'cleanverify-for-woocommerce' ); ?>
								</button>
							</div>
						</div>
					</form>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render the "Free vs Pro" comparison tab content.
	 */
	private function render_free_vs_pro_tab() {
		$template_file = plugin_dir_path( __FILE__ ) . 'templates/free-vs-pro.php';
		if ( file_exists( $template_file ) ) {
			include $template_file;
		} else {
			echo '<p>' . esc_html__( 'Free vs Pro template could not be found.', 'cleanverify-for-woocommerce' ) . '</p>';
		}
	}

	/**
	 * Render the "About Developer" tab content.
	 */
	private function render_about_developer_tab() {
		$template_file = plugin_dir_path( __FILE__ ) . 'templates/about-developer.php';
		if ( file_exists( $template_file ) ) {
			include $template_file;
		} else {
			echo '<p>' . esc_html__( 'About Developer template could not be found.', 'cleanverify-for-woocommerce' ) . '</p>';
		}
	}
}
