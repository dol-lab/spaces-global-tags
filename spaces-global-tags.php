<?php
/**
 * Plugin Name:     Spaces Global Tags
 * Plugin URI:      https://github.com/dol-lab/spaces-global-tags/
 * Description:     WIP: Do not use yet. Experimental plugin to play around with Multisite Taxonomies.
 * Author:          Silvan Hagen
 * Author URI:      https://silvanhagen.com
 * Text Domain:     spaces-global-tags
 * Domain Path:     /languages
 * Version:         0.13.0
 * Network:         true
 *
 * @package         Spaces_Global_Tags
 */

/**
 * Copyright (c) 2019 Silvan Hagen - Consulting (email : silvan@silvanhagen.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Namespace Definition.
 */
namespace Spaces_Global_Tags;

/**
 * Load autoloader for classes.
 */
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require dirname( __FILE__ ) . '/vendor/autoload.php';
}

use Multisite_Term;
use WP_Error;
use WP_REST_Response;

/**
 * Constants to hold the taxonomy names.
 */
const GLOBAL_POST_TAG_TAX    = 'global_post_tag';
const GLOBAL_COMMENT_TAG_TAX = 'global_comment_tag';
/**
 * Constants for the version and assets dir.
 */
define( 'SPACES_GLOBAL_TAGS_ASSETS_URL', plugin_dir_url( __FILE__ ) . 'assets' );

/**
 * Initialize the global archive pages.
 */
new Global_Tags_Archive();

/**
 * Register our custom activation hook.
 *
 * @since 0.1.0
 */
register_activation_hook( __FILE__, __NAMESPACE__ . '\plugin_activate' );

/**
 * Register our custom deactivation hook.
 *
 * @since 0.4.0
 */
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\plugin_deactivate' );

/**
 * Plugin activation hook.
 *
 * Checks if it's a multiste and the necessary dependencies exist.
 * Adds a check to flush the rewrite rules in the system.
 *
 * @since 0.1.0
 */
function plugin_activate() {

	/**
	 * Set a flag to flush rewrite rules.
	 *
	 * @since 0.2.0
	 */
	if ( ! get_option( 'spaces_global_tags_flush_rewrite_rules_flag' ) ) {
		add_option( 'spaces_global_tags_flush_rewrite_rules_flag', true );
	}

	if ( ! is_multisite() ) {
		set_transient( 'spaces_global_tags_not_multisite', true, 5 );
	}

	if ( ! class_exists( 'Multitaxo_Plugin' ) ) {
		set_transient( 'spaces_global_tags_missing_dependency', true, 5 );
	}
}

/**
 * Plugin deactivation hook.
 *
 * @since 0.4.0
 */
function plugin_deactivate() {
	flush_rewrite_rules();
}

/**
 * Checks if during activation any transients were set.
 *
 * Adds dismissable error notices, in case it's not a
 * multisite or the necessary dependencies don't exists.
 *
 * @since 0.1.0
 */
function check_dependencies() {

	if ( get_transient( 'spaces_global_tags_not_multisite' ) ) {
		?>
		<div class="notice-error notice is-dismissible">
			<p><?php echo esc_html_x( 'This plugin needs to be run on a WordPress multisite installation.', 'network admin notice on activation', 'spaces-global-tags' ); ?></p>
		</div>
		<?php
		delete_transient( 'spaces_global_tags_not_multisite' );
	}

	if ( get_transient( 'spaces_global_tags_missing_dependency' ) ) {
		?>
		<div class="notice-error notice is-dismissible">
			<p><?php echo esc_html_x( 'This plugin needs the Multisite Taxonomies plugin to work properly.', 'network admin notice on activation', 'spaces-global-tags' ); ?></p>
		</div>
		<?php
		delete_transient( 'spaces_global_tags_missing_dependency' );
	}
}

add_action( 'network_admin_notices', __NAMESPACE__ . '\check_dependencies' );


/**
 * Flush rewrite rules if the previously added flag exists,
 * and then remove the flag.
 *
 * @since 0.2.0
 */
function flush_rewrite_rules_maybe() {

	if ( get_option( 'spaces_global_tags_flush_rewrite_rules_flag' ) ) {
		flush_rewrite_rules();
		delete_option( 'spaces_global_tags_flush_rewrite_rules_flag' );
	}
}

add_action( 'init', __NAMESPACE__ . '\flush_rewrite_rules_maybe', 20 );

/**
 * Unregister regular `post_tag` for `post` object type.
 *
 * @since 0.6.0
 */
function unregister_post_tag_taxonomy_for_post() {
	unregister_taxonomy_for_object_type( 'post_tag', 'post' );
}
add_action( 'init', __NAMESPACE__ . '\unregister_post_tag_taxonomy_for_post' );

/**
 * Registers `global_post_tag` taxonomy.
 *
 * @sicne 0.6.0
 */
function register_global_post_tag_taxonomy() {

	if ( ! function_exists( 'register_multisite_taxonomy' ) ) {
		return false;
	}

	/**
	 * Load taxonomy for Tags
	 */
	$labels = [
		'name'                       => __( 'Post Tags', 'spaces-global-tags' ),
		'singular_name'              => __( 'Post Tag', 'spaces-global-tags' ),
		'menu_name'                  => __( 'Post Tags', 'spaces-global-tags' ),
		'all_items'                  => __( 'All Post Tags', 'spaces-global-tags' ),
		'new_item_name'              => __( 'New Post Tag Name', 'spaces-global-tags' ),
		'add_new_item'               => __( 'Add New Post Tag', 'spaces-global-tags' ),
		'edit_item'                  => __( 'Edit Post Tag', 'spaces-global-tags' ),
		'update_item'                => __( 'Update Post Tag', 'spaces-global-tags' ),
		'view_item'                  => __( 'View Post Tag', 'spaces-global-tags' ),
		'separate_items_with_commas' => __( 'Separate post tags with commas', 'spaces-global-tags' ),
		'add_or_remove_items'        => __( 'Add or remove post tags', 'spaces-global-tags' ),
		'choose_from_most_used'      => __( 'Choose from the most used post tags', 'spaces-global-tags' ),
		'popular_items'              => __( 'Popular Post Tags', 'spaces-global-tags' ),
		'search_items'               => __( 'Search Post Tags', 'spaces-global-tags' ),
		'not_found'                  => __( 'No Post Tags Found', 'spaces-global-tags' ),
		'no_terms'                   => __( 'No post tags for this category', 'spaces-global-tags' ),
		'most_used'                  => __( 'Most Used', 'spaces-global-tags' ),
		'items_list'                 => __( 'Post Tags list', 'spaces-global-tags' ),
		'items_list_navigation'      => __( 'Post Tags list navigation', 'spaces-global-tags' ),
	];

	$args = [
		'labels'       => $labels,
		'hierarchical' => false,
	];

	$post_types = apply_filters( 'multisite_taxonomy_tags_post_types', [ 'post' ] );
	register_multisite_taxonomy( GLOBAL_POST_TAG_TAX, $post_types, $args );

	new Post_Tags();
}

add_action( 'init', __NAMESPACE__ . '\register_global_post_tag_taxonomy', 0 );

/**
 * Registers `global_comment_tag` taxonomy.
 *
 * @sicne 0.6.0
 */
function register_global_comment_tag_taxonomy() {

	if ( ! function_exists( 'register_multisite_taxonomy' ) ) {
		return false;
	}

	/**
	 * Load taxonomy for Tags
	 */
	$labels = [
		'name'                       => __( 'Comment Tags', 'spaces-global-tags' ),
		'singular_name'              => __( 'Comment Tag', 'spaces-global-tags' ),
		'menu_name'                  => __( 'Comment Tags', 'spaces-global-tags' ),
		'all_items'                  => __( 'All Comment Tags', 'spaces-global-tags' ),
		'new_item_name'              => __( 'New Comment Tag Name', 'spaces-global-tags' ),
		'add_new_item'               => __( 'Add New Comment Tag', 'spaces-global-tags' ),
		'edit_item'                  => __( 'Edit Comment Tag', 'spaces-global-tags' ),
		'update_item'                => __( 'Update Comment Tag', 'spaces-global-tags' ),
		'view_item'                  => __( 'View Comment Tag', 'spaces-global-tags' ),
		'separate_items_with_commas' => __( 'Separate comment tags with commas', 'spaces-global-tags' ),
		'add_or_remove_items'        => __( 'Add or remove comment tags', 'spaces-global-tags' ),
		'choose_from_most_used'      => __( 'Choose from the most used comment tags', 'spaces-global-tags' ),
		'popular_items'              => __( 'Popular Comment Tags', 'spaces-global-tags' ),
		'search_items'               => __( 'Search Comment Tags', 'spaces-global-tags' ),
		'not_found'                  => __( 'No Comment Tags Found', 'spaces-global-tags' ),
		'no_terms'                   => __( 'No comment tags for this category', 'spaces-global-tags' ),
		'most_used'                  => __( 'Most Used', 'spaces-global-tags' ),
		'items_list'                 => __( 'Comment Tags list', 'spaces-global-tags' ),
		'items_list_navigation'      => __( 'Comment Tags list navigation', 'spaces-global-tags' ),
	];

	$args = [
		'public'       => true,
		'labels'       => $labels,
		'hierarchical' => false,
	];

	$post_types = apply_filters( 'multisite_taxonomy_tags_post_types', [ 'post' ] );
	register_multisite_taxonomy( GLOBAL_COMMENT_TAG_TAX, $post_types, $args );

	new Comment_Tags();
}

add_action( 'init', __NAMESPACE__ . '\register_global_comment_tag_taxonomy', 0 );


/**
 * Fix the multisite term archive link on subsites to point to the main site.
 *
 * @param string         $multisite_termlink Link to the term archive page.
 * @param Multisite_Term $multisite_term object containing the multisite term.
 * @param string         $multisite_taxonomy multisite taxonomy name.
 *
 * @return string $multisite_termlink Link to the term archive page.
 *
 * TODO: Make this more robust for multi networks & subdomain installs.
 *
 * @since 0.10.0
 */
function fix_multitaxo_term_link( $multisite_termlink, $multisite_term, $multisite_taxonomy ) {

	if ( is_spaces_install() ) {
		$old_path = get_site()->path;
		$new_path = get_archive_path();

		if ( '/' === $old_path ) {
			$old_path = get_site()->domain . $old_path;
			$new_path = get_site()->domain . $new_path;
		}

		$multisite_termlink = str_replace( $old_path, $new_path, $multisite_termlink );
	} else {
		/**
		 * Works properly on the main site.
		 */
		if ( get_main_site_id() === get_current_blog_id() ) {
			return $multisite_termlink;
		}

		/**
		 * Get the path of the current site.
		 */
		$path               = get_site()->path;
		$multisite_termlink = str_replace( $path, '/', $multisite_termlink );
	}

	return $multisite_termlink;
}

add_filter( 'multisite_term_link', __NAMESPACE__ . '\fix_multitaxo_term_link', 10, 3 );

/**
 * Get the path for the multisite taxonomy archive pages.
 *
 * @return string $path path to the multisite taxonomy archive page.
 */
function get_archive_path() {

	$path = get_site()->path;

	if ( is_spaces_install() ) {
		$spaces_options = get_site_option( 'spaces_options' );
		if ( array_key_exists( 'shared_home_blog', $spaces_options ) && '' !== $spaces_options['shared_home_blog'] ) {
			$path = trailingslashit( $spaces_options['shared_home_blog'] );
			$path = '/' . ltrim( $path, '/\\' );
		}
	}

	return apply_filters( 'spaces_global_tags_archive_path', $path );
}

/**
 * Basic check for a spaces install.
 *
 * @return bool
 */
function is_spaces_install() {

	return class_exists( '\Spaces_Setup' );
}

/**
 * Check if this page could be multitaxo.
 *
 * @return bool
 */
function is_multitaxo() {
	global $wp;

	if ( false !== strpos(
		$wp->request,
		apply_filters( 'multisite_taxonomy_base_url_slug', 'multitaxo' )
	)
	) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check if the multisite term exists.
 *
 * @return bool
 */
function is_multisite_term() {
	if ( multisite_term_exists(
		sanitize_key( get_query_var( 'multisite_term' ) ),
		sanitize_key( get_query_var( 'multisite_taxonomy' ) )
	) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check if the multisite taxonomy exists.
 *
 * @return bool
 */
function is_multisite_taxonomy() {
	if ( multisite_taxonomy_exists(
		sanitize_key( get_query_var( 'multisite_taxonomy' ) )
	) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check if we are just browsing multitaxo and not a term or taxonomy.
 *
 * @return bool
 */
function is_multisite_taxonomies() {
	if ( is_multitaxo() && ! is_multisite_term() && ! is_multisite_taxonomy() ) {
		return true;
	} else {
		return false;
	}
}

function get_plugin_version() {
	$version = 0;
	if ( function_exists( 'get_plugin_data' ) ) {
		$plugin_data = get_plugin_data( __FILE__, false, false );
		$version     = $plugin_data['Version'];
	}
	return $version;
}

/**
 * Custom excerpt length on the archive pages.
 */
add_filter(
	'multitaxo_excerpt_length',
	function() {
		return 55;
	}
);

/**
 * Add rest endpoints for multisite taxonomies.
 */
add_action(
	'rest_api_init',
	function() {
		$multisite_taxonomies = get_multisite_taxonomies( [], 'objects' );

		foreach ( $multisite_taxonomies as $multisite_taxonomy ) {
			register_rest_route(
				'multitaxo/v1',
				$multisite_taxonomy->name,
				[
					'methods'  => 'GET',
					'callback' => __NAMESPACE__ . "\\get_{$multisite_taxonomy->name}_items",
				]
			);
		}

	}
);

/**
 * Get all terms in a certain taxonomy.
 *
 * TODO: Add caching layer
 *
 * @param string $taxonomy name of the taxonomy.
 * @return array|int|WP_Error
 */
function get_global_tag_items( $taxonomy ) {
	$terms = get_multisite_terms(
		[
			'taxonomy'   => $taxonomy,
			'fields'     => 'id=>name',
			'hide_empty' => false,
		]
	);

	$terms_array = [];

	foreach ( $terms as $key => $value ) {
		$terms_array[] = [
			'id'   => $key,
			'name' => $value,
		];
	}

	return $terms_array;
}

/**
 * Get all the post tags.
 *
 * @return WP_REST_Response
 */
function get_global_post_tag_items() {

	return new WP_REST_Response( get_global_tag_items( 'global_post_tag' ) );
}

/**
 * Get all the comment tags.
 *
 * @return WP_REST_Response
 */
function get_global_comment_tag_items() {
	return new WP_REST_Response( get_global_tag_items( 'global_comment_tag' ) );
}

/**
 * Scripts and CSS for tags auto completion.
 *
 * @since 0.13.0
 */
function autocomplete_scripts() {
	wp_enqueue_style( 'tribute', SPACES_GLOBAL_TAGS_ASSETS_URL . '/css/tribute.css', null, get_plugin_version() );
	wp_enqueue_script( 'tribute', SPACES_GLOBAL_TAGS_ASSETS_URL . '/js/tribute.min.js', null, get_plugin_version(), true );
	wp_enqueue_script( 'spaces-global-tags', SPACES_GLOBAL_TAGS_ASSETS_URL . '/js/functions.js', null, get_plugin_version(), true );
	wp_localize_script(
		'spaces-global-tags',
		'SpacesGlobalTags',
		[
			'routes' => [
				'commentTags' => get_rest_url( null, 'multitaxo/v1/global_comment_tag' ),
				'postTags'    => get_rest_url( null, 'multitaxo/v1/global_post_tag' ),
			],
		]
	);
}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\autocomplete_scripts' );

/*-------------------------------------------------  Tiny helpers ----------------------------------------------------*/

// Array for all the hooks.
$debug_tags = [];

/**
 * Simple debug function to display all the hooks run on the page.
 *
 * @param string $tag hook name.
 */
function debug_all_hooks( $tag ) {

	global $debug_tags;

	if ( in_array( $tag, $debug_tags ) ) {
		return;
	}

	echo '<pre>' . $tag . '</pre>';

	$debug_tags[] = $tag;
}

// add_action( 'all', __NAMESPACE__ . '\debug_all_hooks' );

// add_filter( 'found_posts', function( $found_posts, $query ) { var_dump( $found_posts ); }, 10, 2 );
