/**
 * Polyfill for deprecated jQuery.curCSS to ensure compatibility with WordPress 7+
 * Bridges old plugin calls to the modern and native $.css() method.
 */
if (typeof jQuery !== 'undefined' && typeof jQuery.curCSS === 'undefined') {
    jQuery.curCSS = function(element, prop, val) {
        return jQuery(element).css(prop, val);
    };
}
