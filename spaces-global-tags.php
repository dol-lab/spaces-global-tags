<?php
/**
 * Plugin Name:     Spaces Global Tags
 * Plugin URI:      https://github.com/dol-lab/spaces-global-tags/
 * Description:     WIP: Do not use yet. Experimental plugin to play around with True Multisite Indexer.
 * Author:          Silvan Hagen
 * Author URI:      https://silvanhagen.com
 * Text Domain:     spaces-global-tags
 * Domain Path:     /languages
 * Version:         0.4.0
 *
 * @package         Spaces_Global_Tags
 */

/**
 * Namespace Definition.
 */
namespace Spaces_Global_Tags;

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

	if ( ! function_exists( 'network_the_title' ) ) {
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
			<p><?php echo esc_html_x( 'This plugin needs the True Multisite Indexer plugin to work properly.', 'network admin notice on activation', 'spaces-global-tags' ); ?></p>
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
 * Add custom rewrite rules.
 *
 * @since 0.2.0
 */
function add_rewrite_rules() {
	add_rewrite_tag( '%global-tag%', '([^&]+)' );
	add_rewrite_rule( '^global-tag/([^/]*)/?', 'index.php?global-tag=$matches[1]', 'top' );
}

add_action( 'init', __NAMESPACE__ . '\add_rewrite_rules' );

/**
 * Add custom query vars for global tags.
 *
 * @since 0.2.0
 *
 * @param array $vars Available query vars.
 */
function add_query_vars( $vars ) {
	$vars[] = 'global-tag';
	return $vars;
}

add_filter( 'query_vars', __NAMESPACE__ . '\add_query_vars' );

/**
 * A dummy template tag to display posts according to a tag.
 * This is just to play with the possibilites.
 *
 * @since 0.1.0
 *
 * @param string $tag_slug A slug for a tag.
 */
function get_posts_by_tag_slug( string $tag_slug = '' ) {
	if ( '' === $tag_slug ) {
		return;
	}
	$args = [
		'posts_per_page' => 50,
		'tax_query'      => [
			[
				'taxonomy' => 'post_tag',
				'field'    => 'slug',
				'terms'    => esc_sql( $tag_slug ),
			],
		],
	];

	$network_query = new \Network_Query( $args );

	if ( $network_query->have_posts() ) :
		while ( $network_query->have_posts() ) :
			$network_query->the_post();
			$blog_info = get_blog_details( $network_query->post->BLOG_ID );

			echo '<h2 id="post-' . esc_attr( $network_query->post->ID ) . '" class="blog-' . esc_attr( $network_query->post->BLOG_ID ) . '">';
			echo '<a href="' . esc_html( network_get_permalink( $network_query->post->BLOG_ID, $network_query->post->ID ) ) . '">' . esc_html( $network_query->post->post_title ) . '</a>';
			echo '<br><small>' . esc_html( $blog_info->blogname ) . '</small>';
			echo '</h3>';

		endwhile;
	endif;
	network_reset_postdata();
}
