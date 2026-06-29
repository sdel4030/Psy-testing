/**
 * Optimized Fail-Safe Ajax Post Submitter for WordPress 7+
 * Replaces deprecated .unbind(), handles TinyMCE safely, and updates jQuery UI Dialog syntax.
 */
jQuery(function($) {
    var form = $('form#post').on('submit.wpt', ajaxSubmit),
        markerClass = 'wpt-ajax-save',
        currentSubmit = null;

    // ثبت اطلاعات دکمه سابمیتی که کلیک شده است
    form.find(':submit').on('click', function() {
        currentSubmit = { name: this.name, value: this.value };
    });

    function defaultSubmit() {
        // استفاده از .off به جای متدهای منسوخ شده برای آزادسازی سابمیت فرم
        form.off('submit.wpt').removeClass(markerClass).trigger('submit');
    }

    function isPreview() {
        return /^wp-preview/.test(form.attr('target') || '');
    }

    function ajaxSubmit() {
        if (isPreview()) {
            currentSubmit = null;
            return true;
        }

        // همگام‌سازی ایمن ادیتور وردپرس (TinyMCE) در صورت فعال بودن
        if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor) {
            tinyMCE.activeEditor.save();
        }

        var data = form.serializeArray();
        if (currentSubmit !== null) {
            data.push(currentSubmit);
        }

        form.addClass(markerClass);

        // ارسال درخواست آژاکس به وردپرس
        $.post(form.attr('action'), data, function(response) {
            currentSubmit = null;
            
            if (typeof response === 'undefined' || typeof response.success === 'undefined') {
                defaultSubmit();
                return;
            }

            if (!response.success) {
                // نمایش خطا در صورت بازگشت ناموفق از سمت سرور
                var errorTitle = (response.error && response.error.title) ? response.error.title : 'Error';
                var errorContent = (response.error && response.error.content) ? response.error.content : 'An error occurred.';
                showError(errorTitle, errorContent);
                form.removeClass(markerClass); // حذف مارکر لودینگ برای تلاش مجدد کاربر
                return;
            }

            redirectTo(response.redirectTo);
        }, 'json').fail(function() {
            currentSubmit = null;
            defaultSubmit();
        });

        return false;
    }

    function redirectTo(url) {
        if (!url) return;
        // اصلاح متدهای منسوخ شده خروج از صفحه و جایگزینی با ساختار مدرن .off()
        $(window).off('beforeunload.edit-post');
        window.onbeforeunload = null;
        window.location.href = url;
    }

    function showError(title, content) {
        var okButtonText = (typeof Wpt !== 'undefined' && Wpt.locale && Wpt.locale.OK) ? Wpt.locale.OK : 'OK';

        // ایجاد پاپ‌آپ خطا با ساختار کاملاً سازگار با آخرین نسخه‌های jQuery UI در وردپرس 7
        $('<div class="error wpt_test_editor">' + content + '</div>').dialog({
            modal: true,
            title: title,
            width: 400,
            // جایگزینی فیلد منسوخ شده dialogClass با آپشن مدرن classes برای تزریق استایل‌های ادمین وردپرس
            classes: {
                "ui-dialog": "wp-dialog"
            },
            buttons: [{
                text: okButtonText,
                click: function() {
                    $(this).dialog('destroy').remove();
                }
            }]
        });
    }
});
