/**
 * Run on a disposable WordPress Playground (SQLite) site.
 * Install @wp-playground/cli and playwright locally first; FAQ_TEST_MODULES may
 * point at the directory containing their package.json/node_modules.
 * Set FAQ_TEST_WP / FAQ_TEST_PHP to select an exact compatibility target.
 */
import assert from 'node:assert/strict';
import { createRequire } from 'node:module';
import fs from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const repo = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const resolveFrom = process.env.FAQ_TEST_MODULES || repo;
const resolvePackage = createRequire(path.join(resolveFrom, 'test-package.cjs'));
const { runCLI } = await import(pathToFileURL(resolvePackage.resolve('@wp-playground/cli')).href);
const { chromium } = resolvePackage('playwright');
const pluginVfs = '/wordpress/wp-content/plugins/simple-customizable-faq-accordion';
const php = process.env.FAQ_TEST_PHP || '8.3';
const wp = process.env.FAQ_TEST_WP || 'latest';
const resultFile = process.env.FAQ_TEST_RESULTS;
const themePath = process.env.FAQ_TEST_THEME;
let server;
let browser;
let page;
const results = { requested: { php, wp }, browserChecks: [] };

async function runPHP(code) {
  const response = await server.playground.run({ code: '<?php ' + code });
  const text = new TextDecoder().decode(response.bytes);
  if (response.exitCode !== 0 || response.errors) {
    throw new Error(`PHP failed (${response.exitCode}): ${response.errors}\n${text}`);
  }
  return text;
}

async function browserCheck(name, fn) {
  await fn();
  results.browserChecks.push(name);
  console.log('PASS: ' + name);
}

try {
  server = await runCLI({
    command: 'server', port: Number(process.env.FAQ_TEST_PORT || 9482),
    php, wp, workers: Number(process.env.FAQ_TEST_WORKERS || 6), login: false,
    mount: [{ hostPath: repo, vfsPath: pluginVfs }, ...(themePath ? [{ hostPath: themePath, vfsPath: '/wordpress/wp-content/themes/lumina' }] : [])],
    blueprint: { steps: [{ step: 'activatePlugin', pluginPath: pluginVfs + '/simple-customizable-faq-accordion.php' }, ...(themePath ? [{ step: 'activateTheme', themeFolderName: 'lumina' }] : [])] },
    'php-ini': { display_errors: '0', log_errors: '1', error_reporting: '32767' },
    'define-bool': {
      WP_DEBUG: true, WP_DEBUG_LOG: true, WP_DEBUG_DISPLAY: false,
      DISABLE_WP_CRON: true, WP_HTTP_BLOCK_EXTERNAL: true,
    },
  });
  const integration = await runPHP(`require '/wordpress/wp-load.php'; require '${pluginVfs}/tests/wordpress-integration.php';`);
  results.integration = JSON.parse(integration);
  if (themePath) {
    // Lumina has a single-post template; its page fallback displays excerpts.
    results.integration.fixture_url = await runPHP(`require '/wordpress/wp-load.php'; wp_update_post(array('ID'=>${results.integration.fixture_page_id},'post_type'=>'post')); echo get_permalink(${results.integration.fixture_page_id});`);
  }
  console.log(JSON.stringify(results.integration, null, 2));
  assert.equal(results.integration.failed, 0, 'WordPress integration checks must pass');

  const shortcodeTests = await fs.access(path.join(repo, 'tests/shortcode-regression.php')).then(() => true, () => false);
  if (shortcodeTests) {
    results.shortcode = await runPHP(`require '/wordpress/wp-load.php'; require '${pluginVfs}/tests/shortcode-regression.php';`);
    console.log(results.shortcode);
  }

  browser = await chromium.launch({
    headless: true,
    ...(process.env.FAQ_TEST_BROWSER ? { executablePath: process.env.FAQ_TEST_BROWSER } : {}),
  });
  page = await browser.newPage();
  await page.route('**/*', route => new URL(route.request().url()).hostname === '127.0.0.1' ? route.continue() : route.abort());
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  await page.goto(results.integration.fixture_url, { waitUntil: 'networkidle' });
  const triggers = page.locator('.mk-faq-accordion-title');
  await browserCheck('Two repeated shortcodes render four independent buttons', async () => {
    assert.equal(await triggers.count(), 4);
    assert.equal(await page.locator('.mk-faq-heading-title').count(), 2);
  });
  await browserCheck('Click toggles only the controlled answer and ARIA state', async () => {
    const trigger = triggers.nth(0);
    const panelId = await trigger.getAttribute('aria-controls');
    assert.equal(await trigger.getAttribute('aria-expanded'), 'false');
    assert.equal(await page.locator('#' + panelId).isVisible(), false);
    await trigger.click();
    assert.equal(await trigger.getAttribute('aria-expanded'), 'true');
    assert.equal(await page.locator('#' + panelId).isVisible(), true);
    assert.equal(await triggers.nth(2).getAttribute('aria-expanded'), 'false');
    await trigger.click();
    assert.equal(await trigger.getAttribute('aria-expanded'), 'false');
  });
  await browserCheck('Native Enter and Space keys each toggle once', async () => {
    const trigger = triggers.nth(0);
    await trigger.focus();
    await page.keyboard.press('Enter');
    assert.equal(await trigger.getAttribute('aria-expanded'), 'true');
    await page.keyboard.press('Space');
    assert.equal(await trigger.getAttribute('aria-expanded'), 'false');
  });
  await browserCheck('Clicks on nested question text and icon toggle their owning button', async () => {
    const trigger = triggers.nth(0);
    await trigger.locator('.mk-faq-question-text').click();
    assert.equal(await trigger.getAttribute('aria-expanded'), 'true');
    await trigger.locator('[aria-hidden="true"]').click();
    assert.equal(await trigger.getAttribute('aria-expanded'), 'false');
  });
  await browserCheck('Arrow keys and Home/End move focus within one accordion', async () => {
    await triggers.nth(0).focus();
    await page.keyboard.press('ArrowDown');
    assert.equal(await triggers.nth(1).evaluate(button => button === document.activeElement), true);
    await page.keyboard.press('ArrowDown');
    assert.equal(await triggers.nth(0).evaluate(button => button === document.activeElement), true);
    await page.keyboard.press('End');
    assert.equal(await triggers.nth(1).evaluate(button => button === document.activeElement), true);
    await page.keyboard.press('Home');
    assert.equal(await triggers.nth(0).evaluate(button => button === document.activeElement), true);
  });
  await browserCheck('Long answers expand without height clipping', async () => {
    const trigger = triggers.nth(1);
    await trigger.click();
    const panelId = await trigger.getAttribute('aria-controls');
    const size = await page.locator('#' + panelId).evaluate(panel => ({ client: panel.clientHeight, scroll: panel.scrollHeight }));
    assert.ok(size.client > 500, 'Long answer should extend beyond the old maximum height');
    assert.ok(size.scroll <= size.client + 1, 'Expanded answer must not clip its contents');
  });
  await browserCheck('Mobile layout fits a narrow viewport', async () => {
    await page.setViewportSize({ width: 280, height: 700 });
    const overflow = await page.locator('.mk-faq-container').evaluateAll(nodes => nodes.some(node => node.getBoundingClientRect().right > document.documentElement.clientWidth + 1));
    assert.equal(overflow, false);
  });
  await browserCheck('Touch activation toggles once', async () => {
    const touchPage = await browser.newPage({ viewport: { width: 390, height: 844 }, hasTouch: true, isMobile: true });
    await touchPage.goto(results.integration.fixture_url, { waitUntil: 'networkidle' });
    const trigger = touchPage.locator('.mk-faq-accordion-title').first();
    await trigger.tap();
    assert.equal(await trigger.getAttribute('aria-expanded'), 'true');
    await trigger.tap();
    assert.equal(await trigger.getAttribute('aria-expanded'), 'false');
    await touchPage.close();
  });
  await browserCheck('Answers remain readable with JavaScript disabled', async () => {
    const noScript = await browser.newPage({ javaScriptEnabled: false });
    await noScript.goto(results.integration.fixture_url, { waitUntil: 'networkidle' });
    const panelId = await noScript.locator('.mk-faq-accordion-title').first().getAttribute('aria-controls');
    assert.equal(await noScript.locator('#' + panelId).isVisible(), true);
    await noScript.close();
  });
  await browserCheck('Frontend has no uncaught JavaScript errors', async () => assert.deepEqual(errors, []));
  await browserCheck('FAQ editor saves complex rows and title checkbox through the real admin form', async () => {
    await page.goto(server.serverUrl + '/wp-login.php', { waitUntil: 'networkidle' });
    await page.locator('#user_login').fill('admin');
    await page.locator('#user_pass').fill('password');
    await Promise.all([
      page.waitForURL(url => url.pathname.includes('/wp-admin')),
      page.locator('#wp-submit').click(),
    ]);
    await page.goto(server.serverUrl + `/wp-admin/post.php?post=${results.integration.fixture_faq_id}&action=edit`, { waitUntil: 'domcontentloaded' });
    await page.getByLabel(/FAQ Title/).first().fill('Edited through the admin form');
    await page.getByLabel(/FAQ Answer/).first().fill('An answer saved through the actual WordPress editor.');
    await page.getByLabel('Show main title', { exact: true }).uncheck();
    await Promise.all([page.waitForNavigation({ waitUntil: 'domcontentloaded' }), page.locator('#publish').click()]);
    assert.equal(await page.getByLabel(/FAQ Title/).first().inputValue(), 'Edited through the admin form');
    assert.equal(await page.getByLabel(/FAQ Answer/).first().inputValue(), 'An answer saved through the actual WordPress editor.');
    assert.equal(await page.getByLabel('Show main title', { exact: true }).isChecked(), false);
    await page.goto(results.integration.fixture_url, { waitUntil: 'networkidle' });
    assert.equal(await page.locator('.mk-faq-heading-title').count(), 0);
    assert.equal(await page.locator('.mk-faq-accordion-title').first().innerText(), 'Edited through the admin form');
  });
  await browserCheck('Admin settings save into the same design values used by the Customizer', async () => {
    await page.goto(server.serverUrl + '/wp-admin/edit.php?post_type=mk-simple-faq&page=mk-faq-settings', { waitUntil: 'domcontentloaded' });
    await page.locator('#mk_faq_title_color').fill('#112233');
    await page.locator('#mk_faq_title_size').fill('26');
    await Promise.all([page.waitForNavigation({ waitUntil: 'domcontentloaded' }), page.locator('#submit').click()]);
    assert.equal(await page.locator('#mk_faq_title_color').inputValue(), '#112233');
    assert.equal(await page.locator('#mk_faq_title_size').inputValue(), '26');
    const denied = await page.request.post(server.serverUrl + '/wp-admin/admin-post.php', { form: { action: 'mk_faq_save_design', _wpnonce: 'invalid', 'mk_faq_design[mk_faq_title_size]': '45' } });
    assert.equal(denied.status(), 403);
  });
  await browserCheck('Customizer pencil works and live preview survives late stylesheets', async () => {
    await page.setViewportSize({ width: 1280, height: 900 });
    const url = new URL('/wp-admin/customize.php', server.serverUrl);
    url.searchParams.set('url', results.integration.fixture_url);
    url.searchParams.set('autofocus[section]', 'mk_faq_section');
    await page.goto(url.href, { waitUntil: 'domcontentloaded' });
    const preview = page.frameLocator('#customize-preview iframe');
    await preview.locator('.mk-faq-edit-shortcut').first().waitFor();
    assert.equal(await page.locator('#customize-control-mk_faq_title_size input[type="range"]').inputValue(), '26');
    await page.evaluate(() => wp.customize.section('mk_faq_section').collapse());
    await preview.locator('.mk-faq-edit-shortcut').first().click();
    await page.waitForFunction(() => wp.customize.section('mk_faq_section').expanded());
    await preview.locator('body').evaluate(() => {
      const lateStyle = document.createElement('style');
      lateStyle.textContent = '.mk-faq-container{--mk-faq-title-color:#ffffff!important;--mk-faq-title-font-size:13px!important}';
      document.head.appendChild(lateStyle);
    });
    const colorControl = page.locator('#customize-control-mk_faq_title_color');
    await colorControl.locator('.wp-color-result').click();
    const color = colorControl.locator('input.color-picker-hex');
    await color.fill('#123456');
    await color.dispatchEvent('change');
    await color.press('Tab');
    const size = page.locator('#customize-control-mk_faq_title_size input[type="range"]');
    await size.evaluate(input => {
      input.value = '28';
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });
    const previewButton = preview.locator('.mk-faq-accordion-title').first();
    await previewButton.waitFor();
    await page.waitForFunction(() => {
      const frame = document.querySelector('#customize-preview iframe');
      const button = frame && frame.contentDocument && frame.contentDocument.querySelector('.mk-faq-accordion-title');
      return button && frame.contentWindow.getComputedStyle(button).color === 'rgb(18, 52, 86)' && frame.contentWindow.getComputedStyle(button).fontSize === '28px';
    });
    await page.locator('#save').click();
    await page.waitForFunction(() => document.querySelector('#save').value === 'Published' || document.querySelector('#save').textContent.trim() === 'Published');
    await page.goto(results.integration.fixture_url, { waitUntil: 'networkidle' });
    const computed = await page.locator('.mk-faq-accordion-title').first().evaluate(button => ({ color: getComputedStyle(button).color, fontSize: getComputedStyle(button).fontSize }));
    assert.deepEqual(computed, { color: 'rgb(18, 52, 86)', fontSize: '28px' });
    await page.goto(server.serverUrl + '/wp-admin/edit.php?post_type=mk-simple-faq&page=mk-faq-settings', { waitUntil: 'domcontentloaded' });
    assert.equal(await page.locator('#mk_faq_title_color').inputValue(), '#123456');
    assert.equal(await page.locator('#mk_faq_title_size').inputValue(), '28');
  });
  const debugLog = await server.playground.fileExists('/wordpress/wp-content/debug.log')
    ? await server.playground.readFileAsText('/wordpress/wp-content/debug.log') : '';
  results.debugLog = debugLog;
  assert.equal(debugLog, '', 'WP_DEBUG log should be empty after all integration and browser requests');
  assert.deepEqual(errors, [], 'Admin and Customizer should have no uncaught JavaScript errors');
  console.log(JSON.stringify({ browserChecks: results.browserChecks }, null, 2));
} catch (error) {
  results.error = String(error.stack || error);
  if (server) {
    results.debugLog = await server.playground.fileExists('/wordpress/wp-content/debug.log')
      ? await server.playground.readFileAsText('/wordpress/wp-content/debug.log') : '';
  }
  if (page && resultFile) {
    await fs.writeFile(resultFile + '.failure.html', await page.content());
    await page.screenshot({ path: resultFile + '.failure.png', fullPage: true });
  }
  throw error;
} finally {
  if (resultFile) await fs.writeFile(resultFile, JSON.stringify(results, null, 2));
  if (browser) await browser.close();
  if (server) await server[Symbol.asyncDispose]();
}
