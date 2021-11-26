<?php

defined( 'ABSPATH' ) || die;

$page  = ! empty( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : '';
$tab   = ! empty( $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : '';
$table = ! empty( $_GET['table'] ) ? esc_attr( $_GET['table'] ) : '';

$import_url = add_query_arg( array( 'page' => $page, 'tab' => 'import', 'table' => $table ), admin_url( 'tools.php' ) );
$export_url = add_query_arg( array( 'page' => $page, 'tab' => 'export', 'table' => $table ), admin_url( 'tools.php' ) );
?>

<div class="wrap">

    <h1 class="wp-heading-inline">
		<?php echo esc_attr( $_GET['table'] ); ?>
    </h1>
    <a href="<?php echo esc_url( $import_url ); ?>" class="page-title-action">Import</a>
    <a href="<?php echo esc_url( $export_url ); ?>" class="page-title-action">Export CSV</a>
    <hr class="wp-header-end">

    <form id="sp-table-filter" method="get" autocomplete="off" accept-charset="utf-8">
        <input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>"/>
        <input type="hidden" name="tab" value="<?php echo esc_attr( $_GET['tab'] ); ?>"/>
        <input type="hidden" name="table" value="<?php echo esc_attr( $_GET['table'] ); ?>"/>
		<?php
		//Create an instance of our package class...
		$listTable = new \Shapla\Devtools\Modules\DatabaseTool\TableDataListTable;
		// Check if any search result
		$search = isset( $_GET['s'] ) ? $_GET['s'] : null;
		//Fetch, prepare, sort, and filter our data...
		$listTable->prepare_items( $search );
		// Show search form
		// $listTable->search_box( __('Search Table'), 'table' );
		// Display table with data
		$listTable->display();
		?>
    </form>

</div>

<!-- The Modal -->
<div id="tableCellEditor" class="modal modal-small">
    <div class="modal-content">

        <div class="modal-header">
            <span class="modal-close">&times;</span>
            <h2 class="modal-title">
				<?php echo esc_attr( $table ); ?>:
                <span class="modal-title-column"></span>
            </h2>
        </div><!-- .modal-header -->

        <div class="modal-body">
            <textarea
                    id="tableCellValue"
                    rows="6"
                    data-table=''
                    data-primary-key=''
                    data-primary-value=''
                    data-column-name=''
                    style="width: 100%;"
            ></textarea>
            <span class="table-cell-info"></span>
        </div><!-- .modal-body -->

        <div class="modal-footer">
            <div class="modal-action-buttons">
                <button class="modal-btn modal-btn-cancel">Cancel</button>
                <button class="modal-btn modal-btn-confirm">Update</button>
            </div>
        </div><!-- .modal-footer -->

    </div><!-- .modal-content -->
</div>