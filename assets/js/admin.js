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

		// Candidate Table Selection & Deletion Animation
		initCandidateTableControls();
	}

	function initCandidateTableControls() {
		var table = document.getElementById('cv-candidates-table');
		if (!table) {
			return;
		}

		var masterCb = document.getElementById('cv-master-checkbox');
		var selectAllBtn = document.getElementById('cv-select-all-btn');
		var deselectAllBtn = document.getElementById('cv-deselect-all-btn');
		var selectedCountEl = document.getElementById('cv-selected-count');
		var cleanupForm = document.getElementById('cv-cleanup-form');
		var deleteBtn = document.getElementById('cv-delete-btn');
		var confirmCb = document.getElementById('cv-confirm-checkbox');
		var deleteOverlay = document.getElementById('fahim-delete-anim-overlay');
		var deleteMsg = document.getElementById('fahim-delete-anim-msg');

		function getCheckboxes() {
			return Array.prototype.slice.call(table.querySelectorAll('.cv-cb'));
		}

		function updateSelectionState() {
			var checkboxes = getCheckboxes();
			var total = checkboxes.length;
			var checked = checkboxes.filter(function (cb) { return cb.checked; });
			var count = checked.length;

			if (selectedCountEl) {
				selectedCountEl.textContent = count;
			}

			if (masterCb) {
				masterCb.checked = total > 0 && count === total;
				masterCb.indeterminate = count > 0 && count < total;
			}

			if (selectAllBtn && deselectAllBtn) {
				if (count === total && total > 0) {
					selectAllBtn.style.display = 'none';
					deselectAllBtn.style.display = 'inline-flex';
				} else {
					selectAllBtn.style.display = 'inline-flex';
					deselectAllBtn.style.display = count > 0 ? 'inline-flex' : 'none';
				}
			}
		}

		if (masterCb) {
			masterCb.addEventListener('change', function () {
				var isChecked = this.checked;
				getCheckboxes().forEach(function (cb) {
					cb.checked = isChecked;
				});
				updateSelectionState();
			});
		}

		if (selectAllBtn) {
			selectAllBtn.addEventListener('click', function (e) {
				e.preventDefault();
				getCheckboxes().forEach(function (cb) {
					cb.checked = true;
				});
				updateSelectionState();
			});
		}

		if (deselectAllBtn) {
			deselectAllBtn.addEventListener('click', function (e) {
				e.preventDefault();
				getCheckboxes().forEach(function (cb) {
					cb.checked = false;
				});
				updateSelectionState();
			});
		}

		table.addEventListener('change', function (e) {
			if (e.target && e.target.classList.contains('cv-cb')) {
				updateSelectionState();
			}
		});

		// Initial state calculation
		updateSelectionState();

		// Deletion Animation & Confirmation Handling
		if (deleteBtn && cleanupForm) {
			deleteBtn.addEventListener('click', function (e) {
				var checkboxes = getCheckboxes();
				var checked = checkboxes.filter(function (cb) { return cb.checked; });

				if (checked.length === 0) {
					e.preventDefault();
					window.alert('Please select at least one customer account to delete.');
					return false;
				}

				if (confirmCb && !confirmCb.checked) {
					e.preventDefault();
					confirmCb.focus();
					window.alert('Please check the confirmation box before deleting accounts.');
					return false;
				}

				var confirmText = 'Permanently delete ' + checked.length + ' customer account' + (checked.length > 1 ? 's' : '') + '? This cannot be undone.';
				if (!window.confirm(confirmText)) {
					e.preventDefault();
					return false;
				}

				// If confirmed, trigger the smooth row deletion animation + HUD overlay
				e.preventDefault();

				// Staggered disintegrating row animation
				checked.forEach(function (cb, idx) {
					var row = cb.closest('tr');
					if (row) {
						row.style.transitionDelay = Math.min(idx * 20, 250) + 'ms';
						row.classList.add('fahim-row-deleting');
					}
				});

				// Show the animated Deletion Overlay HUD
				if (deleteOverlay) {
					deleteOverlay.style.display = 'flex';
					deleteOverlay.setAttribute('aria-hidden', 'false');
					if (deleteMsg) {
						deleteMsg.textContent = 'Permanently removing ' + checked.length + ' customer account' + (checked.length > 1 ? 's' : '') + ' from WooCommerce...';
					}
				}

				// Allow the animation to play, then submit the form
				setTimeout(function () {
					var actionInput = document.createElement('input');
					actionInput.type = 'hidden';
					actionInput.name = 'cv_action';
					actionInput.value = 'delete';
					cleanupForm.appendChild(actionInput);
					cleanupForm.submit();
				}, 500);
			});
		}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initCleanVerifyAdmin);
	} else {
		initCleanVerifyAdmin();
	}
})();
