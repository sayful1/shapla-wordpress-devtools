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
		return array(
			'cb'              => '<input type="checkbox"/>',
			'TABLE_NAME'      => __( 'Table', 'wordpress-database-admin' ),
			'ENGINE'          => __( 'Type', 'wordpress-database-admin' ),
			'TABLE_COLLATION' => __( 'Collection', 'wordpress-database-admin' ),
			'TABLE_ROWS'      => __( 'Rows', 'wordpress-database-admin' ),
			'size'            => __( 'Size', 'wordpress-database-admin' ),
			'actions'         => __( 'Actions', 'wordpress-database-admin' ),
		);
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
		$value = null;
		if ( is_array( $item ) && isset( $item[ $column_name ] ) ) {
			$value = $item[ $column_name ];
		}
		if ( is_object( $item ) && isset( $item->{$column_name} ) ) {
			$value = $item->{$column_name};
		}

		return $value;
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
	 * Get column data size
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	public function column_size( $item ) {
		return size_format( $item->DATA_LENGTH + $item->INDEX_LENGTH );
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
		$drop_url  = $this->get_action_url( [
			'tab'    => 'tables-list',
			'table'  => esc_attr( $item->TABLE_NAME ),
			'action' => 'drop',
		] );

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
		$defaults = [
			'page'  => $_GET['page'] ?? null,
			'tab'   => $_GET['tab'] ?? null,
			'table' => $_GET['table'] ?? null,
		];

		$args = wp_parse_args( $args, $defaults );

		return wp_nonce_url( add_query_arg( $args, admin_url( $page ) ), 'sp_delete_table_date' );
	}


	/**
	 * Get bulk actions
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return [
			'empty' => __( 'Empty', 'textdomain' ),
			'drop'  => __( 'Drop', 'textdomain' ),
		];
	}

	/**
	 * @inheritDoc
	 */
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

		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = json_decode( json_encode( Helper::get_database_tables_list() ) );

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args( [
			'total_items' => count( $this->items ),
			'per_page'    => $this->get_items_per_page( 'wordpress_database_admin_per_page', 100 ),
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