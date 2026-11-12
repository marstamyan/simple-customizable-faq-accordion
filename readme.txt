=== Simple Customizable FAQ Accordion ===
Contributors: marstamyan
Tags: faq, accordion, shortcode
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create FAQ groups with accessible accordion controls and customizable colors and sizes.

== Description ==

Add questions and answers through the FAQ menu, publish the group, and insert its shortcode into a page or post. Change colors and sizes through FAQ Customization in the WordPress Customizer. Carbon Fields is bundled; a separate library plugin is not required.

Each question supports mouse, touch and keyboard controls. Multiple groups can share a page. Answers remain readable if JavaScript is unavailable.

== Installation ==

1. Upload the complete plugin folder, including vendor, to wp-content/plugins, or upload the installable ZIP in the Plugins screen.
2. Activate Simple Customizable FAQ Accordion.
3. Open FAQ → Add New and publish a group of questions and answers.
4. Copy its shortcode, such as [mk-simple-faq id="123"], into a Shortcode block.
5. Open FAQ → Help for instructions and the customization link.

== Frequently Asked Questions ==

= What is the shortcode? =

[mk-simple-faq id="123"], replacing 123 with the published FAQ group's ID.

= Where are the design settings? =

Open FAQ → Settings to edit and save the design in the admin area, or FAQ → Help → Open FAQ customization for live preview. Both interfaces use the same settings, stored for the active theme.

= Will updating delete existing questions? =

No. Version 1.1.0 retains the existing FAQ post type, Carbon Fields field names and theme modification names. Back up the site before any plugin update.

== Changelog ==

= 1.2.0 =
* Add FAQ → Settings using the same validated theme settings as the Customizer.
* Apply live preview values directly to FAQ containers to withstand reordered stylesheets.
* Add a preview pencil independent of theme selective-refresh support.
* Disable LiteSpeed page optimization inside Customizer previews only.

= 1.1.0 =
* Fix Customizer initialization and WordPress installations in a subdirectory.
* Validate design values and generate styles through the WordPress style API.
* Fix duplicate IDs, title visibility and multiple FAQ instances.
* Respect FAQ publication status and password protection.
* Replace duplicate touch handlers with native accessible button controls.
* Keep long answers complete and narrow layouts usable.
* Update bundled Carbon Fields to 3.6.11 and remove the nested plugin package.
* Correct installation and shortcode documentation.

= 1.0.0 =
* Initial release.
