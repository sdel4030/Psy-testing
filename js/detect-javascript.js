/**
 * Modern & Safe JavaScript Detection for WordPress 7+
 * Replaces old regex manipulation with native classList API.
 */
(function (el) {
    // ۱. حذف کلاس no-js در صورت وجود به صورت کاملاً امن
    el.classList.remove('no-js');
    
    // ۲. افزودن کلاس js بدون ایجاد فاصله‌های خالی تکراری
    el.classList.add('js');
})(document.documentElement);
