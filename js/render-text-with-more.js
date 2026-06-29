/**
 * Optimized "Read More" Toggle for WordPress 7+
 * Implements smooth sliding transitions and modern jQuery event handlers.
 */
jQuery(function($) {
    // پیدا کردن تمام باکس‌های حاوی متن طولانی
    $('.wpt_text_with_more').each(function() {
        const $container = $(this);
        const $content   = $container.find('.text_under_more');
        const $moreLink  = $container.find('.more_link');

        // اگر لینک یا محتوای مخفی وجود نداشت، عملیات را متوقف کن
        if (!$moreLink.length || !$content.length) return;

        // مدیریت رویداد کلیک به صورت بهینه و روان
        $moreLink.on('click.wpt', function(e) {
            e.preventDefault(); // جایگزین استاندارد return false برای مهار رفتار پیش‌فرض لینک

            // تغییر کلاس دکمه برای افکت‌های CSS
            $(this).toggleClass('open');

            // نمایش یا پنهان‌سازی متن به صورت اسلاید ملایم (۳۰۰ میلی‌ثانیه)
            $content.slideToggle(300);
        });
    });
});
