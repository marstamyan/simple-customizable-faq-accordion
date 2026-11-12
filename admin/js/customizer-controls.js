(function () {
    'use strict';
    wp.customize.bind('ready', function () {
        wp.customize.previewer.bind('mk-faq-focus', function () {
            wp.customize.section('mk_faq_section').focus();
        });
    });
}());
