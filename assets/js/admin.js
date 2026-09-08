/**
 * CleanVerify for WooCommerce - Admin JavaScript
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		// Dark / Light theme toggle handler
		var wrap = document.getElementById('fahim-page-wrap');
		if (wrap) {
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
				toggle.addEventListener('click', function () {
					var next = wrap.hasAttribute('data-theme') ? 'light' : 'dark';
					applyTheme(next);
					try {
						localStorage.setItem(STORAGE_KEY, next);
					} catch (e) {}
				});
			}
		}

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
	});
})();
