<?php

namespace StackonetSupportTicket\Integrations\MuslimZone;

use MuslimZone\Modules\PushNotification\TemplateParsers\AbstractTemplateParser;
use StackonetSupportTicket\Models\SupportTicket;
use StackonetSupportTicket\Models\TicketThread;

class SupportTicketParser extends AbstractTemplateParser {
	/**
	 * Support Ticket
	 *
	 * @var SupportTicket
	 */
	private $ticket;

	/**
	 * Ticket Thread
	 *
	 * @var TicketThread
	 */
	private $thread;

	/**
	 * Class constructor.
	 *
	 * @param int $ticket_id
	 * @param int $thread_id
	 */
	public function __construct( int $ticket_id, int $thread_id ) {
		$this->ticket = ( new SupportTicket )->find_by_id( $ticket_id );
		$this->thread = ( new TicketThread )->find_by_id( $thread_id );
		$this->set_category( 'support_ticket' );
		$this->set_unique_identifier( $ticket_id );
	}

	/**
	 * @inheritDoc
	 */
	public function parse( string $string ): string {
		$placeholders = [
			'{{id}}'             => $this->ticket->get_ticket_id(),
			'{{title}}'          => $this->ticket->get_ticket_subject(),
			'{{thread_id}}'      => $this->thread->get_id(),
			'{{thread_content}}' => $this->thread->get_thread_content()
		];

		return str_replace( array_keys( $placeholders ), array_values( $placeholders ), $string );
	}
}
