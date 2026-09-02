# CleanVerify for WooCommerce

[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-Compatible-purple.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4.svg)](https://www.php.net/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

**CleanVerify for WooCommerce** is an all-in-one customer verification, anti-fraud protection, and inactive account cleanup plugin for WooCommerce stores. It helps store owners keep their customer databases lean, fast, and free of spam.

---

## 🚀 Key Features

* **🔍 Smart Inactive Customer Detection:** Accurately identifies customer accounts with zero orders. Checks both customer ID and guest billing emails so real customers who checked out as guests are never falsely flagged.
* **📥 Instant CSV Export:** Export non-purchasing customer lists directly to CSV (supporting both *Export All* and *Export Selected*) with Excel-compatible UTF-8 BOM encoding.
* **🌗 Dark & Light Mode Theme:** Modern card dashboard with global dark/light mode toggle that remembers admin preferences.
* **⏱️ Configurable Inactivity Buffer:** Filter accounts by registration age (e.g., older than 30, 60, or 90 days) so new signups still shopping aren't flagged.
* **🛡️ Role Safety Shield:** Automatically protects Administrators, Editors, Authors, and Shop Managers from accidental deletion.
* **🔒 Enterprise-Grade Security:** Full WordPress nonce verification, user capability checks (`delete_users`), and confirmation dialogs before any bulk actions.
* **👨‍💻 Dedicated Developer Hub:** Integrated tab showcasing custom engineering services, skills, and direct contact channels.

---

## 📋 Requirements

* **WordPress:** 6.0 or higher
* **WooCommerce:** Active installation
* **PHP:** 7.4 or higher

---

## 📦 Installation

1. Download or clone this repository:
   ```bash
   git clone https://github.com/murshed/cleanverify-for-woocommerce.git
   ```
2. Upload the `cleanverify-for-woocommerce` folder to your WordPress plugins directory (`/wp-content/plugins/`).
3. Activate the plugin via the **Plugins** menu in WordPress wp-admin.
4. Navigate to **Users → CleanVerify** to access the dashboard.

---

## 💡 How It Works

1. **Scan & Filter:** The dashboard scans for user accounts with the "Customer" role that have zero associated orders.
2. **Review Candidates:** Filter candidates by account age buffer or search by email/username.
3. **Backup / Export:** Export candidate data to CSV anytime before performing modifications.
4. **Clean Safely:** Select accounts to remove and confirm deletion. CleanVerify handles cascading cleanup safely within WordPress standards.

---

## ❓ Frequently Asked Questions

#### Does CleanVerify delete accounts automatically?
No. CleanVerify only removes accounts that you manually review, select, and explicitly confirm on the admin dashboard.

#### Can I export customer data before deleting?
Yes. You can export all filtered candidates or select specific customer checkboxes and click "Export Selected to CSV".

#### Does it check for guest orders?
Yes. CleanVerify verifies orders linked to the user's account ID as well as any orders placed with the same billing email, ensuring past guest checkout customers are never flagged.

---

## 📄 License

This plugin is licensed under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html).

---

## 👤 Author

**Fahim Murshed**
* Website: [fahimm.com](https://fahimm.com)
* GitHub: [@murshed](https://github.com/murshed)
