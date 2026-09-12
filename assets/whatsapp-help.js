/**
 * WhatsApp Button for WooCommerce — frontend script.
 *
 * - Product page: builds the wa.me URL from the parent product's data
 *   attributes, then re-builds it on WooCommerce `found_variation` /
 *   `reset_data` events so the user gets the selected variation attributes
 *   without a page reload.
 *
 * - Checkout page: renders a placeholder `href="#"` server-side and fetches
 *   the real wa.me URL from the `wcwa_checkout_url` AJAX endpoint. The URL
 *   is refreshed on every `updated_checkout` event so the cart stays current.
 *
 * No PII leaves the browser.
 */
( function ( $ ) {
	'use strict';

	if ( ! window.wcwaData ) {
		return;
	}

	var data     = window.wcwaData;
	var phone    = String( data.phone || '' );
	var ajaxUrl  = String( data.ajaxUrl || '' );
	var nonce    = String( data.checkoutNonce || '' );

	function buildWaLink( message ) {
		if ( ! phone ) {
			return '#';
		}
		return 'https://wa.me/' + phone + '?text=' + encodeURIComponent( message );
	}

	/* ---------- Product page (variation handling) ---------- */

	var $productBtn = $( '#wcwa-product-button' );
	if ( $productBtn.length ) {
		var parentName = String( $productBtn.data( 'wcwa-name' ) || '' );
		var parentUrl  = String( $productBtn.data( 'wcwa-url' ) || '' );

		function buildProductMessage( attrs ) {
			var lines = [
				'আসসালামু আলাইকুম, "' + parentName + '" এই পণ্যটি অর্ডার করতে চাচ্ছি।',
				''
			];

			if ( attrs && typeof attrs === 'object' ) {
				var keys = Object.keys( attrs );
				if ( keys.length ) {
					lines.push( 'নির্বাচিত অপশন:' );
					keys.forEach( function ( key ) {
						if ( ! Object.prototype.hasOwnProperty.call( attrs, key ) ) {
							return;
						}
						var label = String( key )
							.replace( /^attribute_pa_/, '' )
							.replace( /^attribute_/, '' );
						if ( label.length ) {
							label = label.charAt( 0 ).toUpperCase() + label.slice( 1 );
						}
						lines.push( '• ' + label + ': ' + attrs[ key ] );
					} );
					lines.push( '' );
				}
			}

			lines.push( parentUrl );
			return lines.join( '\n' );
		}

		function applyProductLink( attrs ) {
			$productBtn.attr( 'href', buildWaLink( buildProductMessage( attrs ) ) );
		}

		applyProductLink( null );

		$( document ).on( 'found_variation.wcwa', 'form.variations_form', function ( event, variation ) {
			applyProductLink( variation && variation.attributes ? variation.attributes : null );
		} );

		$( document ).on( 'reset_data.wcwa', 'form.variations_form', function () {
			applyProductLink( null );
		} );
	}

	/* ---------- Checkout page (cart refresh) ---------- */

	var $checkoutBtn = $( '#wcwa-checkout-button' );
	if ( $checkoutBtn.length && ajaxUrl ) {
		function setCheckoutHref( url ) {
			$checkoutBtn.attr( 'href', url || '#' );
		}

		function refreshCheckoutLink() {
			$.post(
				ajaxUrl,
				{
					action: 'wcwa_checkout_url',
					nonce:  nonce
				},
				function ( response ) {
					if ( response && response.success && response.data ) {
						setCheckoutHref( response.data.url );
					}
				}
			);
		}

		// Click handler — if JS hasn't populated the URL yet (very fast click),
		// fall back to a fresh AJAX fetch and open in a new tab.
		$checkoutBtn.on( 'click.wcwa', function ( event ) {
			var currentHref = $checkoutBtn.attr( 'href' );
			if ( ! currentHref || '#' === currentHref ) {
				event.preventDefault();
				$.post(
					ajaxUrl,
					{
						action: 'wcwa_checkout_url',
						nonce:  nonce
					},
					function ( response ) {
						if ( response && response.success && response.data && response.data.url ) {
							window.open( response.data.url, '_blank', 'noopener,noreferrer' );
						}
					}
				);
			}
		} );

		refreshCheckoutLink();
		$( document.body ).on( 'updated_checkout', refreshCheckoutLink );
	}
} )( jQuery );
