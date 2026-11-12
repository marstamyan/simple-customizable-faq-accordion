(function () {
    'use strict';

    if (!window.wp || !wp.customize) {
        return;
    }

    var settings = {
        mk_faq_title_background_color: { property: '--mk-faq-title-background-color', fallback: '#38cc70' },
        mk_faq_title_background_color_hover: { property: '--mk-faq-title-hover-color', fallback: '#2ba659' },
        mk_faq_text_background_color: { property: '--mk-faq-background-color', fallback: '#ffffff' },
        mk_faq_title_color: { property: '--mk-faq-title-color', fallback: '#0f0f0f' },
        mk_faq_text_color: { property: '--mk-faq-text-color', fallback: '#0f0f0f' },
        mk_faq_border_line: { property: '--mk-faq-title-border-color', fallback: '#2caf5d' },
        mk_faq_title_size: { property: '--mk-faq-title-font-size', fallback: 18, min: 12, max: 48 },
        mk_faq_text_size: { property: '--mk-faq-text-font-size', fallback: 16, min: 12, max: 48 },
        mk_faq_block_size: { property: '--mk-faq-accordion-max-width', fallback: 480, min: 320, max: 1200 }
    };
    var values = {};
    var style = document.createElement('style');
    style.id = 'mk-faq-customizer-preview';
    document.head.appendChild(style);

    function sanitize(value, setting) {
        if (typeof setting.min === 'number') {
            if (!/^-?\d+$/.test(String(value))) {
                value = setting.fallback;
            }
            return Math.min(setting.max, Math.max(setting.min, Number(value))) + 'px';
        }
        return typeof value === 'string' && /^#(?:[a-f0-9]{3}|[a-f0-9]{6})$/i.test(value) ? value : setting.fallback;
    }

    function render() {
        var declarations = Object.keys(values).map(function (id) {
            return settings[id].property + ':' + values[id] + ';';
        });
        // A stylesheet also updates newly rendered FAQ instances in the preview.
        style.textContent = '.mk-faq-container{' + declarations.join('') + '}';
        document.querySelectorAll('.mk-faq-container').forEach(function (container) {
            Object.keys(values).forEach(function (id) {
                // Optimizers may move the saved stylesheet after the preview style.
                container.style.setProperty(settings[id].property, values[id], 'important');
            });
            if (!container.querySelector('.mk-faq-edit-shortcut')) {
                var edit = document.createElement('button');
                edit.type = 'button';
                edit.className = 'mk-faq-edit-shortcut';
                edit.textContent = '\u270e';
                edit.setAttribute('aria-label', 'Edit FAQ appearance');
                edit.style.cssText = 'position:absolute;right:0;top:0;z-index:10;background:#0073aa;color:white;border:2px solid white;border-radius:50%;width:32px;height:32px;padding:0;cursor:pointer;font:24px/28px sans-serif;';
                container.style.position = 'relative';
                edit.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    wp.customize.preview.send('mk-faq-focus');
                });
                container.appendChild(edit);
            }
        });
    }

    Object.keys(settings).forEach(function (id) {
        wp.customize(id, function (setting) {
            values[id] = sanitize(setting.get(), settings[id]);
            render();
            setting.bind(function (value) {
                values[id] = sanitize(value, settings[id]);
                render();
            });
        });
    });
    // FAQ markup can arrive after this script (lazy loading or selective refresh).
    new MutationObserver(function (records) {
        if (records.some(function (record) {
            return Array.prototype.some.call(record.addedNodes, function (node) {
                return node.nodeType === 1 && (node.matches('.mk-faq-container') || node.querySelector('.mk-faq-container'));
            });
        })) {
            render();
        }
    }).observe(document.documentElement, { childList: true, subtree: true });
}());
