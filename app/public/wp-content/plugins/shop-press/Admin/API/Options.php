<?php
/**
 * Options.
 *
 * @package ShopPress
 */

namespace ShopPress\Admin\API;

defined( 'ABSPATH' ) || exit;

class Options {
	/**
	 * Instance of this class.
	 *
	 * @since  1.0.0
	 */
	public static $instance;

	/**
	 * Provides access to a single instance of a module using the singleton pattern.
	 *
	 * @since   1.0.0
	 *
	 * @return  object
	 */
	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Update options.
	 *
	 * @since 1.0.0
	 */
	private function update_options( $options ) {

		$options = apply_filters( 'shoppress_update_sp_admin_settings', $options );

		$success = update_option( 'sp_admin', json_encode( $options ) );

		update_option( 'sp_need_rewrite_rules', 'yes' );

		return $success;
	}

	/**
	 * Get.
	 *
	 * @since 1.0.0
	 */
	public function get() {
		$sp_admin = json_decode( get_option( 'sp_admin' ), true );

		// Guarantee the response always exposes the full options structure
		// ( templates, modules, addons, general ). When the stored option is
		// missing, partial, or corrupted - e.g. on a fresh setup where the
		// activation hook never ran, or after an upgrade - the admin React app
		// would otherwise receive an undefined "templates"/"modules" set and
		// crash while rendering the Templates and Modules screens.
		$defaults = \ShopPress\Admin\DefaultOptions\Options::get_default_options();

		if ( ! is_array( $sp_admin ) ) {
			$sp_admin = array();
		}

		foreach ( $defaults as $group_key => $group_defaults ) {

			if ( ! isset( $sp_admin[ $group_key ] ) || ! is_array( $sp_admin[ $group_key ] ) ) {

				$sp_admin[ $group_key ] = $group_defaults;
			} elseif ( is_array( $group_defaults ) ) {

				// Keep saved values, add any keys introduced by newer versions.
				$sp_admin[ $group_key ] = $sp_admin[ $group_key ] + $group_defaults;
			}
		}

		$sp_admin = apply_filters( 'shoppress_get_sp_admin_settings', $sp_admin );

		return new \WP_REST_Response(
			$sp_admin,
			200
		);
	}

	/**
	 * Update.
	 *
	 * @since 1.0.0
	 */
	public function update( $request ) {
		$options = $request->get_json_params();

		if ( ! empty( $options ) || is_array( $options ) ) {

			$updated = $this->update_options( $options );

			if ( ! is_wp_error( $updated ) ) {
				return new \WP_REST_Response( true, 200 );
			}
		}

		return false;
	}
}
