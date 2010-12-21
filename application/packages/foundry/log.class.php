<?php
/*
 File: log.class.php
  Provides \foundry\log class
  
 Version:
  2010.06.28
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\log {
	use foundry\db as DB;
	use foundry\config as Conf;

	/*
	 Constant: INFO
	 
	 Namespace:
	  \foundry\log
	*/
	const INFO = 0;

	/*
	 Constant: DEBUG
	 
	 Namespace:
	  \foundry\log
	*/
	const DEBUG = 1;

	/*
	 Constant: NOTICE
	 
	 Namespace:
	  \foundry\log
	*/
	const NOTICE = 2;

	/*
	 Constant: WARNING
	 
	 Namespace:
	  \foundry\log
	*/
	const WARNING = 3;

	/*
	 Constant: ERROR
	 
	 Namespace:
	  \foundry\log
	*/
	const ERROR = 4;

	/*
	 Function: __init__
	  Autoload initializer
	 
	 Parameters:
	  _void_
	  
	 Returns:
	  _void_
	
	 Namespace:
	  \foundry\log
	*/
	function __init__() {

		$handle = DB::create('foundryLog', array(
			'driver'	=> 'sqlite',
			'host'		=> Conf::get('private-path'),
			'database'	=> 'log'
		));
		
		// SQLite only creates a table once, so just do this on every request
		// to make sure the table exists
		$q = 'create table log (level varchar(255), created int,'
			.' path varchar(255), message text, backtrace text)';
		$handle->exec($q);

		\foundry\log::setDefaultHandle($handle);

	}
}
/*

 Valid Warning Levels:
   - info
   - debug
   - notice
   - warning
   - error
*/
namespace foundry {
	use foundry\config as Conf;
	use foundry\queue as Queue;

	/*
	 Class: log
	  Foundry's integrated logging
	  
	 Example:
	 (start code)
	  use foundry\log as Logger;
	  
	  Logger::message('This is a warning!', Logger\WARNING);

	  Logger::message('This is a notice!', Logger\NOTICE);
	 (end)
	 
	 Namespace:
	  \foundry
	*/
	class log {

		private static $defaultHandle;
		
		/*
		 Method: setDefaultHandle
		  sets default database handle. called by the autoload initializer, so
		  you shouldn't ever need to set this
		 
		 Access:
		  public
		 
		 Parameters:
		  h - _resource handle_
		 
		 Returns:
		  _void_
		*/
		public static function setDefaultHandle($h) {
			self::$defaultHandle = $h;
		}
		
		/*
		 Method: message
		  Log a new message
		 
		 Access:
		  public
		 
		 Parameters:
		  message - _string_ the message to log
		  level - _int_ logging level, you can use the contstants to make
		   the levels easier to remember
		 
		 Returns:
		  _void_
		*/
		public function message($message, $level=false) {
			if( !$level ) {
				$level = \foundry\log\INFO;
			}
			$q = 'insert into log (level, created, path, message, backtrace )'
				.' values (:lvl, :tm, :pth, :msg, :bkt)';
			$stmt = self::$defaultHandle->prepare($q);
			$stmt->execute(array(
				':lvl' => $level,
				':tm' => time(),
				':pth' => '',
				':msg' => $message,
				':bkt' => serialize(print_r(debug_backtrace(), true))
			));
			
			self::handleLevel($level, $message);
			
			return;
		}
		
		/*
		 Method: handleLevel
		  Determines the appropriate action for the log level. This is called
		  directly by the message() method.
		 
		 Access:
		  public
		 
		 Parameters:
		  level - _int_
		  message - _string_
		 
		 Returns:
		  _void_
		*/
		public function handleLevel($level, $message) {
			$minWarn = Conf::get('log:minWarn');
			if( $level < $minWarn ) {
				return;
			}

			$from = Conf::get('mail:defaultFrom');
			$to = Conf::get('mail:admin');
			
			Queue::enqueue('\\foundry\\job\\mailer', array(
				'to' => $to,
				'from' => $from,
				'subject' => 'Log warning from ['.\foundry\proc::getPackage()
					.']',
				'message' => $message
					."\nTime: ".time()
					."\n\n"
			));
			
		}
		
		/*
		 Method: cat
		  Display log items
		 
		 Access:
		  public
		 
		 Parameters:
		  limit - _int_
		  offset - _int_
		 
		 Returns:
		  _array_
		*/
		public function cat($limit=false, $offset=0) {
		
			$q = 'select level, message, path, created from log '.
				'order by created desc';
				
			if( $offset ) {
				$limit = '('.$offset.', '.$limit.')';
			}
			if( $limit ) {
				$q .= ' limit '.$limit;
			}

			$stmt = self::$defaultHandle->prepare($q);
			$stmt->execute(null, false, false);

			return $stmt->fetchAll();
		}
	}

}


?>