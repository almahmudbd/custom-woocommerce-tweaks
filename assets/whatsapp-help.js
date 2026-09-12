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
		var template   = String( data.productTemplate || 'আসসালামু আলাইকুম, আমি এই পণ্যটি অর্ডার করতে চাচ্ছি। \n"{name}"\n{url}' );

		function getVariationLabel( key, val ) {
			// 1. Try to read human-readable label from the selected option in the variation form
			var $select = $( 'form.variations_form select[name="' + key + '"]' );
			if ( $select.length ) {
				var $selectedOption = $select.find( 'option:selected' );
				if ( $selectedOption.length && $selectedOption.val() ) {
					var text = $selectedOption.text().trim();
					if ( text && ! /^(choose|select|\-\-)/i.test( text ) && ! text.includes( 'নির্বাচন' ) ) {
						return text;
					}
				}
			}

			// 2. Try WoodMart / custom active swatch element
			var $swatch = $( 'form.variations_form [data-value="' + val + '"].selected, form.variations_form [data-value="' + val + '"].active' );
			if ( $swatch.length ) {
				var swatchTitle = $swatch.attr( 'title' ) || $swatch.data( 'title' ) || $swatch.text().trim();
				if ( swatchTitle ) {
					return swatchTitle;
				}
			}

			// 3. Fallback: decodeURIComponent on URL-encoded slug (%e0%a7%a8%e0%a7%ab... -> ২৫০গ্রাম)
			try {
				var decoded = decodeURIComponent( String( val ) );
				return decoded.replace( /[-_]/g, ' ' );
			} catch ( e ) {
				return String( val );
			}
		}

		function buildProductMessage( variationSummary ) {
			var displayName = parentName;
			if ( variationSummary ) {
				displayName += ' (' + variationSummary + ')';
			}

			return template
				.replace( /\{name\}/g, displayName )
				.replace( /\{url\}/g, parentUrl );
		}

		function applyProductLink( variationSummary ) {
			$productBtn.attr( 'href', buildWaLink( buildProductMessage( variationSummary ) ) );
		}

		applyProductLink( null );

		$( document ).on( 'found_variation.wcwa', 'form.variations_form', function ( event, variation ) {
			if ( ! variation || ! variation.attributes ) {
				applyProductLink( null );
				return;
			}

			var labels = [];
			var attrs  = variation.attributes;
			Object.keys( attrs ).forEach( function ( key ) {
				var val = attrs[ key ];
				if ( val ) {
					var label = getVariationLabel( key, val );
					if ( label ) {
						labels.push( label );
					}
				}
			} );

			applyProductLink( labels.length ? labels.join( ', ' ) : null );
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
