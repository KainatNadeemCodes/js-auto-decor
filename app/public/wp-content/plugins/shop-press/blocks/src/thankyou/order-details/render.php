<?php
/**
 * Thank You Order Details.
 *
 * @package ShopPress
 */

defined( 'ABSPATH' ) || exit;

if ( is_checkout() ) {
	sp_load_builder_template( 'thankyou/order-details' );
}
