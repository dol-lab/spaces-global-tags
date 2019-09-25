# Spaces Global Tags #
**Contributors:** neverything  
**Tags:** multisite, wpmu, taxonomies  
**Requires at least:** 4.5  
**Tested up to:** 5.2.2  
**Stable tag:** 0.11.0  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

**WIP:** Do not use in production. This plugin explores the possibilities of global tags in a multisite install.  

## Description ##

This plugin relies on the True Multisite Indexer to perform network wide queries.

It will in the future provide the following:

* Global Tag archive pages
* An overview of all tags
* Allow tagging of in the P2 style aka adding #foo in the post_content.

## Installation ##

Clone it, love it, hate it.

## Frequently Asked Questions ##

### Does this replace the default tags in a WordPress multisite ###

No. But it does disable them on posts.

### Is there a global search available ###

No.

## Screenshots ##

## Changelog ##

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
