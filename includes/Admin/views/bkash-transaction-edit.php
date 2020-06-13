<div class="wrap">
    <h1><?php _e( 'Edit transaction', 'dc-edd-bkash' ); ?></h1>

    <?php if ( isset( $_GET['transaction-updated'] ) ) { ?>
        <div class="notice notice-success">
            <p><?php _e( 'transaction has been updated successfully!', 'dc-edd-bkash' ); ?></p>
        </div>
    <?php } ?>

    <form action="" method="post">
        <table class="form-table">
            <tbody>
                <tr class="row<?php echo $this->has_error( 'payment_id' ) ? ' form-invalid' : '' ;?>">
                    <th scope="row">
                        <label for="payment_id"><?php _e( 'Payment id', 'dc-edd-bkash' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="payment_id" id="payment_id" class="regular-text" placeholder="<?php echo esc_attr( '', 'dc-edd-bkash' ); ?>" value="<?php echo esc_attr( $transaction->payment_id ); ?>"  />

                        <?php if ( $this->has_error( 'payment_id' ) ) { ?>
                            <p class="description error"><?php echo $this->get_error( 'payment_id' ); ?></p>
                        <?php } ?>
                    </td>
                </tr>
                <tr class="row<?php echo $this->has_error( 'trx_id' ) ? ' form-invalid' : '' ;?>">
                    <th scope="row">
                        <label for="trx_id"><?php _e( 'Trx id', 'dc-edd-bkash' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="trx_id" id="trx_id" class="regular-text" placeholder="<?php echo esc_attr( '', 'dc-edd-bkash' ); ?>" value="<?php echo esc_attr( $transaction->trx_id ); ?>"  />

                        <?php if ( $this->has_error( 'trx_id' ) ) { ?>
                            <p class="description error"><?php echo $this->get_error( 'trx_id' ); ?></p>
                        <?php } ?>
                    </td>
                </tr>
                <tr class="row<?php echo $this->has_error( 'transaction_status' ) ? ' form-invalid' : '' ;?>">
                    <th scope="row">
                        <label for="transaction_status"><?php _e( 'Transaction status', 'dc-edd-bkash' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="transaction_status" id="transaction_status" class="regular-text" placeholder="<?php echo esc_attr( '', 'dc-edd-bkash' ); ?>" value="<?php echo esc_attr( $transaction->transaction_status ); ?>"  />

                        <?php if ( $this->has_error( 'transaction_status' ) ) { ?>
                            <p class="description error"><?php echo $this->get_error( 'transaction_status' ); ?></p>
                        <?php } ?>
                    </td>
                </tr>
                <tr class="row<?php echo $this->has_error( 'invoice_number' ) ? ' form-invalid' : '' ;?>">
                    <th scope="row">
                        <label for="invoice_number"><?php _e( 'Invoice number', 'dc-edd-bkash' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="invoice_number" id="invoice_number" class="regular-text" placeholder="<?php echo esc_attr( '', 'dc-edd-bkash' ); ?>" value="<?php echo esc_attr( $transaction->invoice_number ); ?>"  />

                        <?php if ( $this->has_error( 'invoice_number' ) ) { ?>
                            <p class="description error"><?php echo $this->get_error( 'invoice_number' ); ?></p>
                        <?php } ?>
                    </td>
                </tr>
                <tr class="row<?php echo $this->has_error( 'order_number' ) ? ' form-invalid' : '' ;?>">
                    <th scope="row">
                        <label for="order_number"><?php _e( 'Order number', 'dc-edd-bkash' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="order_number" id="order_number" class="regular-text" placeholder="<?php echo esc_attr( '', 'dc-edd-bkash' ); ?>" value="<?php echo esc_attr( $transaction->order_number ); ?>"  />

                        <?php if ( $this->has_error( 'order_number' ) ) { ?>
                            <p class="description error"><?php echo $this->get_error( 'order_number' ); ?></p>
                        <?php } ?>
                    </td>
                </tr>
                <tr class="row<?php echo $this->has_error( 'amount' ) ? ' form-invalid' : '' ;?>">
                    <th scope="row">
                        <label for="amount"><?php _e( 'Amount', 'dc-edd-bkash' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="amount" id="amount" class="regular-text" placeholder="<?php echo esc_attr( '', 'dc-edd-bkash' ); ?>" value="<?php echo esc_attr( $transaction->amount ); ?>"  />

                        <?php if ( $this->has_error( 'amount' ) ) { ?>
                            <p class="description error"><?php echo $this->get_error( 'amount' ); ?></p>
                        <?php } ?>
                    </td>
                </tr>
                <tr class="row<?php echo $this->has_error( 'created_at' ) ? ' form-invalid' : '' ;?>">
                    <th scope="row">
                        <label for="created_at"><?php _e( 'Created at', 'dc-edd-bkash' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="created_at" id="created_at" class="regular-text" placeholder="<?php echo esc_attr( '', 'dc-edd-bkash' ); ?>" value="<?php echo esc_attr( $transaction->created_at ); ?>"  />

                        <?php if ( $this->has_error( 'created_at' ) ) { ?>
                            <p class="description error"><?php echo $this->get_error( 'created_at' ); ?></p>
                        <?php } ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <input type="hidden" name="id" value="<?php echo esc_attr( $transaction->id ); ?>">

        <?php wp_nonce_field( 'new-edd-bkash' ); ?>
        <?php submit_button( __( 'Update', 'dc-edd-bkash' ), 'primary', 'submit_transaction' ); ?>
    </form>
</div>