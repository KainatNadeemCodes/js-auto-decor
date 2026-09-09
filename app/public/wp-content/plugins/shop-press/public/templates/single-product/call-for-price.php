<?php
/**
 * Call for price.
 *
 * @package ShopPress
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="sp-call-for-price">
	<a href="tel:<?php echo esc_attr( $args['btn_phone_number'] ); ?>" class="button"><?php echo esc_html( $args['btn_text'] ); ?></a>
</div>
