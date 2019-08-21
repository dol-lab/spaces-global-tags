<?php
/**
 * Plugin Name:     Spaces Global Tags
 * Plugin URI:      https://github.com/dol-lab/spaces-global-tags/
 * Description:     WIP: Do not use yet. Experimental plugin to play around with True Multisite Indexer.
 * Author:          Silvan Hagen
 * Author URI:      https://silvanhagen.com
 * Text Domain:     spaces-global-tags
 * Domain Path:     /languages
 * Version:         0.5.0
 *
 * @package         Spaces_Global_Tags
 */

/**
 * Namespace Definition.
 */
namespace Spaces_Global_Tags;

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

