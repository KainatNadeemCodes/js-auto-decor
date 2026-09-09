<?php
/**
 * Order Tracking.
 *
 * @package ShopPress
 */

defined( 'ABSPATH' ) || exit;

global $post;

$description       = $args['description'] ? $args['description'] : '';
$orderid_title     = $args['orderid_title'] ? $args['orderid_title'] : '';
$order_placeholder = $args['order_placeholder'] ? $args['order_placeholder'] : '';
$email_title       = $args['email_title'] ? $args['email_title'] : '';
$email_placeholder = $args['email_placeholder'] ? $args['email_placeholder'] : '';
$button_text       = $args['button_text'] ? $args['button_text'] : '';

?>
<div class="sp-orders-tracking">
	<form action="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" method="post" class="woocommerce-form woocommerce-form-track-order track_order">
		<?php if ( $description ) : ?>
			<p class="order-tracking-description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>

		<p class="form-row form-row-first">
			<label for="orderid"><?php echo esc_html( $orderid_title ); ?></label>
			<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Form repopulation; nonce verified on submit. ?>
			<input class="input-text" type="text" name="orderid" id="orderid" value="<?php echo isset( $_REQUEST['orderid'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['orderid'] ) ) ) : ''; ?>" placeholder="<?php echo esc_attr( $order_placeholder ); ?>" />
        </p><?php // @codingStandardsIgnoreLine ?>

		<p class="form-row form-row-last">
			<label for="order_email"><?php echo esc_html( $email_title ); ?></label>
			<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Form repopulation; nonce verified on submit. ?>
			<input class="input-text" type="text" name="order_email" id="order_email" value="<?php echo isset( $_REQUEST['order_email'] ) ? esc_attr( sanitize_email( wp_unslash( $_REQUEST['order_email'] ) ) ) : ''; ?>" placeholder="<?php echo esc_attr( $email_placeholder ); ?>" />
        </p><?php // @codingStandardsIgnoreLine ?>
		<div class="clear"></div>

		<p class="form-row">
			<button type="submit" class="track-button button" name="track" value="<?php echo esc_attr( $button_text ); ?>"><?php echo esc_html( $button_text ); ?></button>
		</p>
		<?php wp_nonce_field( 'woocommerce-order_tracking', 'woocommerce-order-tracking-nonce' ); ?>
	</form>
</div>
