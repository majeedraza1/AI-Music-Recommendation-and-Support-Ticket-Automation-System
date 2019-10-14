<?php

namespace StackonetSupportTicket\Supports;


defined( 'ABSPATH' ) || exit;

class Utils {

	/**
	 * Generate CSV from array
	 *
	 * @param array $data
	 * @param string $delimiter
	 * @param string $enclosure
	 *
	 * @return string
	 */
	public static function generateCsv( array $data, $delimiter = ',', $enclosure = '"' ) {
		$handle = fopen( 'php://temp', 'r+' );
		foreach ( $data as $line ) {
			fputcsv( $handle, $line, $delimiter, $enclosure );
		}
		rewind( $handle );
		$contents = '';
		while ( ! feof( $handle ) ) {
			$contents .= fread( $handle, 8192 );
		}
		fclose( $handle );

		return $contents;
	}

	/**
	 * Get user IP address
	 *
	 * @return string
	 */
	public static function get_remote_ip() {
		$server_ip_keys = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $server_ip_keys as $key ) {
			if ( isset( $_SERVER[ $key ] ) && filter_var( $_SERVER[ $key ], FILTER_VALIDATE_IP ) ) {
				return $_SERVER[ $key ];
			}
		}

		// Fallback local ip.
		return '127.0.0.1';
	}
}
