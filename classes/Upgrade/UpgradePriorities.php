<?php

namespace StackonetSupportTicket\Upgrade;

defined( 'ABSPATH' ) || exit;

class UpgradePriorities extends UpgradeTerm {
	/**
	 * @var string
	 */
	protected static $old_term_name = 'wpsc_priorities';

	/**
	 * @var string
	 */
	protected static $new_term_name = 'ticket_priority';

	/**
	 * @var string
	 */
	protected static $old_meta_name = 'wpsc_priority_load_order';

	/**
	 * @var string
	 */
	protected static $new_meta_name = 'support_ticket_priority_menu_order';

	/**
	 * @var string
	 */
	protected static $map_option_name = 'support_ticket_priorities_old_new';

	/**
	 * Clone priorities
	 */
	public static function clone_priorities() {
		$data = [];

		foreach ( static::get_old_terms() as $priority ) {
			$data[ $priority->term_id ] = self::clone_priority( $priority );
		}

		update_option( static::$map_option_name, $data, false );
	}

	/**
	 * @param \WP_Term $priority
	 *
	 * @return int
	 */
	protected static function clone_priority( \WP_Term $priority ) {
		$term_id = parent::clone_term( $priority );
		if ( $term_id ) {
			global $wpdb;
			$wpdb->update( $wpdb->prefix . 'support_ticket',
				[ 'ticket_priority' => $term_id ],
				[ 'ticket_priority' => $priority->term_id ]
			);
		}

		return $term_id;
	}
}