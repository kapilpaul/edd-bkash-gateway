<?php
/*
Plugin Name: DC EDD bKash Payment
Plugin URI: https://kapilpaul.me/portfolios/plugins/dc-edd-bkash-payment
Description: bKash payment gateway for Easy Digital Downloads.
Version: 1.0.0
Author: Kapil Paul
Author URI: https://kapilpaul.me
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: dc-edd-bkash
Domain Path: /languages
*/

/**
 * Copyright (c) 2020 Kapil Paul (email: kapilpaul007@gmail.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

//checking edd is active or not
if ( ! in_array( 'easy-digital-downloads/easy-digital-downloads.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}

/**
 * DCoders_EDD_Bkash class
 *
 * @class DCoders_EDD_Bkash The class that holds the entire DCoders_EDD_Bkash plugin
 */
final class DCoders_EDD_Bkash {
    /**
     * Plugin version
     *
     * @var string
     */
    const version = '1.0.0';

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = [];

    /**
     * Constructor for the DCoders_EDD_Bkash class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    private function __construct() {
        $this->define_constants();

        register_activation_hook( __FILE__, [ $this, 'activate' ] );
        register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );

        add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
    }

    /**
     * Initializes the DCoders_EDD_Bkash() class
     *
     * Checks for an existing DCoders_EDD_Bkash() instance
     * and if it doesn't find one, creates it.
     *
     * @return DCoders_EDD_Bkash|bool
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new DCoders_EDD_Bkash();
        }

        return $instance;
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        return $this->{$prop};
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __isset( $prop ) {
        return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
    }

    /**
     * Define the constants
     *
     * @return void
     */
    public function define_constants() {
        define( 'DC_EDD_BKASH_VERSION', self::version );
        define( 'DC_EDD_BKASH_FILE', __FILE__ );
        define( 'DC_EDD_BKASH_PATH', dirname( DC_EDD_BKASH_FILE ) );
        define( 'DC_EDD_BKASH_INCLUDES', DC_EDD_BKASH_PATH . '/includes' );
        define( 'DC_EDD_BKASH_URL', plugins_url( '', DC_EDD_BKASH_FILE ) );
        define( 'DC_EDD_BKASH_ASSETS', DC_EDD_BKASH_URL . '/assets' );
    }

    /**
     * Load the plugin after all plugis are loaded
     *
     * @return void
     */
    public function init_plugin() {
        $this->includes();
        $this->init_hooks();
        $this->init_filters();
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {
        $installer = new DC\EDD\Bkash\Installer();
        $installer->run();
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {

    }

    /**
     * Include the required files
     *
     * @return void
     */
    public function includes() {
        if ( $this->is_request( 'admin' ) ) {
            $this->container['admin'] = new DC\EDD\Bkash\Admin();
        }

        if ( $this->is_request( 'frontend' ) ) {
            $this->container['frontend'] = new DC\EDD\Bkash\Frontend();
        }

        if ( $this->is_request( 'ajax' ) ) {
            // require_once DC_EDD_BKASH_INCLUDES . '/class-ajax.php';
        }

        $this->container['bkash_gateway'] = new DC\EDD\Bkash\EasyDigitalDownloads\Bkash_Gateway();
    }

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function init_hooks() {
        add_action( 'init', [ $this, 'init_classes' ] );

        // Localize our plugin
        add_action( 'init', [ $this, 'localization_setup' ] );

        // bKash does not need a CC form, so remove it.
        add_action( 'edd_dc_bkash_cc_form', '__return_false' );
    }

    /**
     * initialize filters here
     *
     * @return void
     */
    public function init_filters() {
        add_filter( 'edd_payment_gateways', [ $this, 'register_gateway' ] );
    }

    /**
     * Register EDD Payment Gateway
     *
     * @param array $gateways
     *
     * @return array
     */
    public function register_gateway( $gateways ) {
        $gateways['dc_bkash'] = [
            'admin_label'    => 'bKash',
            'checkout_label' => __( 'bKash', 'dc-edd-bkash' )
        ];

        return $gateways;
    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes() {
        if ( $this->is_request( 'ajax' ) ) {
            // $this->container['ajax'] =  new DC\EDD\Bkash\Ajax();
        }

        $this->container['api']    = new DC\EDD\Bkash\Api();
        $this->container['assets'] = new DC\EDD\Bkash\Assets();
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'dc-edd-bkash', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or frontend.
     *
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();

            case 'ajax' :
                return defined( 'DOING_AJAX' );

            case 'rest' :
                return defined( 'REST_REQUEST' );

            case 'cron' :
                return defined( 'DOING_CRON' );

            case 'frontend' :
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }

} // DCoders_EDD_Bkash

/**
 * Initialize the main plugin
 *
 * @return \DCoders_EDD_Bkash|bool
 */
function dcoders_edd_bkash() {
    return DCoders_EDD_Bkash::init();
}

/**
 *  kick-off the plugin
 */
dcoders_edd_bkash();
