<?php
/**
 * Dynamically apply the settings
 * Disable COD based on the selected shipping method if the setting is enabled.
 */
add_filter('woocommerce_available_payment_gateways', 'conditionally_disable_cod_for_courier');
function conditionally_disable_cod_for_courier($available_gateways) {
    if (get_option('disable_cod_for_courier', 'yes') === 'yes') {
        // Logic to disable COD during checkout
        if (!is_admin() && is_checkout()) {
            $chosen_methods = WC()->session->get('chosen_shipping_methods');
            $chosen_shipping = !empty($chosen_methods) ? $chosen_methods[0] : '';

            $courier_shipping_method_id = 'advanced_flat_rate_shipping:18800'; // Update this to your courier method ID.
            $cod_payment_method_id = 'jetpack_custom_gateway_2'; // COD payment method ID.

            if ($chosen_shipping === $courier_shipping_method_id && isset($available_gateways[$cod_payment_method_id])) {
                unset($available_gateways[$cod_payment_method_id]);
            }
        }
    }
    return $available_gateways;
}

/**
 * Disable password change email notifications if the setting is enabled.
 */
add_filter('send_password_change_email', 'conditionally_disable_password_email');
function conditionally_disable_password_email($send) {
    if (get_option('disable_password_email', 'yes') === 'yes') {
        return false;
    }
    return $send;
}

/**
 * Display WhatsApp link in admin order page below the existing phone number (for Bangladeshi orders).
 */
add_action('woocommerce_admin_order_data_after_billing_address', 'custom_wa_phone_link_section', 10, 1);

function custom_wa_phone_link_section($order) {
    // Get the billing phone number from the order
    $phone = $order->get_billing_phone();
    
    // If there is no phone number, do nothing
    if (!$phone) return;

    // Remove any non-digit characters from the phone number
    $digits = preg_replace('/\D/', '', $phone);

    // Ensure the phone number is in Bangladeshi format
    if (preg_match('/^01[0-9]{9}$/', $digits)) {
        $digits = '+88' . $digits; // Add country code for Bangladeshi numbers
    } elseif (preg_match('/^8801[0-9]{9}$/', $digits)) {
        $digits = '+' . $digits; // Ensure proper international format
    } else {
        // If the number is not valid for Bangladesh, stop processing
        return;
    }

    // Generate the WhatsApp link
    $wa_link = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $digits);

    // Display only the WhatsApp section below the phone number
    echo '<p><strong>WhatsApp:</strong> <a href="' . esc_url($wa_link) . '" target="_blank" style="color:#2271b1;text-decoration:underline;">' . esc_html($digits) . '</a></p>';
}
