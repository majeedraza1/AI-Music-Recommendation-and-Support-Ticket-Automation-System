<?php

namespace StackonetSupportTicket\Supports;

use WP_User;

defined( 'ABSPATH' ) || exit;

class Utils {

	/**
	 * Generate CSV from array
	 *
	 * @param array  $data
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

	/**
	 * @param int|string $id_or_email
	 * @param null|array $args
	 *
	 * @return false|string
	 */
	public static function get_avatar_url( $id_or_email, $args = null ) {
		if ( is_numeric( $id_or_email ) ) {
			$user = get_user_by( 'id', absint( $id_or_email ) );

			if ( $user instanceof WP_User ) {
				$avatar_id = (int) get_user_meta( $id_or_email, '_avatar_id', true );

				$url = wp_get_attachment_thumb_url( $avatar_id );
				if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
					return $url;
				}

				$id_or_email = $user->user_email;
			}
		}

		if ( ! is_email( $id_or_email ) ) {
			$id_or_email = 'mail@example.com';
		}

		return get_avatar_url( $id_or_email, $args );
	}
}
