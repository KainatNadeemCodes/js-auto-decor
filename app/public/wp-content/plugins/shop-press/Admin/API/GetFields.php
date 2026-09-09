<?php
/**
 * Get Fields.
 *
 * @package ShopPress
 */

namespace ShopPress\Admin\API;

defined( 'ABSPATH' ) || exit;

use ShopPress\Admin\PagesFields;

class GetFields {
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
	 * Get settings fields.
	 *
	 * @since 1.2.0
	 *
	 * @param object $request
	 */
	public function get_fields( $request ) {
		$type     = $request->get_param( 'type' );
		$template = $request->get_param( 'template' );
		$module   = $request->get_param( 'module' );
		$page     = $request->get_param( 'page' );
		$parent   = $request->get_param( 'parent' );
		$setting  = $request->get_param( 'setting' );

		$class_name     = null;
		$class_name_pro = null;
		$method_name    = null;

		if ( $type === 'features' && isset( $type ) ) {

			$templates = PagesFields\Templates::fields();
			$modules   = PagesFields\Modules::fields();

			return array(
				'templates' => $templates,
				'modules'   => $modules,
			);
		}

		if ( $type === 'template' && isset( $template ) ) {
			$class_name     = 'ShopPress\\Admin\\SettingsFields\\Templates';
			$class_name_pro = 'ShopPressPro\\Admin\\SettingsFields\\Templates';
			$method_name    = $template;
		} elseif ( isset( $parent ) && isset( $setting ) ) {
			$class_name     = 'ShopPress\\Admin\\SettingsFields\\' . $parent;
			$class_name_pro = 'ShopPressPro\\Admin\\SettingsFields\\' . $parent;
			$method_name    = $setting;
		} elseif ( $type === 'module' && isset( $module ) ) {
			$class_name     = 'ShopPress\\Admin\\SettingsFields\\Modules';
			$class_name_pro = 'ShopPressPro\\Admin\\SettingsFields\\Modules';
			$method_name    = $module;
		}

		$fields = array();
		if ( $class_name && $method_name ) {
			if ( method_exists( $class_name, $method_name ) ) {
				$fields = call_user_func( array( $class_name, $method_name ) );
			}
			if ( $class_name_pro && ! class_exists( $class_name_pro ) ) {
				$pro_path = defined( 'SHOPPRESS_PRO_PATH' ) ? SHOPPRESS_PRO_PATH : $this->get_pro_plugin_path();
				if ( $pro_path ) {
					$pro_modules_file = $pro_path . 'Admin/SettingsFields/Modules.php';
					if ( file_exists( $pro_modules_file ) ) {
						require_once $pro_modules_file;
					}
				}
			}
			if ( class_exists( $class_name_pro ) && method_exists( $class_name_pro, $method_name ) ) {
				$fields = call_user_func( array( $class_name_pro, $method_name ) );
			}
		}

		$filter_name = $method_name ? "shoppress/settings_fields/{$type}/{$method_name}" : 'shoppress/settings_fields/unknown';
		return apply_filters( $filter_name, $fields );
	}

	/**
	 * Get ShopPress Pro plugin path when constant is not defined.
	 *
	 * @return string|null
	 */
	private function get_pro_plugin_path() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugins = get_plugins();
		foreach ( array_keys( $plugins ) as $plugin_file ) {
			if ( strpos( $plugin_file, 'shop-press-pro' ) === 0 && is_plugin_active( $plugin_file ) ) {
				return trailingslashit( WP_PLUGIN_DIR . '/' . dirname( $plugin_file ) );
			}
		}
		return null;
	}
}
