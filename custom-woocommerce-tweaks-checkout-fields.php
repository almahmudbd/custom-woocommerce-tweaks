<?php
/**
 * Checkout Fields Customizer Tweak
 * Allows enabling/disabling, editing labels, placeholders, notes (descriptions),
 * and required states for WooCommerce checkout billing, shipping, and order fields.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Returns list of standard core WooCommerce checkout fields.
 *
 * @return array
 */
function cwt_get_checkout_field_definitions() {
    return array(
        'billing' => array(
            'title'  => __( 'Billing Fields', 'custom-woocommerce-tweaks' ),
            'fields' => array(
                'billing_first_name' => array(
                    'label'            => __( 'First Name', 'woocommerce' ),
                    'default_required' => true,
                ),
                'billing_last_name'  => array(
                    'label'            => __( 'Last Name', 'woocommerce' ),
                    'default_required' => true,
                ),
                'billing_company'    => array(
                    'label'            => __( 'Company Name', 'woocommerce' ),
                    'default_required' => false,
                ),
                'billing_country'    => array(
                    'label'            => __( 'Country / Region', 'woocommerce' ),
                    'default_required' => true,
                ),
                'billing_address_1'  => array(
                    'label'            => __( 'Street Address (Line 1)', 'woocommerce' ),
                    'default_required' => true,
                ),
                'billing_address_2'  => array(
                    'label'            => __( 'Apartment, suite, unit (Line 2)', 'woocommerce' ),
                    'default_required' => false,
                ),
                'billing_city'       => array(
                    'label'            => __( 'Town / City', 'woocommerce' ),
                    'default_required' => true,
                ),
                'billing_state'      => array(
                    'label'            => __( 'State / District / County', 'woocommerce' ),
                    'default_required' => true,
                ),
                'billing_postcode'   => array(
                    'label'            => __( 'Postcode / ZIP', 'woocommerce' ),
                    'default_required' => true,
                ),
                'billing_phone'      => array(
                    'label'            => __( 'Phone', 'woocommerce' ),
                    'default_required' => true,
                ),
                'billing_email'      => array(
                    'label'            => __( 'Email Address', 'woocommerce' ),
                    'default_required' => true,
                ),
            ),
        ),
        'shipping' => array(
            'title'  => __( 'Shipping Fields', 'custom-woocommerce-tweaks' ),
            'fields' => array(
                'shipping_first_name' => array(
                    'label'            => __( 'First Name', 'woocommerce' ),
                    'default_required' => true,
                ),
                'shipping_last_name'  => array(
                    'label'            => __( 'Last Name', 'woocommerce' ),
                    'default_required' => true,
                ),
                'shipping_company'    => array(
                    'label'            => __( 'Company Name', 'woocommerce' ),
                    'default_required' => false,
                ),
                'shipping_country'    => array(
                    'label'            => __( 'Country / Region', 'woocommerce' ),
                    'default_required' => true,
                ),
                'shipping_address_1'  => array(
                    'label'            => __( 'Street Address (Line 1)', 'woocommerce' ),
                    'default_required' => true,
                ),
                'shipping_address_2'  => array(
                    'label'            => __( 'Apartment, suite, unit (Line 2)', 'woocommerce' ),
                    'default_required' => false,
                ),
                'shipping_city'       => array(
                    'label'            => __( 'Town / City', 'woocommerce' ),
                    'default_required' => true,
                ),
                'shipping_state'      => array(
                    'label'            => __( 'State / District / County', 'woocommerce' ),
                    'default_required' => true,
                ),
                'shipping_postcode'   => array(
                    'label'            => __( 'Postcode / ZIP', 'woocommerce' ),
                    'default_required' => true,
                ),
                'shipping_phone'      => array(
                    'label'            => __( 'Phone', 'woocommerce' ),
                    'default_required' => false,
                ),
            ),
        ),
        'order' => array(
            'title'  => __( 'Order Notes / Additional Fields', 'custom-woocommerce-tweaks' ),
            'fields' => array(
                'order_comments' => array(
                    'label'            => __( 'Order Notes', 'woocommerce' ),
                    'default_required' => false,
                ),
            ),
        ),
    );
}

/**
 * Get current saved settings for checkout fields.
 *
 * @return array
 */
function cwt_get_checkout_fields_settings() {
    $saved = get_option( 'cwt_checkout_fields_settings', array() );
    if ( ! is_array( $saved ) ) {
        $saved = array();
    }
    return $saved;
}

/**
 * Override WooCommerce checkout fields on frontend.
 *
 * @param array $fields Existing checkout fields.
 * @return array
 */
add_filter( 'woocommerce_checkout_fields', 'cwt_custom_override_checkout_fields', 9999 );
function cwt_custom_override_checkout_fields( $fields ) {
    if ( get_option( 'cwt_enable_checkout_fields_customizer', 'no' ) !== 'yes' ) {
        return $fields;
    }

    $definitions = cwt_get_checkout_field_definitions();
    $settings    = cwt_get_checkout_fields_settings();

    foreach ( $definitions as $section => $section_info ) {
        if ( ! isset( $fields[ $section ] ) || ! is_array( $fields[ $section ] ) ) {
            continue;
        }

        foreach ( $section_info['fields'] as $field_key => $default_info ) {
            $field_settings = isset( $settings[ $field_key ] ) ? $settings[ $field_key ] : array();

            // Check if field is disabled
            $is_enabled = isset( $field_settings['enabled'] ) ? (int) $field_settings['enabled'] : 1;
            if ( ! $is_enabled ) {
                if ( isset( $fields[ $section ][ $field_key ] ) ) {
                    unset( $fields[ $section ][ $field_key ] );
                }
                continue;
            }

            if ( ! isset( $fields[ $section ][ $field_key ] ) ) {
                continue;
            }

            // Custom Label
            if ( isset( $field_settings['label'] ) && trim( $field_settings['label'] ) !== '' ) {
                $fields[ $section ][ $field_key ]['label'] = sanitize_text_field( $field_settings['label'] );
            }

            // Custom Placeholder
            if ( isset( $field_settings['placeholder'] ) && trim( $field_settings['placeholder'] ) !== '' ) {
                $fields[ $section ][ $field_key ]['placeholder'] = sanitize_text_field( $field_settings['placeholder'] );
            }

            // Custom Note / Description
            if ( isset( $field_settings['description'] ) && trim( $field_settings['description'] ) !== '' ) {
                $fields[ $section ][ $field_key ]['description'] = sanitize_text_field( $field_settings['description'] );
            }

            // Custom Required state
            if ( isset( $field_settings['required'] ) && $field_settings['required'] !== 'default' ) {
                $fields[ $section ][ $field_key ]['required'] = ( $field_settings['required'] === 'yes' );
            }
        }
    }

    return $fields;
}

/**
 * Filter default address fields so dynamic country/address changes respect custom settings.
 *
 * @param array $fields
 * @return array
 */
add_filter( 'woocommerce_default_address_fields', 'cwt_custom_override_default_address_fields', 9999 );
function cwt_custom_override_default_address_fields( $fields ) {
    if ( get_option( 'cwt_enable_checkout_fields_customizer', 'no' ) !== 'yes' ) {
        return $fields;
    }

    $settings = cwt_get_checkout_fields_settings();
    if ( empty( $settings ) ) {
        return $fields;
    }

    // Mapping of core address keys to billing setting keys
    $address_keys = array(
        'first_name',
        'last_name',
        'company',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'country',
    );

    foreach ( $address_keys as $key ) {
        $billing_key = 'billing_' . $key;
        if ( ! isset( $settings[ $billing_key ] ) || ! isset( $fields[ $key ] ) ) {
            continue;
        }

        $conf = $settings[ $billing_key ];

        // If billing field is disabled, make sure default address validation doesn't block checkout
        if ( isset( $conf['enabled'] ) && ! (int) $conf['enabled'] ) {
            $fields[ $key ]['required'] = false;
        } elseif ( isset( $conf['required'] ) && $conf['required'] !== 'default' ) {
            $fields[ $key ]['required'] = ( $conf['required'] === 'yes' );
        }

        if ( isset( $conf['label'] ) && trim( $conf['label'] ) !== '' ) {
            $fields[ $key ]['label'] = sanitize_text_field( $conf['label'] );
        }

        if ( isset( $conf['placeholder'] ) && trim( $conf['placeholder'] ) !== '' ) {
            $fields[ $key ]['placeholder'] = sanitize_text_field( $conf['placeholder'] );
        }

        if ( isset( $conf['description'] ) && trim( $conf['description'] ) !== '' ) {
            $fields[ $key ]['description'] = sanitize_text_field( $conf['description'] );
        }
    }

    return $fields;
}

/**
 * Conditionally disable the entire Order Notes section if order_comments is disabled.
 */
add_filter( 'woocommerce_enable_order_notes_field', 'cwt_conditionally_disable_order_notes_section', 9999 );
function cwt_conditionally_disable_order_notes_section( $enabled ) {
    if ( get_option( 'cwt_enable_checkout_fields_customizer', 'no' ) !== 'yes' ) {
        return $enabled;
    }

    $settings = cwt_get_checkout_fields_settings();
    if ( isset( $settings['order_comments']['enabled'] ) && ! (int) $settings['order_comments']['enabled'] ) {
        return false;
    }

    return $enabled;
}
