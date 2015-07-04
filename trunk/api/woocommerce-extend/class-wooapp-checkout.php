<?php
Class WOOAPP_Checkout extends WC_Checkout{
    /**
     * create_order function.
     * @access public
     * @throws Exception
     * @return int|WP_ERROR
     */
    var $current_customer; 
    var $payment_method_dup;
    public function create_order() {
        global $wpdb;

        // Give plugins the opportunity to create an order themselves
        if ( $order_id = apply_filters( 'woocommerce_create_order', null, $this ) ) {
            return $order_id;
        }

        try {
            // Start transaction if available
            $wpdb->query( 'START TRANSACTION' );

            $order_data = array(
                'status'        => apply_filters( 'woocommerce_default_order_status', 'pending' ),
                'customer_id'   => $this->current_customer,
                'customer_note' => isset( $this->posted['order_comments'] ) ? $this->posted['order_comments'] : ''
            );

            // Insert or update the post data
            $order_id = absint( WC()->session->order_awaiting_payment );

            // Resume the unpaid order if its pending
            if ( $order_id > 0 && ( $order = wc_get_order( $order_id ) ) && $order->has_status( array( 'pending', 'failed' ) ) ) {

                $order_data['order_id'] = $order_id;
                $order                  = wc_update_order( $order_data );

                if ( is_wp_error( $order ) ) {
                    throw new Exception( __( 'Error: Unable to create order. Please try again.', 'woocommerce' ) );
                } else {
                    $order->remove_order_items();
                    do_action( 'woocommerce_resume_order', $order_id );
                }

            } else {

                $order = wc_create_order( $order_data );

                if ( is_wp_error( $order ) ) {
                    throw new Exception( __( 'Error: Unable to create order. Please try again.', 'woocommerce' ) );
                } else {
                    $order_id = $order->id;
                    do_action( 'woocommerce_new_order', $order_id );
                }
            }

            // Store the line items to the new/resumed order
            foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
                $item_id = $order->add_product(
                    $values['data'],
                    $values['quantity'],
                    array(
                        'variation' => $values['variation'],
                        'totals'    => array(
                            'subtotal'     => $values['line_subtotal'],
                            'subtotal_tax' => $values['line_subtotal_tax'],
                            'total'        => $values['line_total'],
                            'tax'          => $values['line_tax'],
                            'tax_data'     => $values['line_tax_data'] // Since 2.2
                        )
                    )
                );

                if ( ! $item_id ) {
                    throw new Exception( __( 'Error: Unable to create order. Please try again.', 'woocommerce' ) );
                }

                // Allow plugins to add order item meta
                do_action( 'woocommerce_add_order_item_meta', $item_id, $values, $cart_item_key );
            }

            // Store fees
            foreach ( WC()->cart->get_fees() as $fee_key => $fee ) {
                $item_id = $order->add_fee( $fee );

                if ( ! $item_id ) {
                    throw new Exception( __( 'Error: Unable to create order. Please try again.', 'woocommerce' ) );
                }

                // Allow plugins to add order item meta to fees
                do_action( 'woocommerce_add_order_fee_meta', $order_id, $item_id, $fee, $fee_key );
            }

            // Store shipping for all packages
            foreach ( WC()->shipping->get_packages() as $package_key => $package ) {
                if ( isset( $package['rates'][ $this->shipping_methods[ $package_key ] ] ) ) {
                    $item_id = $order->add_shipping( $package['rates'][ $this->shipping_methods[ $package_key ] ] );

                    if ( ! $item_id ) {
                        throw new Exception( __( 'Error: Unable to create order. Please try again.', 'woocommerce' ) );
                    }

                    // Allows plugins to add order item meta to shipping
                    do_action( 'woocommerce_add_shipping_order_item', $order_id, $item_id, $package_key );
                }
            }

            // Store tax rows
            foreach ( array_keys( WC()->cart->taxes + WC()->cart->shipping_taxes ) as $tax_rate_id ) {
                if ( ! $order->add_tax( $tax_rate_id, WC()->cart->get_tax_amount( $tax_rate_id ), WC()->cart->get_shipping_tax_amount( $tax_rate_id ) ) && 'zero-rated' !== $tax_rate_id ) {
                    throw new Exception( __( 'Error: Unable to create order. Please try again.', 'woocommerce' ) );
                }
            }

            // Store coupons
            foreach ( WC()->cart->get_coupons() as $code => $coupon ) {
                if ( ! $order->add_coupon( $code, WC()->cart->get_coupon_discount_amount( $code ) ) ) {
                    throw new Exception( __( 'Error: Unable to create order. Please try again.', 'woocommerce' ) );
                }
            }

            // Billing address
            $billing_address = array();
            if ( $this->checkout_fields['billing'] ) {
                foreach ( array_keys( $this->checkout_fields['billing'] ) as $field ) {
                    $field_name = str_replace( 'billing_', '', $field );
                    $billing_address[ $field_name ] = $this->get_posted_address_data( $field_name );
                }
            }

            // Shipping address.
            $shipping_address = array();
            if ( $this->checkout_fields['shipping'] ) {
                foreach ( array_keys( $this->checkout_fields['shipping'] ) as $field ) {
                    $field_name = str_replace( 'shipping_', '', $field );
                    $shipping_address[ $field_name ] = $this->get_posted_address_data( $field_name, 'shipping' );
                }
            }

            $order->set_address( $billing_address, 'billing' );
            $order->set_address( $shipping_address, 'shipping' );
            $order->set_payment_method( $this->payment_method_dup );
            $order->set_total( WC()->cart->shipping_total, 'shipping' );
            $order->set_total( WC()->cart->get_order_discount_total(), 'order_discount' );
            $order->set_total( WC()->cart->get_cart_discount_total(), 'cart_discount' );
            $order->set_total( WC()->cart->tax_total, 'tax' );
            $order->set_total( WC()->cart->shipping_tax_total, 'shipping_tax' );
            $order->set_total( WC()->cart->total );

            // Update user meta
            if ( $this->current_customer ) {
                if ( apply_filters( 'woocommerce_checkout_update_customer_data', true, $this ) ) {
                    foreach ( $billing_address as $key => $value ) {
                        update_user_meta( $this->current_customer, 'billing_' . $key, $value );
                    }
                    foreach ( $shipping_address as $key => $value ) {
                        update_user_meta( $this->current_customer, 'shipping_' . $key, $value );
                    }
                }
                do_action( 'woocommerce_checkout_update_user_meta', $this->current_customer, $this->posted );
            }

            // Let plugins add meta
            do_action( 'woocommerce_checkout_update_order_meta', $order_id, $this->posted );

            // If we got here, the order was created without problems!
            $wpdb->query( 'COMMIT' );

        } catch ( Exception $e ) {
            // There was an error adding order data!
            $wpdb->query( 'ROLLBACK' );
            return new WP_Error( 'checkout-error', $e->getMessage() );
        }

        return $order_id;
    }

    /**
     * Process the checkout after the confirm order button is pressed
     *
     * @access public
     * @return void|array
     */
    public function process_checkout() {
       //  if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'woocommerce-process_checkout' ) ) {
       //     return false;
      //  }

        if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) )
            define( 'WOOCOMMERCE_CHECKOUT', true );

        // Prevent timeout
        @set_time_limit(0);

        do_action( 'woocommerce_before_checkout_process' );

        if ( sizeof( WC()->cart->get_cart() ) == 0 ) {
            wc_add_notice( sprintf( __( 'Sorry, your session has expired. <a href="%s" class="wc-backward">Return to homepage</a>', 'woocommerce' ), home_url() ), 'error' );
        }

        do_action( 'woocommerce_checkout_process' );

        // Checkout fields (not defined in checkout_fields)
        $this->posted['terms']                     = isset( $_POST['terms'] ) ? 1 : 0;
        $this->posted['createaccount']             = isset( $_POST['createaccount'] ) && ! empty( $_POST['createaccount'] ) ? 1 : 0;
        $this->posted['payment_method']            = isset( $_POST['payment_method'] ) ? stripslashes( $_POST['payment_method'] ) : '';
        $this->posted['shipping_method']           = isset( $_POST['shipping_method'] ) ? $_POST['shipping_method'] : '';
        $this->posted['ship_to_different_address'] = isset( $_POST['ship_to_different_address'] ) ? true : false;

        if ( isset( $_POST['shiptobilling'] ) ) {
            _deprecated_argument( 'WC_Checkout::process_checkout()', '2.1', 'The "shiptobilling" field is deprecated. THe template files are out of date' );

            $this->posted['ship_to_different_address'] = $_POST['shiptobilling'] ? false : true;
        }

        // Ship to billing only option
        if ( WC()->cart->ship_to_billing_address_only() ) {
            $this->posted['ship_to_different_address']  = false;
        }

        // Update customer shipping and payment method to posted method
        $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

        if ( isset( $this->posted['shipping_method'] ) && is_array( $this->posted['shipping_method'] ) ) {
            foreach ( $this->posted['shipping_method'] as $i => $value ) {
                $chosen_shipping_methods[ $i ] = wc_clean( $value );
            }
        }

        WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
        WC()->session->set( 'chosen_payment_method', $this->posted['payment_method'] );

        // Note if we skip shipping
        $skipped_shipping = false;

        // Get posted checkout_fields and do validation
        foreach ( $this->checkout_fields as $fieldset_key => $fieldset ) {

            // Skip shipping if not needed
            if ( $fieldset_key == 'shipping' && ( $this->posted['ship_to_different_address'] == false || ! WC()->cart->needs_shipping() ) ) {
                $skipped_shipping = true;
                continue;
            }

            // Ship account if not needed
            if ( $fieldset_key == 'account' && ( is_user_logged_in() || ( $this->must_create_account == false && empty( $this->posted['createaccount'] ) ) ) ) {
                continue;
            }

            foreach ( $fieldset as $key => $field ) {

                if ( ! isset( $field['type'] ) ) {
                    $field['type'] = 'text';
                }

                // Get Value
                switch ( $field['type'] ) {
                    case "checkbox" :
                        $this->posted[ $key ] = isset( $_POST[ $key ] ) ? 1 : 0;
                        break;
                    case "multiselect" :
                        $this->posted[ $key ] = isset( $_POST[ $key ] ) ? implode( ', ', array_map( 'wc_clean', $_POST[ $key ] ) ) : '';
                        break;
                    case "textarea" :
                        $this->posted[ $key ] = isset( $_POST[ $key ] ) ? wp_strip_all_tags( wp_check_invalid_utf8( stripslashes( $_POST[ $key ] ) ) ) : '';
                        break;
                    default :
                        $this->posted[ $key ] = isset( $_POST[ $key ] ) ? ( is_array( $_POST[ $key ] ) ? array_map( 'wc_clean', $_POST[ $key ] ) : wc_clean( $_POST[ $key ] ) ) : '';
                        break;
                }

                // Hooks to allow modification of value
                $this->posted[ $key ] = apply_filters( 'woocommerce_process_checkout_' . sanitize_title( $field['type'] ) . '_field', $this->posted[ $key ] );
                $this->posted[ $key ] = apply_filters( 'woocommerce_process_checkout_field_' . $key, $this->posted[ $key ] );

                // Validation: Required fields
                if ( isset( $field['required'] ) && $field['required'] && empty( $this->posted[ $key ] ) ) {
                    wc_add_notice( '<strong>' . $field['label'] . '</strong> ' . __( 'is a required field.', 'woocommerce' ), 'error' );
                }

                if ( ! empty( $this->posted[ $key ] ) ) {

                    // Validation rules
                    if ( ! empty( $field['validate'] ) && is_array( $field['validate'] ) ) {
                        foreach ( $field['validate'] as $rule ) {
                            switch ( $rule ) {
                                case 'postcode' :
                                    $this->posted[ $key ] = strtoupper( str_replace( ' ', '', $this->posted[ $key ] ) );

                                    if ( ! WC_Validation::is_postcode( $this->posted[ $key ], $_POST[ $fieldset_key . '_country' ] ) ) :
                                        wc_add_notice( __( 'Please enter a valid postcode/ZIP.', 'woocommerce' ), 'error' );
                                    else :
                                        $this->posted[ $key ] = wc_format_postcode( $this->posted[ $key ], $_POST[ $fieldset_key . '_country' ] );
                                    endif;
                                    break;
                                case 'phone' :
                                    $this->posted[ $key ] = wc_format_phone_number( $this->posted[ $key ] );

                                    if ( ! WC_Validation::is_phone( $this->posted[ $key ] ) )
                                        wc_add_notice( '<strong>' . $field['label'] . '</strong> ' . __( 'is not a valid phone number.', 'woocommerce' ), 'error' );
                                    break;
                                case 'email' :
                                    $this->posted[ $key ] = strtolower( $this->posted[ $key ] );

                                    if ( ! is_email( $this->posted[ $key ] ) )
                                        wc_add_notice( '<strong>' . $field['label'] . '</strong> ' . __( 'is not a valid email address.', 'woocommerce' ), 'error' );
                                    break;
                                case 'state' :
                                    // Get valid states
                                    $valid_states = WC()->countries->get_states( isset( $_POST[ $fieldset_key . '_country' ] ) ? $_POST[ $fieldset_key . '_country' ] : ( 'billing' === $fieldset_key ? WC()->customer->get_country() : WC()->customer->get_shipping_country() ) );

                                    if ( ! empty( $valid_states ) && is_array( $valid_states ) ) {
                                        $valid_state_values = array_flip( array_map( 'strtolower', $valid_states ) );

                                        // Convert value to key if set
                                        if ( isset( $valid_state_values[ strtolower( $this->posted[ $key ] ) ] ) ) {
                                            $this->posted[ $key ] = $valid_state_values[ strtolower( $this->posted[ $key ] ) ];
                                        }
                                    }

                                    // Only validate if the country has specific state options
                                    if ( ! empty( $valid_states ) && is_array( $valid_states ) && sizeof( $valid_states ) > 0 ) {
                                        if ( ! in_array( $this->posted[ $key ], array_keys( $valid_states ) ) ) {
                                            wc_add_notice( '<strong>' . $field['label'] . '</strong> ' . __( 'is not valid. Please enter one of the following:', 'woocommerce' ) . ' ' . implode( ', ', $valid_states ), 'error' );
                                        }
                                    }
                                    break;
                            }
                        }
                    }
                }
            }
        }

        // Update customer location to posted location so we can correctly check available shipping methods
        if ( isset( $this->posted['billing_country'] ) ) {
            WC()->customer->set_country( $this->posted['billing_country'] );
        }
        if ( isset( $this->posted['billing_state'] ) ) {
            WC()->customer->set_state( $this->posted['billing_state'] );
        }
        if ( isset( $this->posted['billing_postcode'] ) ) {
            WC()->customer->set_postcode( $this->posted['billing_postcode'] );
        }

        // Shipping Information
        if ( ! $skipped_shipping ) {

            // Update customer location to posted location so we can correctly check available shipping methods
            if ( isset( $this->posted['shipping_country'] ) ) {
                WC()->customer->set_shipping_country( $this->posted['shipping_country'] );
            }
            if ( isset( $this->posted['shipping_state'] ) ) {
                WC()->customer->set_shipping_state( $this->posted['shipping_state'] );
            }
            if ( isset( $this->posted['shipping_postcode'] ) ) {
                WC()->customer->set_shipping_postcode( $this->posted['shipping_postcode'] );
            }

        } else {

            // Update customer location to posted location so we can correctly check available shipping methods
            if ( isset( $this->posted['billing_country'] ) ) {
                WC()->customer->set_shipping_country( $this->posted['billing_country'] );
            }
            if ( isset( $this->posted['billing_state'] ) ) {
                WC()->customer->set_shipping_state( $this->posted['billing_state'] );
            }
            if ( isset( $this->posted['billing_postcode'] ) ) {
                WC()->customer->set_shipping_postcode( $this->posted['billing_postcode'] );
            }

        }

        // Update cart totals now we have customer address
        WC()->cart->calculate_totals();

        // Terms
        if ( ! isset( $_POST['woocommerce_checkout_update_totals'] ) && empty( $this->posted['terms'] ) && wc_get_page_id( 'terms' ) > 0 ) {
            wc_add_notice( __( 'You must accept our Terms &amp; Conditions.', 'woocommerce' ), 'error' );
        }

        if ( WC()->cart->needs_shipping() ) {

            if ( ! in_array( WC()->customer->get_shipping_country(), array_keys( WC()->countries->get_shipping_countries() ) ) ) {
                wc_add_notice( sprintf( __( 'Unfortunately <strong>we do not ship %s</strong>. Please enter an alternative shipping address.', 'woocommerce' ), WC()->countries->shipping_to_prefix() . ' ' . WC()->customer->get_shipping_country() ), 'error' );
            }

            // Validate Shipping Methods
            $packages               = WC()->shipping->get_packages();
            $this->shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

            foreach ( $packages as $i => $package ) {
                if ( ! isset( $package['rates'][ $this->shipping_methods[ $i ] ] ) ) {
                    wc_add_notice( __( 'Invalid shipping method.', 'woocommerce' ), 'error' );
                    $this->shipping_methods[ $i ] = '';
                }
            }
        }

        if ( WC()->cart->needs_payment() ) {

            // Payment Method
            $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

            if ( ! isset( $available_gateways[ $this->posted['payment_method'] ] ) ) {
                $this->payment_method_dup = '';
                wc_add_notice( __( 'Invalid payment method.', 'woocommerce' ), 'error' );
            } else {
                $this->payment_method_dup = $available_gateways[ $this->posted['payment_method'] ];
                $this->payment_method_dup->validate_fields();
            }
        }

        // Action after validation
        do_action( 'woocommerce_after_checkout_validation', $this->posted );

        if ( ! isset( $_POST['woocommerce_checkout_update_totals'] ) && wc_notice_count( 'error' ) == 0 ) {

            try {

                // Customer accounts
                $this->current_customer = apply_filters( 'woocommerce_checkout_customer_id', get_current_user_id() );

                if ( ! is_user_logged_in() && ( $this->must_create_account || ! empty( $this->posted['createaccount'] ) ) ) {

                    $username     = ! empty( $this->posted['account_username'] ) ? $this->posted['account_username'] : '';
                    $password     = ! empty( $this->posted['account_password'] ) ? $this->posted['account_password'] : '';
                    $new_customer = wc_create_new_customer( $this->posted['billing_email'], $username, $password );

                    if ( is_wp_error( $new_customer ) ) {
                        throw new Exception( $new_customer->get_error_message() );
                    }

                    $this->current_customer = $new_customer;

                    wc_set_customer_auth_cookie( $this->current_customer );

                    // As we are now logged in, checkout will need to refresh to show logged in data
                    WC()->session->set( 'reload_checkout', true );

                    // Also, recalculate cart totals to reveal any role-based discounts that were unavailable before registering
                    WC()->cart->calculate_totals();

                    // Add customer info from other billing fields
                    if ( $this->posted['billing_first_name'] && apply_filters( 'woocommerce_checkout_update_customer_data', true, $this ) ) {
                        $userdata = array(
                            'ID'           => $this->current_customer,
                            'first_name'   => $this->posted['billing_first_name'] ? $this->posted['billing_first_name'] : '',
                            'last_name'    => $this->posted['billing_last_name'] ? $this->posted['billing_last_name'] : '',
                            'display_name' => $this->posted['billing_first_name'] ? $this->posted['billing_first_name'] : ''
                        );
                        wp_update_user( apply_filters( 'woocommerce_checkout_customer_userdata', $userdata, $this ) );
                    }
                }

                // Do a final stock check at this point
                $this->check_cart_items();

                // Abort if errors are present
                if ( wc_notice_count( 'error' ) > 0 )
                    throw new Exception();

                $order_id = $this->create_order();

                if ( is_wp_error( $order_id ) ) {
                    throw new Exception( $order_id->get_error_message() );
                }

                do_action( 'woocommerce_checkout_order_processed', $order_id, $this->posted );

                // Process payment
                if ( WC()->cart->needs_payment() ) {

                    // Store Order ID in session so it can be re-used after payment failure
                    WC()->session->order_awaiting_payment = $order_id;

                    // Process Payment
                    $result = $available_gateways[ $this->posted['payment_method'] ]->process_payment( $order_id );

                    // Redirect to success/confirmation/payment page
                    if ( $result['result'] == 'success' ) {

                        $result = apply_filters( 'woocommerce_payment_successful_result', $result, $order_id );

                        if ( is_ajax() ) {
                            return $result;
                         //   exit;
                        } else {
                            wp_redirect( $result['redirect'] );
                            exit;
                        }

                    }

                } else {

                    if ( empty( $order ) ) {
                        $order = wc_get_order( $order_id );
                    }

                    // No payment was required for order
                    $order->payment_complete();

                    // Empty the Cart
                    WC()->cart->empty_cart();

                    // Get redirect
                    $return_url = $order->get_checkout_order_received_url();

                    // Redirect to success/confirmation/payment page
                    if ( is_ajax() ) {
                        $return =  array(
                                    'result' 	=> 'success',
                                    'redirect'  => apply_filters( 'woocommerce_checkout_no_payment_needed_redirect', $return_url, $order )
                                );
                        return $return;
                      //  exit;
                    } else {
                        wp_safe_redirect(
                            apply_filters( 'woocommerce_checkout_no_payment_needed_redirect', $return_url, $order )
                        );
                        exit;
                    }

                }

            } catch ( Exception $e ) {
                if ( ! empty( $e ) ) {
                    wc_add_notice( $e->getMessage(), 'error' );
                }
            }

        } // endif

        // If we reached this point then there were errors
        if ( is_ajax() ) {

            // only print notices if not reloading the checkout, otherwise they're lost in the page reload
            if ( ! isset( WC()->session->reload_checkout ) ) {
                ob_start();
                wc_print_notices();
                $messages = ob_get_clean();
            }


            $return =array(
                        'result'	=> 'failure',
                        'messages' 	=> isset( $messages ) ? $messages : '',
                        'refresh' 	=> isset( WC()->session->refresh_totals ) ? 'true' : 'false',
                        'reload'    => isset( WC()->session->reload_checkout ) ? 'true' : 'false'
                    );
            unset( WC()->session->refresh_totals, WC()->session->reload_checkout );
            return $return;
        }
    }
}