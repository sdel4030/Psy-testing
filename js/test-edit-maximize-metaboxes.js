/**
 * Optimized Maximize Initializer for WordPress 7+
 * Ensures fail-safe execution and modern jQuery compatibility.
 */
jQuery(document).ready(function($) {
    // انتخاب المان‌های بخش مدیریت آزمون
    const $tabsToMaximize = $('#wpt_edit_questions_answers, #wpt_edit_scores, #wpt_edit_formulas');

    // ۱. شرط حفاظتی: بررسی وجود المان‌ها در صفحه و تعریف بودن پلاگین wptMaximize
    if ($tabsToMaximize.length && typeof $.fn.wptMaximize === 'function') {
        
        // ۲. ایمن‌سازی متغیرهای ترجمه برای جلوگیری از خطای Uncaught ReferenceError
        const maxLabel = (typeof Wpt !== 'undefined' && Wpt.locale) ? Wpt.locale.maximize : 'Maximize';
        const minLabel = (typeof Wpt !== 'undefined' && Wpt.locale) ? Wpt.locale.minimize : 'Minimize';

        // اجرای پلاگین تمام‌صفحه با کانفیگ ایمن
        $tabsToMaximize.wptMaximize({
            maximizeLabel: maxLabel,
            minimizeLabel: minLabel
        });
    } else {
        // درج لاگ در محیط توسعه در صورت بروز تداخل یا عدم لود پلاگین اصلی
        console.warn('Wpt: Maximize plugin or target elements not found in this page.');
    }
});
