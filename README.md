# Simple Customizable FAQ Accordion

A WordPress plugin for creating FAQ groups and displaying them as keyboard-accessible accordions. Answers support safe HTML and paragraphs. Colors and sizes can be changed in the WordPress Customizer.

## Requirements

- WordPress 6.0 or newer.
- PHP 7.4 or newer. Use a PHP release still supported by your hosting provider for a live site.
- Carbon Fields is bundled; no separate Carbon Fields plugin is required.

## Installation

Upload the complete `simple-customizable-faq-accordion` ZIP through **Plugins → Add New → Upload Plugin**, then activate it. A package must include the `vendor` directory. If checking out source without bundled dependencies, run `composer install --no-dev --prefer-dist --no-interaction` in this directory before activation.

If downloading GitHub's **Code → Download ZIP**, extract the archive and rename its top-level folder (for example, `simple-customizable-faq-accordion-main`) to `simple-customizable-faq-accordion`. Upload that folder to `wp-content/plugins`, or ZIP the renamed folder and upload it through WordPress. Keep this folder name when updating an existing installation.

For updates from 1.0.0, see the [upgrade notes](CHANGELOG.md#upgrade-notes), especially if your theme has custom FAQ CSS.

## Usage

1. Open **FAQ → Add New** and enter a title for the group.
2. Add question/answer rows. Answers can contain paragraphs, links, lists and other safe WordPress HTML.
3. Choose whether to display the group title, then publish the FAQ.
4. Copy the shortcode shown in the editor or FAQ list, for example `[mk-simple-faq id="123"]`.
5. Add it to a **Shortcode** block on a page or post. Replace `123` with your FAQ group's ID.

Several FAQ groups, including repeated copies of the same group, can appear on one page. Each answer opens independently. Native buttons support mouse, touch, Enter and Space; arrow keys, Home and End move between questions. If JavaScript is unavailable, answers remain readable.

Only published FAQ groups are displayed. Password-protected groups are hidden unless a matching WordPress password cookie already exists; the shortcode does not provide a password entry form. Drafts, private posts, scheduled posts, trashed posts and other post types are not displayed.

## Appearance

Open **FAQ → Settings**, or click **Settings** below the plugin name on the Installed Plugins screen, to change colors, font sizes and maximum width directly in the admin area, then save. Alternatively, open **FAQ → Help → Open FAQ customization** and select **FAQ Customization**, or click the pencil on a FAQ in its preview.

Both interfaces edit the same settings for the active theme: there are no separate admin and Customizer overrides. Reload an already open Customizer after saving in the admin area so an older unsaved draft is not published over the new values.

Live preview applies design variables directly to FAQ containers, including FAQ markup added later. The pencil does not require selective-refresh support from the theme. LiteSpeed page optimization is disabled for Customizer preview requests only. After upgrading, clear LiteSpeed's cache once to remove older optimized assets.

Custom themes must call `wp_head()` before `</head>` and `wp_footer()` before `</body>`. WordPress itself prints the Customizer's connection settings in the footer; missing theme hooks can prevent the entire preview connection from initializing. FAQ → Settings remains available without using the Customizer.

Appearance settings belong to the active theme, matching the original plugin. Switching themes uses that theme's settings. Existing setting and FAQ data keys have been preserved. The default question text is now dark for better contrast on the green background; explicitly saved colors remain unchanged.

## Development and testing

Version 1.2.0 passed 28 WordPress integration checks, 26 shortcode checks and 13 browser scenarios on WordPress 7.1.4 / PHP 8.3.36. New scenarios verify admin saves, invalid nonce rejection, settings shared with the Customizer, the edit pencil and live changes despite a later conflicting stylesheet. The PHP debug log and uncaught JavaScript error list were empty.

Version 1.1.0 was tested on WordPress **7.1.4**, PHP **8.3.36**, SQLite and Microsoft Edge: 28 integration checks, 26 shortcode checks and 12 browser scenarios passed, including actual admin form saving and Customizer live preview/publication. The PHP debug log was empty. PHP syntax was also checked on PHP 7.4.

This test run does not establish compatibility with every theme, other plugins, MySQL/MariaDB or all WordPress/PHP versions.

See [tests/README.md](tests/README.md) for standalone checks and real WordPress/browser tests. Integration tests must use a disposable site because they create test content.

Dependencies are locked in `composer.lock`. To refresh the bundled production library, run `composer update --no-dev --prefer-dist --no-interaction`, then repeat the tests. The dependency is the `htmlburger/carbon-fields` library, not the separate plugin installer package.

To build an installable package on Windows:

```powershell
./tools/build-plugin.ps1 -Destination ../outputs/simple-customizable-faq-accordion-1.2.1.zip
```

## Support and license

Report problems through [GitHub issues](https://github.com/marstamyan/simple-customizable-faq-accordion/issues). The plugin is licensed under GPL-2.0-or-later. Bundled Carbon Fields is GPL-2.0-only; see its included license.

The original [video tutorial](https://www.youtube.com/watch?v=Tiqec_swMGY) shows the older interface.
