<?php

namespace DC\EDD\Bkash;

/**
 * Class Installer
 * @package DC\EDD\Bkash
 */
class Installer {
    /**
     * Run the installer
     *
     * @return void
     */
    public function run() {
        $this->add_version();
        $this->create_tables();
    }

    /**
     * Add time and version on DB
     */
    public function add_version() {
        $installed = get_option( 'DC EDD bKash Payment_installed' );

        if ( ! $installed ) {
            update_option( 'DC EDD bKash Payment_installed', time() );
        }

        update_option( 'DC EDD bKash Payment_version', DC_EDD_BKASH_VERSION );

    }

    /**
     * Create necessary database tables
     *
     * @return void
     */
    public function create_tables() {
        if ( ! function_exists( 'dbDelta' ) ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        $this->create_dc_edd_bkash_transactions_table();
    }

    /**
     * Create dc_edd_bkash_transactions table
     *
     * @return void
     */
    public function create_dc_edd_bkash_transactions_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name      = $wpdb->prefix . 'dc_edd_bkash_transactions';

        $schema = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
                      `id` INT(11) NOT NULL AUTO_INCREMENT,
                      `payment_id` VARCHAR(255),
                      `trx_id` VARCHAR(255),
                      `transaction_status` VARCHAR(255),
                      `invoice_number` VARCHAR(255),
                      `order_number` VARCHAR(50),
                      `amount` VARCHAR(50),
                      `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp,
                      `updated_at` TIMESTAMP NOT NULL DEFAULT current_timestamp,
                      PRIMARY KEY (`id`)
                    ) $charset_collate";

        dbDelta($schema);
    }
}
