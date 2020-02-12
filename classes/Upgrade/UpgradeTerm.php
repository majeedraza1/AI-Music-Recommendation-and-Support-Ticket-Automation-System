<?php

namespace StackonetSupportTicket\Upgrade;

use StackonetSupportTicket\Utilities\Logger;
use WP_Term;

defined( 'ABSPATH' ) || exit;

abstract class UpgradeTerm {

	/**
	 * @var string
	 */
	protected static $old_term_name;

	/**
	 * @var string
	 */
	protected static $new_term_name;

	/**
	 * @var string
	 */
	protected static $old_meta_name;

	/**
	 * @var string
	 */
	protected static $new_meta_name;

	/**
	 * @var string
	 */
	protected static $map_option_name;

	/**
	 * Get old terms
	 *
	 * @return \WP_Term[]
	 */
	public static function get_old_terms() {
		/** @var \WP_Term[] $categories */
		$categories = get_terms( [
			'taxonomy'   => static::$old_term_name,
			'hide_empty' => false,
		] );

		return $categories;
	}

	/**
	 * @param WP_Term $old_term
	 *
	 * @return int
	 */
	protected static function clone_term( WP_Term $old_term ) {
		$data = wp_insert_term( $old_term->name, self::$new_term_name, [
			'description' => $old_term->description,
			'slug'        => $old_term->slug . '-1',
			'parent'      => $old_term->parent
		] );

		if ( ! is_wp_error( $data ) ) {
			$menu_order = get_term_meta( $old_term->term_id, static::$old_meta_name, true );
			$term_id    = isset( $data['term_id'] ) ? $data['term_id'] : 0;

			update_term_meta( $term_id, static::$new_meta_name, $menu_order );

			return $term_id;
		}

		return 0;
	}

	/**
	 * Map Status
	 */
	public static function map_terms() {
		/** @var WP_Term[] $old_terms */
		$old_terms = get_terms( [
			'taxonomy'   => static::$old_term_name,
			'hide_empty' => false,
		] );
		/** @var WP_Term[] $new_terms */
		$new_terms = get_terms( [
			'taxonomy'   => static::$new_term_name,
			'hide_empty' => false,
		] );

		$data = [];
		foreach ( $old_terms as $old_status ) {
			foreach ( $new_terms as $new_status ) {
				if ( $old_status->slug . '-1' == $new_status->slug ) {
					$data[ $old_status->term_id ] = $new_status->term_id;
				}
			}
		}

		update_option( static::$map_option_name, $data, false );
	}

	/**
	 * Get new term id
	 *
	 * @param int $old_term_id
	 *
	 * @return int
	 */
	public static function get_new_term_id( $old_term_id ) {
		$map = (array) get_option( static::$map_option_name );

		return isset( $map[ $old_term_id ] ) ? $map[ $old_term_id ] : 0;
	}

	/**
	 * Get old term id
	 *
	 * @param int $new_term_id
	 *
	 * @return int
	 */
	public static function get_old_term_id( $new_term_id ) {
		$map   = (array) get_option( static::$map_option_name );
		$index = array_search( $new_term_id, $map );

		return false !== $index ? $index : 0;
	}
}