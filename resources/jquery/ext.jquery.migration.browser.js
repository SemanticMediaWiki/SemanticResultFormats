/**
 * MW 1.24+ uses jQuery 1.9+
 *
 * @see http://stackoverflow.com/questions/9645803/whats-the-replacement-for-browser
 */
jQuery.uaMatch = function( ua ) {
	'use strict';

    ua = ua.toLowerCase();
    var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
        /(webkit)[ \/]([\w.]+)/.exec( ua ) ||
        /(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
        /(msie) ([\w.]+)/.exec( ua ) ||
        ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) || [];
    return {
        browser: match[ 1 ] || "",
        version: match[ 2 ] || "0"
    };
};

if ( !jQuery.browser ) {
    var
    matched = jQuery.uaMatch( navigator.userAgent ),
    browser = {};
    if ( matched.browser ) {
        browser[ matched.browser ] = true;
        browser.version = matched.version;
    }

    // Chrome is Webkit, but Webkit is also Safari.
    if ( browser.chrome ) {
        browser.webkit = true;
    } else if ( browser.webkit ) {
        browser.safari = true;
    } else {
        browser.msie = true;
    }

    jQuery.browser = browser;
}