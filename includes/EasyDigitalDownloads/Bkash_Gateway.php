<?php

namespace DC\EDD\Bkash\EasyDigitalDownloads;

/**
 * Class Bkash_Gateway
 * @package DC\EDD\Bkash\EasyDigitalDownloads
 */
class Bkash_Gateway {
    /**
     * Payement gateway code
     *
     * @var string
     */
    private $code;

    /**
     * Bkash_Gateway constructor.
     */
    public function __construct() {
        $this->code = 'dc_bkash';

        add_action( 'edd_gateway_dc_bkash', [ $this, 'edd_process_bkash_payment' ] );
        add_filter( 'edd_settings_sections_gateways', [ $this, 'edd_register_bkash_gateway_section' ] );
        add_filter( 'edd_settings_gateways', [ $this, 'edd_register_bkash_gateway_settings' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'payment_scripts' ] );
    }

    /**
     * Register the bkash gateway subsection
     *
     * @param array $gateway_sections Current Gateway Tab subsections
     *
     * @return array Gateway subsections with bKash
     */
    public function edd_register_bkash_gateway_section( $gateway_sections ) {
        $gateway_sections[ $this->code ] = __( 'bKash', 'dc-edd-bkash' );

        return $gateway_sections;
    }

    /**
     * Registers the bKash settings for the bKash subsection
     *
     * @param array $gateway_settings Gateway tab settings
     *
     * @return array Gateway tab settings with the bKash settings
     */
    public function edd_register_bkash_gateway_settings( $gateway_settings ) {
        $bkash_settings = [
            'dc_bkash_settings' => [
                'id'   => 'dc_bkash_settings',
                'name' => '<strong>' . __( 'bKash Settings', 'dc-edd-bkash' ) . '</strong>',
                'type' => 'header',
            ],
        ];

        $api_key_settings = [
            'dc_bkash_test_mode'    => [
                'id'   => 'dc_bkash_test_mode',
                'name' => __( 'Test Mode', 'dc-edd-bkash' ),
                'type' => 'checkbox',
            ],
            'dc_bkash_username'     => [
                'id'   => 'dc_bkash_username',
                'name' => __( 'Username', 'dc-edd-bkash' ),
                'desc' => __( 'Your bKash username. ', 'dc-edd-bkash' ),
                'type' => 'text',
                'size' => 'regular',
            ],
            'dc_bkash_password'     => [
                'id'   => 'dc_bkash_password',
                'name' => __( 'Password', 'dc-edd-bkash' ),
                'desc' => __( 'Your bKash password.', 'dc-edd-bkash' ),
                'type' => 'text',
                'size' => 'regular',
            ],
            'dc_bkash_app_key'      => [
                'id'   => 'dc_bkash_app_key',
                'name' => __( 'App Key', 'dc-edd-bkash' ),
                'desc' => __( 'bKash App Key', 'dc-edd-bkash' ),
                'type' => 'text',
                'size' => 'regular',
            ],
            'dc_bkash_app_password' => [
                'id'   => 'dc_bkash_app_password',
                'name' => __( 'App Password', 'dc-edd-bkash' ),
                'desc' => __( 'bKash App Password', 'dc-edd-bkash' ),
                'type' => 'text',
                'size' => 'regular',
            ],
        ];

        $bkash_settings = array_merge( $bkash_settings, $api_key_settings );

        $bkash_settings                  = apply_filters( 'edd_dc_bkash_settings', $bkash_settings );
        $gateway_settings[ $this->code ] = $bkash_settings;

        return $gateway_settings;
    }

    /**
     * Process EDD payment via bkash
     *
     * @param $purchase_data
     */
    public function edd_process_bkash_payment( $purchase_data ) {
        if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'edd-gateway' ) ) {
            wp_die(
                __( 'Nonce verification has failed', 'dc-edd-bkash' ),
                __( 'Error', 'dc-edd-bkash' ),
                [ 'response' => 403 ]
            );
        }

        global $edd_options;
//        echo "<pre>";
//        print_r( $purchase_data );
//        edd_send_to_success_page();
    }

    /**
     * insert data to edd payment
     *
     * @param $purchase_data
     *
     * @return bool|int
     */
    public function insert_edd_payment( $purchase_data ) {
        // Collect payment data
        $payment_data = array(
            'price'        => $purchase_data['price'],
            'date'         => $purchase_data['date'],
            'user_email'   => $purchase_data['user_email'],
            'purchase_key' => $purchase_data['purchase_key'],
            'currency'     => edd_get_currency(),
            'downloads'    => $purchase_data['downloads'],
            'user_info'    => $purchase_data['user_info'],
            'cart_details' => $purchase_data['cart_details'],
            'gateway'      => $this->code,
            'status'       => ! empty( $purchase_data['buy_now'] ) ? 'private' : 'pending'
        );

        // Record the pending payment
        $payment = edd_insert_payment( $payment_data );

        return $payment;
    }

    /**
     * include payment scripts
     *
     * @return void
     */
    public function payment_scripts() {
        if ( edd_is_checkout() ) {
            wp_enqueue_script( 'edd-bkash-js' );
        }

        $this->localizeScripts();
    }

    /**
     * localize scripts and pass data to js
     *
     * @return void
     */
    public function localizeScripts() {
        global $edd_options;

        if ( $edd_options['dc_bkash_test_mode'] ) {
            $script = "https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js";
        } else {
            $script = "https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js";
        }

        $data = [
            'nonce'      => wp_create_nonce( 'dc-edd-bkash-nonce' ),
            'script_url' => $script,
        ];

        wp_localize_script( 'edd-bkash-js', 'dc_edd_bkash', $data );
    }
}
