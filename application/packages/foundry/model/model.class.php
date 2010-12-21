<?php
/*
 File: model
  Provides \foundry\model class
  
 Version:
  2010.06.14
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\model {
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
	  \foundry\model
	*/
	function __init__() {
		if( !($default = Conf::get('databases:default')) ) {
			throw new DBException('nullValue', 'No default database set');
		}
		
		if( !DB::get('default') ) {
			throw new DBException('nullValue', sprintf(
				'Database config for %s does not exist', $default ));
		}
		
		// set the default database handle
		\foundry\model::setDefaultHandler(DB::get('default'));
	}
	
}

namespace foundry {
	
	define('FOUNDRY_SEARCH_OR', 10001);
	require_once 'foundry/model/standardType.interface.php';
	// basically $model->where->order->limit->find();
	// model events DO NOT PROPAGATE
	
	use foundry\utility as Util;
	use foundry\config as Conf;
	use foundry\event as Event;
	use foundry\model\inflector as Inflector;
	use foundry\model\collection as Collection;
	
	use foundry\exception\model as ModelException;
	
	/*
	 Class: model
	  The model base class from which all other models inherit from. Models
	  use a fluid syntax for generating queries.
	  
	  Models implement ArrayAccess and IteratorAggregate so you can easily
	  enumerate the properties.
	  
	  (begin code)
	   $model = M::init('foo')
	            ->where('bar = 1 and baz like :bz')
	            ->order('created desc')
	            ->limit(10)
	            ->bind(array( ':bz' => '%Hello%'))
	            ->find();
	  (end)
	 
	 Namespace:
	  \foundry
	*/
	class model implements \ArrayAccess, \IteratorAggregate {

		/*
		 Parameter: defaultHandle
		  The default database handle. This is set by the autoload initializer
		 
		 Access:
		  public
		*/
		public static $defaultHandle = false;
		
		/*
		 Parameter: disableCache
		  Disables cache for every model for this session
		  
		 Access:
		  public
		*/
		public static $disableCache = false;
		
		/*
		 Parameter: dbh
		  The database handle for this model
		 
		 Access:
		  public
		*/
		public $dbh = false;
		
		/*
		 Parameter: namespace
		  This model's namespace (package name)
		 
		 Access:
		  public
		*/
		public $namespace = false;

		/*
		 Parameter: modelString
		  The the model name
		 
		 Example:
		  > echo M::init('article')->modelString; // article;
		 
		 Access:
		  public
		*/
		public $modelString = false;

		/*
		 Parameter: tableString
		  The table name of the model
		  
		 Example:
		  > echo M::init('article')->tableString; // namespace_articles;
		 
		 Access:
		  public
		*/
		public $tableString = false;

		/*
		 Parameter: primaryKey
		  The table's primary key column
		 
		 Access:
		  public
		*/
		public $primaryKey = 'uid';
		
		/*
		 Parameter: defaultSearchProperty
		  The default column name to search by when using the 'byXXX' key
		  in the template 'fetch' tag.
		 
		 Access:
		  public
		  
		 See Also:
		  Template documentation
		*/
		public $defaultSearchProperty = false;
		
		/*
		 Parameter: binds
		  An array of bound values
		 
		 Access:
		  protected
		*/
		protected $binds = array();
		
		/*
		 Parameter: cache
		  Cache flag
		 
		 Access:
		  protected
		*/
		protected $cache = null;
		
		protected $forceCache = false;
		
		/*
		 Parameter: hasOne
		  The one to one association array
		 
		 Access:
		  protected
		*/
		protected $hasOne = array();

		/*
		 Parameter: hasMan
		  The one to many association array
		 
		 Access:
		  protected
		*/
		protected $hasMany = array();

		/*
		 Parameter: hasAndBelongsToMany
		  The many to many association array
		 
		 Access:
		  protected
		*/
		protected $hasAndBelongsToMany = array();
		
		/*
		 Parameter: propertyStack
		  The fetched properties for this model
		 
		 Access:
		  protected
		*/
		protected $propertyStack = array();
		
		/*
		 Parameter: callbacks
		  A list of valid event callbacks
		 
		 Access:
		  protected
		*/
		protected $callbacks = array(
			'beforeSave'		=> false,
			'afterSave'			=> false,
			'beforeFind'		=> false,
			'afterFind'			=> false,
			'beforeDelete'		=> false,
			'afterDelete'		=> false,
			'beforeExport'		=> false,
			'afterExport'		=> false			
		);
		
		/*
		 Parameter: schema
		  An array of this model's valid properties
		 
		 Access:
		  protected
		*/
		protected $schema	= array();
		
		/*
		 Parameter: query
		  The un-parsed query stack. It is suggested you don't modify this
		  directly.
		 
		 Access:
		  protected
		*/
		protected $query 	= array(
			'columns'			=> false,
			'where'				=> array(),
			'order'				=> array(),
			'limit'				=> false,
			'join'				=> false,
			'having'			=> false,
			'group'				=> false
		);
		
		/*
		 Parameter: myQuery
		  Holds the un-parsed query stack that generated this model
		  
		 Access:
		  public
		*/
		public $myQuery = false;
		
		/*
		 Parameter: defaults
		  Default limit, order and filter properties
		 
		 Access:
		  protected
		*/
		protected $defaults = array(
			'limit'				=> 100,
			'order'				=> 'self:uid desc',
			'where'				=> false
		);
		
		/*
		 Parameter: myNS
		  This model's namespace (package name)
		 
		 Access:
		  public
		*/
		public $myNS 		= false;
		
		/*
		 Parameter: delayedInsert
		  Stack of associations to inject after model is saved (for new,
		  unsaved models only)
		  
		 Access:
		  protected
		*/
		protected $delayedInsert = array();
		
		/*
		 Method: setDefaultHandler
		  Set the default database handler for all subsequent models. This is
		  called and setup by the autoload initializer. You should only need to
		  call this method if you've directly included the model library via
		  a 'require' or 'include' call.
		 
		 Access:
		  public
		 
		 Parameters:
		  handler - _object_ a foundry\pdo instance
		 
		 Returns:
		  _void_
		*/
		public static function setDefaultHandler($handler) {
			self::$defaultHandle = $handler;
		}

		/*
		 Method: init
		  Convenience method to initialize an empty model.
		  
		 Example:
		 (start code)
		  use foundry\model as M;
		  
		  M::init('tag');
		  M::init('ns\\tag');
		 (end)
		 
		 Access:
		  public
		 
		 Parameters:
		  model - _string_ model name
		  ns - _string_ (optional) force a model namespace
		  defaultHandler - _object_ (optional) force a different default
		  database handler for this model
		 
		 Returns:
		  _object_ \foundry\model instance
		*/
		public static function init($model, $ns=false, $defaultHandler=false) {
			if( !$ns && strpos($model, ':') ) {
				$t = explode(':', $model);
				$ns = $t[0];
				$model = $t[1];
			}
			if( !$ns ) {
				$ns = Conf::get('namespace');
			}
			$cls = sprintf('%s\\model\\%s', $ns, $model);
			
			try {
				return new $cls($model, $ns, $defaultHandler);
			} catch( \foundry\exception $e ) {
				return false;
			}
		}

		/*
		 Method: constructor
		 
		 Access:
		  public
		 
		 Parameters:
		  model - _string_ model name
		  ns - _string_ (optional) force a model namespace
		  defaultHandler - _object_ (optional) force a different default
		  database handler for this model
		 
		 Returns:
		  _object_ \foundry\model instance
		*/
		public function __construct($model=false, $ns=false,
			$defaultHandler=false) {
			
			// handles ns\model
			if( !$ns && strpos($model, ':') ) {
				$t = explode(':', $model);
				$ns = $t[0];
				$model = $t[1];
			}
			if( !$ns ) {
				$ns = Util::rootNamespace(get_class($this));
			}
			
			if( !$model ) {
				$model = Util::classNamespace(get_class($this));
			}
			
			$this->myNS = $ns;
			$this->modelString = $model;
			
			if( !$defaultHandler ) {
				$this->dbh = self::$defaultHandle;
			} else {
				$this->dbh = $defaultHandler;
			}
			
			if( \foundry\model::$disableCache ) {
				$this->cache(false);
			} else {
				$this->cache(true);
			}

			Event::bind($this, 'afterSave', array($this,
				'delayedInsertHandler'));

		}
		
		/*
		 Method: isProperty
		  Determine if a string is a valid property name
		 
		 Access:
		  public
		 
		 Parameters:
		  prp - _string_ property name
		 
		 Returns:
		  _bool_
		*/
		public function isProperty($prp) {
			return in_array($prp, $this->schema);
		}
		
		/*
		 Method: setDefault
		  Set a default value for limit, order or where
		 
		 Access:
		  public
		 
		 Parameters:
		  k - _string_ can be 'limit', 'order' or 'where'
		  v - _string_ the value
		 
		 Returns:
		  _void_
		*/
		public function setDefault($k, $v) {
			$this->defaults[$k] = $v;
		}
		
		/*
		 Method: getProperties
		  Return an array of this model's properties
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _array_
		*/
		public function getProperties() {
			return $this->propertyStack;
		}
		
		/*
		 Method: cache
		  Get or set the cache flag (TRUE == cache, FALSE == no cache)
		 
		 Access:
		  public
		 
		 Parameters:
		  bool - _bool_ (optional)
		 
		 Returns:
		  _bool_
		*/
		public function cache($bool) {
			if( \foundry\model::$disableCache === true ) {
				$this->cache = false;
				return $this;
			}
			
			$this->cache = $bool;

			return $this;
		}

		/*
		 Method: forceCache
		  Get or set the forceCache flag (TRUE == cache, FALSE == no cache)
		  Setting forceCache to TRUE will cause the query data be be cached
		  even if the table is part of the 'skip' config setting
		 
		 Access:
		  public
		 
		 Parameters:
		  bool - _bool_ (optional)
		 
		 Returns:
		  _bool_
		*/
		public function forceCache($bool) {
			$this->forceCache = $bool;

			return $this;
		}
		
		/*
		 Method: hasAssociation
		  Determine if this model has requested association
		 
		 Access:
		  public
		 
		 Parameters:
		  assn - _string_ an association to check for
		 
		 Returns:
		  _bool_
		*/
		public function hasAssociation($assn) {
			if( array_key_exists($assn, $this->hasMany) ) {
				return true;
			} elseif( array_key_exists($assn, $this->hasOne) ) {
				return true;
			} elseif( array_key_exists($assn, $this->hasAndBelongsToMany) ) {
				return true;
			}
			
			return false;
		}
		
		/*
		 Method: serialize
		  Serialize model into a different text format
		 
		 Access:
		  public
		 
		 Parameters:
		  fmt - _string_ a valid serialize format (JSON, YAML, XML, etc)
		  recursive - _bool_ (optional) recursively serialize associated models
		 
		 Returns:
		  _object_ serializer object
		*/
		public function serialize($fmt, $recursive=true) {

			Event::fire($this, 'beforeSerialize', array(
				'model'	=> $this->modelString,
				'format' => $fmt
			), false);

			$cls = sprintf('\\foundry\\model\\serializers\\%s', $fmt);
			$obj = new $cls($this, $recursive);

			Event::fire($this, 'afterSerialize', array(
				'model'	=> $this->modelString,
				'format' => $fmt,
				'serializer' => $obj
			), false);
			
			return $obj;
		}
		
		/*
		 Method: fetchAllAssociations
		  Fetch all of this model's assocated models. Usually this is called
		  before serialization
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _void_
		*/
		public function fetchAllAssociations() {
			$cb = function($str) {
				return Inflector::pluralize($str);
			};
			
			// we want M:N and M:1 assns to be plural, hence the callback
			$assns = array_merge(array_keys($this->hasOne),
				array_map($cb, array_keys($this->hasMany)),
				array_map($cb, array_keys($this->hasAndBelongsToMany)));
			foreach( $assns as $assn ) {
				$this->__get($assn);
			}
		}
		
		/*
		 Method: save
		  Save this model's current state
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _void_
		*/
		public function save() {
			Event::fire($this, 'beforeSave', array(
				'model'	=> $this->modelString
			), false);
		
			$this->tableString = $this->getTableString();

			$pk = $this->primaryKey;
			$uid = $this->__get($pk);
			$q = '';

			$cols = array();
			$i = 0;
			foreach( $this->schema as $col ) {
				if( $col == $this->primaryKey ) {
					continue;
				}
				$cols[] = sprintf('%s = :c%d', $col, $i);
				$i++;
			}
			
			$q = implode(', ', $cols);
			
			if( !$uid ) {
				$q = sprintf('insert into %s set %s', $this->tableString, $q);
			} else {
				$q = sprintf('update %s set %s where %s = %d limit 1',
					$this->tableString, $q, $pk, $uid);
			}

			$stmt = $this->dbh->prepare($q);
			$i=0;
			foreach( $this->schema as $col ) {
				if( $col == $this->primaryKey ) {
					continue;
				}
				$stmt->bindParam(':c'.$i, $this->__get($col));
				$i++;
			}
			
			$stmt->execute();
			
			if( !$uid ) {
				$this->__set($this->primaryKey, $this->dbh->lastInsertId());
			}

			Event::fire($this, 'afterSave', array(
				'model'	=> $this->modelString
			), false);
			
			return $this;
		}
		
		/*
		 Method: delete
		  Remove this model and all references to it.
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _void_
		*/
		public function delete() {
			if( !$this->__get($this->primaryKey) ) {
				return false;
			}

			Event::fire($this, 'beforeDelete', array(
				'model'	=> $this->modelString
			), false);

			$this->tableString = $this->getTableString();
			
			$q = sprintf('delete from %s where %s = %d limit 1',
				$this->tableString, $this->primaryKey, 
				$this->__get($this->primaryKey));
			$this->dbh->exec($q);
			
			Event::fire($this, 'afterDelete', array(
				'model'	=> $this->modelString
			), false);

			return $this;
		}
		
		/*
		 Method: compileQuery
		  Perform the actual query compilation
		 
		 Access:
		  public
		 
		 Parameters:
		  query - _array_ query stack
		  properties - _string_ properties to select against
		 
		 Returns:
		  _string_
		*/
		public function compileQuery($query, $properties=false) {
			$this->tableString = $this->getTableString();

			if( $properties ) {
				$query['columns'] = $properties;
			} else {
				$query['columns'] = $this->modelString.':'.
					implode(sprintf(', %s:', $this->modelString),
						$this->schema);
			}
			
			foreach( $this->defaults as $k => $v ) {
				// default val is not FALSE and query val is FALSE
				if( $v !== false && ($query[$k] === false ||
									 empty($query[$k])) ) {
					$this->$k($v);
				}
			}
			
			$where = false;
			$order = false;
			$limit = false;
			
			if( !empty($query['where']) ) {
				$where = $query['where'];
			} elseif( $this->defaults['where'] ) {
				$where = array($this->defaults['where']);
			}
			
			if( $query['limit'] ) {
				$limit = $query['limit'];
			} elseif( $query['limit'] !== 0 && $this->defaults['limit'] ) {
				$limit = $this->defaults['limit'];
			}
			
			if( !empty($query['order']) ) {
				$order = $query['order'];
			} elseif( $this->defaults['order'] ) {
				$order = array($this->defaults['order']);
			}
			
			$fullQuery = 'select '.
				$query['columns'].' from '.$this->tableString;
			if( !empty($query['join']) ) {
				$j = $query['join'];
				
				$fullQuery .= ' left join '.$j[0].' on '.$j[1];
			}
			if( $where ) {
				$fullQuery .= ' where ('.implode(') and (', $where).')';
			}
			if( $query['group'] ) {
				$fullQuery .= ' group by '.$query['group'];
			}
			if( $query['having'] ) {
				$fullQuery .= ' having '.$query['having'];
			}
			if( $order ) {
				$fullQuery .= ' order by '.implode(' and ', $order);
			}
			if( $limit ) {
				$fullQuery .= ' limit '.$limit;
			}
			
			return $fullQuery;
		}

		/*
		 Method: find
		  Find model data based on the information in the query stack. This is
		  the last method called on a query stack.
		 
		 Example:
		  (start code)
		   $m = M::init('article')
		        ->where('foo = 1')
		        ->limit(1)
		        ->find()
		  (end)
		 
		 Access:
		  public
		 
		 Parameters:
		  properties - _array_ (optional) list of properties to fetch. If left
		  blank, all properties are fetched.
		 
		 Returns:
		  _object_ a new collection of models
		*/
		public function find($properties=false, $querySub=false) {
			
			$this->tableString = $this->getTableString();

			Event::fire($this, 'beforeFind', array(
				'model'	=> $this->modelString
			), false);
			
			$fullQuery = $this->compileQuery($this->query, $properties);

			$useCache = true;
			if( $this->cache === false ) {
				$useCache = false;
			}
			
			$data = $this->executor($fullQuery, $this->binds, $useCache);
			
			$col = new collection;
			foreach( $data as $row ) {
				$col->push( $this->objectify($row) );
			}

			foreach($col as $c) {
				if( !$querySub) {
					$c->myQuery = $this->query;
				} else {
					$c->myQuery = $querySub;
				}
				
				Event::fire($c, 'afterFind', array(
					'model' => $this->modelString
				), false);
			}

			// reset for the next one
			$this->query = array(
				'columns'			=> false,
				'where'				=> array(),
				'order'				=> array(),
				'limit'				=> false,
				'join'				=> false,
				'having'			=> false,
				'group'				=> false
			);
			
			return $col;
			
		}
		
		/*
		 Method: where
		  Add a 'where' clause to the query stack
		 
		 Access:
		  public
		 
		 Parameters:
		  str - _string_ a where clause
		 
		 Returns:
		  _object_ this model
		*/
 		public function where($str) {
			$this->query['where'][] = $str;
			
			return $this;
		}
		
		/*
		 Method: order
		  Add an 'order' clause to the query stack
		 
		 Access:
		  public
		 
		 Parameters:
		  str - _string_ an order clause
		 
		 Returns:
		  _object_ this model
		*/
		public function order($str) {
			$this->query['order'][] = $str;
			return $this;
		}
		
		/*
		 Method: limit
		  Add a 'limit' clause to the query stack
		 
		 Access:
		  public
		 
		 Parameters:
		  str - _string_ (or _int_) a limit clause or integer
		 
		 Returns:
		  _object_ this model
		*/
		public function limit($str) {
			$this->query['limit'] = $str;
			return $this;
		}
		
		/*
		 Method: join
		  Add a 'join' clause to the query stack
		 
		 Access:
		  public
		 
		 Parameters:
		  table - _string_ the NAMESPACED table to join
		  on - _string_ the join clause
		 
		 Returns:
		  _object_ this model
		*/
		public function join($table, $on) {
			$this->query['join'] = array($table, $on);

			return $this;
		}
		
		/*
		 Method: having
		  Add a 'having' clause to the query stack
		 
		 Access:
		  public
		 
		 Parameters:
		  str - _string_ having clause
		 
		 Returns:
		  _object_ this model
		*/
		public function having($str) {
			$this->query['having'] = $str;
			return $this;
		}
		
		/*
		 Method: group
		  Add a 'group by' clause to the query stack
		 
		 Access:
		  public
		 
		 Parameters:
		  str - _string_ group clause
		 
		 Returns:
		  _object_ this model
		*/
		public function group($str) {
			$this->query['group'] = $str;
			return $this;
		}
		
		/*
		 Method: bind
		  Bind a variable to a query placeholder
		 
		 Example:
		 (start code)
		  $m = M::init('article')->where('headline = :hed')
		       ->bind(array(':hed' => 'Hello World'))
		       ->find();
		 (end)
		 
		 Access:
		  public
		 
		 Parameters:
		  binds - _array_ an array of bound variables
		 
		 Returns:
		  _object_ this model
		*/
		public function bind($binds) {
			if( is_array($this->binds) ) {
				$this->binds = array_merge($this->binds, $binds);
			} else {
				$this->binds = $binds;
			}
			
			return $this;
		}
		
		/*
		 Method: executor
		  Parse and execute the query
		 
		 Access:
		  public
		 
		 Parameters:
		  sql - _string_ an un-prased query
		  binds - _array_ (optional) bound variables
		  useCache - _bool_ (optional) force a cache flag
		 
		 Returns:
		  _array_ a nested array of result rows
		*/
		public function executor($sql, $binds=false, $useCache=true) {
		
			Event::fire($this, 'beforeExecute', array(
				'model'	=> $this->modelString,
				'query' => $sql,
				'binds' => $binds
			), false);

			$sql = $this->convertTableRef($sql);
			$stmt = $this->dbh->prepare($sql);

			if( !$stmt ) {
				throw new ModelException('query', 'Error in query could not '.
					'create statement '.$sql);
			}
			
			if( is_array($binds) && !empty($binds) ) {
				foreach( $binds as $key => $val ) {
					$stmt->bindParam($key, $binds[$key]);
				}
			}
			
			if( !$stmt->execute(null, $useCache, $this->forceCache) ) {
				throw new ModelException('query', 'Query could not execute '.
					$sql, print_r($binds, true));
			}

			Event::fire($this, 'afterExecute', array(
				'model'	=> $this->modelString,
				'query'	=> $sql,
				'binds'	=> $binds,
				'statement' => $stmt
			), false);

			return $stmt->fetchAll();

		}
		
		/*
		 Method: objectify
		  Turn an array of result rows into a proper model object
		 
		 Access:
		  public
		 
		 Parameters:
		  data - _array_ result row data
		 
		 Returns:
		  _object_ a foundry\model instance
		*/
		public function objectify($data) {
			$cls = sprintf('\\%s\\model\\%s', $this->myNS, $this->modelString);

			$obj = new $cls($this->modelString, $this->myNS);

			Event::fire($obj, 'beforeObjectify', array(
				'model'	=> $this->modelString,
				'class' => $cls,
				'data' => $data
			), false);

			foreach( $data as $k => $val ) {
				if( is_numeric($k) ) {
					continue;
				}
				$obj->$k = $val;
			}

			Event::fire($obj, 'afterObjectify', array(
				'model'	=> $this->modelString
			), false);
			
			return $obj;
			
		}
		
		/*
		 Method: convertTableRef
		  converts foo:bar to ns_foo.bar

		 Access:
		  public
		 
		 Parameters:
		  str - _string_ un-parsed query
		 
		 Returns:
		  _string_
		*/
		public function convertTableRef($str) {
			$map = array(
				$this->modelString.':'	=> $this->tableString.'.',
				'self:'					=> $this->tableString.'.'
			);
			
			return str_replace(array_keys($map), $map, $str);
			
		}
		
		/*
		 Method: getTableString
		  Generates the string name for this table. Can be overridden for
		  custom table names.
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _string_
		*/
		public function getTableString() {
			$this->tableString = Inflector::tableize($this->myNS,
				$this->modelString);
				
			return $this->tableString;
		}
		
		/*
		 Method: findBy
		  Perform a findBy association query
		 
		 Example:
		 (start code)
		  $tags = M::init('tag')...
		  $articles = M::init('article')
		              ->where('status = 1')
		              ->limit(10)
		              ->findByTags($tags);
		 (end)
		 
		 Access:
		  public
		 
		 Parameters:
		  what - _string_ the association
		  args - _array_ the assocated objects
		 
		 Returns:
		  _object_ a model collection
		*/
		public function findBy($what, $args) {
			if( $this->isProperty($what) ) {
				
				$k = Util::normalize($what);
				
				$where = array();
				$binds = array();
				foreach( $args as $k => $v) {
					$where[] = sprintf('self:%s = :id%s', $what, $k);
					$binds[':id'.$k] = $v;
				}
				
				$item = $this->where(implode(' or ', $where))->
					bind($binds)->
					find();
				
				if( $what == $this->primaryKey ) {
					return $item->pop();
				}
				
				return $item;
				
			} elseif( array_key_exists($what, $this->hasOne) ) {
				// find by one
				$fk = $what.'_id';

				// there could be a number of args
				$ids = array();
				
				foreach( $args as $arg ) {
					if( get_class($arg) == 'foundry\model\collection' ) {
						$ids = array_merge($ids, $arg->splat('uid'));
					} else {
						$ids[] = $arg->__get($arg->primaryKey);
					}
				}
				
				$ids = array_unique($ids);
				
				$ms = $this->modelString;
				
				return $this->where(sprintf('%s:%s=', $ms, $fk ).implode(
					sprintf(' or %s:%s=', $ms, $fk), $ids))->
					find();

			} elseif( array_key_exists(Inflector::singularize($what),
				$this->hasMany) ) {
				// find by many
				
				// return this model as a foreign key - i.e. foo_id
				$fk = Inflector::foreignKeyize($this->myNS, $this->modelString);
				
				// args should be an array of collections
				// so we glom them all together by their primary keys
				$ids = array();
				foreach( $args as $arg ) {
					$ids = array_merge($ids, $arg->splat($fk));
				}

				$ids = array_unique($ids);
				$ms = $this->modelString;
				$pk = $this->primaryKey;
				
				return $this->where(sprintf('%s:%s=', $ms, $pk ).implode(
					sprintf(' or %s:%s=', $ms, $pk), $ids))->
					find();

			} elseif( array_key_exists(Inflector::singularize($what),
				$this->hasAndBelongsToMany) ) {
				
				$this->tableString = $this->getTableString();

				$single = Inflector::singularize($what);
				$assn = $this->hasAndBelongsToMany[$single];
				
				$cls = sprintf('\\%s\\model\\%s', $assn['namespace'],
					$single);
				$item = new $cls($single, $assn['namespace']);
				
				$thisFK = Inflector::foreignKeyize($this->myNS,
					$this->modelString);

				$pk = $item->primaryKey;
				
				$ids = array();
				$searchOr = false;
				foreach( $args as $arg ) {
					if( $arg == FOUNDRY_SEARCH_OR ) {
						$searchOr = true;
						continue;
					}
					$ids = array_merge($ids, $arg->splat($pk));
				}
				$ids = array_unique($ids);
				
				$thisFK = Inflector::foreignKeyize($this->myNS,
					$this->modelString);
				$assnFK = Inflector::foreignKeyize($assn['namespace'], $single);
				
				$lookupTable = Inflector::lookupTableize($cls,
					get_class($this));
				
				// pull the ids from the lookup table
//				$q = sprintf('select %s from %s where %s = ', $thisFK,
//					$lookupTable, $assnFK).implode( ' or '.$assnFK.' = ',
//					$ids);
				
				$this->join($lookupTable,
					sprintf('%s.%s', $this->tableString, $this->primaryKey).
					' = '.
					sprintf('%s.%s', $lookupTable, $thisFK)
				);
				
				$this->where(sprintf('%s.%s =', $lookupTable, $assnFK).
					implode(sprintf(' or %s.%s =', $lookupTable,
					$assnFK),$ids));
				
				if( !$searchOr ) {
					$this->having('total = '.count($ids));
				}
				$this->group('self:'.$pk);
				
				/*
				 it's _considerably_ faster to just pull the UIDs from this
				 search then pull the data with just the UIDs than it is
				 to pull all the data at once.
				 
				 Basically we're saying:
				  select table.uid, count(table.uid) as total
				    from table
				    left join lookup on lookup.model_id = table.uid
				    where (lookup.other_id = N or lookup.other_id = M...)
				    group by table.uid
				    having total = [number of items in where clause]
				    
				  The 'having' clause insures that all items found have all
				  of the requested associations.
				*/
				
				$qTemp = $this->query;
				$qTemp['find'] = sprintf('count(self:%s) as total',
					$pk, $pk);
				
				$data = $this->find(sprintf('self:%s, count(self:%s) as total',
					$pk, $pk));
				
				if( !$data->length ) {
					return new Collection;
				}
				
				$ids = $data->splat('uid');
				
				$this->query['order'] = $qTemp['order'];
//				$this->query['limit'] = $qTemp['limit'];
				
				return $this->where($this->primaryKey.' = '.implode(
					' or '.$this->primaryKey.' = ', $ids))->find(false, $qTemp);
			}
		}
		
		/*
		 Method: __call
		  Overloaded call property. Allows for 'findByAssocation()' methods
		 
		 Access:
		  public
		 
		 Parameters:
		  func - _string_ the called method
		  args - _array_ the arguments passed
		 
		 Returns:
		  _mized_
		*/
		public function __call($func, $args) {
			if( strpos($func, 'findBy') !== false ) {
				$str = strtolower(substr($func, 6));
				return $this->findBy($str, $args);
			}
		}
		
		/*
		 Method: getProperty
		  Formalized property getter
		 
		 Access:
		  public
		 
		 Parameters:
		  k - _string_ property name
		 
		 Returns:
		  _mixed_
		*/
		public function getProperty($k) {
			return $this->__get($k);
		}
		

		/*
		 Method: delayedInsertHandler
		  Event callback that sets model associations on new models after
		  the initial save. This method is called automatically after save. You
		  shouldn't ever need to call this manually.
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _void_
		*/
		public function delayedInsertHandler() {
			foreach( $this->delayedInsert as $k => $v ) {
				$this->__set($k, $v);
			}
			$this->delayedInsert = array();
		}
		

		/*
		 Overloaded getter and setters
		*/
		public function __get($k) {
			if( isset($this->propertyStack[$k]) ) {
				return $this->propertyStack[$k];
			}
			if( method_exists($this, sprintf('__%s', $k)) ) {
				return call_user_func(array($this, sprintf('__%s', $k)));
			} elseif( array_key_exists($k, $this->hasOne) ) {
				// hasOne - i.e. foo->bar->uid
				$cls = sprintf('\\%s\\model\\%s',
					$this->hasOne[$k]['namespace'], $k);

				if( $this->propertyStack[$k] ) {
					return $this->propertyStack[$k];
				}
				
				$item = new $cls($k, $this->hasOne[$k]['namespace']);
				
				if( !$this->__get($k.'_id') ) {
					return $item;
				}
				
				$col = $item->where(sprintf('%s=:pri', $item->primaryKey))->
					bind(array(':pri' => $this->__get($k.'_id')))->
					find()->
					pop();
				
				$this->propertyStack[$k] = $col;
				
				return $col;
			}
			
			$kSingular = Inflector::singularize($k);
			
			if( array_key_exists($kSingular, $this->hasMany) ) {
				// hasMany - i.e. foo->bars->length
				
				// key is plulal, so we need to singularize it
				$single = $kSingular;
				if( $this->propertyStack[$k] ) {
					return $this->propertyStack[$k];
				}

				$assn = $this->hasMany[$single];
				
				$cls = sprintf('\\%s\\model\\%s', $assn['namespace'], $single);
				
				$item = new $cls($single, $assn['namespace']);
				
				// return this model as a foreign key - i.e. foo_id
				$fk = Inflector::foreignKeyize($this->myNS, $this->modelString);
				
				if( $assn['order'] ) {
					$item->order($assn['order']);
				}
				if( $assn['limit'] ) {
					$item->limit($assn['limit']);
				}
				if( $assn['where'] ) {
					$item->where($assn['where']);
				}
				
				$ret = $item->where(sprintf('self:%s = :id', $fk))->
					bind(array(':id' => $this->__get($this->primaryKey)))->
					find();
				
				$this->propertyStack[$k] = $ret;
				
				return $ret;
				
			} elseif( array_key_exists($kSingular,
				$this->hasAndBelongsToMany) ) {
				
				// Yes, this could be done in a single join query. After a 
				// few years of dealing with those unwieldy bastards, I've just
				// about given up. The two query method is quite a bit faster
				// against a large dataset anyway.
				
				$single = $kSingular;
				if( isset($this->propertyStack[$k]) ) {
					return $this->propertyStack[$k];
				}

				$assn = $this->hasAndBelongsToMany[$single];
				
				$cls = sprintf('\\%s\\model\\%s', $assn['namespace'], $single);
				
				$item = new $cls($single, $assn['namespace']);
				
				$thisFK = Inflector::foreignKeyize($this->myNS,
					$this->modelString);
				$assnFK = Inflector::foreignKeyize($assn['namespace'], $single);
				
				$lookupTable = Inflector::lookupTableize($cls,
					sprintf('\\%s\\model\\%s', $this->myNS,
					$this->modelString));
				
				// pull the ids from the lookup table
				$q = sprintf('select %s from %s where %s = :aid', $assnFK,
					$lookupTable, $thisFK);
				$stmt = $this->dbh->prepare($q);
				$stmt->bindParam(':aid', $this->__get($this->primaryKey));
				$stmt->execute(null, $this->cache);
				
/*				$data = $stmt->fetchAll();
				
				if( !count($data) ) {
					$this->propertyStack[$k] = false;
					return new Collection;
				}
				
				$ids = array();
				foreach( $data as $row ) {
					$ids[] = $row[0];
				}
*/
				$ids = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
				if( !count($ids) ) {
					$this->propertyStack[$k] = false;
					return new Collection;
				}
				
				$pk = $item->primaryKey;

				if( $assn['order'] ) {
					$item->order($assn['order']);
				}
				if( $assn['limit'] ) {
					$item->limit($assn['limit']);
				}
				if( $assn['where'] ) {
					$item->where($assn['where']);
				}
			
				$ret = $item->where($pk.' = '.implode(' or '.$pk.' = ', $ids))->
					find();
				
				$this->propertyStack[$k] = $ret;
				
				return $ret;

			}
		
			return $this->propertyStack[$k];
		}
		
		public function __set($k, $v) {
			
			if( in_array($k, $this->schema) ) {
				$this->propertyStack[$k] = $v;
				return $this;
			}
			
			// hasOne
			if( array_key_exists($k, $this->hasOne) && is_object($v) ) {
				$this->propertyStack[$k.'_id'] = $v->uid;
				
				return $this;
			}
			
			$kSingular = Inflector::singularize($k);
			
			if( array_key_exists($kSingular, $this->hasMany) 
					  && is_object($v) ) {

				if( !$this->uid ) {
					// set a delayed insert
					$this->delayedInsert[$k] = $v;
					return $this;
				}

				// key is plulal, so we need to singularize it
				// this is essentially a reassignment
				$single = $kSingular;
				$assn = $this->hasMany[$single];
				
				$cls = sprintf('\\%s\\model\\%s', $assn['namespace'], $single);
				$item = new $cls($single, $assn['namespace']);
				
				$pk = $item->primaryKey;
				$fk = Inflector::foreignKeyize($this->myNS, $this->modelString);
				
				$ids = $v->splat($pk);
				$table = Inflector::tableize($assn['namespace'], $single);
				
				$q = sprintf('update %s set %s=%d where %s=', $table, $fk,
					$this->__get($this->primaryKey), $pk).implode(
					' or '.$pk.'=', $ids);
					
				$this->dbh->exec($q);
				
				return $this;
			} elseif( array_key_exists($kSingular, $this->hasAndBelongsToMany)
					  && is_object($v) ) {

				if( !$this->uid ) {
					// set a delayed insert
					$this->delayedInsert[$k] = $v;
					return $this;
				}

				$single = $kSingular;
				$assn = $this->hasAndBelongsToMany[$single];
				
				$cls = sprintf('\\%s\\model\\%s', $assn['namespace'],
					$single);
				
				$item = new $cls($single, $assn['namespace']);
				
				$thisFK = Inflector::foreignKeyize($this->myNS,
					$this->modelString);
				$assnFK = Inflector::foreignKeyize($assn['namespace'],
					$single);
				
				$lookupTable = Inflector::lookupTableize($cls,
					sprintf('\\%s\\model\\%s', $this->myNS,
					$this->modelString));
					
				// reset association
				$q = sprintf('delete from %s where %s = %d', $lookupTable,
					$thisFK, $this->__get($this->primaryKey));
				$this->dbh->exec($q);

				// set new association
				$thisID = $this->__get($this->primaryKey);
				$ids = $v->splat($item->primaryKey);
				
				// insert into lookup (thisFK, assnFK) values (id,id),...
				$q = sprintf('insert into %s (%s, %s) values ('.$thisID.', '.
					implode('),('.$thisID.', ', $ids).')', $lookupTable,
					$thisFK, $assnFK);
				$this->dbh->exec($q);
				
				$this->propertyStack[$k] = null;
				
				return $this;

			}
			$this->propertyStack[$k] = $v;
		}
		
		// ArrayAccess implementation methods, allows for array like access,
		// i.e. $foo->bar is the same as $foo['bar']
		
		public function offsetSet($offset, $val) {
			return $this->__set($offset, $val);
		}
		
		public function offsetGet($offset) {
			return $this->__get($offset);
		}
		
		public function offsetExists($offset) {
			if( isset($this->propertyStack[$offset]) ) {
				return true;
			}
			
			if( method_exists($this, $offset) ) {
				return true;
			}
			
			if( method_exists($this, '__'.$offset) ) {
				return true;
			}
			
			if( $this->hasAssociation($offset) ||
				$this->hasAssociation(Inflector::singularize($offset)) ) {
				return true;
			}
//			return isset($this->propertyStack[$offset]);
//			return true;
		}
		
		public function offsetUnset($offset) {
			return $this->__set($offset, '');
		}
		
		// Iterator implementation method, allows for loop iteration over
		// properties
		public function getIterator() {
			return new \ArrayObject($this->propertyStack);
		}
	}

}
?>