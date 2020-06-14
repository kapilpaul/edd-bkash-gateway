<?php

namespace DC\EDD\Bkash\Admin;

use DC\EDD\Bkash\Traits\Form_Error;

/**
 * Bkash_Transaction Handler class
 */
class Bkash_Transaction {

    use Form_Error;

    /**
     * Plugin page handler
     *
     * @return void
     */
    public function plugin_page() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $id     = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

        switch ( $action ) {
            default:
                $template = __DIR__ . '/views/bkash-transaction-list.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        }
    }
}
