<?php
/*
 File: cache
 
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php

*/

namespace foundry\config\cache {
	use foundry\fs\path as Path;
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
      \foundry\config\cache
    */
	function __init__() {
		$handle = DB::create('configCache', array(
			'driver'	=> 'sqlite',
			'host'		=> Conf::get('private-path'),
			'database'	=> 'config'
		));
		
		\foundry\config\cache::setHandle($handle);
	}
}

namespace foundry\config {
	use foundry\config as Conf;

    /*
     Class: cache
      Interface for the config cache, managed entirely by Foundry
     
     Namespace:
      \foundry\config
    */
	class cache {
	
		private static $handle = false;
	
		/*
		 Method: setHandle
		  set the shared database handle
		 
		 Access:
		  public
		  
		 Parameters:
		  _object_ a foundry\db\pdo database handle object
		 
		 Returns:
		  _void_
		*/
		public static function setHandle($handle) {
			self::$handle = $handle;
			// sqlite won't create a table twice, so we do this on each request
			$q = 'create table cache (created int, expires int, config text)';
			self::$handle->exec($q);
		}
	
		/*
		 Method: load
		  Load the cached settings from the SQLite database
		 
		 Access:
		  public
		  
		 Parameters:
		  _void_
		 
		 Returns:
		  _bool_ - Returns FALSE on failure
		*/
		public static function load() {
			if( Conf::get('debug') ) {
				return false;
			}
		
			$q = 'select created, expires, config, rowid from cache order by '.
				'rowid desc';
			$stmt = self::$handle->prepare($q);
			$stmt->execute();
			
			$data = $stmt->fetch();
			if( !$data['rowid'] ) {
				return false;
			}
			
			if( time() > $data['expires'] ) {
				$q = 'delete from cache where rowid = :rid';
				$stmt = self::$handle->prepare($q);
				$stmt->execute(array(':rid' => $data['rowid']));
			}

			Conf::load(unserialize($data['config']));
			
			return true;
		}
		
		/*
		 Method: store
		  Store current config settings
		 
		 Access:
		  public
		  
		 Parameters:
		  _void_
		 
		 Returns:
		  _void_
		*/
		public static function store() {
			if( Conf::get('debug') ) {
				return false;
			}
		
			$cfg = serialize(Conf::export());
			$time = time();
			$expires = strtotime('+15 minutes', $time);

			$q = 'insert into cache (created, expires, config) values ('.
				':crt, :exp, :cfg)';
			$stmt = self::$handle->prepare($q);
			$stmt->execute(array(
				':crt' => $time,
				':exp' => $expires,
				':cfg' => $cfg
			));
		}
	
	}
}
?>