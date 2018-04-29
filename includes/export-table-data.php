<?php

add_action( 'admin_init', 'export_table_data' );

function export_table_data() {
	if ( ! isset( $_GET['page'], $_GET['tab'], $_GET['table'] ) ) {
		return;
	}

	if ( $_GET['page'] !== 'shapla-import-export' ) {
		return;
	}

	if ( $_GET['tab'] !== 'export' ) {
		return;
	}

	global $wpdb;

	$table = esc_attr( $_GET['table'] );

	$table_header = $wpdb->get_results( "SHOW COLUMNS FROM $table FROM $wpdb->dbname;" );
	$_header      = array();
	foreach ( $table_header as $_table_header ) {
		$_header[] = $_table_header->Field;
	}

	$items = $wpdb->get_results( "SELECT * FROM $table", ARRAY_N );

	// output headers so that the file is downloaded rather than displayed
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $table . '.csv' );
	// create a file pointer connected to the output stream
	$output = fopen( 'php://output', 'w' );

	// output the column headings
	fputcsv( $output, $_header );

	foreach ( $items as $item ) {
		fputcsv( $output, $item );
	}

	fclose( $output );
	die();
}

add_action( 'admin_init', 'import_table_data' );

function import_table_data() {
	if ( ! isset( $_POST['page'], $_POST['tab'], $_POST['table'] ) ) {
		return;
	}

	if ( $_POST['page'] !== 'shapla-import-export' ) {
		return;
	}

	if ( $_POST['tab'] !== 'import' ) {
		return;
	}

	$file  = isset( $_FILES['file_csv'] ) ? $_FILES['file_csv'] : null;
	$table = isset( $_POST['table'] ) ? $_POST['table'] : null;

	$type     = isset( $file['type'] ) ? $file['type'] : null;
	$size     = isset( $file['size'] ) ? $file['size'] : null;
	$tmp_name = isset( $file['tmp_name'] ) ? $file['tmp_name'] : null;

	$csv_results = array_map( 'str_getcsv', file( $tmp_name ) );
	$csv_header  = array_shift( $csv_results );

	global $wpdb;
	$table_header = $wpdb->get_results( "SHOW COLUMNS FROM $table FROM $wpdb->dbname;" );

	$rows = array_map( function ( $data ) use ( $table_header ) {
		$_header_count = count( $table_header );
		$_data_count   = count( $data );

		if ( $_header_count !== $_data_count ) {
			return array();
		}

		$_header = array();
		foreach ( $table_header as $header ) {
			$_header[] = $header->Field;
		}

		$new_data = array();
		for ( $i = 0; $i < $_data_count; $i ++ ) {
			$new_data[ $_header[ $i ] ] = $data[ $i ];
		}

		return $new_data;

	}, $csv_results );

	foreach ( $rows as $value ) {
		$wpdb->insert( $table, $value );
		echo $wpdb->insert_id . '<br>';
	}
}
