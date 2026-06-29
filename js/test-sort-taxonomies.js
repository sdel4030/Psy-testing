/**
 * Optimized Taxonomy Sort for WordPress 7+ and Modern Browsers
 * Inspired by the-taxonomy-sort plugin
 */
jQuery(document).ready(function($) {
    // لیست تاکسونومی‌های مورد نظر برای مرتب‌سازی
    const taxonomies = [
        'wpt_answer',
        'wpt_result',
        'wpt_scale'
    ];

    // استفاده از حلقه مدرن و سریع‌تر forEach به جای $.each جی‌کوئری
    taxonomies.forEach(function(taxonomy) {
        const $all  = $('#' + taxonomy + 'div');
        const $list = $('#' + taxonomy + 'checklist');

        // اگر المان در صفحه وجود نداشت، پردازش را متوقف کن تا خطایی رخ ندهد
        if (!$list.length) return;

        // افزودن کلاس‌های ساختاری با متدهای بهینه شده
        $list.find('li').addClass('wpt-sortable');
        
        // پیدا کردن آیتم‌های دارای زیرمجموعه (کانتینرها) به روش مدرن و سریع
        $list.find('li').has('ul').addClass('wpt-sortable-container');

        // فعال‌سازی قابلیت کشیدن و رها کردن با کانفیگ روان‌تر و هماهنگ با CSS جدید
        $list.sortable({
            forcePlaceholderSize : true,
            placeholder          : 'sortable-placeholder',
            items                : '.wpt-sortable',
            cursor               : 'move',
            axis                 : 'y',
            containment          : $all,
            opacity              : 0.8,         /* ایجاد شفافیت ملایم برای آیتم در حال حرکت */
            tolerance            : 'pointer',   /* جابجایی دقیق‌تر و حساس‌تر به نوک ماوس */
            
            // اضافه کردن کلاس کاستوم در زمان شروع حرکت برای تعامل بهتر با CSS
            start: function(event, ui) {
                ui.item.addClass('wpt-sorting-active');
            },
            stop: function(event, ui) {
                ui.item.removeClass('wpt-sorting-active');
            }
        });
    });
});
