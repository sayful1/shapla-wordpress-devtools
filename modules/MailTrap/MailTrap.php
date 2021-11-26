<?php

namespace Shapla\Devtools\Modules\MailTrap;

use PHPMailer;
use Shapla\Devtools\Config;

class MailTrap {
	/**
	 * @var array
	 */
	private $config = array();

	/**
	 * @var self
	 */
	private static $instance;

	/**
	 * Only one instance of the class can be loaded
	 *
	 * @param array $config
	 *
	 * @return self
	 */
	public static function init( $config ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $config );

			add_action( 'phpmailer_init', array( self::$instance, 'send_mail_in_trap' ) );
		}

		return self::$instance;
	}

	/**
	 * @param PHPMailer $mailer
	 */
	public function send_mail_in_trap( $mailer ) {
		$mailer->isSMTP();
		$mailer->SMTPAuth = true;
		$mailer->Host     = Config::get( 'mail.host' );
		$mailer->Port     = Config::get( 'mail.port' );
		$mailer->Username = Config::get( 'mail.username' );
		$mailer->Password = Config::get( 'mail.password' );
	}
}
