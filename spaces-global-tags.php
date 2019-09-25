<?php
/**
 * Plugin Name:     Spaces Global Tags
 * Plugin URI:      https://github.com/dol-lab/spaces-global-tags/
 * Description:     WIP: Do not use yet. Experimental plugin to play around with Multisite Taxonomies.
 * Author:          Silvan Hagen
 * Author URI:      https://silvanhagen.com
 * Text Domain:     spaces-global-tags
 * Domain Path:     /languages
 * Version:         0.11.0
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
use Multisite_WP_Query;
use WP_Query;
use WP_Post;

/**
 * Constants to hold the taxonomy names.
 */
const GLOBAL_POST_TAG_TAX    = 'global_post_tag';
const GLOBAL_COMMENT_TAG_TAX = 'global_comment_tag';

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
		'rewrite'      => [
			'slug' => 'post-tag', // Nicer url part.
		],
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
		'rewrite'      => [
			'slug' => 'comment-tag', // Nicer url part.
		],
	];

	$post_types = apply_filters( 'multisite_taxonomy_tags_post_types', [ 'post' ] );
	register_multisite_taxonomy( GLOBAL_COMMENT_TAG_TAX, $post_types, $args );

	new Comment_Tags();
}

add_action( 'init', __NAMESPACE__ . '\register_global_comment_tag_taxonomy', 0 );

/**
 * Pre-populates the posts in WP_Query with Multisite_WP_Query if we are in the right context.
 * This avoids more queries being run.
 *
 * @param array|int $posts collection of Posts.
 * @param WP_Query  $query the default WP_Query.
 *
 * @return array|int array of posts or 0 to run WP_Query.
 *
 * @since 0.10.0
 */
function posts_pre_query_filter( $posts, WP_Query $query ) {

	/**
	 * Bail early if this isn't the main query or we are in admin context.
	 */
	if ( is_admin() || ! $query->is_main_query() ) {
		return null;
	}

	/**
	 * Check for our taxonomies to exists in the query vars.
	 */
	if ( false === array_key_exists( GLOBAL_POST_TAG_TAX, $query->query_vars )
		 && false === array_key_exists( GLOBAL_COMMENT_TAG_TAX, $query->query_vars ) ) {
		return null;
	}

	/**
	 * Prevent duplicate queries.
	 */
	remove_filter(
		'posts_pre_query',
		__NAMESPACE__ . '\posts_pre_query_filter',
		PHP_INT_MAX
	);

	/**
	 * Set the taxonomy we are looking for. If we made it this far,
	 * we are sure to be on either one of these.
	 */
	if ( array_key_exists( GLOBAL_POST_TAG_TAX, $query->query_vars ) ) {
		$multisite_taxonomy = GLOBAL_POST_TAG_TAX;
	} else {
		$multisite_taxonomy = GLOBAL_COMMENT_TAG_TAX;
	}

	/**
	 * Multisite term object.
	 */
	$multisite_term = get_multisite_term_by( 'slug', get_query_var( $multisite_taxonomy ), $multisite_taxonomy );

	/**
	 * Run a multisite query to fetch posts using Multisite_WP_Query.
	 */
	$multisite_query = new Multisite_WP_Query(
		[
			'multisite_term_ids' => [ $multisite_term->multisite_term_id ],
			'posts_per_page'     => 10,
		]
	);

	/**
	 * The famous have_posts() call.
	 *
	 * TODO: Maybe return a soft 404 or do something else.
	 */
	if ( 0 === count( $multisite_query->posts ) ) {
		return null;
	}

	$posts = $multisite_query->posts;

	$posts = transform_to_post_objects( $posts );

	// Set found_posts to allow pagination to work.
	$query->set( 'found_posts', $multisite_term->count );

	// Set max_num_pages to allow pagination to work.
	$query->set( 'max_num_pages', get_option( 'posts_per_page' ) % $multisite_term->count );

	// TODO: this is not the correct soltuion to replace a template part.
	// add_action( 'get_template_part', __NAMESPACE__ . '\replace_archive_content_template', 620, 3 );

	return $posts;
}

add_filter( 'posts_pre_query', __NAMESPACE__ . '\posts_pre_query_filter', PHP_INT_MAX, 2 );

/**
 * Transforms stdClass objects from multisite-taxonomies query to WP_Post objects.
 *
 * @param array $posts of stdClass fake post objects.
 *
 * @return array $posts of WP_Post objects.
 *
 * @since 0.10.0
 */
function transform_to_post_objects( $posts ) {

	$output = [];
	foreach ( $posts as $post ) {
		// Make sure we set the filter to 'raw'.
		$post->filter = 'raw';

		// Fix the post_name on the_post.
		$post->post_name = sanitize_title_with_dashes( $post->post_title );

		// Set correct post type.
		$post->post_type = 'post';

		// Turn them into WP_Post objects even if it's sort of fake.
		$output[] = new WP_Post( $post );
	}
	return $output;
}

/**
 * Function to transform the display of a given post in multisite.
 *
 * @param WP_Post $post current post object.
 *
 * @since 0.10.0
 */
function transform_the_post_maybe( $post ) {

	/**
	 * Check if our fake property exists, if not, bail!
	 */
	if ( ! isset( $post->blog_id ) ) {
		return;
	}

	/**
	 * Filter the post link to provide a proper permalink.
	 */
	add_filter( 'post_link', __NAMESPACE__ . '\get_proper_permalink', 620, 3 );
}

add_action( 'the_post', __NAMESPACE__ . '\transform_the_post_maybe' );

/**
 * Filter the post link to provide a proper permalink.
 *
 * @param null|string $permalink current permalink for the post.
 * @param WP_Post     $post post object.
 * @param bool        $leavename should the name stay or not.
 *
 * @return string updated permalink.
 *
 * @since 0.10.0
 */
function get_proper_permalink( $permalink, $post, $leavename ) {

	if ( get_main_site_id() !== $post->blog_id ) {
		$permalink = get_site_url( $post->blog_id, trailingslashit( $post->post_name ) );
	}
	return $permalink;
}

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
 * Helper to maybe replace the template.
 *
 * @param $slug
 * @param $name
 * @param $templates
 */
function replace_archive_content_template( $slug, $name, $templates ) {

}

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
