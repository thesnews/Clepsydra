<?php
/*
 File: mailer.class.php
  Provides Mailer queue job class
  
 Version:
  2010.09.14
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\job;
use foundry\config as Conf;

require_once 'vendor/Swiftmailer/swift_required.php';

/*
 Class: mailer
  The mailer job does the heavy lifting of actually composing and sending the
  message. It accepts the following arguments:
  
   - to - _array_ or _string_
   - from - _array_ or _string_
   - subject - _string_
   - message - _string_
   - attach - _array_ of file PATHS to attach to the message
   
   - template - _string_ a mail template to use (used in place of 'message')
   - parameters - _array_ an array of parameters to pass to the mail template
   
   - prevent_spamming - _bool_ enforces sending limits
   - check_blocked - _bool_ check the receiving address against the block list
   - allow_blocking - _bool_ automatically insert a link at the bottom of the
     message that allows the receiver to opt out of further messages.
 
 Namespace:
  \gryphon\job
*/
class mailer extends \foundry\queue\job {

	private $transport;
	private $mailer;
	
	private $cleanupFiles = array();
	
	public function setUp() {
		$this->log('setup');
	
		$config = Conf::get('mail');
		if( !$config['transport'] ) {
			throw new \foundry\exception\queue('nullValue', 'No mail transport'
				.' is defined');
		}
		
		$this->transport = \Swift_SendmailTransport::newInstance(
			$config['transport']);
		$this->mailer = \Swift_Mailer::newInstance($this->transport);
	}

	public function run() {
		$this->log('run');
		$to = $this->arguments['to'];
		$from = $this->arguments['from'];
		$subject = $this->arguments['subject'];
		$message = $this->arguments['message'];
		
		if( ($template = $this->arguments['template']) ) {
			$t = new \foundry\view\template($template);
			$args = array();

			if( $this->arguments['parameters'] ) {
				$args = $this->arguments['parameters'];
			}
			
			$message = $t->render($args);
		}
		
		if( $this->arguments['prevent_spamming'] ) {
			$this->log('Checking sent messages');
			// preventSpamming ensures that a user isn't sending too many
			// messages within a predefined interval
			
			$limit = Conf::get('mail:limit');
			$interval = Conf::get('mail:interval');
			if( $limit && $interval ) {
				$delta = time() - $interval;
				$sentMessages = \foundry\model::init('gryphon:sentMessage')
					->where('created >= :delt')
					->where('address_from = :addr or ip = :ip')
					->bind(array(
						':delt'	=> $delta,
						':addr'	=> $from,
						':ip'	=> $this->arguments['ip']
					))
					->find();
				
				if( $sentMessages->length >= $limit ) {
					$this->log('Message limit reached for '.
						$this->arguments['ip']);
					return;
				}
				
				$this->log('Passed sent message check');
			}
			
		}
		
		if( $this->arguments['check_blocked'] ) {
			// checks to see if the user is on the block list
			$this->log('Checking block list');
			$blockedRecipient = \foundry\model::init('gryphon:blockedRecipient')
				->where('address = :addr')
				->bind(array(
					':addr'	=> $receiver
				))
				->find()
				->pop();
			
			if( $blockedRecipient->uid ) {
				$this->log('Recipient is on block list');
				return;
			}
			
			$this->log('Passed block list');
		}
		
		// add the sent message entry and add the 'block these messages'
		// link (if requested)
		$sent = \foundry\model::init('gryphon:sentMessage');
		if( is_array($to) ) {
			$sent->address = implode(',', $to);
		} else {
			$sent->address = $to;
		}
		$sent->address_from	= implode(',', $from);
		$sent->ip = $this->arguments['ip'];
		
		$sent->save();
		
		if( $this->arguments['allow_blocking'] ) {
			$blockURL = \foundry\request\url::urlFor('index.php/mail/block/'.
				$sent->hash);
				
			$this->arguments['message_formatted'] .= "\n\nTo block future ".
				"messages, please visit:\n".$blockURL;
		}


		$mail = \Swift_Message::newInstance()
			->setTo($to)
			->setFrom($from)
			->setSubject($subject)
			->setBody($message, 'text/plain');
			
		if( $this->arguments['message_formatted'] ) {
			$mail->addPart($this->arguments['message_formatted'], 'text/plain');
		}
		
		// get your attach on
		if( $this->arguments['attach'] && 
			is_array($this->arguments['attach']) ) {
		
			foreach($this->arguments['attach'] as $file ) {
				if( !file_exists($file) ) {
					continue;
				}
				
				$mail->attach(\Swift_Attachment::fromPath($file));
				$this->cleanupFiles[] = $file;
				
			}
		}

		try {
			if( !$this->mailer->send($mail) ) {
				$this->markFailed('Could not send message');
			} else {
				$this->log('sent');
			}
		} catch( \Swift_Transport_TransportException $e ) {
			$this->log($e->getMessage());
		}
	}
	
	public function tearDown() {
		$this->log('Cleanup');
		foreach( $this->cleanupFiles as $f ) {
			@unlink($f);
		}
	}

}
?>