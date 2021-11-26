<?php defined( 'ABSPATH' ) || die; ?>
<div class="wrap">

    <h1 class="wp-heading-inline"><?php esc_html_e( 'Tables', 'wordpress-database-admin' ) ?></h1>
    <hr class="wp-header-end">

    <form id="form-tables-list" class="form-tables-list" method="get" autocomplete="off" accept-charset="utf-8">
        <input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>"/>
        <input type="hidden" name="tab" value="<?php echo esc_attr( $_GET['tab'] ); ?>"/>
		<?php
		//Create an instance of our package class...
		$listTable = new \Shapla\Devtools\Modules\DatabaseTool\TablesListTable();
		//Fetch, prepare, sort, and filter our data...
		$listTable->prepare_items();
		// Display table with data
		$listTable->display();
		?>
    </form>

</div>
