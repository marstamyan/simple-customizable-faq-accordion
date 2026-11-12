# Changelog

## 1.2.1

- Add a Settings action link on the Installed Plugins screen for users allowed to edit FAQ design settings.

## 1.2.0

- Add FAQ → Settings for all nine design options, sharing the existing theme mods and sanitizers with the Customizer.
- Protect admin saves with capability and nonce checks.
- Apply preview variables directly to containers so reordered stylesheets cannot override live edits; handle dynamically added FAQ markup.
- Supply a keyboard-accessible pencil and controls message independently of theme selective-refresh support.
- Disable LiteSpeed optimization for Customizer preview requests only.
- Add browser regressions for the pencil, late stylesheets, shared admin/Customizer saves and rejected invalid nonces.

## 1.1.0

- Fix the fatal Customizer callback and load live preview code only in Customizer previews.
- Replace direct PHP stylesheets and document-root assumptions with WordPress inline styles.
- Validate saved and previewed design values, unify defaults, and scope CSS variables to FAQ containers.
- Respect password protection and explicitly restrict shortcode output to published FAQ groups.
- Keep existing shortcode, post type, field keys and design setting names.
- Correct group-title checkbox handling and remove the stray checkbox value from headings.
- Give every repeated FAQ, question and answer its own HTML ID and ARIA relationship.
- Use native buttons, visible keyboard focus, hidden collapsed panels and optional arrow-key navigation.
- Eliminate duplicate synthetic touch/pointer clicks and nested-target JavaScript errors.
- Display complete long answers, preserve safe HTML and paragraphs, and fit narrow screens.
- Make answer content readable when JavaScript is unavailable.
- Use dark default question labels for better contrast on the original green background; preserve explicitly saved colors.
- Update Carbon Fields from 3.6.3 to 3.6.11 and depend on the library directly.
- Prefix Carbon bootstrap callbacks to avoid collisions with common theme example functions.
- Preserve third-party FAQ list columns and limit admin styles to relevant screens.
- Move Help under FAQ and replace the automatically loaded video embed with a link.
- Correct the README shortcode and installation instructions; add tests and a ZIP build script.

### Upgrade notes

No data migration or deletion is performed. Appearance settings still belong to the active theme. Custom site styles that explicitly target the former `a.mk-faq-accordion-title` or `dd` elements should use `.mk-faq-accordion-title` and `.mk-faq-accordion-content` instead: question controls are now buttons and answer panels are divs.

## 1.0.0

Initial version.
