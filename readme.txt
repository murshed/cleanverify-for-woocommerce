=== CleanVerify for WooCommerce ===
Contributors: fahimmurshed
Author URI: https://fahimm.com
Tags: woocommerce, customer cleanup, verification, users, orders
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

All-in-one customer verification, anti-fraud protection, and inactive account cleanup for WooCommerce.

== Description ==

**CleanVerify for WooCommerce** helps store owners protect their stores and keep customer databases lean, fast, and free of spam. Find customer accounts that have never placed an order, review detailed metrics, export backups to CSV, and safely prune inactive bloat.

These zero-order accounts often stem from spam bots, disposable email signups, abandoned carts, or test accounts. CleanVerify shows an interactive, reviewable dashboard in wp-admin so you retain total control over which accounts to export or remove.

**Features**

* **Safe Inactive Customer Detection:** Accurately lists "Customer" role users with zero orders, checked against both customer ID and guest billing emails.
* **Instant CSV Export:** Export non-purchasing customer lists directly to CSV (supporting both "Export All" and "Export Selected") with Excel-compatible UTF-8 BOM encoding.
* **Dark / Light Mode Theme:** Modern card dashboard with global dark/light mode toggle that remembers your preferences.
* **Inactivity Buffer:** Configurable age buffer so newly registered customers who haven't finished shopping aren't flagged.
* **Role Safety Shield:** Automatically protects Administrators, Editors, and Shop Managers from accidental deletion.
* **Dedicated "About Developer" Hub:** Integrated tab showcasing custom engineering services, skills, and direct project contact.
* **Enterprise-grade Security:** Full WordPress nonce verification, user capability checks (`delete_users`), and confirmation dialogs.

Requires WooCommerce to be installed and active.

== Installation ==

1. Upload the `cleanverify-for-woocommerce` folder to `/wp-content/plugins/`, or install it directly through the Plugins screen in wp-admin.
2. Activate the plugin through the "Plugins" screen.
3. Navigate to **Users → CleanVerify**.
4. Review the candidate list, filter by account age, export a CSV backup, or confirm deletion of selected accounts.

== Frequently Asked Questions ==

= Does CleanVerify delete accounts automatically? =

No. CleanVerify only removes accounts you manually review, select, and explicitly confirm on the admin dashboard.

= Can I export customer data before deleting? =

Yes. You can export all filtered candidates or select specific customer checkboxes and click "Export Selected to CSV".

= Does it check for guest orders? =

Yes. CleanVerify verifies orders linked to the user's account ID as well as any orders placed with the same billing email, ensuring past guest checkout customers are never flagged.

= Can I recover a deleted account? =

No, deletion in the free version is permanent. Always export a CSV backup before deleting accounts.

== Changelog ==

= 1.0.0 =
* Initial release of CleanVerify for WooCommerce.
* Modern dashboard design with dark/light mode toggle.
* CSV Export feature with UTF-8 BOM for Excel compatibility.
* Tabbed navigation with "About Developer" portfolio and direct contact channels.