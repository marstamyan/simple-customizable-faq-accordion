'use strict';

// Isolated preview checks; run: node tests/customizer-preview-regression.js
const fs = require('fs');
const path = require('path');
const vm = require('vm');
const assert = require('assert').strict;
const code = fs.readFileSync(path.join(__dirname, '../public/js/mk-faq-theme-customizer.js'), 'utf8');
const callbacks = {};
const values = { mk_faq_title_color: '#abc', mk_faq_block_size: '540' };
let style;
let appended = 0;
let checks = 0;
const wp = {
    customize(id, callback) {
        callback({
            get() { return values[id]; },
            bind(handler) { callbacks[id] = handler; }
        });
    }
};
function check(condition, message) {
    ++checks;
    assert.ok(condition, message);
}

try {
    vm.runInNewContext(code, {
        window: { wp },
        wp,
        document: {
            head: { appendChild(element) { style = element; ++appended; } },
            createElement(tag) {
                assert.equal(tag, 'style');
                return {};
            }
        }
    });
    check(appended === 1, 'Preview creates one stylesheet');
    check(Object.keys(callbacks).length === 9, 'All nine controls have preview listeners');
    check(style.textContent.includes('--mk-faq-title-color:#abc;'), 'Valid saved title color is applied initially');
    check(style.textContent.includes('--mk-faq-accordion-max-width:540px;'), 'Valid saved block width is applied initially');
    check(style.textContent.startsWith('.mk-faq-container{'), 'Preview styles are scoped to every FAQ container');
    callbacks.mk_faq_title_background_color_hover('#123456');
    check(style.textContent.includes('--mk-faq-title-hover-color:#123456;'), 'Hover color updates');
    callbacks.mk_faq_title_background_color('#654321');
    check(style.textContent.includes('--mk-faq-title-background-color:#654321;'), 'Normal background updates');
    check(style.textContent.includes('--mk-faq-title-hover-color:#123456;'), 'Normal color updates preserve the hover setting');
    callbacks.mk_faq_title_color('</style><script>alert(1)</script>');
    check(style.textContent.includes('--mk-faq-title-color:#0f0f0f;'), 'Invalid color falls back safely');
    check(!style.textContent.includes('<'), 'Preview never emits HTML from control values');
    callbacks.mk_faq_title_size('999');
    check(style.textContent.includes('--mk-faq-title-font-size:48px;'), 'Title size has an upper bound');
    callbacks.mk_faq_text_size('-20');
    check(style.textContent.includes('--mk-faq-text-font-size:12px;'), 'Answer size has a lower bound');
    callbacks.mk_faq_block_size('540px;');
    check(style.textContent.includes('--mk-faq-accordion-max-width:480px;'), 'Width rejects CSS fragments');
    callbacks.mk_faq_block_size('900');
    check(style.textContent.includes('--mk-faq-accordion-max-width:900px;'), 'Valid width updates');
    callbacks.mk_faq_text_color(null);
    check(style.textContent.includes('--mk-faq-text-color:#0f0f0f;'), 'Cleared color falls back to the shared default');
    check(appended === 1, 'Repeated edits reuse the same stylesheet');
    vm.runInNewContext(code, { window: {} });
    ++checks;
    console.log('PASS: ' + checks + ' Customizer preview checks.');
} catch (error) {
    console.error('FAIL: ' + error.message);
    process.exitCode = 1;
}
