=== Spaces Global Tags ===
Contributors: neverything
Tags: multisite, wpmu, taxonomies
Requires at least: 4.5
Tested up to: 5.2.2
Stable tag: 0.7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WIP: Do not use in production. This plugin explores the possibilities of global tags in a multisite install.

== Description ==

This plugin relies on the True Multisite Indexer to perform network wide queries.

It will in the future provide the following:

* Global Tag archive pages
* An overview of all tags
* Allow tagging of in the P2 style aka adding #foo in the post_content.

== Installation ==

Clone it, love it, hate it.

== Frequently Asked Questions ==

= Does this replace the default tags in a WordPress multisite =

No.

= Is there a global search available =

No.

== Screenshots ==

== Changelog ==

= 0.7.0 =
Implement abstract class Hashtag_Parser.
Setting up Post_Tags and Comment_Tags classes.

= 0.6.0 =
Remove `post_tag` from `post` object.
Add `global_post_tag` to `post` object.
Add `global_comment_tag` to `post` object.

= 0.5.0 =
Started re-build on Multisite Taxonomies plugin.

= 0.4.0 =
Moved uninstall flush_rewrite_rules to deactivation hook.

= 0.3.0 =
Implemented an uninstall.php file with a class to truly uninstall the plugin.

= 0.2.0 =
Added custom rewrite rules for global tag archive pages.

= 0.1.0 =
Initial version, very limited and only as a playground.
