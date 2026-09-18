<p align="center">
  <img src="assets/icon-256x256.png" width="128" height="128" alt="Persian Live Gold Price Logo" style="border-radius: 20px;">
</p>

# Persian Live Gold Price for WooCommerce | افزونه قیمت لحظه‌ای طلا و سکه ووکامرس

<p align="center">
  <img src="assets/banner-1544x500.jpg" alt="Persian Live Gold Price Banner" width="100%">
</p>

[![WordPress Plugin Version](https://img.shields.io/badge/WordPress.org-v1.5.3-blue.svg)](https://wordpress.org/plugins/live-gold-price/)
[![Tested up to WordPress](https://img.shields.io/badge/WordPress-5.8%20--%206.7-brightgreen.svg)](https://wordpress.org/plugins/live-gold-price/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%20--%208.3-indigo.svg)](https://wordpress.org/plugins/live-gold-price/)
[![License](https://img.shields.io/badge/License-GPLv2%2B-orange.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WooCommerce HPOS](https://img.shields.io/badge/WooCommerce-HPOS%20Ready-success.svg)](https://woocommerce.com/)

**Persian Live Gold Price for WooCommerce** یک افزونه تخصصی، امن و استاندارد جهت اتصال مستقیم فروشگاه‌های اینترنتی طلا و جواهر به وب‌سرویس‌های رسمی استعلام نرخ لحظه‌ای طلا و انواع مسکوکات بانکی است. این افزونه قیمت تمامی محصولات ساده و متغیر را مطابق فرمول‌های مصوب اتحادیه طلا (وزن، اجرت ساخت، سود فروشنده و مالیات بر ارزش افزوده) به‌صورت خودکار در صفحه محصول، سبد خرید و برگه پرداخت به‌روزرسانی می‌کند.

> 📥 **دانلود رایگان از مخزن رسمی وردپرس:**  
> [صفحه رسمی افزونه در WordPress.org](https://wordpress.org/plugins/live-gold-price/)

---

## فهرست مطالب (Table of Contents)
- [چگونه قیمت لحظه‌ای طلا و سکه را در ووکامرس خودکار کنیم؟](#چگونه-قیمت-لحظهای-طلا-و-سکه-را-در-ووکامرس-خودکار-کنیم)
- [Real-Time Gold & Coin Market API Integration](#real-time-gold--coin-market-api-integration)
- [پیش‌نمایش محیط افزونه (Screenshots)](#پیشنمایش-محیط-افزونه-screenshots)
- [معماری فنی و پایداری عملکرد (Technical Architecture)](#معماری-فنی-و-پایداری-عملکرد-technical-architecture)
- [فرمول‌های استاندارد محاسبه قیمت طلا](#فرمولهای-استاندارد-محاسبه-قیمت-طلا)
- [هوک‌ها و مستندات توسعه‌دهندگان (Developer API & Hooks)](#هوکها-و-مستندات-توسعەدهندگان-developer-api--hooks)
- [نصب و راه‌اندازی (Installation)](#نصب-و-راهاندازی-installation)
- [💖 حمایت از پروژه (Support the Project)](#-حمایت-از-پروژه-support-the-project)
- [مشارکت و توسعه (Contributing)](#مشارکت-و-توسعه-contributing)
- [لایسنس (License)](#لایسنس-license)

---

## چگونه قیمت لحظه‌ای طلا و سکه را در ووکامرس خودکار کنیم؟

مدیریت دستی قیمت‌ها در بازار پرنوسان طلا ریسک زیان مالی را به شدت افزایش می‌دهد. این افزونه فرآیند قیمت‌گذاری را کاملاً خودکار می‌سازد:

1. **اتصال به وب‌سرویس نرخ طلا:** نرخ‌های بازار در پس‌زمینه توسط کرون‌جاب وردپرس استعلام و ذخیره می‌شوند.
2. **محاسبه دقیق فرمول طلا:** سیستم بر اساس وزن محصول و ۳ مدل اجرت ساخت (درصدی، گرمی یا ثابت)، سود فروشنده و مالیات قانونی را اعمال می‌کند.
3. **همگام‌سازی آنی سبد خرید:** مبالغ سبد خرید و تسویه حساب با بالاترین اولویت اجرایی (`priority: 9999`) بلافاصله قبل از پرداخت نهایی با آخرین نرخ استعلام‌شده به‌روزرسانی می‌شوند تا از مغایرت قیمت جلوگیری گردد.

---

## Real-Time Gold & Coin Market API Integration

Designed specifically for online jewelry shops, goldsmiths, and bullion merchants operating with WooCommerce:

* **Supported Assets:**
  * 18-Karat Gold (طلای ۱۸ عیار)
  * Emami Coin (سکه تمام طرح امامی)
  * Bahar Azadi Coin (سکه تمام بهار آزادی)
  * Half Coin (نیم سکه)
  * Quarter Coin (ربع سکه)
  * Gerami Coin (سکه گرمی)
* **Variable Products Synchronization:** Fully calculates distinct attributes (e.g. ring sizes, custom weight variations) dynamically via AJAX and lightweight REST API endpoints.
* **HPOS Compatible:** Formally certified for WooCommerce High-Performance Order Storage (Custom Order Tables).

---

## پیش‌نمایش محیط افزونه (Screenshots)

### پیشخوان مدیریت و مانیتورینگ نرخ‌های زنده
<p align="center">
  <img src="assets/screenshot-1.png" alt="پیشخوان افزونه قیمت لحظه‌ای طلا" width="100%" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
</p>

---

## معماری فنی و پایداری عملکرد (Technical Architecture)

```
[ External Gold API ]
        │ (WP-Cron 1-Min Interval)
        ▼
[ Transient Cache (55s) ] ──(If API Times Out)──► [ Persistent Backup Snapshot (DB) ]
        │
        ▼
[ Live_Gold_Price_Calculator ]
        │
        ├──► Single Product Page (Instant REST API Polling)
        ├──► Variable Dropdowns (Event: show_variation)
        └──► WooCommerce Cart & Checkout (woocommerce_before_calculate_totals: 9999)
```

### ۱. کشینگ ترنزینت و عدم کاهش سرعت (Zero TTFB Overhead)
برای جلوگیری از افت سرعت وب‌سایت (Time to First Byte) و مسدودسازی سرور، نرخ‌ها به‌مدت **۵۵ ثانیه** در ترنزینت‌های وردپرس (`set_transient`) کش می‌شوند. کاربران عادی هرگز مستقیماً درخواست شبکه خارجی ارسال نمی‌کنند.

### ۲. مکانیزم پایدار پشتیبان دیتابیس (Failover Protection)
در صورت بروز اختلال اینترنت بین‌الملل، قطعی سرور ارائه‌دهنده وب‌سرویس یا اتمام سقف مجاز، سیستم به آخرین نرخ معتبر ثبت‌شده در دیتابیس سوئیچ می‌کند تا فرآیند فروش و ثبت سفارش متوقف نشود.

### ۳. به‌روزرسانی بدون رفرش (Asynchronous REST API)
قیمت‌های صفحات فرانت‌اند از طریق اندپوینت غیراسبک REST (`/wp-json/live-gold-price/v1/prices`) و به صورت نامتقارن استعلام می‌شوند؛ لذا با افزونه‌های کشینگ مانند WP Rocket و LiteSpeed Cache سازگاری ۱۰۰٪ دارد.

---

## فرمول‌های استاندارد محاسبه قیمت طلا

فرمول پیاده‌سازی‌شده در هسته محاسباتی افزونه مطابق استاندارد رسمی صنف طلا و جواهر است:

$$\text{Base Value} = \text{Weight (g)} \times \text{Live Price (per g)}$$

$$\text{Value with Wage} = \text{Base Value} + \text{Ojrat (Percent, Per Gram, or Flat)}$$

$$\text{Profit} = \text{Value with Wage} \times \left(\frac{\text{Profit \%}}{100}\right)$$

$$\text{VAT Tax} = (\text{Value with Wage} + \text{Profit}) \times \left(\frac{\text{Tax \%}}{100}\right)$$

$$\text{Final Price} = \text{round}(\text{Value with Wage} + \text{Profit} + \text{Tax})$$

---

## هوک‌ها و مستندات توسعه‌دهندگان (Developer API & Hooks)

افزونه شامل فیلترها و اکشن‌های استاندارد برای برنامه‌نویسان و توسعه‌دهندگان قالب‌های اختصاصی است:

### فیلتر محاسبه نهایی قیمت (`live_gold_price_calculated_price`)
شما می‌توانید فرمول محاسبات را برای شرایط ویژه (مانند تخفیف‌های خاص، حق بازاریابی یا تخفیف ویژه همکاران) تغییر دهید:

```php
add_filter( 'live_gold_price_calculated_price', 'custom_modify_gold_calculated_price', 10, 6 );
/**
 * Customize the calculated gold product price.
 *
 * @param float $final_price       The final rounded price.
 * @param int   $product_id        Product or Variation ID.
 * @param float $base_gold_value   Raw gold value (Weight * Rate).
 * @param float $wage_amount       Calculated wage (Ojrat).
 * @param float $profit_amount     Calculated seller profit.
 * @param float $tax_amount        Calculated VAT amount.
 * @return float
 */
function custom_modify_gold_calculated_price( $final_price, $product_id, $base_gold_value, $wage_amount, $profit_amount, $tax_amount ) {
    // مثال: کسر ۲ درصد برای مشتریان ویژه
    if ( current_user_can( 'vip_customer' ) ) {
        $final_price = $final_price * 0.98;
    }
    return round( $final_price );
}
```

### اکشن اجرای دستی کرون استعلام قیمت (`live_gold_price_fetch_prices_cron`)
جهت همگام‌سازی از طریق سیستم‌های خارجی، وب‌هوک‌ها یا شل لینوکس:

```bash
# اجرا از طریق WP-CLI در ترمینال سرور
wp cron event run live_gold_price_fetch_prices_cron
```

---

## نصب و راه‌اندازی (Installation)

### روش ۱: نصب مستقیم از پیشخوان وردپرس (توصیه‌شده)
1. وارد پیشخوان وردپرس خود شوید.
2. به بخش **افزونه‌ها > افزودن** مراجعه نمایید.
3. نام `Live Gold Price` را جستجو کنید.
4. روی **هم‌اکنون نصب کن** کلیک کرده و سپس افزونه را فعال نمایید.
5. از منوی پیشخوان، وارد تب **قیمت زنده طلا** شده و کلید وب‌سرویس خود را ثبت کنید.

### روش ۲: نصب از طریق فایل فشرده (GitHub / WordPress.org)
1. فایل `live-gold-price.zip` را از [مخزن رسمی وردپرس](https://wordpress.org/plugins/live-gold-price/) یا [گیت‌هاب](https://github.com/thekourox/live-gold-price) دانلود کنید.
2. پوشه بازشده را در مسیر `/wp-content/plugins/live-gold-price/` هاست خود آپلود نمایید.
3. افزونه را در صفحه مدیریت افزونه‌ها فعال کنید.

---

## 💖 حمایت از پروژه (Support the Project)

این ابزار کاملاً رایگان و متن‌بازه. اگه کارتون رو راه انداخت و براتون مفید بود، با زدن یه ⭐ روی گیت‌هاب یا حمایت رمزارزی می‌تونید به ادامه‌ی این مسیر انرژی بدید:

* 🔹 **USDT (BEP20):** `0xd593ae9D32bEA690EC62460C54BF3951aFFF7803`
* 🔸 **USDT (TRC20):** `THaaHzoTwXfUfcrtYTDXRsMmk9qhnXa56M`
* 💳 **شماره کارت:** `5022291619360157`

---

## مشارکت و توسعه (Contributing)

مشارکت‌ها، گزارش باگ‌ها و ارسال Pull Request همواره مورد استقبال است:
1. این مخزن را Fork کنید.
2. یک برنچ برای قابلیت جدید خود بسازید (`git checkout -b feature/NewFeature`).
3. تغییرات را کامیت کنید (`git commit -m 'Add NewFeature'`).
4. تغییرات را Push کنید (`git push origin feature/NewFeature`).
5. یک Pull Request جدید ارسال نمایید.

---

## لایسنس (License)

این افزونه تحت لایسنس عمومی **GNU General Public License v2.0 or later** منتشر شده است. استفاده، بازنشر و توسعه آن با رعایت شرایط این لایسنس کاملاً آزاد و رایگان است.
