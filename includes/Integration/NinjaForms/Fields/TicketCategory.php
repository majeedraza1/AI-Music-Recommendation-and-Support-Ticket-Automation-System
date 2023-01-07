<?php

namespace StackonetSupportTicket\Integration\NinjaForms\Fields;

use NF_Abstracts_List;

/**
 * TicketCategory class
 */
class TicketCategory extends NF_Abstracts_List {
	/**
	 * Class constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->_section  = 'support_ticket';
		$this->_name     = 'ticket_category';
		$this->_nicename = __( 'Ticket Category', 'stackonet-support-ticket' );
	}
}
