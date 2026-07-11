# Penova Core

Penova Core یک هستهٔ رایگان برای ساخت محصولات Laravel است؛ یک Laravel Product Factory که چیزهای تکراری اغلب پروژه‌ها (احراز هویت، کاربران، نقش‌ها، تنظیمات، نوتیفیکیشن‌ها، لاگ فعالیت، UI و DataTable) را یک‌بار برای همیشه حل می‌کند و آماده است تا ماژول‌های محصولی مانند Store، SMS و Payment روی آن نصب شوند.

Penova Core is a free foundation for building Laravel products. It ships a production-ready Workspace (auth, users, roles, settings, notifications, activity log, UI components and a reusable DataTable) so you can focus on your product modules.

این مخزن فقط Core است؛ هیچ ماژول بیزنسی (فروشگاه، CRM، Booking و غیره) در آن وجود ندارد.  
Store Module و ماژول‌های دیگر به صورت جداگانه نصب می‌شوند.

## ویژگی‌ها (Features)

Authentication  
- جریان کامل ورود، ثبت‌نام (اختیاری)، فراموشی رمز و ریست رمز، روی GuestLayout.  
- Full login, registration (optional), forgot password, and reset flows powered by Laravel auth.

Users and Roles  
- مدیریت کاربران، نقش‌ها و permissionها بدون نیاز به پکیج اضافی.  
- Workspace screens for users, roles and permissions with a simple permission middleware.

Settings and White Label  
- سیستم تنظیمات runtime با SettingsManager.  
- صفحهٔ Settings شامل بخش Branding / White Label است: نام برند، لوگو، رنگ اصلی و متن فوتر را تنظیم می‌کنید و همان را در WorkspaceLayout و صفحهٔ Welcome می‌بینید.

Notifications  
- استفاده از database notifications لاراول به عنوان سطح مشترک نوتیفیکیشن.  
- یک صفحهٔ Notifications و badge اعلان‌ها در WorkspaceLayout برای همهٔ ماژول‌ها قابل استفاده است.

Activity Logs  
- ثبت خودکار created / updated / deleted برای مدل‌ها با trait RecordsActivity.  
- صفحهٔ Logs در میزکار برای مشاهدهٔ فعالیت‌ها.

Workspace UI and Components  
- WorkspaceLayout و GuestLayout آماده، مبتنی بر Vue 3، Inertia 2 و Tailwind 4.  
- کامپوننت‌های مشترک: Button، TextInput، Modal، Toast، Pagination، DataTable.

DataTable Pattern  
- DataTableBuilder در backend برای جست‌وجو، مرتب‌سازی و صفحه‌بندی سمت سرور.  
- DataTable.vue در frontend برای نمایش هر CRUD به صورت جدولی، با search و sort.

Modular Monolith Architecture  
- app/Core شامل تمام قابلیت‌های shared و product-agnostic است.  
- app/Modules برای ماژول‌های محصولی (Store، SMS، Payment و غیره).  
- Core هرگز به Modules وابسته نمی‌شود؛ Modules روی Core ساخته می‌شوند.

## چه چیزهایی داخل Core نیست؟

برای این‌که Core خالص و product-agnostic بماند، موارد زیر عمداً در این مخزن نیستند:

- ماژول‌های بیزنسی مانند Store، CRM، Booking  
- سبد خرید و Checkout  
- درگاه‌های پرداخت  
- سرویس پیامک  
- صفحات frontend دامین‌محور (مانند فروشگاه یا CRM)

این‌ها به صورت ماژول‌های جداگانه روی Core نصب می‌شوند.

## شروع سریع (Quick Start)

نصب

```bash
git clone https://github.com/penova-laravelproductfactory/core.git
cd core

cp .env.example .env
composer install
php artisan key:generate

php artisan migrate --seed

npm install
npm run build   # یا npm run dev برای توسعه
php artisan serve
```

> نکته: `npm run build` و `npm run dev` ابتدا رجیستریِ فرانت‌اندِ ماژول‌ها را می‌سازند؛
> اجرای مستقیمِ `vite build` بدون آن با خطا مواجه می‌شود — همیشه از همین اسکریپت‌ها استفاده کنید.

ورود به میزکار

بعد از اجرای سرور:

- صفحه اصلی (`/`) صفحه Welcome Penova Core را نشان می‌دهد.  
- صفحه ورود (`/login`) فرم login را نمایش می‌دهد.

هویت پیش‌فرض (برای محیط توسعه):

- ایمیل: `operator@example.com`  
- رمز: `password`

بعد از ورود، میزکار را می‌بینید با:

- Workspace  
- Users  
- Roles  
- Settings (شامل White Label / Branding)  
- Notifications  
- Logs  

> اگر در نصب یا ورود مشکلی پیش آمد، [راهنمای عیب‌یابی](docs/guides/troubleshooting-core.md) را ببینید.

## White Label / Branding

در میزکار به Settings بروید و بخش White Label / Branding را باز کنید. می‌توانید این موارد را تنظیم کنید:

- Brand name: نامی که در WorkspaceLayout و صفحهٔ Welcome نمایش داده می‌شود.  
- Logo URL: لینک لوگوی برند (اختیاری).  
- Primary color: رنگ اصلی (برای استفادهٔ آینده در theme).  
- Footer text: متن پایین میزکار و صفحهٔ Welcome.

اگر چیزی تنظیم نکنید، Core از مقادیر پیش‌فرض در config/penova.php استفاده می‌کند.

## افزودن ماژول‌ها (Store، SMS، Payment)

Core از ابتدا برای نصب ماژول‌های بیزنسی آماده است.

ساختار پیشنهادی:

- app/Modules/Store  
- app/Modules/Sms  
- app/Modules/Payment  

هر ماژول:

- یک ServiceProvider دارد که در config/penova.php (آرایه modules) ثبت می‌شود.  
- می‌تواند روت‌های admin و public خود را ثبت کند.  
- می‌تواند منوهای میزکار را توسعه دهد.  
- می‌تواند ویجت‌های Workspace اضافه کند.  
- می‌تواند به Settings و Notifications متصل شود.

نمونه نصب ServiceProvider یک ماژول:

```php
// config/penova.php

'modules' => [
    App\Modules\Store\StoreServiceProvider::class,
    App\Modules\Sms\SmsServiceProvider::class,
    App\Modules\Payment\PaymentServiceProvider::class,
],
```

جزئیات نصب هر ماژول در README مربوط به همان ماژول توضیح داده خواهد شد.

## معماری در یک نگاه (Architecture Overview)

ساختار backend

- app/Core  
  - Auth (login، register، reset)  
  - Users  
  - Roles و Permissions  
  - Settings (+ Branding / White Label)  
  - Notifications  
  - Logs  
  - DataTable  
  - PenovaCoreServiceProvider (ثبت middleware، policies، boot ماژول‌ها از config/penova.php)

- app/Modules  
  - در Core خالی است؛ ماژول‌ها را جداگانه اضافه می‌کنید.

ساختار frontend

- resources/js/Core  
  - Layouts: WorkspaceLayout.vue، GuestLayout.vue  
  - Components: Button، TextInput، Modal، Toast، Pagination، DataTable  
  - Pages: Auth (Login، Register، Reset)، Workspace، Users، Roles، Settings، Logs، Notifications  
  - Welcome.vue: صفحهٔ خوش‌آمد Core در روت `/`

Pages مربوط به Store / SMS / Payment در resources/js/Modules/* قرار خواهند گرفت، نه در Core.

## برای چه کسانی مناسب است؟ (Who is it for?)

- توسعه‌دهندهٔ Laravel که نمی‌خواهد برای هر پروژه احراز هویت، کاربران، نقش‌ها، تنظیمات و لاگ‌ها را از صفر بسازد.  
- کسی که می‌خواهد یک فروشگاه یا محصول دیگری را روی یک هستهٔ تمیز و قابل‌گسترش بسازد.  
- کسی که در آینده به چند محصول/ماژول روی یک Core مشترک فکر می‌کند.

اگر تنها یک اپلیکیشن کوچک و یک‌باره می‌سازید، شاید این سطح از معماری برایتان زیاد باشد.  
اگر به یک هستهٔ مشترک برای چند محصول نیاز دارید، Penova Core برای همین طراحی شده است.

## وضعیت پروژه (Project Status)

- Core features (auth، users، roles، settings، notifications، logs، UI، DataTable) پیاده‌سازی شده‌اند.  
- White Label / Branding در Settings موجود است.  
- ماژول‌های Store, Sms, Payment به عنوان پروژه‌های جداگانه توسعه داده می‌شوند.  
- ماژول‌های CRM / Booking عمداً در Core نیستند و اگر اضافه شوند، به‌صورت ماژول مستقل خواهند بود، نه بخشی از Core.

## لایسنس (License)

نوع لایسنس پروژه (MIT، اختصاصی، یا هر چه که انتخاب می‌کنید) را اینجا بنویسید.

## مشارکت (Contributing)

پیشنهادها، باگ‌ریپورت‌ها و Pull Requestها برای بهبود Core (نه ماژول‌های محصولی) استقبال می‌شوند، به‌خصوص در این بخش‌ها:

- بهبود UX / UI میزکار.  
- افزایش سطح تست‌های یکپارچه برای Core.  
- پیشنهاد برای بهبود قرارداد ماژول‌ها (Store، Sms، Payment).
