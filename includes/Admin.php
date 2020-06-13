<?php

namespace DC\EDD\Bkash;

/**
 * The admin class
 */
class Admin {

    /**
     * Initialize the class
     */
    public function __construct() {
        $this->dispatch_actions();
        new Admin\Menu();
    }

    /**
     * Dispatch and bind actions
     *
     * @return void
     */
    public function dispatch_actions() {
        $bkash_transaction = new Admin\Bkash_Transaction();
        add_action( 'admin_init', [ $bkash_transaction, 'form_handler' ] );
        add_action( 'admin_post_dc-edd-bkash-delete-transaction', [ $bkash_transaction, 'delete_transaction' ] );
    }

}
