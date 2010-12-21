<?php
/*
 File: queue
  Provides \foundry\queue class
  
 Version:
  2010.06.17
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\queue {
	use foundry\db as DB;
	use foundry\config as Conf;
	
	/*
	 Function: __init__
	  Autoload initializer
	 
	 Parameters:
	  _void_
	  
	 Returns:
	  _void_
	
	 Namespace:
	  \foundry\queue
	*/
	function __init__() {
		$handle = DB::create('queue', array(
			'driver'	=> 'sqlite',
			'host'		=> Conf::get('private-path'),
			'database'	=> 'queue'
		));
		
		\foundry\queue::setHandle($handle);
			
	}
}

namespace foundry {
	use foundry\sys as Sys;
	use foundry\fs\path as Path;
	use foundry\config as Conf;
	use foundry\proc as FProc;
	
	/*
	 Class: queue
	  PHP frontend to the job queue system. If the Node.js queue server isn't
	  installed or running, jobs are processed immediately (in a non-blocking
	  mannor).
	  
	  Please note that all jobs are unique (i.e. only a single instance of a
	  job with a particular set of parameters will run at a time).
	 
	 Namespace:
	  \foundry\queue
	*/
	class queue {
		
		private static $handle = false;
		
		/*
		 Method: setHandle
		  Sets the database handle, called by the autoload initializer
		 
		 Access:
		  public
		 
		 Parameters:
		  handle - _object_
		 
		 Returns:
		  _void_
		*/
		public static function setHandle($handle) {
			self::$handle = $handle;
			$q = 'create table queue (job_id varchar(255) unique,'
				.' name varchar(255), arguments text, status int default 0,'
				.' namespace varchar(255))';
			self::$handle->exec($q);

			$q = 'create table queue_finished (job_id varchar(255),'
				.' name varchar(255), arguments text, status int default 0,'
				.' namespace varchar(255), modified int)';
			self::$handle->exec($q);
		}
	
		/*
		 Method: enqueue
		  Add a job to the queue.
		 
		 Access:
		  public
		 
		 Parameters:
		  name - _string_ job name (class name)
		  args - _array_ (optional)
		 
		 Returns:
		  _string_ job id
		*/
		public static function enqueue($name, $args=false) {
			if( !$args ) {
				$args = array();
			}
			
			$args = json_encode($args);
			
			$id = self::generateID($name, $args);
			
			$q = 'insert into queue (job_id, name, arguments, namespace)'
				.' values (:id, :nm, :ag, :ns)';
			
			$stmt = self::$handle->prepare($q);
			if( $stmt->execute(array(
					':id' => $id,
					':nm' => $name,
					':ag' => $args,
					':ns' => FProc::getPackage()
				)) ) {

				self::pingWorker($id);
			}
			
			return $id;
		}
		
		public static function stat($id) {
		
		}
		
		public static function dequeue($id) {
		
		}
		
		public static function get($id) {
		
		}
		
		/*
		 Method: pingWorker
		  In cases where the Node.js-based queue server isn't installed, this
		  calls the job right away
		 
		 Access:
		  public
		 
		 Parameters:
		  id - _string_ job id
		 
		 Returns:
		  _void_
		*/
		public static function pingWorker($id) {
			
			$path = FProc::getBin();
			$path = sprintf('%s %s', $path, Path::join(FProc::getAppRoot(),
				sprintf('scripts/queue/worker.php %s %s %s',
				FProc::getPackage(), $id, Conf::get('private-path'))));
				
			Sys::execnb($path);
		
		}
		
		protected static function generateID($name, $args) {
			return md5($name.$args);
		}
	}
}
?>