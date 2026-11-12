# Simple Customizable FAQ Accordion

A WordPress plugin for creating FAQ groups and displaying them as keyboard-accessible accordions. Answers support safe HTML and paragraphs. Colors and sizes can be changed in the WordPress Customizer.

## Requirements

- WordPress 6.0 or newer.
- PHP 7.4 or newer. Use a PHP release still supported by your hosting provider for a live site.
- Carbon Fields is bundled; no separate Carbon Fields plugin is required.

## Installation

Upload the complete `simple-customizable-faq-accordion` ZIP through **Plugins → Add New → Upload Plugin**, then activate it. A package must include the `vendor` directory. If checking out source without bundled dependencies, run `composer install --no-dev --prefer-dist --no-interaction` in this directory before activation.

## Usage

1. Open **FAQ → Add New** and enter a title for the group.
2. Add question/answer rows. Answers can contain paragraphs, links, lists and other safe WordPress HTML.
3. Choose whether to display the group title, then publish the FAQ.
4. Copy the shortcode shown in the editor or FAQ list, for example `[mk-simple-faq id="123"]`.
5. Add it to a **Shortcode** block on a page or post. Replace `123` with your FAQ group's ID.

Several FAQ groups, including repeated copies of the same group, can appear on one page. Each answer opens independently. Native buttons support mouse, touch, Enter and Space; arrow keys, Home and End move between questions. If JavaScript is unavailable, answers remain readable.

Only published FAQ groups are displayed. Password-protected groups require the normal WordPress password cookie. Drafts, private posts, scheduled posts, trashed posts and other post types are not displayed.

The old README incorrectly mentioned `[faq_accordion]`; the implemented shortcode is `[mk-simple-faq id="123"]`.

## Appearance

Open **FAQ → Help → Open FAQ customization** (or **Appearance → Customize**) and select **FAQ Customization**. View a page containing a FAQ in the preview, then change colors, font sizes and maximum width. This also works with block themes through the Help link.

Appearance settings belong to the active theme, matching the original plugin. Switching themes uses that theme's settings. Existing setting and FAQ data keys have been preserved. The default question text is now dark for better contrast on the green background; explicitly saved colors remain unchanged.

## Development and testing

See [tests/README.md](tests/README.md) for standalone checks and real WordPress/browser tests. Integration tests must use a disposable site because they create test content.

Dependencies are locked in `composer.lock`. To refresh the bundled production library, run `composer update --no-dev --prefer-dist --no-interaction`, then repeat the tests. The dependency is the `htmlburger/carbon-fields` library, not the separate plugin installer package.

To build an installable package on Windows:

```powershell
./tools/build-plugin.ps1 -Destination ../outputs/simple-customizable-faq-accordion-1.1.0.zip
```

## Support and license

Report problems through [GitHub issues](https://github.com/marstamyan/simple-customizable-faq-accordion/issues). The plugin is licensed under GPL-2.0-or-later. Bundled Carbon Fields is GPL-2.0-only; see its included license.

The original [video tutorial](https://www.youtube.com/watch?v=Tiqec_swMGY) shows the older interface.
