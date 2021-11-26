<?php

namespace Shapla\Devtools\Modules\DatabaseTool;

use WP_List_Table;

defined( 'ABSPATH' ) || die;

if ( ! class_exists( WP_List_Table::class ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class TablesListTable extends WP_List_Table {
	/**
	 * Set list table default arguments
	 * WordPress_Database_Admin_List_Table constructor.
	 */
	public function __construct() {
		$args = array(
			'singular' => 'table',
			'plural'   => 'tables',
			'ajax'     => false
		);
		parent::__construct( $args );
	}

	/**
	 * Message to show if no table found
	 *
	 * @return void
	 */
	public function no_items() {
		esc_html_e( 'No table found', 'wordpress-database-admin' );
	}

	/**
	 * the table's columns and titles
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'         => '<input type="checkbox"/>',
			'table'      => __( 'Table', 'wordpress-database-admin' ),
			'type'       => __( 'Type', 'wordpress-database-admin' ),
			'collection' => __( 'Collection', 'wordpress-database-admin' ),
			'rows'       => __( 'Rows', 'wordpress-database-admin' ),
			'size'       => __( 'Size', 'wordpress-database-admin' ),
			'actions'    => __( 'Actions', 'wordpress-database-admin' ),
		);

		return $columns;
	}


	/**
	 * Get sortable columns
	 * @return array
	 */
	public function get_sortable_columns() {
		return array();
	}


	/**
	 * Get column value
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		return isset( $item->{$column_name} ) ? esc_attr( $item->{$column_name} ) : null;
	}

	/**
	 * Get content for checkbox column
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return '<input type="checkbox" name="table[]" value="' . esc_attr( $item->TABLE_NAME ) . '" />';
	}

	/**
	 * Get table name
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	public function column_table( $item ) {
		return esc_attr( $item->TABLE_NAME );
	}

	/**
	 * Get database engine
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	public function column_type( $item ) {
		return esc_attr( $item->ENGINE );
	}

	/**
	 * Get table collation
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	public function column_collection( $item ) {
		return esc_attr( $item->TABLE_COLLATION );
	}

	/**
	 * Get table rows count
	 *
	 * @param object $item
	 *
	 * @return int
	 */
	public function column_rows( $item ) {
		return intval( $item->TABLE_ROWS );
	}

	/**
	 * Get column data size
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	public function column_size( $item ) {
		$data_length  = $item->DATA_LENGTH;
		$index_length = $item->INDEX_LENGTH;
		$size_in_kb   = $data_length + $index_length;

		return $this->formatSizeUnits( $size_in_kb );
	}

	/**
	 * Format size in human readable way
	 *
	 * @param $bytes
	 *
	 * @return string
	 */
	private function formatSizeUnits( $bytes ) {
		if ( $bytes >= 1073741824 ) {
			$bytes = number_format( $bytes / ( 1024 * 1024 * 1024 ), 2 ) . ' GB';
		} elseif ( $bytes >= 1048576 ) {
			$bytes = number_format( $bytes / ( 1024 * 1024 ), 2 ) . ' MB';
		} elseif ( $bytes >= 1024 ) {
			$bytes = number_format( $bytes / 1024, 2 ) . ' KB';
		} elseif ( $bytes > 1 ) {
			$bytes = $bytes . ' bytes';
		} elseif ( $bytes == 1 ) {
			$bytes = $bytes . ' byte';
		} else {
			$bytes = '0 bytes';
		}

		return $bytes;
	}

	/**
	 * Get actions column
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_actions( $item ) {

		$view_url  = $this->get_action_url( array( 'tab' => 'table-data', 'table' => esc_attr( $item->TABLE_NAME ) ) );
		$empty_url = $this->get_action_url( array(
			'tab'    => 'tables-list',
			'table'  => esc_attr( $item->TABLE_NAME ),
			'action' => 'empty',
		) );
		$drop_url  = $this->get_action_url( array(
			'tab'    => 'tables-list',
			'table'  => esc_attr( $item->TABLE_NAME ),
			'action' => 'drop',
		) );

		//Build row actions
		$actions          = array();
		$actions['view']  = sprintf( '<a title="Browse table" href="%1$s">%2$s</a>', $view_url, __( 'Browse', 'wordpress-database-admin' ) );
		$actions['empty'] = sprintf( '<a title="Empty table" href="%1$s">%2$s</a>', $empty_url, __( 'Empty', 'wordpress-database-admin' ) );
		$actions['drop']  = sprintf( '<a title="Drop table" href="%1$s">%2$s</a>', $drop_url, __( 'Drop', 'wordpress-database-admin' ) );

		$item_value = sprintf( '<span class="row-info" data-table="%1$s"></span>',
			esc_attr( $item->TABLE_NAME ) // %1$s
		);

		//Return the title contents
		return sprintf( '%1$s %2$s', $item_value, $this->row_actions( $actions ) );
	}

	/**
	 * Get action url
	 *
	 * @param array $args
	 * @param string $page
	 *
	 * @return string
	 */
	private function get_action_url( array $args, $page = 'tools.php' ) {
		$defaults = array(
			'page'  => empty( $_GET['page'] ) ? null : esc_attr( $_GET['page'] ),
			'tab'   => empty( $_GET['tab'] ) ? null : esc_attr( $_GET['tab'] ),
			'table' => empty( $_GET['table'] ) ? null : esc_attr( $_GET['table'] ),
		);

		$args = wp_parse_args( $args, $defaults );

		return wp_nonce_url( add_query_arg( $args, admin_url( $page ) ), 'sp_delete_table_date' );
	}


	/**
	 * Get bulk actions
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'empty' => __( 'Empty', 'textdomain' ),
			'drop'  => __( 'Drop', 'textdomain' ),
		);

		return $actions;
	}

	/** ************************************************************************
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here...
	 *
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/
	public function prepare_items( $search = null ) {
		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable.
		 *
		 * Finally, we build an array to be used by the class for column headers.
		 */
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		/**
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		$this->process_bulk_action();

		// First, lets decide how many records per page to show
		$per_page = $this->get_items_per_page( 'wordpress_database_admin_per_page', 100 );
		// What page the user is currently looking at
		$current_page = $this->get_pagenum();

		$args = array(
			'orderby'  => ! empty( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : $this->_primary_key,
			'order'    => ! empty( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'desc',
			'offset'   => ( $current_page - 1 ) * $per_page,
			'per_page' => $per_page,
		);

		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = json_decode( json_encode( Helper::get_database_tables_list() ) );

		// Total number of items
		$total_items = count( $this->items );

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page )
		] );
	}

	/**
	 * Process bulk actions
	 *
	 * @return void
	 */
	public function process_bulk_action() {
		$tables = ! empty( $_GET['table'] ) ? $_GET['table'] : '';

		//Detect when a bulk action is being triggered...
		if ( 'empty' === $this->current_action() ) {
			Helper::truncate_table( $tables );
		}

		if ( 'drop' === $this->current_action() ) {
			Helper::drop_table( $tables );
		}
	}
}