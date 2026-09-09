<?php
/**
 * Suggest Price.
 *
 * @package ShopPress
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( isset( $_REQUEST[ 'sp_suggest_price_' . $args['id'] ] ) && wp_verify_nonce( isset( $_POST['sp_suggest_price_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['sp_suggest_price_nonce'] ) ) : '', 'sp_suggest_price_' . $args['id'] ) ) {

	$sp_name    = isset( $_POST['sp_price_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sp_price_name'] ) ) : '';
	$sp_email   = isset( $_POST['sp_price_email'] ) ? sanitize_email( wp_unslash( $_POST['sp_price_email'] ) ) : '';
	$sp_price   = isset( $_POST['sp_price'] ) ? sanitize_text_field( wp_unslash( $_POST['sp_price'] ) ) : '';
	$sp_message = isset( $_POST['sp_price_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sp_price_message'] ) ) : '';

	$to      = esc_attr( $args['email_address'] );
	$subject = sprintf(
			/* translators: 1: product title, 2: price value */
			__( 'Suggest price for %1$s Price: %2$s', 'shop-press' ),
			$product->get_title(),
			$sp_price
		);
	$body    = $sp_message;
	$headers = array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . $sp_name . ' <' . $sp_email . '>' );

	wp_mail( $to, $subject, $body, $headers );
}

?>

<div class="sp-suggest-price">
	<a href="#" class="button"><?php echo esc_html( $args['btn_text'] ); ?></a>
</div>

<div class="sp-suggest-price-form" data-post_id="<?php echo esc_attr( $args['id'] ); ?>" data-el_id="<?php echo esc_attr( $args['id'] ); ?>">
	<?php // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- REQUEST_URI for form action, output escaped with esc_url. ?>
	<form action="<?php echo esc_url( isset( $_SERVER['REQUEST_URI'] ) ? home_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' ); ?>" method="post">
		<?php wp_nonce_field( 'sp_suggest_price_' . $args['id'], 'sp_suggest_price_nonce' ); ?>
		<a href="#" class="sp-suggest-close"></a>
		<input type="text" name="sp_price_name" placeholder="<?php echo esc_attr( $args['name_placeholder'] ); ?>">
		<input type="text" name="sp_price_email" placeholder="<?php echo esc_attr( $args['email_placeholder'] ); ?>">
		<input type="text" name="sp_price" placeholder="<?php echo esc_attr( $args['price_placeholder'] ); ?>">
		<textarea name="sp_price_message" cols="30" rows="5" placeholder="<?php echo esc_attr( $args['message_placeholder'] ); ?>"></textarea>
		<input type="submit" name="sp_suggest_price_<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_html( $args['submit_btn_text'] ); ?>">
	</form>
</div>
