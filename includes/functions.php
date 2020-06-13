<?php
/**
 * Insert a new transaction
 *
 * @param  array  $args
 *
 * @return int|WP_Error
 */
function dc_edd_bkash_insert_transaction( $args = [] ) {
    global $wpdb;

    
    
    $defaults = [
        'payment_id' => '',
        'trx_id' => '',
        'transaction_status' => '',
        'invoice_number' => '',
        'order_number' => '',
        'amount' => '',
        'created_at' => '',
        'updated_at' => '',
    ];

    $data = wp_parse_args( $args, $defaults );

    if ( isset( $data['id'] ) ) {

        $id = $data['id'];
        unset( $data['id'] );

        $updated = $wpdb->update(
            $wpdb->prefix . 'dc_edd_bkash_transactions',
            $data,
            [ 'id' => $id ],
            [
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            ],
            [ '%d' ]
        );

        dc_edd_bkash_transaction_purge_cache( $id );

        return $updated;

    } else {

        $inserted = $wpdb->insert(
            $wpdb->prefix . 'dc_edd_bkash_transactions',
            $data,
            [
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            ]
        );

        if ( ! $inserted ) {
            return new \WP_Error( 'failed-to-insert', __( 'Failed to insert data', 'dc-edd-bkash' ) );
        }

        dc_edd_bkash_transaction_purge_cache();

        return $wpdb->insert_id;
    }
}

/**
 * Fetch transactions
 *
 * @param  array  $args
 *
 * @return array
 */
function dc_edd_bkash_get_transactions( $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'id',
        'order'   => 'ASC'
    ];

    $args = wp_parse_args( $args, $defaults );

    $last_changed = wp_cache_get_last_changed( 'transaction' );
    $key          = md5( serialize( array_diff_assoc( $args, $defaults ) ) );
    $cache_key    = "all:$key:$last_changed";

    $sql = $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dc_edd_bkash_transactions
            ORDER BY {$args['orderby']} {$args['order']}
            LIMIT %d, %d",
            $args['offset'], $args['number']
    );

    $items = wp_cache_get( $cache_key, 'dc_edd_bkash_transactions' );

    if ( false === $items ) {
        $items = $wpdb->get_results( $sql );

        wp_cache_set( $cache_key, $items, 'dc_edd_bkash_transactions' );
    }

    return $items;
}

/**
 * Get the count of total transactions
 *
 * @return int
 */
function dc_edd_bkash_transaction_count() {
    global $wpdb;

    $count = wp_cache_get( 'count', 'dc_edd_bkash_transactions' );

    if ( false === $count ) {
        $count = (int) $wpdb->get_var( "SELECT count(id) FROM {$wpdb->prefix}dc_edd_bkash_transactions" );

        wp_cache_set( 'count', $count, 'dc_edd_bkash_transactions' );
    }

    return $count;
}

/**
 * Fetch a single transaction from the DB
 *
 * @param  int $id
 *
 * @return object
 */
function dc_edd_bkash_get_transaction( $id ) {
    global $wpdb;

    $item = wp_cache_get( 'transaction-item-' . $id, 'dc_edd_bkash_transactions' );

    if ( false === $item ) {
        $item = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}dc_edd_bkash_transactions WHERE id = %d", $id )
        );

        wp_cache_set( 'transaction-item-' . $id, $item, 'dc_edd_bkash_transactions' );
    }

    return $item;
}

/**
 * Delete an transaction
 *
 * @param  int $id
 *
 * @return int|boolean
 */
function dc_edd_bkash_delete_transaction( $id ) {
    global $wpdb;

    dc_edd_bkash_transaction_purge_cache( $id );

    return $wpdb->delete(
        $wpdb->prefix . 'dc_edd_bkash_transactions',
        [ 'id' => $id ],
        [ '%d' ]
    );
}

/**
 * Delete multiple data from table
 *
 * @param array $ids
 *
 * @return bool|int
 */
function dc_edd_bkash_delete_multiple_transactions( array $ids ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dc_edd_bkash_transactions';

    foreach ( $ids as $id ) {
        dc_edd_bkash_transaction_purge_cache( $id );
    }

    $ids = implode( ',', $ids );
    return $wpdb->query( "DELETE FROM {$table_name} WHERE ID IN($ids)" );
}

/**
 * Purge the cache for dc_edd_bkash_transactions items
 *
 * @param  int $item_id
 *
 * @return void
 */
function dc_edd_bkash_transaction_purge_cache( $item_id = null ) {
    $group = 'dc_edd_bkash_transactions';

    if ( $item_id ) {
        wp_cache_delete( 'transaction-item-' . $item_id, $group );
    }

    wp_cache_delete( 'count', $group );
    wp_cache_set( 'last_changed', microtime(), $group );
}

