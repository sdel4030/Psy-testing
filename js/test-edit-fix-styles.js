/**
 * Optimized Publishing Actions Layout Fix for WordPress 7+
 * Caches DOM selections to enhance performance in backend post-editing.
 */
jQuery(function($) {
    // ۱. کش کردن المان‌ها برای جلوگیری از جستجوی تکراری در DOM
    const $sections = $('#misc-publishing-actions .misc-pub-section');

    if ($sections.length) {
        // ۲. حذف کلاس قدیمی از تمام بخش‌ها و اعطای زنجیره‌ای آن به آخرین المان واقعی
        $sections
            .removeClass('misc-pub-section-last')
            .last()
            .addClass('misc-pub-section-last');
    }
});
