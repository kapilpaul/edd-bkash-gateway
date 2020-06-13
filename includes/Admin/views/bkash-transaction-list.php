<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e( 'EDD bKash', 'dc-edd-bkash' ); ?></h1>

    <a href="<?php echo admin_url( 'admin.php?page=dc-edd-bkash&action=new' ); ?>" class="page-title-action"><?php _e( 'Add New', 'dc-edd-bkash' ); ?></a>

    <?php if ( isset( $_GET['inserted'] ) ) { ?>
        <div class="notice notice-success">
            <p><?php _e( 'transaction has been added successfully!', 'dc-edd-bkash' ); ?></p>
        </div>
    <?php } ?>

    <?php if ( isset( $_GET['transaction-deleted'] ) && $_GET['transaction-deleted'] == 'true' ) { ?>
        <div class="notice notice-success">
            <p><?php _e( 'transaction has been deleted successfully!', 'dc-edd-bkash' ); ?></p>
        </div>
    <?php } ?>

    <form action="" method="post">
        <?php
        $table = new DC\EDD\Bkash\Admin\Bkash_Transaction_List();
        $table->prepare_items();
        $table->search_box( 'search', 'search_id' );
        $table->display();
        ?>
    </form>
</div>