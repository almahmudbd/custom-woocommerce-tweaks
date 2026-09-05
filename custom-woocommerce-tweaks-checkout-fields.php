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
    if ( get_option( 'cwt_enable_checkout_fields_customizer', 'yes' ) !== 'yes' ) {
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
 * Override billing fields directly via woocommerce_billing_fields filter.
 * Ensures billing_phone, billing_email, and address fields are customized
 * regardless of whether themes or plugins call get_address_fields() or checkout_fields.
 */
add_filter( 'woocommerce_billing_fields', 'cwt_custom_override_billing_fields', 9999, 2 );
function cwt_custom_override_billing_fields( $fields, $country = '' ) {
    if ( get_option( 'cwt_enable_checkout_fields_customizer', 'yes' ) !== 'yes' ) {
        return $fields;
    }

    $settings = cwt_get_checkout_fields_settings();
    if ( empty( $settings ) || ! is_array( $fields ) ) {
        return $fields;
    }

    foreach ( $fields as $field_key => $field_args ) {
        if ( isset( $settings[ $field_key ] ) ) {
            $conf = $settings[ $field_key ];

            if ( isset( $conf['enabled'] ) && ! (int) $conf['enabled'] ) {
                unset( $fields[ $field_key ] );
                continue;
            }

            if ( isset( $conf['label'] ) && trim( $conf['label'] ) !== '' ) {
                $fields[ $field_key ]['label'] = sanitize_text_field( $conf['label'] );
            }

            if ( isset( $conf['placeholder'] ) && trim( $conf['placeholder'] ) !== '' ) {
                $fields[ $field_key ]['placeholder'] = sanitize_text_field( $conf['placeholder'] );
            }

            if ( isset( $conf['description'] ) && trim( $conf['description'] ) !== '' ) {
                $fields[ $field_key ]['description'] = sanitize_text_field( $conf['description'] );
            }

            if ( isset( $conf['required'] ) && $conf['required'] !== 'default' ) {
                $fields[ $field_key ]['required'] = ( $conf['required'] === 'yes' );
            }
        }
    }

    return $fields;
}

/**
 * Override shipping fields directly via woocommerce_shipping_fields filter.
 */
add_filter( 'woocommerce_shipping_fields', 'cwt_custom_override_shipping_fields', 9999, 2 );
function cwt_custom_override_shipping_fields( $fields, $country = '' ) {
    if ( get_option( 'cwt_enable_checkout_fields_customizer', 'yes' ) !== 'yes' ) {
        return $fields;
    }

    $settings = cwt_get_checkout_fields_settings();
    if ( empty( $settings ) || ! is_array( $fields ) ) {
        return $fields;
    }

    foreach ( $fields as $field_key => $field_args ) {
        if ( isset( $settings[ $field_key ] ) ) {
            $conf = $settings[ $field_key ];

            if ( isset( $conf['enabled'] ) && ! (int) $conf['enabled'] ) {
                unset( $fields[ $field_key ] );
                continue;
            }

            if ( isset( $conf['label'] ) && trim( $conf['label'] ) !== '' ) {
                $fields[ $field_key ]['label'] = sanitize_text_field( $conf['label'] );
            }

            if ( isset( $conf['placeholder'] ) && trim( $conf['placeholder'] ) !== '' ) {
                $fields[ $field_key ]['placeholder'] = sanitize_text_field( $conf['placeholder'] );
            }

            if ( isset( $conf['description'] ) && trim( $conf['description'] ) !== '' ) {
                $fields[ $field_key ]['description'] = sanitize_text_field( $conf['description'] );
            }

            if ( isset( $conf['required'] ) && $conf['required'] !== 'default' ) {
                $fields[ $field_key ]['required'] = ( $conf['required'] === 'yes' );
            }
        }
    }

    return $fields;
}

/**
 * Filter form field args right before rendering to ensure theme templates don't bypass customizations.
 */
add_filter( 'woocommerce_form_field_args', 'cwt_custom_override_form_field_args', 9999, 3 );
function cwt_custom_override_form_field_args( $args, $key, $value ) {
    if ( get_option( 'cwt_enable_checkout_fields_customizer', 'yes' ) !== 'yes' ) {
        return $args;
    }

    $settings = cwt_get_checkout_fields_settings();
    if ( isset( $settings[ $key ] ) ) {
        $conf = $settings[ $key ];

        if ( isset( $conf['label'] ) && trim( $conf['label'] ) !== '' ) {
            $args['label'] = sanitize_text_field( $conf['label'] );
        }

        if ( isset( $conf['placeholder'] ) && trim( $conf['placeholder'] ) !== '' ) {
            $args['placeholder'] = sanitize_text_field( $conf['placeholder'] );
        }

        if ( isset( $conf['description'] ) && trim( $conf['description'] ) !== '' ) {
            $args['description'] = sanitize_text_field( $conf['description'] );
        }

        if ( isset( $conf['required'] ) && $conf['required'] !== 'default' ) {
            $args['required'] = ( $conf['required'] === 'yes' );
        }
    }

    return $args;
}

/**
 * Prevent disabled fields from outputting HTML if a theme template renders them directly.
 */
add_filter( 'woocommerce_form_field', 'cwt_custom_filter_form_field_html', 9999, 4 );
function cwt_custom_filter_form_field_html( $field_html, $key, $args, $value ) {
    if ( get_option( 'cwt_enable_checkout_fields_customizer', 'yes' ) !== 'yes' ) {
        return $field_html;
    }

    $settings = cwt_get_checkout_fields_settings();
    if ( isset( $settings[ $key ]['enabled'] ) && ! (int) $settings[ $key ]['enabled'] ) {
        return '';
    }

    return $field_html;
}

/**
 * Sync WooCommerce core phone field setting dynamically.
 */
add_filter( 'option_woocommerce_checkout_phone_field', 'cwt_sync_core_phone_field_option', 9999 );
function cwt_sync_core_phone_field_option( $value ) {
    if ( get_option( 'cwt_enable_checkout_fields_customizer', 'yes' ) !== 'yes' ) {
        return $value;
    }
    $settings = cwt_get_checkout_fields_settings();
    if ( isset( $settings['billing_phone'] ) ) {
        $conf = $settings['billing_phone'];
        if ( isset( $conf['enabled'] ) && ! (int) $conf['enabled'] ) {
            return 'hidden';
        }
        if ( isset( $conf['required'] ) ) {
            if ( 'yes' === $conf['required'] ) {
                return 'required';
            } elseif ( 'no' === $conf['required'] ) {
                return 'optional';
            }
        }
    }
    return $value;
}

/**
 * Sync WooCommerce core company field setting dynamically.
 */
add_filter( 'option_woocommerce_checkout_company_field', 'cwt_sync_core_company_field_option', 9999 );
function cwt_sync_core_company_field_option( $value ) {
    if ( get_option( 'cwt_enable_checkout_fields_customizer', 'yes' ) !== 'yes' ) {
        return $value;
    }
    $settings = cwt_get_checkout_fields_settings();
    if ( isset( $settings['billing_company'] ) ) {
        $conf = $settings['billing_company'];
        if ( isset( $conf['enabled'] ) && ! (int) $conf['enabled'] ) {
            return 'hidden';
        }
        if ( isset( $conf['required'] ) ) {
            if ( 'yes' === $conf['required'] ) {
                return 'required';
            } elseif ( 'no' === $conf['required'] ) {
                return 'optional';
            }
        }
    }
    return $value;
}

/**
 * Filter default address fields so dynamic country/address changes respect custom settings.
 *
 * @param array $fields
 * @return array
 */
add_filter( 'woocommerce_default_address_fields', 'cwt_custom_override_default_address_fields', 9999 );
function cwt_custom_override_default_address_fields( $fields ) {
    if ( get_option( 'cwt_enable_checkout_fields_customizer', 'yes' ) !== 'yes' ) {
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
    if ( get_option( 'cwt_enable_checkout_fields_customizer', 'yes' ) !== 'yes' ) {
        return $enabled;
    }

    $settings = cwt_get_checkout_fields_settings();
    if ( isset( $settings['order_comments']['enabled'] ) && ! (int) $settings['order_comments']['enabled'] ) {
        return false;
    }

    return $enabled;
}

/**
 * Override country locale data so WooCommerce's address-i18n.js uses our custom
 * labels/placeholders/required states instead of reverting to defaults on country change.
 *
 * This is the fix for the "label flashes custom then reverts to English" issue.
 * WooCommerce JS fetches locale data and re-applies it on country change.
 */
add_filter( 'woocommerce_get_country_locale', 'cwt_override_country_locale', 9999 );
function cwt_override_country_locale( $locales ) {
    if ( get_option( 'cwt_enable_checkout_fields_customizer', 'yes' ) !== 'yes' ) {
        return $locales;
    }

    $settings = cwt_get_checkout_fields_settings();
    if ( empty( $settings ) ) {
        return $locales;
    }

    // Map billing field keys to their locale short keys
    $locale_field_map = array(
        'billing_first_name' => 'first_name',
        'billing_last_name'  => 'last_name',
        'billing_company'    => 'company',
        'billing_address_1'  => 'address_1',
        'billing_address_2'  => 'address_2',
        'billing_city'       => 'city',
        'billing_state'      => 'state',
        'billing_postcode'   => 'postcode',
    );

    $overrides = array();
    foreach ( $locale_field_map as $billing_key => $locale_key ) {
        if ( ! isset( $settings[ $billing_key ] ) ) {
            continue;
        }
        $conf = $settings[ $billing_key ];
        $field_override = array();

        if ( isset( $conf['label'] ) && trim( $conf['label'] ) !== '' ) {
            $field_override['label'] = sanitize_text_field( $conf['label'] );
        }
        if ( isset( $conf['placeholder'] ) && trim( $conf['placeholder'] ) !== '' ) {
            $field_override['placeholder'] = sanitize_text_field( $conf['placeholder'] );
        }
        if ( isset( $conf['required'] ) && $conf['required'] !== 'default' ) {
            $field_override['required'] = ( $conf['required'] === 'yes' );
        }
        if ( isset( $conf['enabled'] ) && ! (int) $conf['enabled'] ) {
            $field_override['hidden']   = true;
            $field_override['required'] = false;
        }

        if ( ! empty( $field_override ) ) {
            $overrides[ $locale_key ] = $field_override;
        }
    }

    if ( empty( $overrides ) ) {
        return $locales;
    }

    // Inject our overrides into every country's locale
    foreach ( $locales as $country_code => $country_locale ) {
        foreach ( $overrides as $locale_key => $override_data ) {
            if ( ! isset( $locales[ $country_code ][ $locale_key ] ) ) {
                $locales[ $country_code ][ $locale_key ] = array();
            }
            $locales[ $country_code ][ $locale_key ] = array_merge(
                $locales[ $country_code ][ $locale_key ],
                $override_data
            );
        }
    }

    return $locales;
}

/**
 * Override the default country locale (used when no specific country locale is found).
 */
add_filter( 'woocommerce_get_country_locale_default', 'cwt_override_country_locale_default', 9999 );
function cwt_override_country_locale_default( $defaults ) {
    if ( get_option( 'cwt_enable_checkout_fields_customizer', 'yes' ) !== 'yes' ) {
        return $defaults;
    }

    $settings = cwt_get_checkout_fields_settings();
    if ( empty( $settings ) ) {
        return $defaults;
    }

    $locale_field_map = array(
        'billing_first_name' => 'first_name',
        'billing_last_name'  => 'last_name',
        'billing_company'    => 'company',
        'billing_address_1'  => 'address_1',
        'billing_address_2'  => 'address_2',
        'billing_city'       => 'city',
        'billing_state'      => 'state',
        'billing_postcode'   => 'postcode',
    );

    foreach ( $locale_field_map as $billing_key => $locale_key ) {
        if ( ! isset( $settings[ $billing_key ] ) ) {
            continue;
        }
        $conf = $settings[ $billing_key ];

        if ( ! isset( $defaults[ $locale_key ] ) ) {
            $defaults[ $locale_key ] = array();
        }

        if ( isset( $conf['label'] ) && trim( $conf['label'] ) !== '' ) {
            $defaults[ $locale_key ]['label'] = sanitize_text_field( $conf['label'] );
        }
        if ( isset( $conf['placeholder'] ) && trim( $conf['placeholder'] ) !== '' ) {
            $defaults[ $locale_key ]['placeholder'] = sanitize_text_field( $conf['placeholder'] );
        }
        if ( isset( $conf['required'] ) && $conf['required'] !== 'default' ) {
            $defaults[ $locale_key ]['required'] = ( $conf['required'] === 'yes' );
        }
    }

    return $defaults;
}

/**
 * Output frontend JS on checkout to forcefully re-apply custom labels after
 * WooCommerce's AJAX update_checkout completes. This catches phone, email,
 * and any fields not covered by locale data.
 */
add_action( 'wp_footer', 'cwt_checkout_fields_frontend_js', 9999 );
function cwt_checkout_fields_frontend_js() {
    if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_wc_endpoint_url() ) {
        return;
    }
    if ( get_option( 'cwt_enable_checkout_fields_customizer', 'yes' ) !== 'yes' ) {
        return;
    }

    $settings = cwt_get_checkout_fields_settings();
    if ( empty( $settings ) ) {
        return;
    }

    // Build JS data for fields that have custom labels/placeholders
    $js_overrides = array();
    foreach ( $settings as $field_key => $conf ) {
        $override = array();
        if ( isset( $conf['label'] ) && trim( $conf['label'] ) !== '' ) {
            $override['label'] = sanitize_text_field( $conf['label'] );
        }
        if ( isset( $conf['placeholder'] ) && trim( $conf['placeholder'] ) !== '' ) {
            $override['placeholder'] = sanitize_text_field( $conf['placeholder'] );
        }
        if ( ! empty( $override ) ) {
            $js_overrides[ $field_key ] = $override;
        }
    }

    if ( empty( $js_overrides ) ) {
        return;
    }
    ?>
    <script type="text/javascript">
    (function() {
        var cwtOverrides = <?php echo wp_json_encode( $js_overrides ); ?>;

        function cwtApplyOverrides() {
            for (var fieldKey in cwtOverrides) {
                if (!cwtOverrides.hasOwnProperty(fieldKey)) continue;
                var data = cwtOverrides[fieldKey];
                var field = document.getElementById(fieldKey + '_field');
                if (!field) continue;

                if (data.label) {
                    var labelEl = field.querySelector('label');
                    if (labelEl) {
                        // Preserve the <abbr> (required asterisk) if present
                        var abbr = labelEl.querySelector('abbr');
                        labelEl.textContent = data.label;
                        if (abbr) {
                            labelEl.appendChild(document.createTextNode(' '));
                            labelEl.appendChild(abbr);
                        }
                    }
                }

                if (data.placeholder) {
                    var input = field.querySelector('input, textarea, select');
                    if (input && (input.tagName === 'INPUT' || input.tagName === 'TEXTAREA')) {
                        input.setAttribute('placeholder', data.placeholder);
                    }
                }
            }
        }

        // Apply immediately
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', cwtApplyOverrides);
        } else {
            cwtApplyOverrides();
        }

        // Re-apply after WooCommerce AJAX events
        if (typeof jQuery !== 'undefined') {
            jQuery(document.body).on('updated_checkout', cwtApplyOverrides);
            jQuery(document.body).on('country_to_state_changed', cwtApplyOverrides);
        }
    })();
    </script>
    <?php
}
