<?php
/**
 * Plugin Name:       CleanVerify for WooCommerce
 * Description:       All-in-one customer verification, anti-fraud protection, and inactive customer cleanup for WooCommerce stores.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Tested up to:      7.1
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
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_action_links' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Enqueue admin styles for plugin settings pages.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'users_page_' . self::MENU_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'cleanverify-admin-style',
			plugins_url( 'assets/css/admin.css', __FILE__ ),
			array(),
			CLEANVERIFY_VERSION
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
			esc_url( admin_url( 'users.php?page=' . self::MENU_SLUG ) ),
			esc_html__( 'Settings', 'cleanverify-for-woocommerce' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

	public function add_admin_page() {
		$hook = add_users_page(
			esc_html__( 'CleanVerify for WooCommerce', 'cleanverify-for-woocommerce' ),
			esc_html__( 'CleanVerify', 'cleanverify-for-woocommerce' ),
			'delete_users',
			self::MENU_SLUG,
			array( $this, 'render_admin_page' )
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

		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'cleanup';
		if ( ! in_array( $active_tab, array( 'cleanup', 'about' ), true ) ) {
			$active_tab = 'cleanup';
		}

		$min_age          = isset( $_REQUEST['cv_min_age'] ) ? absint( $_REQUEST['cv_min_age'] ) : 30;
		$candidates_count = count( $this->get_customers_without_orders( $min_age ) );
		?>
		<div class="fahim-page-wrap" id="fahim-page-wrap">
			<!-- Header Bar -->
			<div class="fahim-header-bar">
				<div class="fahim-header-left">
					<span class="fahim-header-badge">🛡️ <?php esc_html_e( 'CleanVerify Suite', 'cleanverify-for-woocommerce' ); ?></span>
					<h1 class="fahim-header-title">
						Clean<span class="highlight">Verify</span> <?php esc_html_e( 'for WooCommerce', 'cleanverify-for-woocommerce' ); ?>
					</h1>
					<p class="fahim-header-subtitle">
						<?php esc_html_e( 'Safely verify accounts, block fake registrations, and clean customers with zero orders.', 'cleanverify-for-woocommerce' ); ?>
					</p>
				</div>
				<div class="fahim-header-actions">
					<button type="button" class="fahim-theme-toggle" id="fahim-theme-toggle" aria-label="<?php esc_attr_e( 'Toggle dark mode', 'cleanverify-for-woocommerce' ); ?>">
						<span class="icon-moon" title="<?php esc_attr_e( 'Switch to Dark Mode', 'cleanverify-for-woocommerce' ); ?>">🌙</span>
						<span class="icon-sun" title="<?php esc_attr_e( 'Switch to Light Mode', 'cleanverify-for-woocommerce' ); ?>">☀️</span>
					</button>
				</div>
			</div>

			<!-- Navigation Tabs -->
			<div class="fahim-nav-tabs">
				<a href="<?php echo esc_url( admin_url( 'users.php?page=' . self::MENU_SLUG . '&tab=cleanup' ) ); ?>" class="fahim-nav-tab <?php echo 'cleanup' === $active_tab ? 'is-active' : ''; ?>">
					<span>👥 <?php esc_html_e( 'Customer Cleanup', 'cleanverify-for-woocommerce' ); ?></span>
					<span class="fahim-tab-badge"><?php echo esc_html( $candidates_count ); ?></span>
				</a>
				<a href="<?php echo esc_url( admin_url( 'users.php?page=' . self::MENU_SLUG . '&tab=about' ) ); ?>" class="fahim-nav-tab <?php echo 'about' === $active_tab ? 'is-active' : ''; ?>">
					<span>🚀 <?php esc_html_e( 'About Developer', 'cleanverify-for-woocommerce' ); ?></span>
				</a>
			</div>

			<!-- Tab Content Body -->
			<?php
			if ( 'about' === $active_tab ) {
				$this->render_about_developer_tab();
			} else {
				$this->render_cleanup_tab( $min_age );
			}
			?>
		</div>

		<script>
		(function() {
			var wrap = document.getElementById('fahim-page-wrap');
			if (!wrap) return;

			var STORAGE_KEY = 'fahim-theme';

			function applyTheme(theme) {
				if (theme === 'dark') {
					wrap.setAttribute('data-theme', 'dark');
				} else {
					wrap.removeAttribute('data-theme');
				}
			}

			function getStoredTheme() {
				try {
					return localStorage.getItem(STORAGE_KEY);
				} catch (e) {
					return null;
				}
			}

			function systemPrefersDark() {
				return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
			}

			var stored = getStoredTheme();
			applyTheme(stored || (systemPrefersDark() ? 'dark' : 'light'));

			var toggle = document.getElementById('fahim-theme-toggle');
			if (toggle) {
				toggle.addEventListener('click', function() {
					var next = wrap.hasAttribute('data-theme') ? 'light' : 'dark';
					applyTheme(next);
					try {
						localStorage.setItem(STORAGE_KEY, next);
					} catch (e) {}
				});
			}
		})();
		</script>
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
							$label         = 0 === $p ? __( 'All Time (0d)', 'cleanverify-for-woocommerce' ) : sprintf( __( '%d Days', 'cleanverify-for-woocommerce' ), $p );
							$is_selected   = (int) $min_age === $p ? 'is-selected' : '';
							$url           = add_query_arg(
								array(
									'page'       => self::MENU_SLUG,
									'tab'        => 'cleanup',
									'cv_min_age' => $p,
								),
								admin_url( 'users.php' )
							);
							echo '<a href="' . esc_url( $url ) . '" class="fahim-preset-btn ' . esc_attr( $is_selected ) . '">' . esc_html( $label ) . '</a> ';
						}
						?>
					</div>
					<div class="fahim-filter-actions">
						<form method="get" action="<?php echo esc_url( admin_url( 'users.php' ) ); ?>" class="fahim-input-group">
							<input type="hidden" name="page" value="<?php echo esc_attr( self::MENU_SLUG ); ?>">
							<input type="hidden" name="tab" value="cleanup">
							<label for="cv_min_age"><?php esc_html_e( 'Older than:', 'cleanverify-for-woocommerce' ); ?></label>
							<input type="number" id="cv_min_age" name="cv_min_age" value="<?php echo esc_attr( $min_age ); ?>" min="0" class="fahim-number-input">
							<span><?php esc_html_e( 'days', 'cleanverify-for-woocommerce' ); ?></span>
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
					<a href="<?php echo esc_url( add_query_arg( array( 'page' => self::MENU_SLUG, 'tab' => 'cleanup', 'cv_min_age' => 0 ), admin_url( 'users.php' ) ) ); ?>" class="fahim-btn-secondary">
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
												<br><span style="font-size:11.5px; color:var(--muted);"><?php printf( esc_html__( '%s ago', 'cleanverify-for-woocommerce' ), esc_html( $time_diff ) ); ?></span>
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
