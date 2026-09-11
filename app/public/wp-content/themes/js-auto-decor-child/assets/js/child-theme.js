( function ( $ ) {
	'use strict';

	$( function () {
		$( '.jsad-menu-toggle' ).on( 'click', function () {
			var $toggle = $( this );
			var target = $toggle.attr( 'aria-controls' );
			var $menu = target ? $( '#' + target ) : $();
			var expanded = $toggle.attr( 'aria-expanded' ) === 'true';

			$toggle.attr( 'aria-expanded', expanded ? 'false' : 'true' );
			$menu.attr( 'aria-hidden', expanded ? 'true' : 'false' ).toggleClass( 'is-open', ! expanded );
		} );

		$( document.body ).on( 'added_to_cart', function () {
			$( '.jsad-cart-feedback' ).stop( true, true ).addClass( 'is-visible' ).delay( 3000 ).fadeOut( 250 );
		} );
	} );
} )( jQuery );
