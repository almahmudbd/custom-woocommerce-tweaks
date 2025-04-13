<?php
/**
 * Plugin Name: Custom WooCommerce Tweaks
 * Plugin URI:  https://sukkarshop.com/
 * Description: Hide COD when Courier is selected, disable password change notification, and make billing phone clickable for WhatsApp in admin.
 * Version:     2.0.0
 * Author:      almahmud@sukkarshop
 * Author URI:  https://thealmahmud.blogspot.com/
 * License:     GPL-3.0+
 */

// Include the settings page
require_once plugin_dir_path(__FILE__) . 'custom-woocommerce-tweaks-settings.php';

// Include the core functionality
require_once plugin_dir_path(__FILE__) . 'custom-woocommerce-tweaks-core.php';
