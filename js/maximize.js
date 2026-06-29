/**
 * Modernized jQuery.wptMaximize Plugin for WordPress 7+
 * Replaces deprecated .bind(), updates DOM body reference, and cleans up ancient TinyMCE skin hooks.
 */
(function ($) {
    'use strict';

    function maximizeItem($maximizable, settings) {
        // ۱. استفاده از ارجاع مستقیم و سریع‌تر به بادی مرورگر
        var $body        = $(document.body),
            $scrollable  = $maximizable.find('.inside:first'),
            $toggleButton = $('<button/>'),
            $buttonText   = $('<span/>').addClass('screen-reader-text').appendTo($toggleButton),
            isMaximized  = false;

        function changeLabel() {
            var label = isMaximized ? settings.minimizeLabel : settings.maximizeLabel;
            $toggleButton.attr('title', label);
            $buttonText.text(label);
        }

        function toggleMaximize(e) {
            e.preventDefault(); // مهار لرزش یا رفتارهای ناخواسته دکمه در سابمیت‌های ادمین
            isMaximized = !isMaximized;

            changeLabel();

            // جابجایی هوشمند کلاس‌ها برای مدیریت تمام‌صفحه در CSS
            $body.toggleClass('wpt_maximize', isMaximized);
            $maximizable.toggleClass('wpt_maximized', isMaximized);
            $toggleButton.toggleClass('active', isMaximized);

            // تریگر کردن رویداد ریسایز برای بازتنظیم چارت‌ها در حالت تمام‌صفحه
            $(window).trigger('resize.wptDiagram');
        }

        changeLabel();

        // افزودن کلاس‌های ساختاری افزونه
        $maximizable.addClass('wpt_maximizable');
        $scrollable.addClass('wpt_scroll');

        // ۲. اصلاح کلیدی: جایگزینی .bind() با .on() و الحاق دکمه به بالای متاباکس وردپرس
        $toggleButton
            .attr('type', 'button')
            .addClass('handlediv button-link qt-dfw wpt-toggle-fullscreen')
            .on('click.wptMaximize', toggleMaximize)
            .insertBefore($maximizable.find('.handlediv:first'));

        // ۳. مدرن‌سازی ساختار آیکون‌ها: استفاده از فونت‌آیکون استاندارد ادمین وردپرس (Dashicons)
        $('<span/>').addClass('dashicons dashicons-editor-expand').prependTo($toggleButton);
    }

    $.fn.wptMaximize = function(options) {
        var settings = $.extend({
            maximizeLabel: 'Maximize',
            minimizeLabel: 'Minimize'
        }, options);

        return this.each(function(i, item) {
            maximizeItem($(item), settings);
        });
    };

}(jQuery));
