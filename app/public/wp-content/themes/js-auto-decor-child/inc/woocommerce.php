<?php
/**
 * JS Auto Decor WooCommerce integration.
 *
 * @package JS_Auto_Decor_Child
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) && ! function_exists( 'WC' ) ) {
	return;
}

/** Add a child-theme class to WooCommerce product cards. */
function jsad_product_loop_classes( $classes ) {
	$classes[] = 'jsad-product-card';
	return $classes;
}
add_filter( 'woocommerce_post_class', 'jsad_product_loop_classes' );

/** Add accessibility text to the cart button. */
function jsad_cart_button_text( $text ) {
	return $text ? $text : __( 'Add to cart', 'js-auto-decor-child' );
}
add_filter( 'woocommerce_product_add_to_cart_text', 'jsad_cart_button_text' );
add_filter( 'woocommerce_product_single_add_to_cart_text', 'jsad_cart_button_text' );

/** Set brand-consistent image sizes without changing product data. */
function jsad_product_image_sizes( $sizes ) {
	$sizes['width']  = 700;
	$sizes['height'] = 700;
	$sizes['crop']   = 1;
	return $sizes;
}
add_filter( 'single_product_archive_thumbnail_size', 'jsad_product_image_sizes' );

/** Add a body class to WooCommerce pages. */
function jsad_woocommerce_body_class( $classes ) {
	$classes[] = 'jsad-commerce-page';
	return $classes;
}
add_filter( 'body_class', 'jsad_woocommerce_body_class' );
