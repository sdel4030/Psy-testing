/**
 * Optimized "Rate Us" Feedback Submitter for WordPress 7+
 * Replaces deprecated .click() and safely handles default anchor link behavior.
 */
jQuery(function($) {
    // مدیریت رویداد کلیک با متد مدرن .on و استفاده از فضای نام اختصاصی .wpt
    $('a.wpt_rateus').on('click.wpt', function(e) {
        e.preventDefault(); // مهار رفتار پیش‌فرض تگ <a> جهت جلوگیری از پرش صفحه

        // بررسی وجود نانس و متغیرهای امنیتی قبل از ارسال درخواست
        var nonce = (typeof Wpt !== 'undefined' && Wpt.nonce) ? Wpt.nonce.feedbackRateUs : '';
        
        // اطمینان از تعریف بودن متغیر سراسری ajaxurl در وردپرس
        var targetUrl = (typeof ajaxurl !== 'undefined') ? ajaxurl : '/wp-admin/admin-ajax.php';

        // ارسال درخواست آژاکس به بک‌اند وردپرس
        $.post(targetUrl, {
            action: 'wpt_rateus',
            _ajax_nonce: nonce
        });

        // مخفی کردن یا غیرفعال کردن دکمه بلافاصله پس از کلیک برای بهبود UX
        $(this).fadeOut(300);
    });
});
