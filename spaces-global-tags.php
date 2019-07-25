<?php
/**
 * Plugin Name:     Spaces Global Tags
 * Plugin URI:      https://github.com/dol-lab/spaces-global-tags/
 * Description:     WIP: Do not use yet. Experimental plugin to play around with True Multisite Indexer.
 * Author:          Silvan Hagen
 * Author URI:      https://silvanhagen.com
 * Text Domain:     spaces-global-tags
 * Domain Path:     /languages
 * Version:         0.1.0
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
 * Plugin activation hook.
 * Checks if it's a multiste and the necessary dependencies exist.
 *
 * @since 0.1.0
 */
function plugin_activate() {
	if ( ! is_multisite() ) {
		set_transient( 'spaces_global_tags_not_multisite', true, 5 );
	}

	if ( ! function_exists( 'network_the_title' ) ) {
		set_transient( 'spaces_global_tags_missing_dependency', true, 5 );
	}
}

/**
 * Checks if during activation any transients were set.
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
 * A dummy template tag to display posts according to a tag.
 * This is just to play with the possibilites.
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
