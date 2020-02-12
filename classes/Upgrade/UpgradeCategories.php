<?php

namespace StackonetSupportTicket\Upgrade;

defined( 'ABSPATH' ) || exit;

class UpgradeCategories extends UpgradeTerm {

	/**
	 * @var string
	 */
	protected static $old_term_name = 'wpsc_categories';

	/**
	 * @var string
	 */
	protected static $new_term_name = 'ticket_category';

	/**
	 * @var string
	 */
	protected static $old_meta_name = 'wpsc_category_load_order';

	/**
	 * @var string
	 */
	protected static $new_meta_name = 'support_ticket_category_menu_order';

	/**
	 * @var string
	 */
	protected static $map_option_name = 'support_ticket_categories_old_new';

	/**
	 * Clone categories
	 */
	public static function clone_categories() {
		$data = [];
		foreach ( self::get_old_terms() as $category ) {
			$data[ $category->term_id ] = self::clone_category( $category );
		}

		update_option( static::$map_option_name, $data, false );
	}

	/**
	 * @param \WP_Term $category
	 *
	 * @return int
	 */
	protected static function clone_category( \WP_Term $category ) {
		$term_id = parent::clone_term( $category );
		if ( $term_id ) {
			global $wpdb;
			$wpdb->update( $wpdb->prefix . 'support_ticket',
				[ 'ticket_category' => $term_id ],
				[ 'ticket_category' => $category->term_id ]
			);
		}

		return $term_id;
	}
}