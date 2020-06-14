<?php

namespace DC\EDD\Bkash\Admin;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List Table Class
 */
class Bkash_Transaction_List extends \WP_List_Table {

    /**
     * Bkash_Transaction_List constructor
     */
    public function __construct() {
        parent::__construct( [
            'singular' => 'transaction',
            'plural'   => 'transactions',
            'ajax'     => false
        ] );
    }

    /**
     * Message to show if no designation found
     *
     * @return void
     */
    public function no_items() {
        _e( 'No Transactions Found!', 'dc-edd-bkash' );
    }

    /**
     * Get the column names
     *
     * @return array
     */
    public function get_columns() {
        return [
            'cb'                 => '<input type="checkbox" />',
            'payment_id'         => __( 'Payment id', 'dc-edd-bkash' ),
            'trx_id'             => __( 'Trx id', 'dc-edd-bkash' ),
            'transaction_status' => __( 'Transaction status', 'dc-edd-bkash' ),
            'invoice_number'     => __( 'Invoice number', 'dc-edd-bkash' ),
            'order_number'       => __( 'Order number', 'dc-edd-bkash' ),
            'amount'             => __( 'Amount', 'dc-edd-bkash' ),
            'created_at'         => __( 'Created at', 'dc-edd-bkash' ),
        ];
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = [];

        return $sortable_columns;
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = array(
            'trash' => __( 'Move to Trash', 'dc-edd-bkash' ),
        );

        return $actions;
    }

    /**
     * Default column values
     *
     * @param object $item
     * @param string $column_name
     *
     * @return string
     */
    protected function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    /**
     * Render the "payment_id" column
     *
     * @param object $item
     *
     * @return string
     */
    public function column_payment_id( $item ) {
        return $this->get_column_actions( $item, 'payment_id' );
    }

    /**
     * get column actions
     *
     * @param object $item
     *
     * @param $column_name
     *
     * @return string
     */
    public function get_column_actions( $item, $column_name ) {
        $actions = [];

        $actions['delete'] = sprintf(
            '<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>',
            add_query_arg(
                [
                    'id'     => absint( $item->id ),
                    'action' => 'delete',
                ]
            ),
            $item->id,
            __( 'Delete this item', 'dc-edd-bkash' ),
            __( 'Delete', 'dc-edd-bkash' )
        );

        return sprintf(
            '<a href="%1$s"><strong>%2$s</strong></a> %3$s', admin_url( 'admin.php?page=dc-edd-bkash&action=view&id' . $item->id ), $item->$column_name, $this->row_actions( $actions )
        );
    }

    /**
     * Render the "cb" column
     *
     * @param object $item
     *
     * @return string
     */
    protected function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="transaction_id[]" value="%d" />', $item->id
        );
    }

    /**
     * Prepare the transaction items
     *
     * @return void
     */
    public function prepare_items() {
        $column   = $this->get_columns();
        $hidden   = [];
        $sortable = $this->get_sortable_columns();
        $this->process_bulk_action();

        $this->_column_headers = [ $column, $hidden, $sortable ];

        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $offset       = ( $current_page - 1 ) * $per_page;

        $args = [
            'number' => $per_page,
            'offset' => $offset,
        ];

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = $_REQUEST['order'];
        }

        $this->items = dc_edd_bkash_get_transactions( $args );

        $this->set_pagination_args( [
            'total_items' => dc_edd_bkash_transaction_count(),
            'per_page'    => $per_page
        ] );
    }

    /**
     * process bulk action
     *
     * @return void
     */
    public function process_bulk_action() {
        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {
            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) ) {
                wp_die( 'Are you cheating?' );
            }
        }

        $action = $this->current_action();

        switch ( $action ) {
            case 'trash':
                if ( dc_edd_bkash_delete_multiple_transactions( $_POST['transaction_id'] ) ) {
                    $this->success_notice( 'Transactions has been deleted' );
                }

                break;
            case 'delete':
                if ( dc_edd_bkash_delete_transaction( sanitize_text_field( $_REQUEST['id'] ) ) ) {
                    $this->success_notice( 'Transaction has been deleted' );
                }

                break;
            default:
                // do nothing or something else
                return;
                break;
        }

        return;
    }

    /**
     * Print success message on the page
     *
     * @param $message
     *
     * @return void
     */
    public function success_notice( $message ) {
        $class   = 'notice notice-success';
        $message = sprintf( __( '%s', 'dc-edd-bkash' ), $message );
        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
    }
}
