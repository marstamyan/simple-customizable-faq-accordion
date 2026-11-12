# Regression checks

Use a disposable test site. The WordPress integration script creates FAQ groups
and a fixture page, and the browser tests change the test site's FAQ content and
theme modifications. Do not include these files on a production site.

## Fast isolated checks

These checks use small WordPress stubs and do not replace the real site tests:

```sh
php tests/customizer-regression.php
node tests/customizer-preview-regression.js
```

## Full WordPress and browser checks

The runner uses the official WordPress Playground CLI with PHP WebAssembly and
SQLite, so no local PHP, MySQL, Docker, or WordPress installation is required.
Install a current Node.js version supported by Playground and install the test
dependencies separately from the plugin's production Composer dependencies:

```sh
npm install --prefix /path/to/test-tools @wp-playground/cli playwright
/path/to/test-tools/node_modules/.bin/playwright install chromium
```

Run from the plugin repository, setting `FAQ_TEST_MODULES` to the directory
containing that test-tools `node_modules` directory:

```sh
FAQ_TEST_MODULES=/path/to/test-tools node tests/browser-integration.mjs
```

PowerShell example:

```powershell
$env:FAQ_TEST_MODULES = 'C:\path\to\test-tools'
node tests/browser-integration.mjs
```

Optional environment variables:

| Variable | Purpose / default |
| --- | --- |
| `FAQ_TEST_WP` | WordPress version, default `latest` |
| `FAQ_TEST_PHP` | PHP version, default `8.3` |
| `FAQ_TEST_BROWSER` | Existing Chromium/Edge executable; otherwise Playwright's installed Chromium |
| `FAQ_TEST_PORT` | Local HTTP port, default `9482` |
| `FAQ_TEST_WORKERS` | PHP workers, default `6` (Playground recommendation) |
| `FAQ_TEST_RESULTS` | Absolute path for JSON results, including PHP/WP versions and failures |

Network access is needed to download WordPress and test dependencies. The runner
mounts this repository as a plugin in a fresh test site, activates it, runs the
PHP integration suite and `shortcode-regression.php`, and opens a headless browser.
Its `finally` block closes the browser and Playground instance. Default Playground
test credentials (`admin` / `password`) exist only in the disposable site.
WordPress background update requests/cron and external browser requests are
disabled in this fixture so third-party services cannot stall the checks.

Checks cover activation, Carbon Fields registration and database round trips,
shortcode escaping and access protection, repeated instances/ARIA IDs, ordinary
page assets, real Customizer registration, mouse/touch/keyboard behavior,
long answers, narrow viewports, JavaScript disabled, actual FAQ admin form saving,
and Customizer live preview/publication/persistence. PHP debug output and uncaught
browser errors fail the full runner.

## PHP checks inside another disposable WordPress environment

After installing/activating the plugin, these files may also be included after
`wp-load.php`, or invoked with `wp eval-file`:

```sh
wp eval-file wp-content/plugins/simple-customizable-faq-accordion/tests/shortcode-regression.php
wp eval-file wp-content/plugins/simple-customizable-faq-accordion/tests/wordpress-integration.php
```

`shortcode-regression.php` removes its fixture posts in `finally`.
`wordpress-integration.php` leaves a published fixture FAQ and page for browser
checks; discard the test database when finished.

These checks verify the versions actually reported by the running instance.
They do not establish compatibility with every theme, caching plugin, or database
engine. Run the full suite against the exact WordPress/PHP versions and theme
used on the destination site before a release.
