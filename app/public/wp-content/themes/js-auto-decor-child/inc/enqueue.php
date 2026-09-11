<?php
/**
 * Front-end asset loading for JS Auto Decor Child.
 *
 * @package JS_Auto_Decor_Child
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return a version based on the file modification time when available.
 *
 * File modification versions improve cache invalidation during development
 * while falling back safely to the child-theme version in production.
 *
 * @param string $file Absolute file path.
 * @return string
 */
function js_auto_decor_child_asset_version( $file ) {
	if ( file_exists( $file ) ) {
		$modified = filemtime( $file );

		if ( false !== $modified ) {
			return (string) $modified;
		}
	}

	return JS_AUTO_DECOR_CHILD_VERSION;
}

/**
 * Enqueue parent and child theme assets.
 *
 * The parent stylesheet is loaded first. Child stylesheets are loaded
 * afterward so the child theme can safely override parent rules.
 *
 * @return void
 */
function js_auto_decor_child_enqueue_assets() {
	$child_dir = JS_AUTO_DECOR_CHILD_DIR;
	$child_uri = JS_AUTO_DECOR_CHILD_URI;

	$parent_style_path = get_template_directory() . '/style.css';
	$child_style_path  = $child_dir . '/style.css';
	$variables_path    = $child_dir . '/assets/css/variables.css';
	$base_path         = $child_dir . '/assets/css/base.css';

	/*
	 * Load the parent stylesheet explicitly. This avoids relying on CSS
	 * imports and preserves WordPress dependency ordering.
	 */
	wp_enqueue_style(
		'kata-parent-style',
		get_template_directory_uri() . '/style.css',
		array(),
		js_auto_decor_child_asset_version( $parent_style_path )
	);

	/*
	 * Load the child theme stylesheet after the parent stylesheet.
	 */
	wp_enqueue_style(
		'js-auto-decor-child-style',
		$child_uri . '/style.css',
		array( 'kata-parent-style' ),
		js_auto_decor_child_asset_version( $child_style_path )
	);

	/*
	 * Brand variables are loaded before the base layer.
	 */
	wp_enqueue_style(
		'js-auto-decor-child-variables',
		$child_uri . '/assets/css/variables.css',
		array( 'js-auto-decor-child-style' ),
		js_auto_decor_child_asset_version( $variables_path )
	);

	/*
	 * Base styles are loaded after variables and before future component
	 * stylesheets.
	 */
	wp_enqueue_style(
		'js-auto-decor-child-base',
		$child_uri . '/assets/css/base.css',
		array( 'js-auto-decor-child-variables' ),
		js_auto_decor_child_asset_version( $base_path )
	);
}
add_action( 'wp_enqueue_scripts', 'js_auto_decor_child_enqueue_assets', 100 );

/**
 * Add a preload hint for the child theme's primary stylesheet.
 *
 * The stylesheet itself remains registered through wp_enqueue_style().
 *
 * @param string[] $hints         Existing resource hints.
 * @param string   $relation_type Resource hint relation type.
 * @return string[]
 */
function js_auto_decor_child_resource_hints( $hints, $relation_type ) {
	if ( 'preload' !== $relation_type ) {
		return $hints;
	}

	$stylesheet_url = JS_AUTO_DECOR_CHILD_URI . '/assets/css/variables.css';

	$hints[] = array(
		'href' => esc_url( $stylesheet_url ),
		'as'   => 'style',
	);

	return $hints;
}
add_filter( 'wp_resource_hints', 'js_auto_decor_child_resource_hints', 10, 2 );
