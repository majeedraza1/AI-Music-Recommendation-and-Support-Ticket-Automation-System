<?php

namespace StackonetSupportTicket\Emails;

use Exception;
use Stackonet\WP\Framework\Abstracts\BackgroundProcess;
use Stackonet\WP\Framework\Emails\Mailer;
use Stackonet\WP\Framework\Supports\Logger;
use StackonetSupportTicket\Models\SupportTicket;
use StackonetSupportTicket\Models\TicketThread;

class AdminRepliedToTicket extends BackgroundProcess {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	public static $instance = null;

	/**
	 * Action
	 *
	 * @var string
	 * @access protected
	 */
	protected $action = 'background_email_admin_replied_to_ticket';

	/**
	 * Only one instance of the class can be loaded
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'shutdown', [ self::$instance, 'dispatch_data' ] );
		}

		return self::$instance;
	}

	/**
	 * Save and run background on shutdown of all code
	 */
	public function dispatch_data() {
		if ( ! empty( $this->data ) ) {
			$this->save()->dispatch();
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function task( $item ) {
		$ticket_id = $item['ticket_id'] ?? 0;
		$thread_id = $item['thread_id'] ?? 0;

		$support = SupportTicket::find_single( $ticket_id );
		if ( ! $support instanceof SupportTicket ) {
			Logger::log( 'Ticket not found for id #' . $ticket_id );

			return false;
		}

		$thread = TicketThread::find_single( $thread_id );

		$email = $support->get_prop( 'customer_email' );
		$name  = $support->get_prop( 'customer_name' );

		try {
			$support_url = add_query_arg( [
				'ticket' => $support->get_prop( 'ticket_auth_code' ),
				'ref'    => 'email',
			], site_url( '/' ) );

			$mailer = new Mailer();
			$mailer->setTo( $email, $name );
			$mailer->setSubject( 'Reply from support again.' );
			$mailer->set_greeting( ! empty( $name ) ? 'Hello ' . $name . '!' : 'Hello!' );
			$mailer->set_intro_lines( 'A support agent replied for your request. Please check it.' );
			if ( $thread instanceof TicketThread ) {
				$mailer->set_intro_lines( $thread->get_thread_content() );
			}
			$mailer->set_action( 'Check on site', $support_url, 'success' );
			$mailer->send();
		} catch ( Exception $e ) {
			Logger::log( 'Error sending email to customer: ' . $e->getMessage() );
		}

		// Set false to remove task from queue
		return false;
	}
}