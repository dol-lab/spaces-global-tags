<?php
/**
 * Spaces Global Tags uninstaller
 *
 * Used when clicking "Delete" from inside of WordPress's plugins page.
 *
 * @package Spaces_Global_Tags
 * @since   0.3.0
 */

/**
 * Class Spaces_Global_Tags_Uninstaller
 */
class Spaces_Global_Tags_Uninstaller {

	/**
	 * Initialize uninstaller
	 *
	 * Perform some checks to make sure plugin can/should be uninstalled
	 *
	 * @since 0.3.0
	 */
	public function __construct() {

		// Exit if accessed directly.
		if ( ! defined( 'ABSPATH' ) ) {
			$this->exit_uninstaller();
		}

		// Not uninstalling.
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			$this->exit_uninstaller();
		}

		// Not uninstalling.
		if ( ! WP_UNINSTALL_PLUGIN ) {
			$this->exit_uninstaller();
		}

		// Not uninstalling this plugin.
		if ( dirname( WP_UNINSTALL_PLUGIN ) !== dirname( plugin_basename( __FILE__ ) ) ) {
			$this->exit_uninstaller();
		}

		// Uninstall Spaces_Global_Tags.
		self::clean_options();
	}

	/**
	 * Cleanup options
	 *
	 * Deletes Spaces_Global_Tags options and transients.
	 *
	 * @since 0.3.0
	 * @return void
	 */
	protected static function clean_options() {

		// Delete options.
		delete_option( 'spaces_global_tags_flush_rewrite_rules_flag' );

		// Delete transients.
		delete_transient( 'spaces_global_tags_not_multisite' );
		delete_transient( 'spaces_global_tags_missing_dependency' );

	}

	/**
	 * Exit uninstaller
	 *
	 * Gracefully exit the uninstaller if we should not be here
	 *
	 * @since 0.3.0
	 * @return void
	 */
	protected function exit_uninstaller() {

		status_header( 404 );
		exit;

	}
}

new Spaces_Global_Tags_Uninstaller();
