<?php

namespace DC\EDD\Bkash\Admin;

/**
 * Admin Pages Handler
 *
 * Class Menu
 * @package DC\EDD\Bkash\Admin
 */
class Menu {
    /**
     * Menu constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
    }

    /**
     * Register our menu page
     *
     * @return void
     */
    public function admin_menu() {
        $parent_slug = 'DC EDD bKash Payment';
        $capability  = 'manage_options';

        $hook = add_menu_page( __( 'bKash Transactions for EDD', 'dc-edd-bkash' ), __( 'EDD bKash', 'dc-edd-bkash' ),
            'manage_options', 'dc-edd-bkash', [ $this, 'dc_edd_bkash_page' ] );

        add_action( 'load-' . $hook, [ $this, 'init_hooks' ] );
    }

    /**
     * Initialize our hooks for the admin page
     *
     * @return void
     */
    public function init_hooks() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Load scripts and styles for the app
     *
     * @return void
     */
    public function enqueue_scripts() {
        // wp_enqueue_style( 'admin' );
        // wp_enqueue_script( 'admin' );
    }

    /**
     * Handles the EDD bKash page
     *
     * @return void
     */
    public function dc_edd_bkash_page() {
        $Bkash_Transaction = new Bkash_Transaction();
        $Bkash_Transaction->plugin_page();
    }
}
