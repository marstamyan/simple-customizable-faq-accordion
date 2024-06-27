(function ($) {
    "use strict";

    wp.customize('mk_faq_title_background_color', function (value) {
        value.bind(function (value) {
            $('.mk-faq-accordion-title').css('background-color', value);
        });
    });

    wp.customize('mk_faq_title_background_color_hover', function (value) {
        value.bind(function (new_val) {
            var old_faq_title_bg_color = $('.mk-faq-accordion-title').css('background-color');
            $('.mk-faq-accordion-title').hover(
                function () {
                    $(this).css('background-color', new_val);
                },
                function () {
                    $(this).css('background-color', old_faq_title_bg_color);
                }
            );
        });
    });

    wp.customize('mk_faq_text_background_color', function (value) {
        value.bind(function (value) {
            $('.mk-faq-accordion-content p').css('background-color', value);
        });
    });

    wp.customize('mk_faq_title_color', function (value) {
        value.bind(function (value) {
            $('.mk-faq-accordion-title').css('color', value);
        });
    });

    wp.customize('mk_faq_border_line', function (value) {
        value.bind(function (value) {
            $('.mk-faq-accordion-title, .mk-faq-accordion__Heading').css('border-bottom', '1px solid' + value);
        });
    });

    wp.customize('mk_faq_text_color', function (value) {
        value.bind(function (value) {
            $('.mk-faq-accordion-content p').css('color', value);
        });
    });

    wp.customize('mk_faq_title_size', function (value) {
        value.bind(function (value) {
            $('a.mk-faq-accordion-title').css('font-size', value + 'px');
        });
    });

    wp.customize('mk_faq_text_size', function (value) {
        value.bind(function (value) {
            $('.mk-faq-accordion dd, .mk-faq-accordion__panel').css('font-size', value + 'px');
        });
    });

    wp.customize('mk_faq_block_size', function (value) {
        value.bind(function (value) {
            $('.mk-faq-container').css('max-width', value + 'px');
        });
    });

})(jQuery);

