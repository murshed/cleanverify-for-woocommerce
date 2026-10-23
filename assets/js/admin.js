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

		// Candidate Table Selection, AJAX Filtering, and Background Deletion Controls
		initCandidateTableControls();
	}

	function initCandidateTableControls() {
		var table = document.getElementById('cv-candidates-table');
		var tableBody = document.getElementById('cv-candidates-tbody');
		var masterCb = document.getElementById('cv-master-checkbox');
		var selectAllBtn = document.getElementById('cv-select-all-btn');
		var deselectAllBtn = document.getElementById('cv-deselect-all-btn');
		var selectedCountEl = document.getElementById('cv-selected-count');
		var toolbarTotalEl = document.getElementById('cv-toolbar-total-count');
		var showingTextEl = document.getElementById('cv-showing-text');
		var cleanupForm = document.getElementById('cv-cleanup-form');
		var deleteBtn = document.getElementById('cv-delete-btn');
		var confirmCb = document.getElementById('cv-confirm-checkbox');

		var candidatesCard = document.getElementById('cv-candidates-card');
		var emptyCard = document.getElementById('cv-empty-card');
		var filterCard = document.getElementById('cv-filter-card');
		var alertsContainer = document.getElementById('cv-alerts-container');

		var metricCandidatesCount = document.getElementById('cv-metric-candidates-count');
		var metricMinAge = document.getElementById('cv-metric-min-age');
		var tabBadgeCleanup = document.getElementById('cv-tab-badge-cleanup');
		var exportAllCount = document.getElementById('cv-export-all-count');
		var exportAllForm = document.getElementById('cv-export-all-form');

		var deleteOverlay = document.getElementById('fahim-delete-anim-overlay');
		var deleteTitle = document.getElementById('fahim-delete-anim-title');
		var deleteMsg = document.getElementById('fahim-delete-anim-msg');
		var deleteProgressFill = document.getElementById('fahim-delete-progress-fill');
		var deleteCountText = document.getElementById('fahim-delete-count-text');
		var deletePercentText = document.getElementById('fahim-delete-percent-text');
		var deleteCancelBtn = document.getElementById('fahim-delete-cancel-btn');

		var cvData = window.cleanverify_data || {};
		var currentMinAge = cvData.current_min_age || 30;
		var currentPerPage = cvData.current_per_page || '100';
		var batchSize = cvData.batch_size || 250;
		var isLiveDeleting = false;

		function getCheckboxes() {
			if (!table) {
				return [];
			}
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
			if (toolbarTotalEl) {
				toolbarTotalEl.textContent = total;
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

		if (table) {
			table.addEventListener('change', function (e) {
				if (e.target && e.target.classList.contains('cv-cb')) {
					updateSelectionState();
				}
			});

			// Allow clicking row to toggle customer checkbox smoothly
			table.addEventListener('click', function (e) {
				var target = e.target;
				if (target.tagName === 'INPUT' || target.tagName === 'BUTTON' || target.tagName === 'A' || target.closest('a') || target.closest('button')) {
					return;
				}
				var row = target.closest('tbody tr');
				if (row) {
					var cb = row.querySelector('.cv-cb');
					if (cb) {
						cb.checked = !cb.checked;
						updateSelectionState();
					}
				}
			});
		}

		updateSelectionState();

		/* ======================================================================
		   1. AJAX Candidate Filtering & Pagination (Zero Page Reloads)
		   ====================================================================== */
		function loadCandidatesAjax(minAge, perPage, isSilent) {
			minAge = parseInt(minAge, 10);
			if (isNaN(minAge) || minAge < 0) {
				minAge = 0;
			}
			currentMinAge = minAge;
			currentPerPage = perPage;

			if (!isSilent) {
				if (candidatesCard) {
					candidatesCard.classList.add('is-loading');
				}
				if (emptyCard) {
					emptyCard.classList.add('is-loading');
				}
			}

			// Update all hidden inputs across page forms
			var minAgeInput = document.getElementById('cv_min_age');
			if (minAgeInput) {
				minAgeInput.value = minAge;
			}
			var hiddenPerPages = document.querySelectorAll('#cv_hidden_per_page, #cv_export_per_page, #cv_cleanup_per_page');
			hiddenPerPages.forEach(function (el) {
				el.value = perPage;
			});
			var hiddenMinAges = document.querySelectorAll('#cv_export_min_age, #cv_cleanup_min_age');
			hiddenMinAges.forEach(function (el) {
				el.value = minAge;
			});

			// Update presets active class
			document.querySelectorAll('.fahim-preset-btn').forEach(function (btn) {
				var bAge = parseInt(btn.getAttribute('data-min-age'), 10);
				if (bAge === minAge) {
					btn.classList.add('is-selected');
				} else {
					btn.classList.remove('is-selected');
				}
			});

			// Update limit pills active class
			document.querySelectorAll('.fahim-limit-pill').forEach(function (pill) {
				var pVal = pill.getAttribute('data-per-page');
				if (pVal === perPage) {
					pill.classList.add('is-active');
				} else {
					pill.classList.remove('is-active');
				}
			});

			var formData = new FormData();
			formData.append('action', 'cleanverify_get_candidates');
			formData.append('cv_filter_nonce', cvData.filter_nonce || '');
			formData.append('cv_min_age', minAge);
			formData.append('cv_per_page', perPage);

			fetch(cvData.ajax_url, {
				method: 'POST',
				body: formData
			})
				.then(function (res) {
					return res.json();
				})
				.then(function (response) {
					if (!isSilent) {
						if (candidatesCard) {
							candidatesCard.classList.remove('is-loading');
						}
						if (emptyCard) {
							emptyCard.classList.remove('is-loading');
						}
					}

					if (response && response.success && response.data) {
						var d = response.data;

						// Update metrics cards
						if (metricCandidatesCount) {
							metricCandidatesCount.textContent = d.total_candidates;
						}
						if (metricMinAge) {
							metricMinAge.textContent = d.min_age + '+ Days';
						}
						if (tabBadgeCleanup) {
							tabBadgeCleanup.textContent = d.total_candidates;
						}
						if (exportAllCount) {
							exportAllCount.textContent = d.total_candidates;
						}
						if (exportAllForm) {
							exportAllForm.style.display = d.total_candidates > 0 ? 'inline-block' : 'none';
						}

						if (d.is_empty) {
							if (candidatesCard) {
								candidatesCard.style.display = 'none';
							}
							if (emptyCard) {
								emptyCard.style.display = '';
							}
						} else {
							if (emptyCard) {
								emptyCard.style.display = 'none';
							}
							if (candidatesCard) {
								candidatesCard.style.display = '';
							}
							if (tableBody) {
								tableBody.innerHTML = d.rows_html;
							}
							if (showingTextEl) {
								var showingStr = (cvData.i18n && cvData.i18n.showing_text) || 'Showing %1$d of %2$d candidates';
								showingTextEl.textContent = showingStr.replace('%1$d', d.displayed_candidates).replace('%2$d', d.total_candidates);
							}
						}

						updateSelectionState();

						// Update URL history without page reload
						if (window.history && window.history.pushState) {
							var url = new URL(window.location.href);
							url.searchParams.set('tab', 'cleanup');
							url.searchParams.set('cv_min_age', minAge);
							url.searchParams.set('cv_per_page', perPage);
							window.history.pushState({ tab: 'cleanup', cv_min_age: minAge, cv_per_page: perPage }, '', url.toString());
						}
					}
				})
				.catch(function (err) {
					if (!isSilent) {
						if (candidatesCard) {
							candidatesCard.classList.remove('is-loading');
						}
						if (emptyCard) {
							emptyCard.classList.remove('is-loading');
						}
					}
					// eslint-disable-next-line no-console
					console.error('CleanVerify filter error:', err);
				});
		}

		// Preset Pills click delegation
		document.addEventListener('click', function (e) {
			var presetBtn = e.target.closest('.fahim-preset-btn');
			if (presetBtn) {
				e.preventDefault();
				var pAge = presetBtn.getAttribute('data-min-age');
				loadCandidatesAjax(pAge, currentPerPage, false);
				return;
			}

			var limitPill = e.target.closest('.fahim-limit-pill');
			if (limitPill) {
				e.preventDefault();
				var pLimit = limitPill.getAttribute('data-per-page');
				loadCandidatesAjax(currentMinAge, pLimit, false);
				return;
			}

			var checkAll0dBtn = e.target.closest('#cv-check-all-0d-btn');
			if (checkAll0dBtn) {
				e.preventDefault();
				loadCandidatesAjax(0, currentPerPage, false);
				return;
			}
		});

		// Custom Days Filter Form submission without page reload
		var filterForm = document.getElementById('cv-filter-form');
		if (filterForm) {
			filterForm.addEventListener('submit', function (e) {
				e.preventDefault();
				var minAgeInput = document.getElementById('cv_min_age');
				var ageVal = minAgeInput ? minAgeInput.value : 0;
				loadCandidatesAjax(ageVal, currentPerPage, false);
			});
		}

		/* ======================================================================
		   2. Interactive Live Batch Deletion (Zero Page Reloads)
		   ====================================================================== */
		function showAlert(type, message) {
			if (!alertsContainer) {
				return;
			}
			var icon = type === 'success' ? '✅' : '⚠️';
			var alertDiv = document.createElement('div');
			alertDiv.className = 'fahim-alert fahim-alert-' + type + ' fahim-alert-animated';
			alertDiv.innerHTML = '<span>' + icon + '</span><span>' + message + '</span>';
			alertsContainer.appendChild(alertDiv);

			// Scroll into view if needed
			alertDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
		}

		function clearAlerts() {
			if (alertsContainer) {
				alertsContainer.innerHTML = '';
			}
		}

		// Prevent full page reload on cleanup form submission and run fast live AJAX batch deletion
		if (cleanupForm) {
			cleanupForm.addEventListener('submit', function (e) {
				var submitter = e.submitter;
				if (submitter && submitter.value === 'export_csv') {
					return true;
				}
				e.preventDefault();
				if (deleteBtn) {
					deleteBtn.click();
				}
			});
		}

		if (deleteBtn) {
			deleteBtn.addEventListener('click', function (e) {
				e.preventDefault();

				var checkboxes = getCheckboxes();
				var checked = checkboxes.filter(function (cb) { return cb.checked; });

				if (checked.length === 0) {
					window.alert((cvData.i18n && cvData.i18n.select_at_least_one) || 'Please select at least one customer account to delete.');
					return false;
				}

				if (confirmCb && !confirmCb.checked) {
					confirmCb.focus();
					window.alert((cvData.i18n && cvData.i18n.confirm_checkbox) || 'Please check the confirmation box before deleting accounts.');
					return false;
				}

				var confirmMsgTpl = (cvData.i18n && cvData.i18n.confirm_delete) || 'Permanently delete %d customer account(s)? This cannot be undone.';
				var confirmMsg = confirmMsgTpl.replace('%d', checked.length);
				if (!window.confirm(confirmMsg)) {
					return false;
				}

				// Collect selected user IDs
				var userIdsToDelete = checked.map(function (cb) {
					return parseInt(cb.value, 10);
				});

				startLiveBatchDeletion(userIdsToDelete);
			});
		}

		function startLiveBatchDeletion(userIds) {
			var total = userIds.length;
			var processed = 0;
			var remainingIds = userIds.slice();
			var totalBatches = Math.ceil(total / batchSize);
			var currentBatchIdx = 0;
			isLiveDeleting = true;

			clearAlerts();

			// Show animated HUD overlay
			if (deleteOverlay) {
				deleteOverlay.style.display = 'flex';
				deleteOverlay.setAttribute('aria-hidden', 'false');
				if (deleteTitle) {
					deleteTitle.textContent = 'Deleting Customer Records...';
				}
				if (deleteProgressFill) {
					deleteProgressFill.style.width = '0%';
				}
				if (deleteCountText) {
					deleteCountText.textContent = '0 / ' + total;
				}
				if (deletePercentText) {
					deletePercentText.textContent = '0%';
				}
				if (deleteMsg) {
					var batchMsgTpl = (cvData.i18n && cvData.i18n.batch_progress) || 'Processing batch %1$d of %2$d...';
					deleteMsg.textContent = batchMsgTpl.replace('%1$d', '1').replace('%2$d', totalBatches);
				}
			}

			// Hook overlay stop button
			if (deleteCancelBtn) {
				deleteCancelBtn.onclick = function () {
					isLiveDeleting = false;
					if (deleteOverlay) {
						deleteOverlay.style.display = 'none';
					}
					var stopMsgTpl = (cvData.i18n && cvData.i18n.stopped_early) || 'Deletion stopped. %d account(s) were deleted.';
					showAlert('error', stopMsgTpl.replace('%d', processed));
				};
			}

			function processNextBatch() {
				if (!isLiveDeleting || remainingIds.length === 0) {
					finishDeletion();
					return;
				}

				currentBatchIdx++;
				var batch = remainingIds.splice(0, batchSize);

				if (deleteMsg) {
					var batchMsgTpl = (cvData.i18n && cvData.i18n.batch_progress) || 'Processing batch %1$d of %2$d...';
					deleteMsg.textContent = batchMsgTpl.replace('%1$d', currentBatchIdx).replace('%2$d', totalBatches);
				}

				var formData = new FormData();
				formData.append('action', 'cleanverify_delete_batch');
				formData.append('cv_nonce', cvData.delete_nonce || '');
				batch.forEach(function (id) {
					formData.append('cv_user_ids[]', id);
				});

				fetch(cvData.ajax_url, {
					method: 'POST',
					body: formData
				})
					.then(function (res) {
						return res.json();
					})
					.then(function (response) {
						if (!isLiveDeleting) {
							return;
						}

						if (response && response.success && response.data) {
							var deletedIds = response.data.deleted_ids || [];
							processed += deletedIds.length;

							// Instant single-pass removal of deleted rows from DOM
							for (var d = 0; d < deletedIds.length; d++) {
								var row = document.getElementById('cv-row-' + deletedIds[d]);
								if (row && row.parentNode) {
									row.parentNode.removeChild(row);
								}
							}
							// Recalculate selection state ONCE per batch (prevents thousands of DOM queries)
							updateSelectionState();

							// Update progress HUD
							var percent = Math.min(100, Math.round((processed / total) * 100));
							if (deleteProgressFill) {
								deleteProgressFill.style.width = percent + '%';
							}
							if (deleteCountText) {
								deleteCountText.textContent = processed + ' / ' + total;
							}
							if (deletePercentText) {
								deletePercentText.textContent = percent + '%';
							}

							// Decrement total metrics counters
							if (metricCandidatesCount) {
								var cCount = Math.max(0, parseInt(metricCandidatesCount.textContent, 10) - deletedIds.length);
								metricCandidatesCount.textContent = cCount;
							}
							if (tabBadgeCleanup) {
								var tCount = Math.max(0, parseInt(tabBadgeCleanup.textContent, 10) - deletedIds.length);
								tabBadgeCleanup.textContent = tCount;
							}
							if (exportAllCount) {
								var eCount = Math.max(0, parseInt(exportAllCount.textContent, 10) - deletedIds.length);
								exportAllCount.textContent = eCount;
							}

							if (remainingIds.length > 0) {
								// Zero-delay next batch dispatch with animation frame rendering
								if (window.requestAnimationFrame) {
									window.requestAnimationFrame(function () {
										setTimeout(processNextBatch, 0);
									});
								} else {
									setTimeout(processNextBatch, 0);
								}
							} else {
								finishDeletion();
							}
						} else {
							finishDeletion();
						}
					})
					.catch(function (err) {
						// eslint-disable-next-line no-console
						console.error('Batch deletion error:', err);
						showAlert('error', (cvData.i18n && cvData.i18n.error_deleting) || 'Could not delete batch.');
						finishDeletion();
					});
			}

			function finishDeletion() {
				isLiveDeleting = false;
				if (deleteOverlay) {
					deleteOverlay.style.display = 'none';
				}

				if (confirmCb) {
					confirmCb.checked = false;
				}

				if (processed > 0) {
					var successTpl = (cvData.i18n && cvData.i18n.success_deleted) || 'Successfully deleted %d customer account(s).';
					showAlert('success', successTpl.replace('%d', processed));
				}

				// Check remaining rows in table
				var remainingCheckboxes = getCheckboxes();
				if (remainingCheckboxes.length === 0) {
					if (candidatesCard) {
						candidatesCard.style.display = 'none';
					}
					if (emptyCard) {
						emptyCard.style.display = '';
					}
					if (exportAllForm) {
						exportAllForm.style.display = 'none';
					}
				}

				updateSelectionState();
			}

			processNextBatch();
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initCleanVerifyAdmin);
	} else {
		initCleanVerifyAdmin();
	}
})();
