# 🗺️ CleanVerify Core (Free) - Product Roadmap

This document outlines upcoming open-source enhancements, performance upgrades, and filtering tools planned for **CleanVerify for WooCommerce** (Free Core).

---

## 🚀 Version 1.1.0 - Enhanced Filtering & Export Suite

### 🔍 Advanced Date & Inactivity Range Selector
* **Custom Registration Date Ranges:** Filter cleanup candidates by precise start and end registration dates.
* **Days-Since-Last-Login Threshold:** Isolate accounts inactive for specified day counts (e.g., 90, 180, 365+ days).

### 📥 Custom CSV Export Column Selector
* **Selectable Export Fields:** Customize export columns including User ID, Email, Registered Date, Last Login, Order Count, and Total Spend before downloading.
* **Streamlined CSV Downloads:** Client-side blob download optimized for quick audit backups.

### 📊 Pre-Deletion Summary Breakdown
* **Storage Footprint Estimator:** Estimated database space to be reclaimed before executing batch deletion.
* **User Role Breakdown:** Visual breakdown of user roles matched by current cleanup criteria.

---

## ⚡ Version 1.2.0 - Performance & Core Scalability

### ⚡ High-Volume Asynchronous Batch Scanner
* **Background Chunk Processing:** High-performance AJAX worker for scanning stores with 100,000+ registered customer accounts without PHP execution timeouts.
* **Live Progress Bar:** Real-time visual progress counter during large-scale database scans and deletions.

### 🛡️ Custom User Role Exclusion Whitelist
* **Granular Role Protection:** Interactive whitelist checklist protecting custom WordPress roles (Wholesaler, B2B, Affiliate, Vendor) from accidental cleanup.
* **Domain Search & Instant Filter:** Live instant search box in the candidate table to quickly filter accounts by specific email domains (e.g., `@example.com`).

---

## 🔌 Version 1.3.0 - Developer & CLI Extensibility

### 💻 Native WP-CLI Integration
* **Command Line Management:** Dedicated WP-CLI commands (`wp cleanverify scan`, `wp cleanverify prune --dry-run`, `wp cleanverify export`) for automated server cron jobs and DevOps workflows.

### 🪝 Extended Action Hooks & Filter Matrix
* **Developer Hooks:** New lifecycle hooks for external CRM synchronization and custom pre-deletion audit logging.
