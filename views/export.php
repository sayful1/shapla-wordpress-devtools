<?php

global $wpdb;

$table = esc_attr( $_GET['table'] );

$table_header = $wpdb->get_results( "SHOW COLUMNS FROM $table FROM $wpdb->dbname;" );
$_header      = array();
foreach ( $table_header as $_table_header ) {
	$_header[] = $_table_header->Field;
}

$items = $wpdb->get_results( "SELECT * FROM $table", ARRAY_N );

// var_dump( $items );


// output headers so that the file is downloaded rather than displayed
// header('Content-Type: text/csv; charset=utf-8');
// header('Content-Disposition: attachment; filename=data.csv');

// create a file pointer connected to the output stream
$output = fopen( 'php://output', 'w' );

// output the column headings
fputcsv( $output, $_header );

foreach ( $items as $item ) {
	fputcsv( $output, $item );
}

fclose( $output );