<?php

namespace Shapla\Devtools\Modules\DatabaseTool;

use WP_List_Table;

defined( 'ABSPATH' ) || die;

if ( ! class_exists( WP_List_Table::class ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class TableDataListTable extends WP_List_Table {
	private $_table_header;
	private $_primary_key;

	public function __construct() {
		//Set parent defaults
		parent::__construct( array(
			'singular' => 'column',
			'plural'   => 'columns',
			'ajax'     => false
		) );

		$this->_table_header = $this->get_table_headers( $_GET['table'] );
		$this->_primary_key  = $this->get_primary_key( $_GET['table'] );

		// $_table_header    = $this->get_table_headers( $_GET['table'], true );
		// var_dump( $_table_header );
	}

	/**
	 * Get table header for particular table
	 *
	 * @param string $table_name MySQL table name
	 *
	 * @return array
	 */
	public function get_table_headers( $table_name, $full = false ) {
		global $wpdb;
		$table_header = $wpdb->get_results( "SHOW COLUMNS FROM $table_name FROM $wpdb->dbname;" );

		$tables = array();
		foreach ( $table_header as $_table_header ) {
			$tables[] = $_table_header->Field;
		}

		if ( $full ) {
			return $table_header;
		}

		return $tables;
	}

	/**
	 * Get primary key for current table
	 *
	 * @param string $table_name
	 *
	 * @return string
	 */
	public function get_primary_key( $table_name ) {
		global $wpdb;
		$result = $wpdb->get_row( "SHOW INDEX FROM $table_name FROM $wpdb->dbname;" );

		if ( isset( $result->Column_name ) ) {
			return $result->Column_name;
		}

		return false;
	}

	/**
	 * Message to show if no designation found
	 *
	 * @return void
	 */
	public function no_items() {
		esc_html_e( 'No data found' );
	}

	/**
	 * the table's columns and titles
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'      => '<input type="checkbox"/>',
			'actions' => __( 'Actions' ),
		);
		foreach ( $this->_table_header as $_header ) {
			$columns[ $_header ] = $_header;
		}

		return $columns;
	}


	/**
	 * Get sortable columns
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array();
		foreach ( $this->_table_header as $_header ) {
			$sortable_columns[ $_header ] = array( $_header, false );
		}

		return $sortable_columns;
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
		$column_value = isset( $item->{$column_name} ) ? $item->{$column_name} : false;

		if ( is_null( $column_value ) ) {
			return 'NULL';
		}

		if ( $column_value !== false ) {
			$value = esc_html( $column_value );
			$value = mb_strimwidth( $value, 0, 50, ' ...', 'UTF-8' );

			return $value;
		}

		return '';
	}


	/**
	 * Get actions clumn
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_actions( $item ) {

		$delete_url = $this->__action_url( array(
			'action'            => 'delete',
			$this->_primary_key => $item->{$this->_primary_key}
		) );
		//Build row actions
		$actions = array(
			'edit'   => sprintf( '<a title="Edit" href="?page=%1$s&action=edit&%4$s=%2$s">%3$s</a>', $_GET['page'], $item->{$this->_primary_key}, '<span class="dashicons dashicons-edit"></span>', $this->_primary_key ),
			'delete' => sprintf( '<a title="Delete" href="%1$s"><span class="dashicons dashicons-trash"></span></a>', $delete_url )
		);

		$item_value = sprintf( '<span class="row-info" id="record_id-%2$s" data-primary-key="%1$s" data-primary-value="%2$s"></span>',
			$this->_primary_key, // %1$s
			$item->{$this->_primary_key} // %2$s
		);

		//Return the title contents
		return sprintf( '%1$s %2$s',
			$item_value,
			$this->row_actions( $actions )
		);
	}

	public function __action_url( array $args, $page = 'tools.php' ) {
		$defaults = array(
			'page'  => esc_attr( $_GET['page'] ),
			'tab'   => esc_attr( $_GET['tab'] ),
			'table' => esc_attr( $_GET['table'] ),
		);

		$args = wp_parse_args( $args, $defaults );

		return wp_nonce_url( add_query_arg( $args, admin_url( $page ) ), 'sp_delete_table_date' );
	}


	/**
	 * checkboxes or using bulk actions
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {

		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_primary_key,
			$item->{$this->_primary_key}
		);
	}


	/**
	 * Get bult action
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'textdomain' )
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
	 * @global WPDB $wpdb
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
		$per_page = $this->get_items_per_page( 'shapla_import_export_per_page', 300 );
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
		$this->items = $this->get_results( $args, $search );

		// Total number of items
		$total_items = $this->count_results();

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page )
		) );
	}

	/**
	 * Get all movies data from movies database table
	 *
	 * @return object
	 */
	public function get_results( array $args, $search = null ) {
		global $wpdb;
		$table = ! empty( $_GET['table'] ) ? esc_attr( $_GET['table'] ) : '';
		$paged = ! empty( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;

		$orderby  = $args['orderby'];
		$order    = $args['order'];
		$offset   = $args['offset'];
		$per_page = $args['per_page'];

		$cache_key = sprintf( '%1$s-%2$s', $table, $paged );

		$items = wp_cache_get( $cache_key, 'shapla-database-admin' );

		if ( false === $items ) {

			$items = $wpdb->get_results( "
                SELECT * FROM $table
                ORDER BY $orderby $order
                LIMIT $per_page
                OFFSET $offset
            " );

			if ( $search !== null ) {

			}

			wp_cache_set( $cache_key, $items, 'shapla-database-admin' );
		}

		return $items;
	}

	/**
	 * Get number of total row from database
	 *
	 * @return array
	 */
	public function count_results() {
		global $wpdb;
		$table = ! empty( $_GET['table'] ) ? esc_attr( $_GET['table'] ) : '';

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
	}

	/** ************************************************************************
	 * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
	 * For this example package, we will handle it in the class to keep things
	 * clean and organized.
	 *
	 * @see $this->prepare_items()
	 **************************************************************************/
	public function process_bulk_action() {

		global $wpdb;
		$table = ! empty( $_GET['table'] ) ? esc_attr( $_GET['table'] ) : '';

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			$_primary_value = $_GET[ $this->_primary_key ] ?? null;

			if ( $_primary_value ) {
				if ( is_array( $_primary_value ) ) {
					foreach ( $_primary_value as $id ) {
						$wpdb->delete( $table, [ $this->_primary_key => intval( $id ) ] );
					}
				} else {
					$wpdb->delete( $table, [ $this->_primary_key => intval( $_primary_value ) ] );
				}
			}
		}
	}
}