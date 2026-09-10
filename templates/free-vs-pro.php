<?php
/**
 * Free vs Pro Comparison Tab Template
 *
 * @package CleanVerify_For_WooCommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="fahim-page">
	<!-- Pro Hero Banner -->
	<div class="fahim-pro-hero">
		<div class="fahim-pro-hero-content">
			<span class="fahim-pro-pill"><svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor" style="vertical-align:-1px; margin-right:4px;"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg><?php esc_html_e( 'Level Up Your Store Protection', 'cleanverify-for-woocommerce' ); ?></span>
			<h2 class="fahim-pro-hero-title">
				<?php esc_html_e( 'Automate Account Maintenance &', 'cleanverify-for-woocommerce' ); ?>
				<span class="highlight"><?php esc_html_e( 'Block Spam Registrations', 'cleanverify-for-woocommerce' ); ?></span>
			</h2>
			<p class="fahim-pro-hero-desc">
				<?php esc_html_e( 'While CleanVerify Free gives you safe manual cleanup and CSV exports, CleanVerify Pro turns your store into a self-cleaning, fraud-resistant powerhouse with automated scheduling, disposable email blocking, email OTP verification, soft-delete recovery, and GDPR anonymization.', 'cleanverify-for-woocommerce' ); ?>
			</p>
			<div class="fahim-pro-hero-actions">
				<a href="https://fahimm.com/contact?subject=CleanVerify%20Pro%20Inquiry" target="_blank" rel="noopener noreferrer" class="fahim-btn-gradient">
					<?php esc_html_e( 'Get CleanVerify Pro', 'cleanverify-for-woocommerce' ); ?>
				</a>
				<a href="#cv-comparison-table" class="fahim-btn-outline">
					<?php esc_html_e( 'Compare Features', 'cleanverify-for-woocommerce' ); ?>
				</a>
			</div>
		</div>
	</div>

	<!-- Top 6 Highlighted Pro Features Grid -->
	<div class="fahim-pro-features-grid">
		<div class="fahim-pro-card">
			<div class="fahim-pro-card-icon fahim-icon-blue">⏱️</div>
			<div class="fahim-pro-card-badge"><?php esc_html_e( 'Automation', 'cleanverify-for-woocommerce' ); ?></div>
			<h3 class="fahim-pro-card-title"><?php esc_html_e( 'Scheduled Background Cleanup', 'cleanverify-for-woocommerce' ); ?></h3>
			<p class="fahim-pro-card-desc">
				<?php esc_html_e( 'Set rules to automatically prune zero-order accounts on a recurring schedule (e.g. weekly or monthly) using WooCommerce Action Scheduler without opening wp-admin.', 'cleanverify-for-woocommerce' ); ?>
			</p>
		</div>

		<div class="fahim-pro-card">
			<div class="fahim-pro-card-icon fahim-icon-purple">🚫</div>
			<div class="fahim-pro-card-badge"><?php esc_html_e( 'Anti-Spam', 'cleanverify-for-woocommerce' ); ?></div>
			<h3 class="fahim-pro-card-title"><?php esc_html_e( 'Disposable Email Shield', 'cleanverify-for-woocommerce' ); ?></h3>
			<p class="fahim-pro-card-desc">
				<?php esc_html_e( 'Automatically block fake registrations and disposable temporary emails (Mailinator, 10minutemail, etc.) with a live blacklist of 5,000+ burner domains.', 'cleanverify-for-woocommerce' ); ?>
			</p>
		</div>

		<div class="fahim-pro-card">
			<div class="fahim-pro-card-icon fahim-icon-green">✉️</div>
			<div class="fahim-pro-card-badge"><?php esc_html_e( 'Verification', 'cleanverify-for-woocommerce' ); ?></div>
			<h3 class="fahim-pro-card-title"><?php esc_html_e( 'Email OTP & Magic Links', 'cleanverify-for-woocommerce' ); ?></h3>
			<p class="fahim-pro-card-desc">
				<?php esc_html_e( 'Verify genuine customers before account creation or checkout with 6-digit email OTP codes or frictionless one-click magic login links.', 'cleanverify-for-woocommerce' ); ?>
			</p>
		</div>

		<div class="fahim-pro-card">
			<div class="fahim-pro-card-icon fahim-icon-amber">🗄️</div>
			<div class="fahim-pro-card-badge"><?php esc_html_e( 'Safety', 'cleanverify-for-woocommerce' ); ?></div>
			<h3 class="fahim-pro-card-title"><?php esc_html_e( 'Trash Bin & Soft-Delete Undo', 'cleanverify-for-woocommerce' ); ?></h3>
			<p class="fahim-pro-card-desc">
				<?php esc_html_e( 'Never fear accidental deletion. Soft-deleted accounts enter a 30-day quarantine bin where they can be restored with a single click at any time.', 'cleanverify-for-woocommerce' ); ?>
			</p>
		</div>

		<div class="fahim-pro-card">
			<div class="fahim-pro-card-icon fahim-icon-teal">⚖️</div>
			<div class="fahim-pro-card-badge"><?php esc_html_e( 'GDPR / CCPA', 'cleanverify-for-woocommerce' ); ?></div>
			<h3 class="fahim-pro-card-title"><?php esc_html_e( 'Customer Data Anonymization', 'cleanverify-for-woocommerce' ); ?></h3>
			<p class="fahim-pro-card-desc">
				<?php esc_html_e( 'Scrub personal identifiable information (PII) to comply with privacy laws while keeping your WooCommerce financial statistics and revenue analytics 100% intact.', 'cleanverify-for-woocommerce' ); ?>
			</p>
		</div>

		<div class="fahim-pro-card">
			<div class="fahim-pro-card-icon fahim-icon-rose">🤖</div>
			<div class="fahim-pro-card-badge"><?php esc_html_e( 'Security', 'cleanverify-for-woocommerce' ); ?></div>
			<h3 class="fahim-pro-card-title"><?php esc_html_e( 'Turnstile & reCAPTCHA v3', 'cleanverify-for-woocommerce' ); ?></h3>
			<p class="fahim-pro-card-desc">
				<?php esc_html_e( 'Stop registration bot floods dead in their tracks on WooCommerce signup, login, and checkout forms without annoying interactive puzzle captchas.', 'cleanverify-for-woocommerce' ); ?>
			</p>
		</div>
	</div>

	<!-- Full Feature Comparison Table -->
	<div class="fahim-section-card" id="cv-comparison-table" style="margin-top: 32px;">
		<div class="fahim-section-header">
			<h3 class="fahim-section-title"><?php esc_html_e( 'Detailed Plan Comparison', 'cleanverify-for-woocommerce' ); ?></h3>
			<p class="fahim-section-desc">
				<?php esc_html_e( 'See how CleanVerify Free and CleanVerify Pro compare across cleanup capabilities, security, privacy, and automation.', 'cleanverify-for-woocommerce' ); ?>
			</p>
		</div>

		<div class="fahim-compare-table-wrap">
			<table class="fahim-compare-table">
				<thead>
					<tr>
						<th class="col-feature"><?php esc_html_e( 'Feature / Capability', 'cleanverify-for-woocommerce' ); ?></th>
						<th class="col-free">
							<span class="fahim-plan-tag"><?php esc_html_e( 'Free (Active)', 'cleanverify-for-woocommerce' ); ?></span>
						</th>
						<th class="col-pro">
							<span class="fahim-plan-tag fahim-plan-tag-pro"><svg viewBox="0 0 24 24" width="11" height="11" fill="currentColor" style="vertical-align:-1px; margin-right:3px;"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg><?php esc_html_e( 'Pro (Premium)', 'cleanverify-for-woocommerce' ); ?></span>
						</th>
					</tr>
				</thead>
				<tbody>
					<!-- Category 1: Account Detection & Cleanup -->
					<tr class="category-header">
						<td colspan="3">👥 <?php esc_html_e( 'Account Detection & Cleanup', 'cleanverify-for-woocommerce' ); ?></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'Zero-Order Customer Detection', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Find registered customers who have never placed an order.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-check">✓</span></td>
						<td class="col-pro"><span class="cv-check">✓</span></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'Guest Order Cross-Check', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Verifies orders linked via account ID and guest billing email.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-check">✓</span></td>
						<td class="col-pro"><span class="cv-check">✓</span></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'Account Age Threshold Filters', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Preset and custom age buffers (30d, 60d, 90d, custom days).', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-check">✓</span></td>
						<td class="col-pro"><span class="cv-check">✓</span></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'CSV Export with UTF-8 BOM', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Export candidate lists with Excel compatibility.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-check">✓</span></td>
						<td class="col-pro"><span class="cv-check">✓ (Extended Metadata)</span></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'Dormant Customer Detection (Lapsed Buyers)', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Identify past buyers with no orders in 12–24+ months for re-engagement or pruning.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-cross">✕</span></td>
						<td class="col-pro"><span class="cv-check">✓</span></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'Failed / Cancelled Only Order Accounts', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Filter accounts that only created failed, fraudulent, or cancelled orders.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-cross">✕</span></td>
						<td class="col-pro"><span class="cv-check">✓</span></td>
					</tr>

					<!-- Category 2: Automation & Background Jobs -->
					<tr class="category-header">
						<td colspan="3">⏱️ <?php esc_html_e( 'Automation & Background Scheduling', 'cleanverify-for-woocommerce' ); ?></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'Manual Bulk Review & Delete', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Select accounts in wp-admin and confirm deletion.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-check">✓</span></td>
						<td class="col-pro"><span class="cv-check">✓</span></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'Scheduled Auto-Cleanup (Action Scheduler)', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Automate cleanup rules daily, weekly, or monthly in the background.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-cross">✕</span></td>
						<td class="col-pro"><span class="cv-badge-highlight"><?php esc_html_e( 'Automated', 'cleanverify-for-woocommerce' ); ?></span></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'Dry-Run Simulation Mode', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Test automated rules safely before applying them.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-cross">✕</span></td>
						<td class="col-pro"><span class="cv-check">✓</span></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'Email Digest Reports & Webhooks', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Weekly summary emails and Slack/Discord notifications on cleanup events.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-cross">✕</span></td>
						<td class="col-pro"><span class="cv-check">✓</span></td>
					</tr>

					<!-- Category 3: Anti-Spam & Verification -->
					<tr class="category-header">
						<td colspan="3">🛡️ <?php esc_html_e( 'Pre-Registration Anti-Spam & Verification', 'cleanverify-for-woocommerce' ); ?></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'Disposable & Burner Email Blocking', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Live blacklist of 5,000+ temp email providers to prevent fake signups.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-cross">✕</span></td>
						<td class="col-pro"><span class="cv-badge-highlight"><?php esc_html_e( 'Live Blacklist', 'cleanverify-for-woocommerce' ); ?></span></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'Email OTP & Magic Link Verification', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Require 6-digit email confirmation code before granting active account status.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-cross">✕</span></td>
						<td class="col-pro"><span class="cv-check">✓</span></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'Cloudflare Turnstile & Google reCAPTCHA v3', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Invisible bot blocking on registration, login, and checkout.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-cross">✕</span></td>
						<td class="col-pro"><span class="cv-check">✓</span></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'Custom Domain Blacklisting & Regex Rules', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Block specific TLDs (e.g. .xyz, .top, .ru) or spam email patterns.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-cross">✕</span></td>
						<td class="col-pro"><span class="cv-check">✓</span></td>
					</tr>

					<!-- Category 4: Safety, Recovery & GDPR -->
					<tr class="category-header">
						<td colspan="3">⚖️ <?php esc_html_e( 'Safety, Recovery & Privacy Compliance', 'cleanverify-for-woocommerce' ); ?></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'Role Safety Shield (Admin/Staff Protected)', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Admins, Editors, and Shop Managers can never be deleted.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-check">✓</span></td>
						<td class="col-pro"><span class="cv-check">✓</span></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'Soft-Delete Trash Bin & 1-Click Restore', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Quarantine accounts for 30 days before permanent wipe.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-cross">✕ (Hard Delete)</span></td>
						<td class="col-pro"><span class="cv-badge-highlight"><?php esc_html_e( '30-Day Undo', 'cleanverify-for-woocommerce' ); ?></span></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'GDPR / CCPA Customer PII Anonymization', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Anonymize personal data while keeping financial reports accurate.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-cross">✕</span></td>
						<td class="col-pro"><span class="cv-check">✓</span></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'Audit Trail & Action Logs', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Full history log of who cleaned, deleted, or verified accounts.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-cross">✕</span></td>
						<td class="col-pro"><span class="cv-check">✓</span></td>
					</tr>

					<!-- Category 5: Architecture & Developer Tools -->
					<tr class="category-header">
						<td colspan="3">🚀 <?php esc_html_e( 'Performance & Developer Tools', 'cleanverify-for-woocommerce' ); ?></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'High-Performance Order Storage (HPOS) Ready', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Fully certified for WooCommerce HPOS custom order tables.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-check">✓</span></td>
						<td class="col-pro"><span class="cv-check">✓</span></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'WP-CLI Command Line Interface', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Run cleanup tasks directly via terminal or server cron.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-cross">✕</span></td>
						<td class="col-pro"><span class="cv-check">✓</span></td>
					</tr>
					<tr>
						<td class="col-feature">
							<strong><?php esc_html_e( 'Priority Support & Custom Integration', 'cleanverify-for-woocommerce' ); ?></strong>
							<span class="feature-sub"><?php esc_html_e( 'Direct developer support and custom rule engineering.', 'cleanverify-for-woocommerce' ); ?></span>
						</td>
						<td class="col-free"><span class="cv-cross">✕</span></td>
						<td class="col-pro"><span class="cv-check">✓</span></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<!-- Bottom Conversion & Contact Box -->
	<div class="fahim-pro-cta-card">
		<div class="fahim-pro-cta-content">
			<div class="fahim-pro-cta-badge">🚀 <?php esc_html_e( 'Custom Engineering & Pro Access', 'cleanverify-for-woocommerce' ); ?></div>
			<h3 class="fahim-pro-cta-title"><?php esc_html_e( 'Ready to Supercharge Your WooCommerce Store?', 'cleanverify-for-woocommerce' ); ?></h3>
			<p class="fahim-pro-cta-desc">
				<?php esc_html_e( 'CleanVerify Pro is being tailored for high-volume stores, agencies, and security-conscious WooCommerce merchants. Get early access or request custom enterprise modules directly from the author.', 'cleanverify-for-woocommerce' ); ?>
			</p>
			<div class="fahim-pro-cta-actions">
				<a href="https://fahimm.com/contact?subject=CleanVerify%20Pro%20Upgrade" target="_blank" rel="noopener noreferrer" class="fahim-btn-gradient">
					<span>💬</span> <?php esc_html_e( 'Inquire About Pro / Early Access', 'cleanverify-for-woocommerce' ); ?>
				</a>
				<a href="https://fahimm.com" target="_blank" rel="noopener noreferrer" class="fahim-btn-outline">
					<span>🌐</span> <?php esc_html_e( 'Visit Developer Website', 'cleanverify-for-woocommerce' ); ?>
				</a>
			</div>
		</div>
	</div>
</div>
