/**
 * CleanVerify for WooCommerce - Admin JavaScript
 */
(function () {
	'use strict';

	function initCleanVerifyAdmin() {
		// Instant Tab Switching without page reloads
		var navTabs = document.querySelectorAll('.fahim-nav-tab[data-tab]');
		var tabPanes = document.querySelectorAll('.fahim-tab-pane');

		function switchTab(tabId, updateUrl) {
			if (!tabId) {
				return;
			}

			var targetPane = document.getElementById('fahim-tab-' + tabId);
			if (!targetPane) {
				return;
			}

			// Update tab active classes
			navTabs.forEach(function (tab) {
				if (tab.getAttribute('data-tab') === tabId) {
					tab.classList.add('is-active');
				} else {
					tab.classList.remove('is-active');
				}
			});

			// Update pane active classes
			tabPanes.forEach(function (pane) {
				if (pane.id === 'fahim-tab-' + tabId) {
					pane.classList.add('is-active');
				} else {
					pane.classList.remove('is-active');
				}
			});

			if (updateUrl && window.history && window.history.pushState) {
				var url = new URL(window.location.href);
				url.searchParams.set('tab', tabId);
				window.history.pushState({ tab: tabId }, '', url.toString());
			}
		}

		navTabs.forEach(function (tab) {
			tab.addEventListener('click', function (e) {
				var tabId = this.getAttribute('data-tab');
				if (tabId) {
					e.preventDefault();
					switchTab(tabId, true);
				}
			});
		});

		// Listen to popstate for browser back/forward buttons
		window.addEventListener('popstate', function (e) {
			var tabId = (e.state && e.state.tab) || (new URLSearchParams(window.location.search)).get('tab') || 'cleanup';
			switchTab(tabId, false);
		});

		// Modal Dialog Handlers (Roadmap & Changelog)
		function openModal(modalId) {
			var modal = document.getElementById(modalId);
			if (modal) {
				modal.classList.add('is-open');
				modal.setAttribute('aria-hidden', 'false');
				document.body.style.overflow = 'hidden';
			}
		}

		function closeModal(modal) {
			if (modal) {
				modal.classList.remove('is-open');
				modal.setAttribute('aria-hidden', 'true');
				if (!document.querySelector('.fahim-modal-backdrop.is-open')) {
					document.body.style.overflow = '';
				}
			}
		}

		document.addEventListener('click', function (e) {
			var trigger = e.target.closest('[data-cv-modal]');
			if (trigger) {
				e.preventDefault();
				var targetModalId = trigger.getAttribute('data-cv-modal');
				openModal(targetModalId);
				return;
			}

			var closeBtn = e.target.closest('[data-cv-modal-close]');
			if (closeBtn) {
				e.preventDefault();
				var modal = closeBtn.closest('.fahim-modal-backdrop');
				closeModal(modal);
				return;
			}

			// Backdrop click to close
			if (e.target.classList && e.target.classList.contains('fahim-modal-backdrop')) {
				closeModal(e.target);
			}
		});

		// ESC key to close open modals
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' || e.keyCode === 27) {
				var openModalEl = document.querySelector('.fahim-modal-backdrop.is-open');
				if (openModalEl) {
					closeModal(openModalEl);
				}
			}
		});

		// Sticky sidebar observer for Developer Hub
		var sidebar = document.querySelector('.fahim-author-sidebar');
		if (sidebar && 'IntersectionObserver' in window) {
			var observer = new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						if (entry.isIntersecting && entry.boundingClientRect.top < 0) {
							sidebar.classList.add('is-stuck');
						} else {
							sidebar.classList.remove('is-stuck');
						}
					});
				},
				{ rootMargin: '0px 0px -100% 0px' }
			);
			observer.observe(sidebar);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initCleanVerifyAdmin);
	} else {
		initCleanVerifyAdmin();
	}
})();
