<?php
/**
 * Product Content
 *
 * @package ShopPress\Templates
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
global $sp_custom_loop_template_id;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
$builder_type = sp_get_builder_type( $sp_custom_loop_template_id );

?>
<li <?php wc_product_class( '', $product ); ?>>
	<?php
	if ( 'elementor' === $builder_type && class_exists( '\Elementor\Plugin' ) ) {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor outputs safe builder HTML.
		echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $sp_custom_loop_template_id, false );
	} elseif ( 'block_editor' === $builder_type ) {

		$content = get_post_field( 'post_content', $sp_custom_loop_template_id );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- do_blocks outputs safe block HTML.
		echo do_blocks( $content );
	}
	?>
</li>


