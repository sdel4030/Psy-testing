/**
 * Optimized ASAP Wrapper Toggle for WordPress 7+
 * Simplifies class toggling using modern jQuery event handling.
 */
jQuery(function($) {
    const $wrapper = $('.asap-wrap');
    const $asapCheckbox = $('#Asap');

    // شرط حفاظتی: اگر المان‌ها در صفحه نبودند، پردازش را متوقف کن
    if (!$wrapper.length || !$asapCheckbox.length) return;

    // استفاده از متد استاندارد .on به جای .change() منسوخ شده
    $asapCheckbox.on('change.wpt', function() {
        const isChecked = $(this).prop('checked');

        // بهینه‌سازی هوشمند: جابجایی کلاس‌ها بدون نیاز به ساختار تکه‌تکه if/else
        $wrapper.toggleClass('asap-1', isChecked)
                .toggleClass('asap-0', !isChecked);
    });
});
