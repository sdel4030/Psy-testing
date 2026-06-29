/**
 * Fully Optimized & Bug-Free Taxonomy Sort for WordPress 7+
 * Resolves nested elements conflicts in jQuery UI Sortable
 */
jQuery(document).ready(function($) {
    const taxonomies = [
        'wpt_answer',
        'wpt_result',
        'wpt_scale'
    ];

    taxonomies.forEach(function(taxonomy) {
        const $all  = $('#' + taxonomy + 'div');
        const $list = $('#' + taxonomy + 'checklist');

        if (!$list.length) return;

        // اصلاح مهم: فقط liهای سطح اول (فرزند مستقیم) را به عنوان آیتم قابل جابجایی والد علامت‌گذاری می‌کنیم
        const $topLevelItems = $list.children('li');
        $topLevelItems.addClass('wpt-sortable');
        
        // تشخیص المان‌های دارای زیرمجموعه
        $list.find('li').has('ul').addClass('wpt-sortable-container');

        // کانفیگ سورت برای لیست اصلی (والدین)
        $list.sortable({
            forcePlaceholderSize : true,
            placeholder          : 'sortable-placeholder',
            items                : '> .wpt-sortable', /* مهار حرکت فقط به سطح اول با استفاده از دایرکتوری > */
            cursor               : 'move',
            axis                 : 'y',
            containment          : $all,
            opacity              : 0.8,
            tolerance            : 'pointer',
            
            start: function(event, ui) {
                ui.item.addClass('wpt-sorting-active');
                // تازه سازی موقعیت‌ها برای جلوگیری از پرش در لیست‌های وردپرس
                $(this).sortable('refreshPositions'); 
            },
            stop: function(event, ui) {
                ui.item.removeClass('wpt-sorting-active');
            }
        });

        // پشتیبانی از جابجایی المان‌های داخلی (فرزندان) بدون تداخل با والد
        const $nestedLists = $list.find('ul');
        if ($nestedLists.length) {
            $nestedLists.sortable({
                forcePlaceholderSize : true,
                placeholder          : 'sortable-placeholder',
                items                : '> li', /* جابجایی فرزندان فقط در محیط لیست داخلی خودشان */
                cursor               : 'move',
                axis                 : 'y',
                opacity              : 0.8,
                tolerance            : 'pointer',
                
                start: function(event, ui) {
                    event.stopPropagation(); /* جلوگیری از انتشار رویداد به منوی والد */
                    ui.item.addClass('wpt-sorting-active');
                },
                stop: function(event, ui) {
                    ui.item.removeClass('wpt-sorting-active');
                }
            });
        }
    });
});
