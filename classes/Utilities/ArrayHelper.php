<?php

namespace StackonetSupportTicket\Utilities;

defined( 'ABSPATH' ) || exit;

class ArrayHelper {
	/**
	 * Insert the given element after the given key in the array
	 *
	 * Sample usage:
	 *
	 * given:
	 * array( 'item_1' => 'foo', 'item_2' => 'bar' )
	 *
	 * array_insert_after( $array, 'item_1', array( 'item_1.5' => 'w00t' ) )
	 *
	 * becomes:
	 * array( 'item_1' => 'foo', 'item_1.5' => 'w00t', 'item_2' => 'bar' )
	 *
	 *
	 * @param array $array array to insert the given element into
	 * @param string $insert_key key to insert given element after
	 * @param array $element element to insert into array
	 *
	 * @return array
	 */
	public static function insert_after( array $array, $insert_key, array $element ) {
		$new_array = array();

		foreach ( $array as $key => $value ) {

			$new_array[ $key ] = $value;
			if ( $insert_key == $key ) {

				foreach ( $element as $k => $v ) {
					$new_array[ $k ] = $v;
				}
			}
		}

		return $new_array;
	}
}
