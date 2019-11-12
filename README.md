# Spaces Global Tags #
**Contributors:** neverything  
**Tags:** multisite, wpmu, taxonomies  
**Requires at least:** 4.5  
**Tested up to:** 5.3  
**Stable tag:** 0.18.0  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

Adds global tags for posts and comments in a multisite installation. Uses the [Multisite Taxonomies](https://github.com/dol-lab/multisite-taxonomies) plugin
to create taxonomies.

## Description ##

This plugin relies on the [Multisite Taxonomies](https://github.com/dol-lab/multisite-taxonomies) plugin to perform network wide queries. Adding and managing taxonomies in the network admin.

Spaces Global Tags creates two new multisite taxonomies. Both taxonomies are assigned to the `post` post-type. The first one `global_comment_tags` is storing `#tags` made in comments on the post. The second one `global_post_tags` allows post authors to create `#tags` while writing posts.

When using the frontend editor provided by [Spaces](https://kisd.de/en/spaces) it shows an autocompletion for existing tags and allows you to create new ones on the fly.

Other notable features:

* Global Tag archive pages `https://example.com/multitaxo/<taxonomy_name>/<tag_slug>`
* Global Taxonomy archive pages `https://example.com/multitaxo/<taxonomy_name>/`
* Allow tagging in the P2 style aka adding `#foo` in the `post_content` and `comment_text`.

## Installation ##

Clone it, love it, hate it.

1. Install the [Multisite Taxonomies](https://github.com/dol-lab/multisite-taxonomies) and network activate it.
2. Install this plugin and network activate it.
3. Find the taxonomies in the network admin.

## Frequently Asked Questions ##

### Does this replace the default tags in a WordPress multisite ###

No. But it does disable them on the `post` object type.

### Is there a global search available ###

No, not yet. It will be integrated with the Spaces search, but for now you will have to click on the tags to reach the archive pages.

### I found a bug, where can I report it? ###

Please check if the bug is already mentioned on the Github repository or [create a new issue](https://github.com/dol-lab/spaces-global-tags/issues).

### Can it do X, could you add Y? ###

For feature request, please head over to the [Github repository](https://github.com/dol-lab/spaces-global-tags/) and create a feature request or a pull request with your proposed feature.

We can't guarantee to build or merge your feature request, so consider creating a companion plugin using the filters provided by our plugin.

### Can I extend or change the behaviour of the plugin? ###

Certainly, we provide a bunch of useful hooks and so does the [Multisite Taxonomies](https://github.com/dol-lab/multisite-taxonomies) plugin.

* Filter `spaces_global_tags_archive_template_path`, change the path to the archive page template, default: `plugin_dir_path( __DIR__ ) . templates/template.php`.
* Action `spaces_global_tags_below_archive_title`, change what is displayed below the archive title, defaults to related tags of the same taxonomy.
* Filter `spaces_global_tags_post_types`, change the post types using the global taxonomies, defaults to `[ 'post' ]`.
* Filter `spaces_global_tags_user_roles`, change the roles allowed to create and add tags, defaults to `[ 'administrator', 'editor', 'author' ]`.
* Filter `spaces_global_tags_user_capabilities`, change the capabilities granted to the mentioned roles, defaults to `[ 'manage_multisite_terms', 'edit_multisite_terms', 'assign_multisite_terms' ]`.
* Filter `spaces_global_tags_archive_path`, change the default path for the archive pages. In the context of spaces it's `/home/` otherwise the main network site.
* Filter `spaces_global_tags_found_tags`, allows you to change the found tags in a text string (`post_content`, `comment_text`), defaults to array of found tags.
* Filter `spaces_global_tags_tag_link`, allows you to change the link destination for the tag archive page.
* Filter `spaces_global_tags_number_of_related_tags`, allows you to change the number of other tags shown in the same taxonomy, default is `30`.

## Screenshots ##

## Changelog ##

### 0.18.0 ###
Remove shortcodes from global tag archive excerpts.
Only show a max. of 30 other tags on a tag term archive.

### 0.17.0 ###
Updated readme with more useful information
Fixed a few typos in the code
Updated inline documentation

### 0.16.0 ###
Added a filter for roles to be allowed managing global tags.
Check and add capabilities to user roles so they can add and create global tags, fixes https://github.com/dol-lab/spaces-global-tags/issues/20

### 0.15.0 ###
Added a filter for post types with multisite taxonomies.
Implemented a check for post type when finding tags in content, fixes https://github.com/dol-lab/spaces-global-tags/issues/18

### 0.14.0 ###
Removed a CSS class on the global tag archive template for production.

### 0.13.0 ###
Added REST API endpoints for global comment and post tags.
Added Tribute JS by ZURB to allow auto completion for tags.
Implemented auto completion for CKEditor and comment forms.

### 0.12.0 ###
Changed approach for archive pages for now, using parts of https://github.com/HarvardChanSchool/multisite-taxonomies-frontend

### 0.11.0 ###
Added first spaces specific fixes for urls using spaces_options.

### 0.10.0 ###
Fixed a bug to link to the proper multisite term archive page.
First pass at filling the multisite term archive pages.
Changed reusable strings to constants.
Added GPL in plugin header.

### 0.9.0 ###
Fixed a bug that prevented comment and post tags to be linked properly.
Added implementation for global post tags to work in the same fashion as comment tags.
Added README.md
Added translation file.

### 0.8.0 ###
Add composer.json support.
Implement PSR4 autoloader for classes.
Add method to filter displayed comment/content with links to the tag archive pages.
Implement logic for parsing and settings terms for posts in comments.

### 0.7.0 ###
Implement abstract class Hashtag_Parser.
Setting up Post_Tags and Comment_Tags classes.

### 0.6.0 ###
Remove `post_tag` from `post` object.
Add `global_post_tag` to `post` object.
Add `global_comment_tag` to `post` object.

### 0.5.0 ###
Started re-build on Multisite Taxonomies plugin.

### 0.4.0 ###
Moved uninstall flush_rewrite_rules to deactivation hook.

### 0.3.0 ###
Implemented an uninstall.php file with a class to truly uninstall the plugin.

### 0.2.0 ###
Added custom rewrite rules for global tag archive pages.

### 0.1.0 ###
Initial version, very limited and only as a playground.
