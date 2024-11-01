<?php

class WOS_Mailer{

	public $opts;
	protected static $instance = null;

	public function __construct() {

		$wc_os_child_email = get_option( 'wc_os_child_email', array());
		$this->opts        =  isset($wc_os_child_email['smtp']) ? $wc_os_child_email['smtp'] : array();
		$this->opts        = !is_array( $this->opts ) ? array() : $this->opts;
		
		$this->opts['status'] = isset($this->opts['status'])?$this->opts['status']:false;
		$this->opts['username'] = isset($this->opts['username'])?$this->opts['username']:'';

		$this->opts['from_email_field'] = $this->opts['username'];
		$this->opts['from_name_field'] = get_bloginfo('name');
		$this->opts['force_from_name_replace'] = false;
		$this->opts['reply_to_email'] = '';
		$this->opts['sub_mode'] = false;
		$this->opts['type_encryption'] = 'ssl'; //tls
		$this->opts['port'] = ((isset($this->opts['port']) && trim($this->opts['port']))?$this->opts['port']:465);
		$this->opts['autentication'] = 'yes';


		$this->opts['email_ignore_list'] = '';
		$this->opts['insecure_ssl'] = false;
		$this->opts['enable_debug'] = true;




		if($this->opts['status']){
			add_action( 'phpmailer_init', array( $this, 'init_smtp' ), 999 );
		}


	}


	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init_smtp( &$phpmailer ) {
		//check if SMTP credentials have been configured.
		if ( ! $this->credentials_configured() ) {
			return;
		}


		/* Set the mailer type as per config above, this overrides the already called isMail method */
		$phpmailer->IsSMTP();
		if ( isset( $this->opts['force_from_name_replace'] ) && 1 === $this->opts['force_from_name_replace'] ) {
			$from_name = $this->opts['from_name_field'];
		} else {
			$from_name = ! empty( $phpmailer->FromName ) ? $phpmailer->FromName : $this->opts['from_name_field'];
		}
		$from_email = $this->opts['from_email_field'];
		//set ReplyTo option if needed
		//this should be set before SetFrom, otherwise might be ignored
		if ( ! empty( $this->opts['reply_to_email'] ) ) {
			if ( isset( $this->opts['sub_mode'] ) && 1 === $this->opts['sub_mode'] ) {
				if ( count( $phpmailer->getReplyToAddresses() ) >= 1 ) {
					// Substitute from_email_field with reply_to_email
					if ( array_key_exists( $this->opts['from_email_field'], $phpmailer->getReplyToAddresses() ) ) {
						$reply_to_emails = $phpmailer->getReplyToAddresses();
						unset( $reply_to_emails[ $this->opts['from_email_field'] ] );
						$phpmailer->clearReplyTos();
						foreach ( $reply_to_emails as $reply_to_email => $reply_to_name ) {
							$phpmailer->AddReplyTo( $reply_to_email, $reply_to_name );
						}
						$phpmailer->AddReplyTo( $this->opts['reply_to_email'], $from_name );
					}
				} else { // Reply-to array is empty so add reply_to_email
					$phpmailer->AddReplyTo( $this->opts['reply_to_email'], $from_name );
				}
			} else { // Default behaviour
				$phpmailer->AddReplyTo( $this->opts['reply_to_email'], $from_name );
			}
		}
		// let's see if we have email ignore list populated
		if ( isset( $this->opts['email_ignore_list'] ) && ! empty( $this->opts['email_ignore_list'] ) ) {
			$emails_arr  = explode( ',', $this->opts['email_ignore_list'] );
			$from        = $phpmailer->From;
			$match_found = false;
			foreach ( $emails_arr as $email ) {
				if ( strtolower( trim( $email ) ) === strtolower( trim( $from ) ) ) {
					$match_found = true;
					break;
				}
			}
			if ( $match_found ) {
				//we should not override From and Fromname
				$from_email = $phpmailer->From;
				$from_name  = $phpmailer->FromName;
			}
		}
		$phpmailer->From     = $from_email;
		$phpmailer->FromName = $from_name;
		$phpmailer->SetFrom( $phpmailer->From, $phpmailer->FromName );
		//This should set Return-Path header for servers that are not properly handling it, but needs testing first
		//$phpmailer->Sender	 = $phpmailer->From;
		/* Set the SMTPSecure value */
		if ( 'none' !== $this->opts['type_encryption'] ) {
			$phpmailer->SMTPSecure = $this->opts['type_encryption'];
		}

		/* Set the other options */
		$phpmailer->Host = $this->opts['host'];
		$phpmailer->Port = $this->opts['port'];

		/* If we're using smtp auth, set the username & password */
		if ( 'yes' === $this->opts['autentication'] ) {
			$phpmailer->SMTPAuth = true;
			$phpmailer->Username = $this->opts['username'];
			$phpmailer->Password = $this->opts['password'];
		}
		//PHPMailer 5.2.10 introduced this option. However, this might cause issues if the server is advertising TLS with an invalid certificate.
		$phpmailer->SMTPAutoTLS = false;

		if ( isset( $this->opts['insecure_ssl'] ) && false !== $this->opts['insecure_ssl'] ) {
			// Insecure SSL option enabled
			$phpmailer->SMTPOptions = array(
				'ssl' => array(
					'verify_peer'       => false,
					'verify_peer_name'  => false,
					'allow_self_signed' => true,
				),
			);
		}

		if ( isset( $this->opts['enable_debug'] ) && $this->opts['enable_debug'] ) {
			$phpmailer->Debugoutput = function ( $str, $level ) {
//				$this->log( $str );
			};
			$phpmailer->SMTPDebug   = 1;
		}
		//set reasonable timeout
		$phpmailer->Timeout = 10;
	}


	public function send_mail( $to_email, $subject, $message ) {
		$ret = array('status_msg'=>'');
		if ( ! $this->credentials_configured() ) {
			
			//global $woocommerce;
			//$mailer = $woocommerce->mailer();			
			
			$co_efrom_name = get_bloginfo('name');
			$co_efrom_email = get_bloginfo('admin_email');
			$co_ereplyto_email = get_bloginfo('admin_email');
			
			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
				'From: '.$co_efrom_name.' <'.$co_efrom_email.'>',
				($co_ereplyto_email?'Reply-To: '.$co_efrom_name.' <'.$co_ereplyto_email.'>':'')
			);			

			/*ob_start();
			wc_get_template( 'emails/email-header.php', array( 'email_heading' => get_bloginfo('name') ) );
			echo $message;
			wc_get_template( 'emails/email-footer.php' );
			$message = ob_get_clean();*/
			
			$status = wp_mail($to_email, $subject, $message, $headers);
			wc_os_logger('debug', 'FIRST '.($status?'SENT':'NOT SENT').' to '.$to_email, true);
			if(!$status && function_exists('mail')){	
				$status = @mail($to_email, $subject, $message, $headers);
				wc_os_logger('debug', 'SECOND '.($status?'SENT':'NOT SENT').' to '.$to_email, true);
			}
			
			//$status = $mailer->send( $to_email, $subject, $message, $headers );
			
			$ret['status_msg'] = ($status?__('Successfully sent.', 'woo-order-splitter'):__("Couldn't send an email.", 'woo-order-splitter')).' '.__('SMTP fields were empty. WordPress default function', 'woo-order-splitter').' <a href="https://developer.wordpress.org/reference/functions/wp_mail" target="_blank">wp_mail()</a> '.__('is in use.', 'woo-order-splitter');
			$ret['status'] = $status;
			
			return $ret;
		}

		require_once ABSPATH . WPINC . '/class-phpmailer.php';
		$mail = new PHPMailer( true );
		$email_staus = false;

		try {

			$charset       = get_bloginfo( 'charset' );
			$mail->CharSet = $charset;

			$from_name  = $this->opts['from_name_field'];
			$from_email = $this->opts['from_email_field'];

			$mail->IsSMTP();

			// send plain text test email
			$mail->ContentType = 'text/html';
			$mail->IsHTML( true );

			/* If using smtp auth, set the username & password */
			if ( 'yes' === $this->opts['autentication'] ) {
				$mail->SMTPAuth = true;
				$mail->Username = $this->opts['username'];
				$mail->Password = $this->opts['password'];
			}

			/* Set the SMTPSecure value, if set to none, leave this blank */
			if ( 'none' !== $this->opts['type_encryption'] ) {
				$mail->SMTPSecure = $this->opts['type_encryption'];
			}

			/* PHPMailer 5.2.10 introduced this option. However, this might cause issues if the server is advertising TLS with an invalid certificate. */
			$mail->SMTPAutoTLS = false;

			if ( isset( $this->opts['insecure_ssl'] ) && false !== $this->opts['insecure_ssl'] ) {
				// Insecure SSL option enabled
				$mail->SMTPOptions = array(
					'ssl' => array(
						'verify_peer'       => false,
						'verify_peer_name'  => false,
						'allow_self_signed' => true,
					),
				);
			}

			/* Set the other options */
			$mail->Host = $this->opts['host'];
			$mail->Port = $this->opts['port'];
			if ( ! empty( $this->opts['reply_to_email'] ) ) {
				$mail->AddReplyTo( $this->opts['reply_to_email'], $from_name );
			}
			$mail->SetFrom( $from_email, $from_name );
			//This should set Return-Path header for servers that are not properly handling it, but needs testing first
			//$mail->Sender		 = $mail->From;
			$mail->Subject = $subject;
			$mail->Body    = $message;
			$mail->AddAddress( $to_email );
			global $debug_msg;
			$debug_msg         = '';
			$mail->Debugoutput = function ( $str, $level ) {
				global $debug_msg;
				$debug_msg .= $str;
			};
			$mail->SMTPDebug   = 1;
			//set reasonable timeout
			$mail->Timeout = 10;

			/* Send mail and return result */
			$email_staus = $mail->Send();
			$mail->ClearAddresses();
			$mail->ClearAllRecipients();
		} catch ( Exception $e ) {
			$ret['error'] = $mail->ErrorInfo;
		}

		$ret['debug_log'] = $debug_msg;
		$ret['status'] = $email_staus;

		return $ret;
	}


	public function credentials_configured() {
		$credentials_configured = true;
		
		if ( $credentials_configured && (!isset( $this->opts['username'] ) || empty( $this->opts['username'] )) ) {
			$credentials_configured = false;
		}
		if ( $credentials_configured && (!isset( $this->opts['host'] ) || empty( $this->opts['host'] )) ) {
			$credentials_configured = false;
		}
		if ( $credentials_configured && !isset( $this->opts['status'] ) ) {
			$credentials_configured = false;
		}
		return $credentials_configured;
	}

}

if(class_exists('WOS_Mailer')){
	WOS_Mailer::get_instance();
}



