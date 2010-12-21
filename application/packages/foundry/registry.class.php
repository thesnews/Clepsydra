<?php
/*
 File: registry.class.php
  Provides \foundry\registry class
  
 Version:
  2010.09.03
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\registry {
	use foundry\config as Conf;
	use foundry\db as DB;
	use foundry\exception\db as DBException;

	/*
	 Function: __init__
	  Autoload initializer
	 
	 Parameters:
	  _void_
	  
	 Returns:
	  _void_
	
	 Namespace:
	  \foundry\registry
	*/
	function __init__() {
		if( !($default = Conf::get('databases:default')) ) {
			throw new DBException('nullValue', 'No default database set');
		}
		
		if( !DB::get('default') ) {
			throw new DBException('nullValue', sprintf(
				'Database config for %s does not exist', $default ));
		}
		
		$handle = DB::get('default');
		// set the default database handle
		\foundry\registry::setDefaultHandler($handle);
		
		$q = 'describe foundry_registry';
		$stmt = $handle->prepare($q);
		$stmt->execute();
		
		if( !$stmt->fetch() ) {
			$q = 'CREATE TABLE foundry_registry (
				uid int(11) NOT NULL auto_increment,
				name varchar(255) default NULL,
				data text,
				modified int(11) default NULL,
				package varchar(255) default NULL,
				PRIMARY KEY (uid),
				UNIQUE KEY name (name)
			)';
			$handle->exec($q);
		}
	}

}

namespace foundry {

	/*
	 Class: registry
	  Site-wide object registry. The registry maintains state between and
	  across requests.
	  
	  Please note that while any package can read any registry value, all 
	  registry values are written in the context of the primary package and can
	  only be updated by that package.
	  
	  For example, any values created by Gryphon while in primary context can
	  be read by AdPilot, while in primary context, but not written to.
	 
	  Any attempt to write to the value of another package will silently fail.
	 
	 Example:
	 (start code)
	  use foundry\registry as Reg;
	  
	  Reg::set('foo:bar', 'some value');
	  echo Reg::get('foo:bar'); // some value
	 (end)
	 
	 Namespace:
	  foundry
	*/
	class registry {
	

		/*
		 Parameter: defaultHandle
		  The default database handle. This is set by the autoload initializer
		 
		 Access:
		  public
		*/
		public static $dbh = false;

		private static $stack = array();

		/*
		 Method: setDefaultHandler
		  Sets default database handle. This method is only called by the
		  autoload initializer
		 
		 Access:
		  public
		 
		 Parameters:
		  handler - _object_
		 
		 Returns:
		  _void_
		*/
		public static function setDefaultHandler($handler) {
			self::$dbh = $handler;
		}
		
		/*
		 Method: get
		  Get a registry value. Second optional parameter contains the last
		  modified timestamp of the value.
		 
		 Access:
		  public
		 
		 Parameters:
		  k - _string_ registry key
		  mod - _int_ (OPTIONAL) contains the last modified timestamp
		  force - _bool_ (OPTIONAL) force registry to reload the value from the
		  	database
		 
		 Returns:
		  _string_
		*/
		public static function get($k, &$mod = null, $force = false) {
			if( strpos($k, ':') === false ) {
				throw new \foundry\exception('badInput', 'Registry values '.
					'must be namespaced');
			}

			if( self::$stack[$k] && $force == false ) {
				return self::$stack[$k];
			}
			
			
			$q = 'select data, modified from foundry_registry '.
				'where name = :nm limit 1';
			$stmt = self::$dbh->prepare($q);
			$stmt->bindParam(':nm', $k);
			
			$stmt->execute(null, false, false);
			
			$row = $stmt->fetch();
			
			self::$stack[$k] = $row['data'];
			$mod = $row['modified'];
			
			return self::$stack[$k];
			
		}
	
		/*
		 Method: set
		  Set a registry value
		 
		 Access:
		  public
		 
		 Parameters:
		  k - _string_ registry key
		  v - _string_ value
		 
		 Returns:
		  _mixed_ value
		*/
		public static function set($k, $v) {
			if( strpos($k, ':') === false ) {
				throw new \foundry\exception('badInput', 'Registry values '.
					'must be namespaced');
			}
			
			if( self::get($k) ) {
				$q = 'update foundry_registry set data = :dta, modified = :mod'.
					', package = :pkg where name = :nm';
			} else {
				$q = 'insert into foundry_registry set data = :dta, '.
					'modified = :mod, name = :nm, package = :pkg';
			}
			
			$package = \foundry\proc::getPackage();
			
			$stmt = self::$dbh->prepare($q);
			$stmt->bindParam(':dta', $v);
			$stmt->bindParam(':nm', $k);
			$stmt->bindParam(':mod', time());
			$stmt->bindParam(':pkg', $package);
			$stmt->execute();
			
			return $v;
			
		}
		
		/*
		 Method: remove
		  Remove a registry value
		 
		 Access:
		  public
		 
		 Parameters:
		  k - _string_ registry key
		 
		 Returns:
		  _void_
		*/
		public static function remove($k) {
			if( strpos($k, ':') === false ) {
				throw new \foundry\exception('badInput', 'Registry values '.
					'must be namespaced');
			}
			
			$package = \foundry\proc::getPackage();

			$q = 'delete from foundry_registry where name = :nm and'
				.' package = :pkg';
			
			$stmt = self::$dbh->prepare($q);
			$stmt->bindParam(':nm', $k);
			$stmt->bindParam(':pkg', $package);
			$stmt->execute();
			
			return $v;
		}
		
	}


}


?>