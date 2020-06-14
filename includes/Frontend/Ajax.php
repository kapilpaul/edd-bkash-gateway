<?php

namespace DC\EDD\Bkash\Frontend;

use DC\EDD\Bkash\EasyDigitalDownloads\BkashProcessor;
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

            $order = edd_get_payment( $order_number );

            if ( $order ) {
                $price = edd_get_payment_amount( $order_number );

                $payment_process = BkashProcessor::create_payment( $price, "INV-" . $order_number );

                if ( $payment_process ) {
                    wp_send_json_success( $payment_process );
                }
            }
            $this->send_json_error( 'Error in creating payment request' );
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

            $order = edd_get_payment( $order_number );

            if ( $order ) {
                $execute = BkashProcessor::execute_payment( $payment_id );

                if ( $execute ) {
                    $this->payment_store( $execute['paymentID'], $order_number, $execute );
                    $execute['order_success_url'] = edd_get_success_page_uri();
                    wp_send_json_success( $execute );
                }
            }

            $this->send_json_error( 'Error in executing payment request' );
        } catch ( \Exception $e ) {
            $this->send_json_error( $e->getMessage() );
        }
    }

    /**
     * Store the payment and insert and validation on bKash end by payment id
     *
     * @param $payment_id
     *
     * @param $order_number
     *
     * @param bool $bkash_response_data
     *
     * @return bool
     */
    public function payment_store( $payment_id, $order_number, $bkash_response_data ) {
        try {
            $payment_id                    = sanitize_text_field( $payment_id );
            $order                         = edd_get_payment( $order_number );
            $bkash_response_data['trxID']  = sanitize_text_field( $bkash_response_data['trxID'] );
            $bkash_response_data['amount'] = sanitize_text_field( $bkash_response_data['amount'] );

            if ( $order ) {
                $orderGrandTotal = edd_get_payment_amount( $order_number );

                if ( $bkash_response_data['amount'] == $orderGrandTotal ) {
                    //insert edd note
                    edd_insert_payment_note(
                        $order_number,
                        sprintf( __( 'bKash payment completed. Transaction ID #%s! Amount: %s', 'dc-edd-bkash' ),
                            $bkash_response_data['trxID'],
                            $orderGrandTotal
                        ) );

                    edd_update_payment_status( $order_number );

                    $order->add_meta( 'edd_bkash_trx_id', $bkash_response_data['trxID'], true );
                } else {
                    edd_insert_payment_note(
                        $order_number,
                        sprintf( __( 'Partial payment has been made. Transaction ID #%s! Amount: %s', 'dc-edd-bkash' ),
                            $bkash_response_data['trxID'],
                            $orderGrandTotal
                        ) );
                }

                //verify payment
                $paymentInfo = BkashProcessor::verify_payment( $payment_id );

                if ( isset( $paymentInfo['transactionStatus'] ) && isset( $paymentInfo['trxID'] ) ) {
                } else {
                    $paymentInfo = $bkash_response_data;
                }

                $insertData = [
                    "order_number"       => sanitize_text_field( $order_number ),
                    "payment_id"         => sanitize_text_field( $paymentInfo['paymentID'] ),
                    "trx_id"             => sanitize_text_field( $paymentInfo['trxID'] ),
                    "transaction_status" => sanitize_text_field( $paymentInfo['transactionStatus'] ),
                    "invoice_number"     => sanitize_text_field( $paymentInfo['merchantInvoiceNumber'] ),
                    "amount"             => sanitize_text_field( $paymentInfo['amount'] ),
                ];

                if ( dc_edd_bkash_insert_transaction( $insertData ) ) {
                    return true;
                }
            }
        } catch ( \Exception $e ) {
            return false;
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
