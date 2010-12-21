<?php
/*
 File: pdo
  Provides \foundry\db\pdo and \foundry\db\pdo\statement classes
  
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

	/*
	 Class: pdo
	  Foundry-specific extension of the PDO system
	 
	 Namespace:
	  \foundry\db
	*/
	class pdo extends \PDO {

		/*
		 Parameter: counter
		  Tracks number of queries executed
		 
		 Access:
		  public
		*/
		public static $counter = 0;

		/*
		 Method: constructor
		 
		 Access:
		  public
		 
		 Parameters:
		  dsn - _string_
		  un - _string_ username
		  pass - _string_ database password
		  opts - _mixed_ (optional) database conenction options
		 
		 Returns:
		  _void_
		*/
		public function __construct($dsn, $un, $pass, $opts=array()) {
			parent::__construct($dsn, $un, $pass, $opts);
			
			$this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array(
				'\\foundry\\db\pdo\\statement', array($this)
			));
		}
			
		/*
		 Method: prepare
		  Prepare database statement
		 
		 Access:
		  public
		 
		 Parameters:
		  q - _string_ query
		 
		 Returns:
		  _object_ prepared statement
		*/
		public function prepare($q) {
			return parent::prepare($q);
		}
		
	}
}

namespace foundry\db\pdo {
	use foundry\cache as Cache;
	use foundry\config as Conf;
	
	/*
	 Class: statement
	  Foundry-specific PDO statement extension
	 
	 Namespace:
	  \foundry\db\pdo
	*/
	class statement extends \PDOStatement {

		/*
		 Parameter: dbh
		  database handle
		 
		 Access:
		  public
		*/
		public $dbh = false;
		
		private $binds = array();
		private $data = false;
		private $iterator = -1;
		private $doCacheOnFetch = false;
			
		/*
		 Method: constructor
		  statement constructor MUST be protected
		 
		 Access:
		  protected
		 
		 Parameters:
		  dbh - _object_ database handle
		 
		 Returns:
		  _void_
		*/
		protected function __construct($dbh) {
			$this->dbh = $dbh;
		}
		
		/*
		 Method: execute
		  Execute a prepared statement
		 
		 Access:
		  public
		 
		 Parameters:
		  params - _mixed_ bound parameters
		  useCache - _bool_
		  forceCache - _bool_ if TRUE select data will be cached even if a 
		   select table is part of the 'skip' list
		 
		 Returns:
		  _mixed_ result resource
		*/
		public function execute($params=null, $useCache=true, 
			$forceCache=false) {
			$q = $this->queryString;

			$key = md5($q.serialize($this->binds));
			
			// check to see if data is cached
			if( $useCache && Cache::isEnabled() &&
				($data = Cache::retrieve($key)) ) {
				
				$this->data = unserialize($data);
				return true;
			}
			
			// check to see if this is a select
			$isSelect = false;
			if( substr($q, 0, 6) == 'select' ) {
				$isSelect = true;
			}

			$doCache = false;
			if( $useCache && $isSelect && Cache::isEnabled() ) {
				// are we supposed to skip this table?
				$doCache = true;
				if( !$forceCache ) {
					foreach( Conf::get('cache:skip') as $tbl ) {
						if( strpos($q, $tbl) !== false ) {
							$doCache = false;
							break;
						}
					}
				}
			}

			if( $doCache ) {
				$status = parent::execute($params);
				\foundry\db\pdo::$counter++;
				
//				$this->data = $this->fetchAll();
				
//				if( $status && count($this->data) ) {
//					Cache::store($key, serialize($this->data));
				if( $status ) {
					$this->doCacheOnFetch = $key;
				}
				
				return $status;
			}

			\foundry\db\pdo::$counter++;
			return parent::execute($params);
		}

		/*
		 Method: fetchAll
		  fetch all results as a nested array
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _array_
		*/
		public function fetchAll() {

			if( $this->data ) {
				return $this->data;
			}
			
			$data = forward_static_call_array(array('parent', 'fetchAll'),
				func_get_args());
				
			if( $this->doCacheOnFetch !== false ) {
				Cache::store($this->doCacheOnFetch, serialize($data));
				$this->doCacheOnFetch = false;
				
				$this->data = $data;
			}
			
			return $data;
			
//			return parent::fetchAll();
		}
		
		/*
		 Method: fetch
		  Fetch a single row result
		 
		 Access:
		  public
		 
		 Parameters:
		  flags - _mixed_ (optional) result flags
		 
		 Returns:
		  _array_
		*/
		public function fetch($flags=false) {
			if( $this->doCacheOnFetch !== false ) {
				$this->fetchAll($flags);
			}
		
			if( $this->data ) {
				$this->iterator++;
				return $this->data[$this->iterator];
			}
			
			return parent::fetch($flags);
		}
	
		/*
		 Method: bindParam
		  Bind a parameter to a placeholder in the query string
		 
		 Access:
		  public
		 
		 Parameters:
		  col - _string_ name of the bound string
		  param - _string_ bound value
		  type - _string_ (optional) force type of bound param
		 
		 Returns:
		  _void_
		*/
		public function bindParam($col, $param, $type = null) {
			$this->binds[$col] = $param;
			
			parent::bindParam($col, $param, $type);
		}
		
	}

}

?>