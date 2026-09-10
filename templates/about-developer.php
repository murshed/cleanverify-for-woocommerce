<?php
/**
 * About Developer Tab Template
 *
 * @package Non_Purchasing_Customer_Cleanup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cleanverify_images_url = plugins_url( 'assets/images/', dirname( __DIR__ ) . '/cleanverify-for-woocommerce.php' );
$cleanverify_avatar_url = $cleanverify_images_url . 'fahim-murshed.jpg';
?>

<div class="fahim-page">
		<div class="fahim-about-grid">
			<!-- Sidebar Profile -->
			<div class="fahim-author-sidebar">
				<div class="fahim-avatar-ring">
					<img src="<?php echo esc_url( $cleanverify_avatar_url ); ?>" alt="Fahim Murshid" class="fahim-avatar-img">
				</div>
				<h3 class="fahim-author-name">Fahim Murshid</h3>
				<div class="fahim-author-title">Turning Ideas into <span class="highlight">Intelligent Web Experiences</span></div>
				<div class="fahim-author-tagline">WordPress&nbsp;&nbsp;·&nbsp;&nbsp;AI&nbsp;&nbsp;·&nbsp;&nbsp;Full-Stack Development</div>
				<span class="fahim-badge-pill">🏆 10+ Years Experience (Since 2014)</span>
				<div class="fahim-availability">
					<span class="pulse"></span>
					Available for new projects
				</div>
				<p class="fahim-author-bio">
					I help founders, agencies, and creative studios launch fast, secure, high-performing WooCommerce stores, bespoke WordPress themes, and custom plugins that actually convert.
				</p>

				<div class="fahim-sidebar-stats">
					<div class="fahim-sidebar-stat">
						<strong>10+</strong>
						<span>Years Exp.</span>
					</div>
					<div class="fahim-sidebar-stat">
						<strong>100+</strong>
						<span>Projects</span>
					</div>
					<div class="fahim-sidebar-stat">
						<strong>5.0</strong>
						<span>Client Rating</span>
					</div>
				</div>

				<div class="fahim-sidebar-skills">
					<span class="fahim-skill-tag">WordPress</span>
					<span class="fahim-skill-tag">WooCommerce</span>
					<span class="fahim-skill-tag">PHP</span>
					<span class="fahim-skill-tag">JavaScript</span>
					<span class="fahim-skill-tag">React</span>
					<span class="fahim-skill-tag">Next.js</span>
					<span class="fahim-skill-tag">UI/UX</span>
					<span class="fahim-skill-tag">SEO</span>
				</div>

				<div class="fahim-sidebar-socials">
					<a href="https://www.linkedin.com/in/fahimmurshid/" target="_blank" rel="noopener noreferrer" class="fahim-social-btn" title="LinkedIn">
						<img src="<?php echo esc_url( $cleanverify_images_url . 'linkedin.svg' ); ?>" alt="LinkedIn" width="18" height="18" class="fahim-social-icon">
					</a>
					<a href="https://profiles.wordpress.org/fahimmurshed/" target="_blank" rel="noopener noreferrer" class="fahim-social-btn" title="WordPress.org">
						<img src="<?php echo esc_url( $cleanverify_images_url . 'wordpress.svg' ); ?>" alt="WordPress.org" width="18" height="18" class="fahim-social-icon">
					</a>
					<a href="https://github.com/murshed" target="_blank" rel="noopener noreferrer" class="fahim-social-btn" title="GitHub">
						<img src="<?php echo esc_url( $cleanverify_images_url . 'github.svg' ); ?>" alt="GitHub" width="18" height="18" class="fahim-social-icon">
					</a>
					<a href="https://x.com/wpfahim" target="_blank" rel="noopener noreferrer" class="fahim-social-btn" title="X (Twitter)">
						<img src="<?php echo esc_url( $cleanverify_images_url . 'x.svg' ); ?>" alt="X (Twitter)" width="18" height="18" class="fahim-social-icon">
					</a>
				</div>

				<a href="https://fahimm.com/contact" target="_blank" rel="noopener noreferrer" class="fahim-btn-contact">
					<span>🚀</span> <?php esc_html_e( 'Contact for Custom Work', 'cleanverify-for-woocommerce' ); ?>
				</a>
			</div>

			<!-- Main Content Area -->
			<div>
				<!-- Why Work With Me Highlights Grid -->
				<div class="fahim-section-card" style="margin-bottom: 24px;">
					<h3 class="fahim-section-title">✨ <?php esc_html_e( 'Why Work With Me', 'cleanverify-for-woocommerce' ); ?></h3>
					<div class="fahim-services-grid fahim-core-grid">
						<div class="fahim-service-card fahim-service-card-green">
							<div class="fahim-service-icon">🚀</div>
							<h4 class="fahim-service-title">10+ Years of Experience</h4>
							<p class="fahim-service-desc">Since 2014 I've helped founders, agencies, and creative studios launch fast, reliable WordPress sites, WooCommerce stores, and custom plugins that perform and scale.</p>
						</div>

						<div class="fahim-service-card fahim-service-card-blue">
							<div class="fahim-service-icon">🎨</div>
							<h4 class="fahim-service-title">Modern Design &amp; User Experience</h4>
							<p class="fahim-service-desc">Pixel-perfect, responsive interfaces built with accessibility in mind — so every visitor enjoys a seamless experience on any device.</p>
						</div>

						<div class="fahim-service-card fahim-service-card-amber">
							<div class="fahim-service-icon">🛡️</div>
							<h4 class="fahim-service-title">Secure &amp; Reliable by Default</h4>
							<p class="fahim-service-desc">Security-first engineering with hardened code, careful plugin selection, and proactive maintenance to keep your site safe, fast, and online.</p>
						</div>

						<div class="fahim-service-card fahim-service-card-purple">
							<div class="fahim-service-icon">🤝</div>
							<h4 class="fahim-service-title">Direct, Honest Collaboration</h4>
							<p class="fahim-service-desc">You work directly with me — clear communication, honest timelines, and ongoing support. No middlemen, no handoffs, no surprises.</p>
						</div>
					</div>
				</div>

				<!-- Professional Services Grid -->
				<div class="fahim-section-card" style="margin-bottom: 24px;">
					<h3 class="fahim-section-title">🛠️ <?php esc_html_e( 'Professional Services & Custom Engineering', 'cleanverify-for-woocommerce' ); ?></h3>
					<div class="fahim-services-intro">
						<p class="fahim-subtext">
							From <strong>Full-Stack Web Development</strong> to dedicated <strong>WordPress &amp; WooCommerce engineering</strong> — I take projects from idea to launch, and beyond. Whether you need a custom plugin, a high-performance store, or a complete AI-powered web experience, here's what I bring to the table:
						</p>
					</div>

					<div class="fahim-services-grid">
						<!-- Full-Stack: Frontend -->
						<div class="fahim-service-card fahim-service-card-blue">
							<div class="fahim-service-icon">🖥️</div>
							<h4 class="fahim-service-title">Modern Frontend Development</h4>
							<p class="fahim-service-desc">Fast, accessible interfaces with vanilla ES6+, React, and Next.js — responsive, animated, and built for flawless Core Web Vitals.</p>
						</div>

						<!-- Full-Stack: Backend -->
						<div class="fahim-service-card fahim-service-card-green">
							<div class="fahim-service-icon">⚙️</div>
							<h4 class="fahim-service-title">Robust Backend &amp; APIs</h4>
							<p class="fahim-service-desc">PHP 8.3, WordPress internals, and custom REST/GraphQL APIs — secure, well-structured logic that scales with your business.</p>
						</div>

						<!-- Full-Stack: Data -->
						<div class="fahim-service-card fahim-service-card-purple">
							<div class="fahim-service-icon">🗄️</div>
							<h4 class="fahim-service-title">Database Design &amp; Optimization</h4>
							<p class="fahim-service-desc">Schema planning, query tuning, indexing, and object caching (Redis/Memcached) that keep your data fast even at scale.</p>
						</div>

						<!-- Full-Stack: AI -->
						<div class="fahim-service-card fahim-service-card-amber">
							<div class="fahim-service-icon">🤖</div>
							<h4 class="fahim-service-title">AI-Powered Features</h4>
							<p class="fahim-service-desc">Chatbots, content generation, smart search, and automation flows powered by OpenAI and modern AI APIs — integrated seamlessly.</p>
						</div>

						<!-- Card 1 -->
						<div class="fahim-service-card">
							<div class="fahim-service-icon">🔌</div>
							<h4 class="fahim-service-title">Custom Plugin Development</h4>
							<p class="fahim-service-desc">Bespoke WordPress &amp; WooCommerce plugins engineered to your exact business logic and REST/GraphQL API specifications.</p>
						</div>

						<!-- Card 2 -->
						<div class="fahim-service-card">
							<div class="fahim-service-icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg></div>
							<h4 class="fahim-service-title">Speed &amp; Performance Optimization</h4>
							<p class="fahim-service-desc">Database cleanup, query tuning, object caching (Redis/Memcached), asset minification, and Core Web Vitals optimization.</p>
						</div>

						<!-- Card 3 -->
						<div class="fahim-service-card">
							<div class="fahim-service-icon">🛡️</div>
							<h4 class="fahim-service-title">Security Hardening &amp; Malware Removal</h4>
							<p class="fahim-service-desc">Malware removal, security auditing, firewall configuration, vulnerability patch implementations, and site hardening.</p>
						</div>

						<!-- Card 4 -->
						<div class="fahim-service-card">
							<div class="fahim-service-icon">🔄</div>
							<h4 class="fahim-service-title">Store &amp; Site Migration</h4>
							<p class="fahim-service-desc">Zero-downtime website &amp; database migrations, server upgrades, PHP 8.3 compatibility updates, and ongoing care.</p>
						</div>

						<!-- Card 5 -->
						<div class="fahim-service-card">
							<div class="fahim-service-icon">🛠️</div>
							<h4 class="fahim-service-title">Website Care &amp; Maintenance</h4>
							<p class="fahim-service-desc">Proactive core/plugin updates, daily offsite cloud backups, 24/7 uptime monitoring, and priority SLA support plans.</p>
						</div>

						<!-- Card 6 -->
						<div class="fahim-service-card">
							<div class="fahim-service-icon">🔗</div>
							<h4 class="fahim-service-title">API &amp; Custom Integrations</h4>
							<p class="fahim-service-desc">Seamless CRM, ERP, payment gateway, and third-party SaaS integrations via REST APIs, webhooks &amp; GraphQL.</p>
						</div>

						<!-- Card 7 -->
						<div class="fahim-service-card">
							<div class="fahim-service-icon">🎨</div>
							<h4 class="fahim-service-title">Bespoke Theme Engineering</h4>
							<p class="fahim-service-desc">Custom block themes, Full Site Editing (FSE) templates, component design systems, and responsive layouts.</p>
						</div>

						<!-- Card 8 -->
						<div class="fahim-service-card">
							<div class="fahim-service-icon">🧩</div>
							<h4 class="fahim-service-title">Gutenberg &amp; React Blocks</h4>
							<p class="fahim-service-desc">Native WordPress editor blocks built with React, ES6, and custom dynamic PHP renderers.</p>
						</div>

						<!-- Card 9 -->
						<div class="fahim-service-card">
							<div class="fahim-service-icon">🛒</div>
							<h4 class="fahim-service-title">WooCommerce Enterprise Scaling</h4>
							<p class="fahim-service-desc">High-performance order storage (HPOS) tuning, custom checkout flows, dynamic pricing, and marketplace builds.</p>
						</div>

						<!-- Card 10 -->
						<div class="fahim-service-card">
							<div class="fahim-service-icon">📊</div>
							<h4 class="fahim-service-title">SEO &amp; Structured Data Engine</h4>
							<p class="fahim-service-desc">Built-in JSON-LD CreativeWork schema markup, Open Graph metadata, dynamic sitemaps, and indexing optimizations.</p>
						</div>

						<!-- Card 11 -->
						<div class="fahim-service-card">
							<div class="fahim-service-icon">🚀</div>
							<h4 class="fahim-service-title">Headless WP &amp; Next.js Builds</h4>
							<p class="fahim-service-desc">Modern decoupled WordPress architectures paired with Next.js, Vercel deployment, and SSG rendering.</p>
						</div>

						<!-- Card 12 -->
						<div class="fahim-service-card">
							<div class="fahim-service-icon">💬</div>
							<h4 class="fahim-service-title">Code Audits &amp; Consulting</h4>
							<p class="fahim-service-desc">Deep-dive technical reviews, security compliance audits, performance profiling, and architectural guidance.</p>
						</div>
					</div>
				</div>

				<!-- Final CTA Section -->
				<div class="fahim-cta" style="margin-bottom: 0;">
					<span class="fahim-cta-eyebrow">📬 <?php esc_html_e( "Let's build something great", 'cleanverify-for-woocommerce' ); ?></span>
					<h4 class="fahim-cta-title"><?php esc_html_e( "Have a project in mind? Let's talk about it.", 'cleanverify-for-woocommerce' ); ?></h4>
					<p class="fahim-cta-text"><?php esc_html_e( "Share your goals and I'll reply within 24 hours with honest advice and a clear plan — no strings attached.", 'cleanverify-for-woocommerce' ); ?></p>
					<div class="fahim-cta-actions">
						<a href="https://fahimm.com/contact" target="_blank" rel="noopener noreferrer" class="fahim-cta-btn fahim-cta-btn-primary">
							<span>🚀</span> <?php esc_html_e( 'Start a Project', 'cleanverify-for-woocommerce' ); ?>
						</a>
						<a href="https://fahimm.com/" target="_blank" rel="noopener noreferrer" class="fahim-cta-btn fahim-cta-btn-ghost">
							<span>🌐</span> <?php esc_html_e( 'View My Work', 'cleanverify-for-woocommerce' ); ?>
						</a>
					</div>
					<p class="fahim-cta-note"><span class="dot">●</span> <?php esc_html_e( 'Usually replies within 24 hours', 'cleanverify-for-woocommerce' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>
