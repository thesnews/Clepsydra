<?php
/*
 File: timer
  Provides \foundry\timer class
  
 Version:
  2010.06.08
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry;

/*
 Class: timer
  A micro-timer for execution timing
 
 Example:
 (start code)
  // standard timer
  $timer = new \foundry\timer;
  $timer->start();
  ... Later in the same scope ...
  $timer->stop();
  echo $timer->execution();
  
  // named timer
  $timer = new \foundry\timer('fooTimer');
  $timer->start();
  ... some time later in a different scope ...
  $timer = \foundry\timer::get('fooTimer');
  echo $timer->execution(); // you can get the execution time without stopping
 (end)
 
 Namespace:
  \foundry
*/
class timer {

	private static $timers = array();

	private $startTime;
	private $endTime;
	
	/*
	 Method: get
	  Return a named timer
	 
	 Access:
	  public
	 
	 Parameters:
	  id - _string_ a timer ID
	 
	 Returns:
	  _mixed_ if timer doesn't exit FALSE is returned
	*/
	public static function get($id) {
		return self::$timers[$id];
	}
	
	/*
	 Method: constructor
	  Providing an optional id will allow you to grab the timer later, if needed
	 
	 Access:
	  public
	 
	 Parameters:
	  id - _string_ (optional) a timer ID
	 
	 Returns:
	  _object_
	*/
	public function __construct($id=false) {
		if( $id ) {
			self::$timers[$id] = $this;
		}
	}

	/*
	 Method: microtime
	  Get the current time in microseconds
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _int_
	*/
	public function microtime() {
		$parts = explode(' ', microtime());
		return $parts[1].substr($parts[0], 1);
	}
	
	/*
	 Method: start
	  Start the timer. This will also reset an existing timer.
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _void_
	*/
	public function start() {
		$this->endTime = false;
	
		$this->startTime = $this->microtime();
	}
	
	/*
	 Method: stop
	  Stop the timer
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _int_ the execution time
	*/
	public function stop() {
		$this->endTime = $this->microtime();
		
		return $this->execution();
	}
	
	/*
	 Method: execution
	  Return the current execution time. Note: you can call this without 
	  stopping the timer.
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _int_ the current execution time
	*/
	public function execution() {
		if( $this->startTime && $this->endTime ) {
			return $this->endTime - $this->startTime;
		}
	}

}


?>