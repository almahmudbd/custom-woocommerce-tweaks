<?php
/**
 * WhatsApp click-to-chat integration for Custom WooCommerce Tweaks.
 *
 * Adds WhatsApp buttons to:
 * 1. Single Product Page (below Add to Cart button).
 * 2. Checkout Page (on the right half, above the mini-cart / order review section).
 *
 * All texts, templates, and numbers can be configured/hardcoded below.
 * Buttons can be toggled on/off in WooCommerce > Tweaks Settings (General Tweaks tab).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =======  CONFIGURATION & TEXTS  ======== */

if ( ! defined( 'CWT_WA_PHONE' ) ) {
	define( 'CWT_WA_PHONE', '8801312345678' ); // WhatsApp ফোন নম্বর (যেমন: 88013...)
}

// 1. Single Product Page Button
if ( ! defined( 'CWT_WA_PRODUCT_TEXT' ) ) {
	define( 'CWT_WA_PRODUCT_TEXT', 'WhatsApp-এ অর্ডার করুন' );
}

if ( ! defined( 'CWT_WA_PRODUCT_TEMPLATE' ) ) {
	// Placeholders: {name}, {url}, {price}, {sku}, {id}
	define( 'CWT_WA_PRODUCT_TEMPLATE', "আসসালামু আলাইকুম, আমি এই পণ্যটি অর্ডার করতে চাচ্ছি। \n\"{name}\"\n{url}" );
}

// 2. Checkout Page Button (Right Half, Above Mini-Cart)
if ( ! defined( 'CWT_WA_CHECKOUT_TEXT' ) ) {
	define( 'CWT_WA_CHECKOUT_TEXT', 'অর্ডার করতে সমস্যা হলে আমাদের মেসেজ করুন' );
}

if ( ! defined( 'CWT_WA_CHECKOUT_TEMPLATE' ) ) {
	// Placeholders: {items}, {total}
	define( 'CWT_WA_CHECKOUT_TEMPLATE', "আসসালামু আলাইকুম, অর্ডার করতে একটু হেল্প দরকার।\nএই পণ্যগুলো নিতে চাচ্ছি:\n\n{items}\n\nমোট: {total}\n" );
}

/* ====  WhatsApp Core Implementation  ======== */

if ( ! class_exists( 'CWT_WhatsApp' ) ) {

	class CWT_WhatsApp {

		const NONCE_ACTION = 'wcwa_checkout';

		private static $instance = null;

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		private function __construct() {
			// Frontend button hooks
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'render_product_button' ) );
			add_action( 'woocommerce_checkout_before_order_review_heading', array( $this, 'render_checkout_button' ), 10 );

			// AJAX endpoint for checkout cart refresh
			add_action( 'wp_ajax_wcwa_checkout_url', array( $this, 'ajax_checkout_url' ) );
			add_action( 'wp_ajax_nopriv_wcwa_checkout_url', array( $this, 'ajax_checkout_url' ) );

			// Frontend assets
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		}

		/**
		 * Get phone number (digits only).
		 */
		public static function get_phone() {
			return preg_replace( '/\D+/', '', (string) CWT_WA_PHONE );
		}

		/**
		 * Check whether a button is enabled from Tweaks Settings.
		 */
		public static function is_enabled( $feature ) {
			if ( 'product' === $feature ) {
				return get_option( 'cwt_wa_product_enabled', 'yes' ) === 'yes';
			}

			if ( 'checkout' === $feature ) {
				return get_option( 'cwt_wa_checkout_enabled', 'yes' ) === 'yes';
			}

			return false;
		}

		/**
		 * Build WhatsApp URL from message string.
		 */
		public static function build_url( $message ) {
			$phone = self::get_phone();
			if ( '' === $phone ) {
				return '';
			}
			return 'https://wa.me/' . $phone . '?text=' . rawurlencode( $message );
		}

		/**
		 * Build checkout message with cart contents.
		 */
		public static function build_checkout_message() {
			if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
				return '';
			}

			$items = array();
			foreach ( WC()->cart->get_cart() as $item ) {
				if ( ! isset( $item['data'] ) || ! is_object( $item['data'] ) ) {
					continue;
				}
				/** @var WC_Product $product */
				$product = $item['data'];
				$name    = $product->get_name();
				$qty     = isset( $item['quantity'] ) ? (int) $item['quantity'] : 1;
				$items[] = sprintf( '• %s × %dটি', $name, $qty );
			}

			$total    = wp_strip_all_tags( WC()->cart->get_cart_total() );
			$template = CWT_WA_CHECKOUT_TEMPLATE;

			return strtr(
				$template,
				array(
					'{items}' => implode( "\n", $items ),
					'{total}' => $total,
				)
			);
		}

		/**
		 * WhatsApp SVG icon.
		 */
		public static function icon_svg() {
			return '<svg class="wcwa-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>';
		}

		/**
		 * Render single product page WhatsApp button.
		 */
		public function render_product_button() {
			if ( ! function_exists( 'is_product' ) || ! is_product() ) {
				return;
			}
			if ( ! self::is_enabled( 'product' ) ) {
				return;
			}

			global $product;
			if ( ! $product instanceof WC_Product ) {
				return;
			}

			if ( '' === self::get_phone() ) {
				return;
			}

			$product_name = $product->get_name();
			$product_url  = (string) get_permalink( $product->get_id() );
			$label        = CWT_WA_PRODUCT_TEXT;

			$initial_msg = strtr(
				CWT_WA_PRODUCT_TEMPLATE,
				array(
					'{name}' => $product_name,
					'{url}'  => $product_url,
				)
			);
			$initial_url = self::build_url( $initial_msg );

			echo '<div class="wcwa-product-wrap">';
			echo '<a id="wcwa-product-button" class="wcwa-button wcwa-button--product" href="' . esc_url( $initial_url ) . '" target="_blank" rel="noopener noreferrer" style="display:inline-flex;width:auto;max-width:max-content;" data-wcwa-name="' . esc_attr( $product_name ) . '" data-wcwa-url="' . esc_url( $product_url ) . '">';
			echo self::icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<span class="wcwa-button__label">' . esc_html( $label ) . '</span>';
			echo '</a>';
			echo '</div>';
		}

		/**
		 * Render checkout page WhatsApp button on the right-hand half, above the mini-cart / order review section.
		 */
		public function render_checkout_button() {
			static $rendered = false;
			if ( $rendered ) {
				return;
			}

			if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
				return;
			}
			if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
				return;
			}
			if ( ! self::is_enabled( 'checkout' ) ) {
				return;
			}
			if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
				return;
			}
			if ( '' === self::get_phone() ) {
				return;
			}

			$rendered = true;
			$label    = CWT_WA_CHECKOUT_TEXT;

			echo '<div class="wcwa-checkout-wrap">';
			echo '<a id="wcwa-checkout-button" class="wcwa-button wcwa-button--block wcwa-button--checkout" href="#" target="_blank" rel="noopener noreferrer">';
			echo self::icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<span class="wcwa-button__label">' . esc_html( $label ) . '</span>';
			echo '</a>';
			echo '</div>';
		}

		/**
		 * AJAX endpoint returning updated checkout WhatsApp URL.
		 */
		public function ajax_checkout_url() {
			check_ajax_referer( self::NONCE_ACTION, 'nonce' );

			if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
				wp_send_json_success( array( 'url' => '', 'empty' => true ) );
			}

			$message = self::build_checkout_message();
			$url     = self::build_url( $message );

			wp_send_json_success( array( 'url' => $url ) );
		}

		/**
		 * Enqueue frontend scripts and styles.
		 */
		public function enqueue_assets() {
			$on_product  = function_exists( 'is_product' ) && is_product();
			$on_checkout = function_exists( 'is_checkout' ) && is_checkout();

			if ( ! $on_product && ! $on_checkout ) {
				return;
			}

			$prod_enabled = self::is_enabled( 'product' );
			$chk_enabled  = self::is_enabled( 'checkout' );

			// Skip the enqueue entirely if both buttons are off — no DOM targets exist.
			if ( ! $prod_enabled && ! $chk_enabled ) {
				return;
			}

			$version = '3.4';

			wp_enqueue_style(
				'cwt-whatsapp-frontend',
				plugin_dir_url( __FILE__ ) . 'assets/whatsapp-help.css',
				array(),
				$version
			);

			wp_enqueue_script(
				'cwt-whatsapp-frontend',
				plugin_dir_url( __FILE__ ) . 'assets/whatsapp-help.js',
				array( 'jquery' ),
				$version,
				true
			);

			$data = array(
				'phone'           => self::get_phone(),
				'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
				'checkoutNonce'   => wp_create_nonce( self::NONCE_ACTION ),
				'productTemplate' => CWT_WA_PRODUCT_TEMPLATE,
			);

			wp_add_inline_script(
				'cwt-whatsapp-frontend',
				'window.wcwaData = ' . wp_json_encode( $data ) . ';',
				'before'
			);
		}
	}
}

// Initialize WhatsApp component.
add_action( 'plugins_loaded', array( 'CWT_WhatsApp', 'instance' ), 25 );
