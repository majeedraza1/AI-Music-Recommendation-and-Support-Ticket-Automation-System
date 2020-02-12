<?php

namespace StackonetSupportTicket\Upgrade;

defined( 'ABSPATH' ) || exit;

class UpgradeStatus extends UpgradeTerm {

	/**
	 * @var string
	 */
	protected static $old_term_name = 'wpsc_statuses';

	/**
	 * @var string
	 */
	protected static $new_term_name = 'ticket_status';

	/**
	 * @var string
	 */
	protected static $old_meta_name = 'wpsc_status_load_order';

	/**
	 * @var string
	 */
	protected static $new_meta_name = 'support_ticket_status_menu_order';

	/**
	 * @var string
	 */
	protected static $map_option_name = 'support_ticket_statuses_old_new';

	/**
	 * Clone priorities
	 */
	public static function clone_statuses() {
		$data = [];
		foreach ( static::get_old_terms() as $status ) {
			$data[ $status->term_id ] = self::clone_status( $status );
		}

		update_option( static::$map_option_name, $data, false );
	}

	/**
	 * @param \WP_Term $status
	 *
	 * @return int
	 */
	protected static function clone_status( \WP_Term $status ) {
		$term_id = static::clone_term( $status );
		if ( $term_id ) {
			global $wpdb;
			$wpdb->update( $wpdb->prefix . 'support_ticket',
				[ 'ticket_status' => $term_id ],
				[ 'ticket_status' => $status->term_id ]
			);
		}

		return $term_id;
	}
}