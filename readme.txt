=== Live Gold Price ===
Contributors: amirmarandi
Donate link: https://kourox.ir
Tags: woocommerce, gold-price, live-price, coin-price, currency
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.5.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automate live gold and coin prices in WooCommerce via real-time market API with dynamic wage and tax formulas without manual updates or site slowdown.

== Description ==

Persian Live Gold Price for WooCommerce connects your store to real-time gold and coin market APIs, automatically synchronizing product catalog pricing with current market fluctuations. Engineered specifically for jewelry shops, goldsmiths, and bullion dealers, the plugin dynamically calculates retail product valuations using precise formula variables: raw gold weight, manufacturing wages (Ojrat), merchant profit margins, and value-added tax (VAT).

The plugin supports both simple and variable WooCommerce products, recalculating prices seamlessly across product catalog pages, variation selection dropdowns, shopping carts, and checkout totals. Built on an asynchronous REST API polling architecture and smart 55-second transient caching with persistent fallback storage, it guarantees accurate real-time pricing without server performance degradation or checkout disruptions.

* **Automated Market Synchronization:** Regularly queries financial market endpoints via background WP-Cron at 1-minute intervals.
* **Support for Major Gold & Coin Types:** Built-in handling for 18-Karat Gold, Emami Coins, Bahar Azadi Coins, Half Coins, Quarter Coins, and Gerami Coins.
* **3 Manufacturing Wage Models (Ojrat):** Compute manufacturing fees as a percentage of raw gold, fixed cost per gram, or flat fee per item.
* **Hierarchical Profit & Tax Engine:** Establish global store-wide defaults for profit margin and VAT tax with granular product-level and variation-level overrides.
* **Dynamic Cart & Checkout Valuation:** Automatically adjusts line-item prices at priority 9999 right before checkout totals are determined.
* **Zero-Downtime Failover & Transient Cache:** Instant local fallback to the most recent verified price during external API outages.
* **WooCommerce HPOS & Dark Mode Compatibility:** Fully compatible with High-Performance Order Storage (HPOS) and themes featuring dark mode palettes.

---

=== معرفی افزونه ===

نوسانات شدید و لحظه‌ای قیمت طلا و مسکوکات در بازار ایران، تغییر دستی قیمت‌ها را در فروشگاه‌های اینترنتی ووکامرس غیرممکن ساخته است. افزونه «قیمت لحظه‌ای طلا برای ووکامرس» (Live Gold Price) پاسخی پایدار و استاندارد به دغدغه طلافروشان، کارگاه‌های ساخت طلا و صرافی‌های آنلاین است که با اتصال مستقیم به وب‌سرویس‌های استعلام نرخ لحظه‌ای طلا و ارز، تمامی قیمت‌های فروشگاه را همگام با نوسانات بازار و بر اساس فرمول‌های رسمی صنف طلا و جواهر به‌روزرسانی می‌کند.

این افزونه از انواع مختلف طلا و سکه (طلای ۱۸ عیار، سکه امامی، سکه بهار آزادی، نیم سکه، ربع سکه و سکه گرمی) پشتیبانی می‌کند. معماری نرم‌افزاری پلاگین با تکیه بر کشینگ هوشمند ترنزینت (Transient Caching)، فراخوانی نامتقارن از طریق REST API اختصاصی و اتصال به هوک‌های اصلی محاسبات ووکامرس، بدون کوچک‌ترین افت سرعت در سرور یا مسدودسازی دیتابیس (Database Blocking)، خریدی مطمئن را برای کاربران رقم می‌زند.

=== امکانات و ویژگی‌های تخصصی افزونه ===
* **اتصال به وب‌سرویس و دریافت خودکار نرخ‌ها:** دریافت خودکار آخرین قیمت‌های زنده طلا و سکه از طریق زمان‌بندی پس‌زمینه (WP-Cron) در فواصل ۱ دقیقه‌ای.
* **فرمول‌های ۳ گانه محاسبه اجرت ساخت:** امکان محاسبه اجرت بر اساس درصد ارزش طلای خام، مبلغ ثابت به ازای هر گرم یا مبلغ ثابت کل برای هر قطعه کار.
* **مدیریت پیشرفته سود فروشنده و مالیات بر ارزش افزوده:** اعمال درصد سود و مالیات (VAT) به‌صورت سراسری در تنظیمات یا اختصاصی برای هر محصول و متغیر.
* **پشتیبانی کامل از محصولات متغیر (Variable Products):** قابلیت تعیین عیار، وزن بر حسب گرم، اجرت و سود مجزا برای ویژگی‌های مختلف محصول (مانند سایز انگشتر یا وزن زنجیر).
* **به‌روزرسانی لحظه‌ای سبد خرید و تسویه حساب:** بازنویسی آنی مبالغ سبد خرید (Cart) و برگه پرداخت (Checkout) با اولویت اجرایی بالا برای پیشگیری از نوسان قیمت در فاصله سفارش تا پرداخت.
* **سیستم پشتیبان و تضمین عدم قطعی (Persistent Failover):** در صورت کندی یا قطعی موقت وب‌سرویس خارجی، افزونه به‌طور خودکار آخرین نرخ معتبر ذخیره‌شده را از دیتابیس فراخوانی می‌کند تا فرآیند فروش مختل نشود.
* **سازگاری با قالب‌های تیره (Dark Mode) و طراحی RTL:** اصلاح‌کننده داخلی کنتراست رنگ متون و لیست‌های بازشونده (Select/Select2) برای خوانایی کامل در تم‌های تیره.
* **پشتیبانی از مخزن سفارشات پرسرعت ووکامرس (HPOS):** سازگاری تاییدشده با High-Performance Order Storage بدون توابع منسوخ.

=== 💖 حمایت از پروژه ===

این ابزار کاملاً رایگان و متن‌بازه. اگه کارتون رو راه انداخت و براتون مفید بود، با زدن یه ⭐ روی گیت‌هاب یا حمایت رمزارزی می‌تونید به ادامه‌ی این مسیر انرژی بدید:

* 🔹 **USDT (BEP20):** `0xd593ae9D32bEA690EC62460C54BF3951aFFF7803`
* 🔸 **USDT (TRC20):** `THaaHzoTwXfUfcrtYTDXRsMmk9qhnXa56M`
* 💳 **شماره کارت:** `5022291619360157`

== Frequently Asked Questions ==

= آیا این افزونه باعث کندی سرعت لود وب‌سایت می‌شود؟ =
خیر. افزونه از سیستم کش ترنزینت ۵۵ ثانیه‌ای و زمان‌بندی WP-Cron استفاده می‌کند؛ بنابراین هیچ درخواست خارجی به وب‌سرویس در زمان بارگذاری صفحه توسط کاربر ارسال نمی‌شود و تاثیری بر زمان پاسخ سرور (TTFB) ندارد.

Does Live Gold Price slow down website page load times?
No. The plugin utilizes a 55-second transient caching system and background WP-Cron scheduling. Visitors never initiate external API calls during page loads, ensuring zero impact on Time to First Byte (TTFB).

= در صورت قطعی یا خطای وب‌سرویس، قیمت‌ها چگونه محاسبه می‌شوند؟ =
افزونه دارای مکانیزم پشتیبان (Fallback) دائمی در دیتابیس است. در شرایط بروز قطعی، اختلال اینترنت بین‌الملل یا خطای ارائه‌دهنده وب‌سرویس، آخرین نرخ موفق بدون وقفه برای محاسبات سبد خرید و پرداخت استفاده می‌شود.

How are product prices calculated if the external API is down?
The plugin maintains a persistent backup snapshot in the database. During network disruptions or API downtime, it instantly uses the last successfully verified rates to keep the storefront fully operational.

= آیا امکان تنظیم وزن و اجرت متفاوت برای محصولات متغیر وجود دارد؟ =
بله. در تمامی محصولات متغیر ووکامرس، تب اختصاصی افزونه در تنظیمات هر متغیر تعبیه شده و می‌توانید برای هر متغیر (مثلاً سایزهای مختلف یک دستبند)، وزن، نوع طلا، اجرت، سود و مالیات مجزا تعیین کنید.

Can I configure different weights and wages for product variations?
Yes. Each variation in WooCommerce contains independent input fields for gold type, weight in grams, manufacturing wage type, seller profit, and tax rate.

= آیا افزونه با سیستم جدید سفارشات ووکامرس (HPOS) سازگار است؟ =
بله. افزونه قیمت لحظه‌ای طلا سازگاری کامل خود را با High-Performance Order Storage و جداول سفارشی ووکامرس اعلام کرده و از تمامی آزمون‌های Plugin Check عبور کرده است.

Is the plugin compatible with WooCommerce HPOS (High-Performance Order Storage)?
Yes. The plugin is fully certified for WooCommerce HPOS compatibility and adheres strictly to WordPress.org Plugin Check guidelines.

== Installation ==

= Automatic Installation (نصب خودکار از پیشخوان وردپرس) =
1. In your WordPress Admin Dashboard, navigate to **Plugins > Add New** (پیشخوان > افزونه‌ها > افزودن).
2. Search for `Live Gold Price` in the search bar.
3. Click **Install Now** (هم‌اکنون نصب کن) and then click **Activate** (فعال‌سازی).
4. Go to **قیمت زنده طلا** in your admin menu and enter your API credentials.

= Manual Installation (نصب دستی از طریق فایل ZIP یا FTP) =
1. Download the plugin archive (`live-gold-price.zip`).
2. Upload the extracted `live-gold-price` directory to `/wp-content/plugins/` on your web server.
3. Activate the plugin through the **Plugins** menu in WordPress.
4. Navigate to **قیمت زنده طلا** to set your API Key, base profit, and VAT percentages.
5. Edit any product, navigate to the **قیمت زنده طلا** product data tab, and enable real-time pricing.

== Screenshots ==

1. Live Gold Price Dashboard - محیط کاربری و داشبورد نمایش نرخ لحظه‌ای طلا
2. screenshot-2.png - Simple Product Configuration Tab: Purity selection, weight in grams, wage models, and custom profit overrides. - تب تنظیمات محصول ساده: انتخاب عیار یا نوع سکه، وزن، نحوه محاسبه اجرت ساخت و سود اختصاصی.
3. screenshot-3.png - Variable Product Attributes: Granular weight, purity, and manufacturing wage controls per variation item. - تنظیمات متغیرهای محصول: کنترل عیار، وزن، اجرت و سود برای هر متغیر به‌صورت مستقل.
4. screenshot-4.png - Frontend Product Display: Live pulsing indicator badge, dynamic variation price updates, and price formatting in Tomans. - نمایش فرانت‌اند در صفحه محصول: نشانگر زنده، محاسبه آنی قیمت با تغییر متغیرها و فرمت استاندارد تومان.
5. screenshot-5.png - Cart & Checkout Dynamic Recalculation: Automatic totals recalculation reflecting current gold rates prior to payment. - برگه سبد خرید و تسویه حساب: محاسبه خودکار و به‌روز اقلام سبد خرید بر مبنای آخرین نرخ بازار قبل از پرداخت.

== Changelog ==

= 1.5.3 =
* Production release prepared for official WordPress.org Plugin Directory submission.
* Integrated real-time gold and coin API connector with 1-minute WP-Cron scheduling.
* Implemented 3 standard manufacturing wage calculation models (percentage, per-gram, and flat rate).
* Added non-blocking REST API endpoint for asynchronous frontend price synchronization.
* Added 55-second transient caching with persistent database failover protection.
* Added native compatibility with WooCommerce High-Performance Order Storage (HPOS).
* Added automatic dark mode text contrast and selector contrast fixes.
* Fully audited and validated for WordPress Plugin Check (PCP) and security standards.
