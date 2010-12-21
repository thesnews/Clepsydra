<?php
/*
 File: db
  Provides \foundry\db class
  
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\db {
	function __init__() {
	}
}

namespace foundry {
	use foundry\config as Conf;
	use foundry\db\pdo as DBPDO;
	use foundry\exception\db as DBException;
	
	/*
	 Class: db
	  Base database handle class
	 
	 Namespace:
	  \foundry
	*/
	class db {
		
		/*
		 Parameter: handles
		  An array of registered database handles
		 
		 Access:
		  public
		*/
		public static $handles = array();

		/*
		 Method: init
		  Initializes registered database configs
		 
		 Access:
		  public
		 
		 Parameters:
		  configs - _array_ an array of database configs (see the config file)
		 
		 Returns:
		  _void_
		*/
		public static function init($configs) {
		
			// init configs
			foreach( $configs as $k => $config ) {
				self::create($k, $config);
			}
		
		}
		
		/*
		 Method: create
		  Create a new, non-registered, database handle
		 
		 Example:
		  (begin code)
		  	use foundry\db as DB;
		  	...
			$handle = DB::create('someDatabase', array(
				'driver'	=> 'sqlite',
				'host'		=> '/path/to/db,
				'database'	=> 'dbName'
			));
		  (end)

		 Access:
		  public
		 
		 Parameters:
		  id - _string_ a unique identifer for this handle
		  config - _array_ database config info
		 
		 Returns:
		  _object_ the new database handle
		*/
		public static function create($id, $config) {
			if( $config['driver'] == 'sqlite' ) {
				self::$handles[$id] = new DBPDO(sprintf(
					'sqlite:%s/%s.sqlite', $config['host'],
					$config['database']
				), null, null);
			} elseif( $config['driver'] == 'mysql' ) {
				self::$handles[$id] = new DBPDO(sprintf(
					'mysql:host=%s;dbname=%s', $config['host'],
					$config['database']
				), $config['user'], $config['password']);
			} else {
				throw new DBException( 'nullValue', sprintf(
					'%s is not supported', $config['driver']));
			}
		
			return self::$handles[$id];
		}
		
		/*
		 Method: get
		  Retrieve a defined database handle
		 
		 Access:
		  public
		 
		 Parameters:
		  config - _string_ a config identifier
		 
		 Returns:
		  _mixed_ Returns database handle or FALSE on error
		*/
		public static function get($config) {
			if( isset(self::$handles[$config]) ) {
				return self::$handles[$config];
			}
			
			return false;
		}
	}
}

?>