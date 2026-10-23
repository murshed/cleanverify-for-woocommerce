# 📋 CleanVerify Core (Free) - Changelog

All notable changes, releases, and updates for **CleanVerify for WooCommerce** (Free Core) are documented in this file.

---

## ⚡ [1.0.1] - High-Performance Batch Deletion & Timeout Fix

### 🛠️ Bug Fixes
* **Fix (HTTP 500 Execution Timeout):** Resolved fatal PHP script timeouts and HTTP 500 errors when selecting and deleting large customer batches (500+ or 1,000+ candidate accounts).

### 🚀 Performance & Optimizations
* **1.46ms Single-Pass Indexed Order Lookup:** Replaced nested `wc_get_orders()` customer loops with indexed single-pass SQL queries across WooCommerce HPOS (`wc_orders`) and Classic Postmeta (`_customer_user`, `_billing_email`) with O(1) in-memory hash map lookup. Reduced scanning time from 30+ seconds to under 5ms.
* **Direct SQL Batch Deletion Engine:** Implemented high-speed direct SQL chunked deletions (250 records per query) across `usermeta`, `users`, and WooCommerce lookup tables (`wc_customer_lookup`, `woocommerce_downloadable_product_permissions`).
* **100-Account Live AJAX Chunks:** Scaled the client-side batch deletion worker to 100 accounts per request with real-time animated HUD progress overlay, completing 1,000 account cleanups in ~1 second with zero page reloads.
* **Full Cache & Hook Integrity:** Maintained WordPress object cache invalidation (`clean_user_cache`, `wp_cache_delete`) and lifecycle action dispatches (`delete_user`, `deleted_user`, `cleanverify_customer_deleted`).

### 🛡️ Architecture & Integrity
* **Strict Free vs. Pro Separation:** Removed Action Scheduler background queueing and workers from Free Core to maintain a clear boundary with CleanVerify Pro's automated recurring background pruning scheduler.

---

## 🚀 [1.0.0] - Initial Release

* **Smart Zero-Order Customer Detection:** Safe inactive customer detection cross-referenced with order history and guest billing emails.
* **Inactivity Age Thresholds:** 1-click filter presets for 30, 60, 90, 180, and 0 days (all time).
* **Instant CSV Export:** Export filtered or selected customer data to CSV with UTF-8 BOM encoding.
* **Modern Unified Dashboard:** Responsive card-based interface with instant tab switching and zero page reloads.
* **Hardcoded Role Safety Shield:** Automatic protection preventing administrators and shop managers from being modified.
* **WooCommerce HPOS Ready:** Full native support for High-Performance Order Storage.
