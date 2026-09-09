<?php
/**
 * Checkout coupon form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-coupon.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.8.4
 */

defined( 'ABSPATH' ) || exit;

if ( ! wc_coupons_enabled() ) { // @codingStandardsIgnoreLine.
	return;
}

?>

<div class="woocommerce-form-coupon-toggle">
	<?php
		/**
		 * Filter checkout coupon message.
		 *
		 * @param string $message coupon message.
		 * @return string Filtered message.
		 *
		 * @since 1.0.0
		 */
		wc_print_notice( apply_filters( 'woocommerce_checkout_coupon_message', esc_html__( 'Have a coupon?', 'kata' ) . ' <a href="#" role="button" aria-label="' . esc_attr__( 'Enter your coupon code', 'kata' ) . '" aria-controls="woocommerce-checkout-form-coupon" aria-expanded="false" class="showcoupon">' . esc_html__( 'Click here to enter your code', 'kata' ) . '</a>' ), 'notice' );
	?>
</div>

<form class="checkout_coupon woocommerce-form-coupon" method="post" style="display:block" id="woocommerce-checkout-form-coupon">
	<p><?php esc_html_e( 'If you have a coupon code, please apply it below.', 'kata' ); ?></p>
	<p class="form-row form-row-first">
		<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'kata' ); ?></label>
		<svg class="coupon-icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="20" height="13.975" viewBox="0 0 20 13.975">
			<defs>
				<clipPath id="clip-path">
				<rect id="Rectangle_1884" data-name="Rectangle 1884" width="20" height="13.975" transform="translate(104 348)" fill="#b7bec9"/>
				</clipPath>
			</defs>
			<g id="coupon-code" transform="translate(-104 -348)" clip-path="url(#clip-path)">
				<g id="coupon-code-2" data-name="coupon-code" transform="translate(104.21 348.21)">
				<path id="Path_4609" data-name="Path 4609" d="M316.839,178.678a1.839,1.839,0,1,1,1.839-1.839A1.841,1.841,0,0,1,316.839,178.678Zm0-2.452a.613.613,0,1,0,.613.613A.614.614,0,0,0,316.839,176.226Z" transform="translate(-306.419 -172.548)" fill="#b7bec9"/>
				<path id="Path_4610" data-name="Path 4610" d="M421.839,318.678a1.839,1.839,0,1,1,1.839-1.839A1.841,1.841,0,0,1,421.839,318.678Zm0-2.452a.613.613,0,1,0,.613.613A.615.615,0,0,0,421.839,316.226Z" transform="translate(-407.741 -307.644)" fill="#b7bec9"/>
				<path id="Path_4611" data-name="Path 4611" d="M315.616,183.626a.613.613,0,0,1-.471-1.005l6.13-7.356a.613.613,0,1,1,.94.786l-6.13,7.356a.613.613,0,0,1-.47.219Z" transform="translate(-306.421 -172.592)" fill="#b7bec9"/>
				<path id="Path_4616" data-name="Path 4616" d="M88.389,118.485H71.226A1.212,1.212,0,0,1,70,117.259v-3.065a.613.613,0,0,1,.613-.613,1.839,1.839,0,1,0,0-3.678.613.613,0,0,1-.613-.613v-3.065A1.228,1.228,0,0,1,71.226,105H88.389a1.212,1.212,0,0,1,1.226,1.226v11.033A1.212,1.212,0,0,1,88.389,118.485Zm-17.163-3.739v2.513H88.389V106.226H71.226v2.513a3.066,3.066,0,0,1,0,6.007Z" transform="translate(-70 -105)" fill="#b7bec9"/>
				</g>
			</g>
		</svg>
		<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Coupon code', 'kata' ); ?>" id="coupon_code" value="" />
	</p>

	<p class="form-row form-row-last">
		<button type="submit" class="button apply-coupon<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="apply_coupon" value="<?php esc_attr_e( 'Apply', 'kata' ); ?>"><?php esc_html_e( 'Apply', 'kata' ); ?></button>
	</p>

	<div class="clear"></div>
</form>
