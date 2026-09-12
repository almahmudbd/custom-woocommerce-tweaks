<?php
/**
 * Plugin Name: Custom WooCommerce Tweaks
 * Plugin URI:  https://sukkarshop.com/
 * Description: A collection of WooCommerce/WordPress tweaks (COD rules, checkout & cart tweaks, admin helpers), each toggleable from WooCommerce > Tweaks Settings.
 * Version:     3.4
 * Author:      almahmud
 * Author URI:  https://thealmahmud.blogspot.com/
 * License:     GPL-3.0+
 */

// Include the settings page
require_once plugin_dir_path(__FILE__) . 'custom-woocommerce-tweaks-settings.php';

// Include the core functionality
require_once plugin_dir_path(__FILE__) . 'custom-woocommerce-tweaks-core.php';

// Include the registration email domain restriction
require_once plugin_dir_path(__FILE__) . 'custom-woocommerce-tweaks-email-restriction.php';

// Include checkout fields customizer
require_once plugin_dir_path(__FILE__) . 'custom-woocommerce-tweaks-checkout-fields.php';

// Include WhatsApp click-to-chat buttons
require_once plugin_dir_path(__FILE__) . 'custom-woocommerce-tweaks-whatsapp.php';
