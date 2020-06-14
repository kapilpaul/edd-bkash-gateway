<?php

namespace DC\EDD\Bkash\EasyDigitalDownloads;

/**
 * Class BkashProcessor
 * @package DC\EDD\Bkash\EasyDigitalDownloads
 */
class BkashProcessor {
    /**
     * class instance
     */
    private static $selfClassInstance;

    /**
     * @return BkashProcessor
     */
    public static function get_self_class() {
        if ( ! self::$selfClassInstance ) {
            return self::$selfClassInstance = ( new self );
        }

        return self::$selfClassInstance;
    }

    /**
     * Grant Token Url
     *
     * @return string
     */
    public static function grant_token_url() {
        $env = self::check_test_mode() ? 'sandbox' : 'pay';

        return "https://checkout.$env.bka.sh/v1.2.0-beta/checkout/token/grant";
    }

    /**
     * Payment Query Url
     *
     * @return string
     */
    public static function payment_query_url() {
        $env = self::check_test_mode() ? 'sandbox' : 'pay';

        return "https://direct.$env.bka.sh/v1.2.0-beta/checkout/payment/query/";
    }

    /**
     * Payment Create Url
     *
     * @return string
     */
    public static function payment_create_url() {
        return self::get_payment_url( 'create' );
    }

    /**
     * Payment execute Url
     *
     * @param $payment_id
     *
     * @return string
     */
    public static function payment_execute_url( $payment_id = '' ) {
        $url = self::get_payment_url( 'execute' );
        $url = self::check_test_mode() ? $url : $url . "/$payment_id";

        return $url;
    }

    /**
     * get payment url by type
     *
     * @param $type
     *
     * @return string
     */
    public static function get_payment_url( $type ) {
        if ( self::check_test_mode() ) {
            return "https://merchantserver.sandbox.bka.sh/api/checkout/v1.2.0-beta/payment/$type";
        }

        return "https://checkout.pay.bka.sh/v1.2.0-beta/checkout/payment/$type";
    }

    /**
     * Get Token
     *
     * @return bool|mixed
     */
    public static function get_token() {
        if ( $token = get_transient( 'bkash_token' ) ) {
            return $token;
        }

        $user_name = edd_get_option( 'dc_bkash_username' );
        $password  = edd_get_option( 'dc_bkash_password' );

        $data = [
            "app_key"    => edd_get_option( 'dc_bkash_app_key' ),
            "app_secret" => edd_get_option( 'dc_bkash_app_secret' ),
        ];

        $headers = [
            "username"     => $user_name,
            "password"     => $password,
            "Content-Type" => "application/json",
        ];

        $result = self::make_request( self::grant_token_url(), $data, $headers );

        if ( isset( $result['id_token'] ) && isset( $result['token_type'] ) ) {
            $token = $result['id_token'];
            set_transient( 'bkash_token', $token, $result['expires_in'] );

            return $result['id_token'];
        }

        return false;
    }

    /**
     * sending curl request
     *
     * @param $url
     * @param $data
     * @param array $headers
     *
     * @return mixed|string
     */
    public static function make_request( $url, $data, $headers = [] ) {
        if ( isset( $headers['headers'] ) ) {
            $headers = $headers['headers'];
        }

        $args = array(
            'body'        => json_encode( $data ),
            'timeout'     => '30',
            'redirection' => '30',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => $headers,
            'cookies'     => [],
        );

        $response = wp_remote_retrieve_body( wp_remote_post( esc_url_raw( $url ), $args ) );

        return json_decode( $response, true );
    }

    /**
     * verify payment on bKash end
     *
     * @param $paymentID
     *
     * @return bool|mixed|string
     */
    public static function verify_payment( $paymentID ) {
        if ( $token = self::get_token() ) {
            $url      = self::payment_query_url() . $paymentID;
            $response = wp_remote_get( $url, self::get_authorization_header() );
            $result   = json_decode( wp_remote_retrieve_body( $response ), true );

            if ( isset( $result['errorCode'] ) && isset( $result['errorMessage'] ) ) {
                return false;
            }

            return $result;
        }

        return false;
    }

    /**
     * create payment request on bKash
     *
     * @param $invoice
     * @param $amount
     *
     * @return bool|mixed|string
     */
    public static function create_payment( $amount, $invoice ) {
        if ( ! self::check_test_mode() && ! self::get_token() ) {
            return false;
        }

        $payment_data = [
            'amount'                => $amount,
            'currency'              => 'BDT',
            'intent'                => 'sale',
            'merchantInvoiceNumber' => $invoice,
        ];

        $response = self::make_request( self::payment_create_url(), $payment_data, self::get_authorization_header() );

        if ( isset( $response['paymentID'] ) && $response['paymentID'] ) {
            return $response;
        }

        return false;
    }

    /**
     * Execute payment request on bKash
     *
     * @param $payment_id
     *
     * @return bool|mixed|string
     */
    public static function execute_payment( $payment_id ) {
        if ( ! self::check_test_mode() && ! self::get_token() ) {
            return false;
        }

        $data = [];

        if ( self::check_test_mode() ) {
            $data = [ 'paymentID' => $payment_id ];
        }

        $response = self::make_request( self::payment_execute_url( $payment_id ), $data, self::get_authorization_header() );

        if ( isset( $response['transactionStatus'] ) && $response['transactionStatus'] == 'Completed' ) {
            return $response;
        }

        return false;
    }

    /**
     * Get Authorization header for bkash
     *
     * @return array
     */
    public static function get_authorization_header() {
        if ( $token = self::get_token() ) {
            $headers = [
                "Authorization" => "Bearer {$token}",
                "X-App-Key"     => edd_get_option( 'dc_bkash_app_key' ),
                "Content-Type"  => 'application/json',
            ];

            $args = [ 'headers' => $headers ];

            return $args;
        }

        return [ 'headers' => [ "Content-Type" => 'application/json' ] ];
    }

    /**
     * Check test mode is on or not
     *
     * @return bool
     */
    public static function check_test_mode() {
        if ( edd_get_option( 'dc_bkash_test_mode' ) ) {
            return true;
        }

        return false;
    }
}

