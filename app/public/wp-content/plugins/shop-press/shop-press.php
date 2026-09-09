<?php
/**
 * Plugin Name:       ShopPress - Shop Builder for Elementor and WooCommerce
 * Plugin URI:        https://webnus.net/shoppress
 * Description:       Shop builder for WooCommerce with Elementor integration.
 * Version:           1.6.0
 * Requires at least: 6.2
 * Requires PHP:      7.1
 * Author:            Webnus
 * Author URI:        https://webnus.net
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       shop-press
 * Domain Path:       /languages
 * Elementor tested up to: 4.1.3
 */

defined( 'ABSPATH' ) || exit;

define( 'SHOPPRESS_VERSION', '1.6.0' );
define( 'SHOPPRESS_PATH', plugin_dir_path( __FILE__ ) );
define( 'SHOPPRESS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Declare compatibility with WooCommerce High-Performance Order Storage (HPOS)
 * and the Cart & Checkout Blocks.
 *
 * ShopPress accesses order data through the WooCommerce order objects, so it is
 * compatible with the custom order tables feature, and it keeps the classic
 * cart/checkout shortcode experience available alongside the block-based one.
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	}
);

if ( ! file_exists( SHOPPRESS_PATH . 'vendor/autoload.php' ) ) {
	return;
}

require SHOPPRESS_PATH . 'vendor/autoload.php';

/**
 * Initializes the Plugin class.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'shoppress' ) ) {
	function shoppress() {
		ShopPress\Plugin::instance();
	}
}

// Initialize the plugin.
shoppress();

// Add default option.
register_activation_hook( __FILE__, array( 'ShopPress\Plugin', 'add_sp_option' ) );

// Create woocommerce default templates.
register_activation_hook( __FILE__, array( 'ShopPress\Templates\Main', 'create_templates' ) );
