<?php
/**
 * Plugin Name:     Spaces Global Tags
 * Plugin URI:      https://github.com/dol-lab/spaces-global-tags/
 * Description:     WIP: Do not use yet. Experimental plugin to play around with Multisite Taxonomies.
 * Author:          Silvan Hagen
 * Author URI:      https://silvanhagen.com
 * Text Domain:     spaces-global-tags
 * Domain Path:     /languages
 * Version:         0.8.0
 * Network:         true
 *
 * @package         Spaces_Global_Tags
 */

/**
 * Namespace Definition.
 */
namespace Spaces_Global_Tags;

require __DIR__ . '/vendor/autoload.php';

/**
 * Dependency check.
 */
use Multitaxo_Plugin;

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
	/**
	 * Load taxonomy for Tags
	 */
	$labels     = [
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

	$args       = [
		'labels'       => $labels,
		'hierarchical' => false,
	];

	$post_types = apply_filters( 'multisite_taxonomy_tags_post_types', [ 'post' ] );
	register_multisite_taxonomy( 'global_post_tag', $post_types, $args );

	new Post_Tags();
}

add_action( 'init', __NAMESPACE__ . '\register_global_post_tag_taxonomy', 0 );

/**
 * Registers `global_comment_tag` taxonomy.
 *
 * @sicne 0.6.0
 */
function register_global_comment_tag_taxonomy() {
	/**
	 * Load taxonomy for Tags
	 */
	$labels     = [
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

	$args       = [
		'labels'       => $labels,
		'hierarchical' => false,
	];

	$post_types = apply_filters( 'multisite_taxonomy_tags_post_types', [ 'post' ] );
	register_multisite_taxonomy( 'global_comment_tag', $post_types, $args );

	new Comment_Tags();
}

add_action( 'init', __NAMESPACE__ . '\register_global_comment_tag_taxonomy', 0 );



