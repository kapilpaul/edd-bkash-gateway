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
            case 'new':
                $template = __DIR__ . '/views/bkash-transaction-new.php';
                break;

            case 'edit':
                $transaction  = dc_edd_bkash_get_transaction( $id );
                $template = __DIR__ . '/views/bkash-transaction-edit.php';
                break;

            case 'view':
                $template = __DIR__ . '/views/bkash-transaction-view.php';
                break;

            default:
                $template = __DIR__ . '/views/bkash-transaction-list.php';
                break;
        }

        if ( file_exists( $template ) ) {
            include $template;
        }
    }

    /**
     * Handle the form
     *
     * @return void
     */
    public function form_handler() {
        if ( ! isset( $_POST['submit_transaction'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'new-edd-bkash' ) ) {
            wp_die( 'Are you cheating?' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Are you cheating?' );
        }

        $id      = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        

        

        if ( ! empty( $this->errors ) ) {
            return;
        }

        $args = [
            
        ];

        if ( $id ) {
            $args['id'] = $id;
        }

        $insert_id = dc_edd_bkash_insert_transaction( $args );

        if ( is_wp_error( $insert_id ) ) {
            wp_die( $insert_id->get_error_message() );
        }

        if ( $id ) {
            $redirected_to = admin_url( 'admin.php?page=dc-edd-bkash&action=edit&transaction-updated=true&id=' . $id );
        } else {
            $redirected_to = admin_url( 'admin.php?page=dc-edd-bkash&inserted=true' );
        }

        wp_redirect( $redirected_to );
        exit;
    }

    /**
     * Handle delete action
     * 
     * @return void
     */
    public function delete_transaction() {
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'dc-edd-bkash-delete-transaction' ) ) {
            wp_die( 'Are you cheating?' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Are you cheating?' );
        }

        $id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;

        if ( dc_edd_bkash_delete_transaction( $id ) ) {
            $redirected_to = admin_url( 'admin.php?page=dc-edd-bkash&transaction-deleted=true' );
        } else {
            $redirected_to = admin_url( 'admin.php?page=dc-edd-bkash&transaction-deleted=false' );
        }

        wp_redirect( $redirected_to );
        exit;
    }
}
