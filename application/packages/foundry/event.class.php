<?php
/*
 File: event
  Provides \foundry\event class
  
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/

namespace foundry;

/*
 Class: event
  Bind event listener to object or class

 Examples:
  (start code)
  use foundry\event as Event;
  $obj = new \foo\bar;

  Event::bind('\foo\bar', 'someEvent', function() {
 	echo 'foo';
  }); // will print foo when ANY \foo\bar object fires 'someEvent'

  Event::bind($obj, 'someEvent', function() {
 	echo 'bar!';
  }); // will print bar when ONLY this object fires 'someEvent'
  (end)
  
 Namespace:
  \foundry
*/
class event {
   
   private static $listeners = array();
   
	/*
	 Method: bind
	  Bind an event callback to an object
	 
	 Example:
	  (start code)
	   use foundry\event as Event;
	   
	   Event::bind($obj, 'save', function() {
	    ...
	   });
	   
	   Event::bind($obj, 'save', array($otherObj, 'method'));
	  (end)
	 
	 Access:
	  public
	 
	 Parameters:
	  callee - _mixed_ the event emitter
	  event - _string_ the event to bind to
	  callback - _mixed_ the callback to process the event
	 
	 Returns:
	  _void_
	*/
   public static function bind($callee, $event, $callback) {
   		
   		$object = false;
		if( is_object($callee) ) {
			$object = $callee;
			$callee = spl_object_hash($callee);
		}
   
		if( !isset(self::$listeners[$callee])
			|| !is_array(self::$listeners[$callee]) ) {

			self::$listeners[$callee] = array();
		}
	   
		if( !isset(self::$listeners[$callee][$event])
			|| !is_array(self::$listeners[$callee][$event]) ) {

			self::$listeners[$callee][$event] = array();
		}
	   
		self::$listeners[$callee][$event][] = $callback;
   }
   
   public static function pass() {
   
   }
   
	/*
	 Method: fire
      This will fire the event on the objects called AND any classes associated
      with the event. The move up the class tree to the parents. This way
      you can add a global call to all classes or a single object and all are
      fired at the correct time.

      The following will print 'Hello World' when the \ns\foo\bar class is
      fired with the 'someEvent' event.

     (start code)
      Event::bind( '\ns\foo\bar', 'someEvent', function() {
     	print 'Hello world';
      });
     (end)
	 
      The following will print 'Hello World' when the \ns\foo\bar class is
      fired with the 'someEvent' event AND the $obj object is fired with the
      same event.
      
     (start code)
      Event::bind( '\ns\foo\bar', 'someEvent', function() {
     	print 'Hello world';
      });

      $obj = new \ns\foo\bar;
      Event::bind($obj, 'someEvent', function() {
     	print 'Called too!';
      });
     (end)
     
	 Access:
	  public
	 
	 Parameters:
	  callee - _mixed_ the object that emits the event
	  event - _string_ the event
	  kwargs - _array_ extra keyword arguments to send to the handler
	 
	 Returns:
	  _void_
	*/
   public static function fire($callee, $event, $kwargs=array(), 
		$propagate=true) {

		// works on both classes and objects, so we have to get the class name
		$object = false;
		if( is_object($callee) ) {
			$object = $callee;
			$callee = spl_object_hash($callee);
		}
	   
		if( isset(self::$listeners[$callee]) ) {
			$callbacks = self::$listeners[$callee][$event];
			if( $callbacks ) {
				// fire all the callbacks
				array_walk($callbacks, function($cb, $key, $kwargs) {
					$o = $kwargs['object'];
					$k = $kwargs['kwargs'];
					
					if( is_array($cb) ) {
						call_user_func($cb, $o, $k);
					} else {
						$cb($o, $k);
					}
				}, array(
					'object' => $object,
					'kwargs' => $kwargs
				));
/*				
				foreach( $callbacks as $cb ) {
					if( is_array($cb) ) {
						call_user_func($cb, $object, $kwargs);
					} else {
						$cb($object, $kwargs);
					}
				}
*/			}
		}
		
		if( !$propagate ) {
//			echo 'dont propagate '.$callee.':'.$event.'<br />';
			return;
		}

		// propagate event to parents
		if( is_object($object) && ($pc = get_parent_class($object)) ) {
//			echo 'propagate '.$callee.':'.$event.'<br />';
			self::fire(get_parent_class($object), $event, $kwargs);
		}
   }
   
}

?>