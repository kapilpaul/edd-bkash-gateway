<?php

namespace DC\EDD\Bkash\Frontend;

use EDD_Customer;
use EDD_Payment;

/**
 * Class Ajax
 * @package DC\EDD\Bkash\Frontend
 */
class Ajax {
    /**
     * Ajax constructor.
     */
    public function __construct() {
        add_action( 'wp_ajax_dc-edd-bkash-create-payment-request', [ $this, 'create_payment_request' ] );
        add_action( 'wp_ajax_dc-edd-bkash-execute-payment-request', [ $this, 'execute_payment_request' ] );
    }

    /**
     * create payment request for bKash
     *
     * @return void
     */
    public function create_payment_request() {
        try {
            if ( ! wp_verify_nonce( $_POST['_ajax_nonce'], 'dc-edd-bkash-nonce' ) ) {
                $this->send_json_error( 'Are you cheating?' );
            }

            if ( ! $this->validate_fields( $_POST ) ) {
                $this->send_json_error( 'Empty value is not allowed' );
            }

            $order_number = ( isset( $_POST['order_number'] ) ) ? sanitize_key( $_POST['order_number'] ) : '';

            $order = edd_get_download( $order_number );

            if ( ! is_object( $order ) ) {
                $this->send_json_error( 'Wrong or invalid order ID' );
            }

//            $payment_process = PaymentProcessor::checkout( $order->get_id(), $order->get_total() );
//
//            $url = $payment_process['status'] == 'success' ? $payment_process['url'] : $order->get_checkout_payment_url();
//
//            wp_send_json_success( esc_url_raw( $url ) );

        } catch ( \Exception $e ) {
            $this->send_json_error( $e->getMessage() );
        }
    }

    /**
     * Execute payment request for bKash
     *
     * @return void
     */
    public function execute_payment_request() {
        try {
            if ( ! wp_verify_nonce( $_POST['_ajax_nonce'], 'dc-edd-bkash-nonce' ) ) {
                $this->send_json_error( 'Are you cheating?' );
            }

            if ( ! $this->validate_fields( $_POST ) ) {
                $this->send_json_error( 'Empty value is not allowed' );
            }

            $payment_id   = ( isset( $_POST['payment_id'] ) ) ? sanitize_text_field( $_POST['payment_id'] ) : '';
            $order_number = ( isset( $_POST['order_number'] ) ) ? sanitize_text_field( $_POST['order_number'] ) : '';

            $order = edd_get_download( $order_number );

//            if ( ! is_object( $order ) ) {
//                $this->send_json_error( 'Wrong or invalid order ID' );
//            }

//            $response = BkashQuery::executePayment( $payment_id );

//            if ( $response ) {
//                $this->payment_store( $response['paymentID'], $order_number, $response );
//                $response['order_success_url'] = $order->get_checkout_order_received_url();
//                wp_send_json_success( $response );
//            }
//
//            $this->send_json_error( 'Something went wrong!' );

        } catch ( \Exception $e ) {
            $this->send_json_error( $e->getMessage() );
        }
    }

    /**
     * send json error
     *
     * @param $text
     *
     * @return void
     */
    public function send_json_error( $text ) {
        wp_send_json_error( __( $text, 'dc-edd-bkash' ) );
        wp_die();
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function validate_fields( $data ) {
        foreach ( $data as $key => $value ) {
            if ( empty( $value ) ) {
                return false;
            }
        }

        return true;
    }
}
