<?php

/*
|--------------------------------------------------------------------------
| Core UI messages — Persian (fa) catalog (RFC-005 / D-027)
|--------------------------------------------------------------------------
| Persian is a first-party, opt-in supported locale — never Core's default
| or identity. A deployment selects it with APP_LOCALE=fa. Any key absent
| here falls back to the English base (lang/en/ui.php).
*/

return [
    'common' => [
        'save' => 'ذخیره',
        'cancel' => 'انصراف',
        'logout' => 'خروج',
        'edit' => 'ویرایش',
        'delete' => 'حذف',
        'name' => 'نام',
        'email' => 'ایمیل',
        'role' => 'نقش',
        'back_to_list' => 'بازگشت به فهرست',
        'close' => 'بستن',
    ],

    'status' => [
        'ready' => 'آماده',
        'warning' => 'هشدار',
    ],

    'table' => [
        'search_placeholder' => 'جستجو…',
        'no_results' => 'موردی یافت نشد.',
    ],

    'render' => [
        'widget_missing_before' => 'ویجت «',
        'widget_missing_after' => '» پیدا نشد.',
    ],

    'shell' => [
        'workspace_subtitle' => 'میزکار محصولات شما',
        'tagline' => 'کارخانه ساخت محصولات لاراول',
        'guest_footer' => 'هستهٔ توسعه محصول با لاراول',
    ],

    'nav' => [
        'workspace' => 'میزکار',
        'users' => 'کاربران',
        'roles' => 'نقش‌ها و دسترسی‌ها',
        'settings' => 'تنظیمات',
        'logs' => 'گزارش فعالیت‌ها',
        'notifications' => 'اعلان‌ها',
    ],

    'workspace' => [
        'title' => 'میزکار',
        'subtitle' => 'مدیریت پلتفرم Penova',
    ],

    'users' => [
        'title' => 'کاربران',
        'subtitle' => 'مدیریت حساب‌های کاربری میزکار',
        'new' => 'کاربر جدید',
        'col_created' => 'تاریخ ایجاد',
        'confirm_delete' => 'کاربر «:name» حذف شود؟',
        'create_title' => 'کاربر جدید',
        'edit_title' => 'ویرایش کاربر',
        'edit_document_title' => 'ویرایش کاربر — :name',
        'password' => 'رمز عبور',
        'password_confirm' => 'تکرار رمز عبور',
        'password_new' => 'رمز عبور جدید (برای حفظ رمز فعلی خالی بگذارید)',
        'password_new_confirm' => 'تکرار رمز عبور جدید',
        'roles_legend' => 'نقش‌ها',
        'create_submit' => 'ایجاد کاربر',
        'save_changes' => 'ذخیره تغییرات',
    ],

    'roles' => [
        'title' => 'نقش‌ها و دسترسی‌ها',
        'subtitle' => 'کنترل دسترسی مبتنی بر نقش',
        'new' => 'نقش جدید',
        'edit_title' => 'ویرایش نقش',
        'col_slug' => 'شناسه (slug)',
        'col_users_count' => 'تعداد کاربران',
        'permissions_legend' => 'دسترسی‌ها',
    ],

    'settings' => [
        'title' => 'تنظیمات',
        'subtitle' => 'پیکربندی سایت، قابل ویرایش توسط مدیران',
        'site_name' => 'نام سایت',
        'contact_email' => 'ایمیل تماس',
        'branding_card' => 'White Label / Branding',
        'branding_help' => 'نام برند و برندینگ Core را برای میزکار و صفحهٔ خوش‌آمد تنظیم کنید.',
        'brand_name' => 'نام برند',
        'logo_url' => 'نشانی لوگو',
        'primary_color' => 'رنگ اصلی (hex)',
        'footer_text' => 'متن پاورقی',
        'save' => 'ذخیره تنظیمات',
    ],

    'notifications' => [
        'title' => 'اعلان‌ها',
        'subtitle' => 'اعلان‌های حساب کاربری شما',
        'mark_all_read' => 'علامت‌گذاری همه به‌عنوان خوانده‌شده',
        'mark_read' => 'خوانده شد',
        'empty' => 'فعلاً اعلانی ندارید.',
    ],

    'logs' => [
        'title' => 'گزارش فعالیت‌ها',
        'subtitle' => 'چه کسی، چه کاری، چه زمانی',
        'col_time' => 'زمان',
        'col_user' => 'کاربر',
        'col_action' => 'عملیات',
        'col_subject' => 'موضوع',
        'system' => 'سیستم',
    ],

    'auth' => [
        'sign_in' => 'ورود',
        'login_document_title' => 'ورود به میزکار',
        'password' => 'رمز عبور',
        'password_confirm' => 'تکرار رمز عبور',
        'password_new' => 'رمز عبور جدید',
        'password_new_confirm' => 'تکرار رمز عبور جدید',
        'remember_me' => 'من را به خاطر بسپار',
        'forgot_link' => 'رمز عبور را فراموش کرده‌اید؟',
        'register' => 'ساخت حساب کاربری',
        'register_submit' => 'ساخت حساب',
        'register_help' => 'اگر مدیر سیستم هستید و نیاز به حساب جدید دارید، این فرم را تکمیل کنید.',
        'have_account' => 'قبلاً حساب ساخته‌اید؟ وارد شوید',
        'forgot_title' => 'بازیابی رمز عبور',
        'forgot_help' => 'ایمیل حساب خود را وارد کنید تا لینک بازیابی رمز عبور برایتان ارسال شود.',
        'forgot_submit' => 'ارسال لینک بازیابی',
        'reset_title' => 'تنظیم رمز عبور جدید',
        'reset_submit' => 'ثبت رمز عبور',
    ],

    'home' => [
        'hero_welcome' => 'به Penova Core خوش‌آمدید',
        'hero_tagline' => 'محصول لاراولی بعدی‌تان را در چند دقیقه بسازید، نه چند هفته.',
        'link_documentation' => 'مستندات',
        'link_release_notes' => 'یادداشت‌های انتشار',
        'get_started' => 'برای شروع',
        'gs_module_title' => 'اولین ماژول خود را بسازید',
        'gs_module_desc' => 'با ژنراتور یک ماژول تجاری جدید بسازید.',
        'configure_branding' => 'پیکربندی برندینگ',
        'gs_branding_desc' => 'پیش از عرضه، میزکار را از آنِ خود کنید.',
        'gs_resource_title' => 'اولین منبع خود را بسازید',
        'gs_resource_desc' => 'یک منبع CRUD را در چند دقیقه بسازید.',
        'gs_users_title' => 'مدیریت کاربران و نقش‌ها',
        'gs_users_desc' => 'تعیین کنید چه کسی به چه چیزی دسترسی دارد.',
        'gs_docs_title' => 'خواندن مستندات',
        'gs_docs_desc' => 'هرآنچه برای عرضهٔ سریع‌تر لازم دارید.',
        'setup_heading' => 'راه‌اندازی پلتفرم',
        'keep_building' => 'ادامهٔ ساخت',
        'overview_heading' => 'نمای کلی',
        'overview_users' => 'کاربران',
        'overview_roles' => 'نقش‌ها',
        'overview_unread' => 'اعلان‌های خوانده‌نشده',
        'health_heading' => 'سلامت پلتفرم',
        'modules_heading' => 'ماژول‌های نصب‌شده',
        'modules_empty_title' => 'پلتفرم شما آماده است.',
        'modules_empty_body' => 'اولین ماژول تجاری خود را نصب کنید تا Penova Core به یک محصول واقعی تبدیل شود.',
        'branding_reminder_title' => 'محصول شما هنوز از برندینگ پیش‌فرض Penova استفاده می‌کند.',
        'branding_reminder_body' => 'پیش از عرضه، لوگو، نام برنامه و پاورقی خود را پیکربندی کنید.',
        'whats_new' => 'تازه‌های نسخهٔ :version',

        'onboarding' => [
            'step_core_installed' => 'Penova Core نصب شد',
            'step_authentication' => 'احراز هویت آماده است',
            'step_workspace_ready' => 'میزکار آماده است',
            'step_branding' => 'پیکربندی برندینگ',
            'step_first_module' => 'اولین ماژول خود را نصب کنید',
            'guide_resource_label' => 'اولین منبع خود را بسازید',
            'guide_resource_desc' => 'با ژنراتور ماژول، یک منبع CRUD بسازید.',
            'guide_product_label' => 'اولین محصول خود را بسازید',
            'guide_product_desc' => 'ماژول‌ها را در یک محصول لاراولی قابل‌عرضه ترکیب کنید.',
            'cta_configure' => 'پیکربندی',
            'cta_browse_docs' => 'مشاهدهٔ مستندات',
            'cta_guide' => 'راهنما',
        ],
    ],

    'widgets' => [
        'users_title' => 'کاربران',
        'users_subtitle' => 'حساب‌های ثبت‌شده در میزکار',
        'roles_title' => 'نقش‌ها',
        'roles_subtitle' => 'نقش‌های تعریف‌شده',
        'no_activity' => 'فعلاً فعالیتی ثبت نشده است.',
        'no_notifications' => 'اعلان جدیدی ندارید.',
        'modules_body' => 'قابلیت‌های تجاری Penova به‌صورت <strong>ماژول</strong> اضافه می‌شوند (مثل فروشگاه، رزرو، CRM، CMS و …). هر ماژول ویجت‌هایش را با یک descriptor ساده از service provider خودش ثبت می‌کند و در همین grid ظاهر می‌شود — بدون دست‌زدن به Core.',
    ],

    'welcome' => [
        'hero_intro' => 'یک هستهٔ آماده برای محصولات لاراولی شما؛ با احراز هویت، مدیریت کاربران و نقش‌ها، تنظیمات، نوتیفیکیشن‌ها و یک میزکار تمیز که آمادهٔ نصب ماژول‌های محصول شماست.',
        'cta_workspace' => 'ورود به میزکار',
        'cta_docs' => 'مشاهدهٔ مستندات',
        'features_heading' => 'Core چه چیزهایی برایت آماده کرده است؟',
        'modules_heading' => 'وقتی آمادهٔ محصول شدی، ماژول‌ها را اضافه کن',
        'modules_intro' => 'Penova Core به‌صورت یک هستهٔ رایگان می‌آید. هر زمان به محصول واقعی نیاز داشتی، ماژول‌های محصول را روی همین هسته اضافه می‌کنی، بدون این‌که دوباره همه‌چیز را بنویسی.',
        'coming_soon' => 'به‌زودی',
        'footer_docs' => 'مستندات',
        'features' => [
            'auth' => ['title' => 'احراز هویت و حساب‌ها', 'desc' => 'جریان کامل ورود، ثبت‌نام و بازیابی رمز عبور، آمادهٔ استفاده در هر میزکار محصول.'],
            'users' => ['title' => 'کاربران و نقش‌ها', 'desc' => 'مدیریت کاربران، نقش‌ها و دسترسی‌ها بدون نیاز به پکیج خارجی.'],
            'settings' => ['title' => 'تنظیمات و اعلان‌ها', 'desc' => 'تنظیمات runtime و یک feed مشترک نوتیفیکیشن که همهٔ ماژول‌ها می‌توانند روی آن سوار شوند.'],
            'ui' => ['title' => 'میزکار و DataTable', 'desc' => 'یک میزکار تمیز، کامپوننت‌های تکرارپذیر و الگوی DataTable سمت سرور برای هر صفحهٔ CRUD.'],
        ],
        'modules' => [
            'commerce' => ['title' => 'تجارت', 'desc' => 'قابلیت فروش را به‌صورت یک ماژول اضافه کنید: محصولات، سبد خرید، پرداخت و سفارش‌ها.'],
            'messaging' => ['title' => 'پیام‌رسانی', 'desc' => 'ارسال پیامک و اعلان‌های تراکنشی از طریق یک ماژول یکپارچه.'],
            'payments' => ['title' => 'پرداخت‌ها', 'desc' => 'اتصال به درگاه‌های پرداخت به‌صورت یک لایهٔ ماژولار و قابل‌گسترش.'],
        ],
    ],
];
