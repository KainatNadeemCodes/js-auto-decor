<?php
/**
 * JS Auto Decor Child Theme
 *
 * Child theme for the Kata WordPress theme.
 *
 * @package JS_Auto_Decor_Child
 * @since   1.0.0
 */

defined( 'ABSPATH'  ) || exit;

/**
 * Child theme version.
 *
 * @var string
 */
define( 'JS_AUTO_DECOR_CHILD_VERSION', '1.0.0' );

/**
 * Child theme directory path.
 *
 * @var string
 */
define( 'JS_AUTO_DECOR_CHILD_DIR', get_stylesheet_directory() );

/**
 * Child theme directory URI.
 *
 * @var string
 */
define( 'JS_AUTO_DECOR_CHILD_URI', get_stylesheet_directory_uri() );

/**
 * Load child-theme functionality.
 */
require_once JS_AUTO_DECOR_CHILD_DIR . '/inc/enqueue.php';

/**
 * Register child-theme support and compatibility behavior.
 *
 * @return void
 */
function js_auto_decor_child_setup() {
	load_child_theme_textdomain(
		'js-auto-decor-child',
		JS_AUTO_DECOR_CHILD_DIR . '/languages'
	);

	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 900,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 2,
				'max_rows'        => 8,
				'default_columns' => 4,
				'min_columns'     => 2,
				'max_columns'     => 5,
			),
		)
	);
}
add_action( 'after_setup_theme', 'js_auto_decor_child_setup', 20 );

/**
 * Add useful compatibility classes to the body element.
 *
 * These classes allow the child theme to target integrations without
 * assuming that optional plugins are installed or active.
 *
 * @param string[] $classes Existing body classes.
 * @return string[]
 */
function js_auto_decor_child_body_classes( $classes ) {
	if ( class_exists( 'WooCommerce' ) || function_exists( 'WC' ) ) {
		$classes[] = 'jsad-woocommerce-active';
	}

	if ( defined( 'ELEMENTOR_VERSION' ) || did_action( 'elementor/loaded' ) ) {
		$classes[] = 'jsad-elementor-active';
	}

	if (
		class_exists( 'ShopPress' ) ||
		class_exists( 'ShopPress\\Plugin' ) ||
		function_exists( 'shoppress' )
	) {
		$classes[] = 'jsad-shoppress-active';
	}

	if ( is_front_page() ) {
		$classes[] = 'jsad-front-page';
	}

	return array_unique( $classes );
}
add_filter( 'body_class', 'js_auto_decor_child_body_classes' );
