<?php
/*
 File: collection
  Provides \foundry\model\collection class
  
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\model\collection {
	const SORT_REVERSE = true;
}

namespace foundry\model {

	use foundry\config as Conf;
	use foundry\model\inflector as Inflector;

	/*
	 Class: collection
	  A collection of model objects
	 
	 Namespace:
	  \foundry\model
	*/
	class collection implements \ArrayAccess, \IteratorAggregate{
	
		protected $itemStack = array();
	
		/*
		 Method: constructor
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _void_
		*/
		public function __construct() {
			
		}
	
		/*
		 Method: push
		  Push a foundry\model object onto the stack
		 
		 Access:
		  public
		 
		 Parameters:
		  obj - _object_ a foundry\model object
		 
		 Returns:
		  _void_
		*/
		public function push($obj) {
			$this->itemStack[] = $obj;
		}
		
		/*
		 Method: pop
		  Remove and return the last object in the stack
		 
		 Access:
		  public
		 
		 Parameters:
		  count - _int_ (optional) return a number of models
		 
		 Returns:
		  _mixed_ if *count* is omitted a single object is returned. If *count*
		  is set, a collection is returned
		*/
		public function pop($count=0) {
			
			if( !$count ) {
				return array_pop($this->itemStack);
			}
			
			if( $count >= count($this->itemStack) ) {
				return $this;
			}
			
			$out = new self;
			for( $i=0; $i<$count; $i++ ) {
				$out->unshift(array_pop($this->itemStack));
			}
			
			return $out;
		}
	
		/*
		 Method: peekBack
		  Return the last model on the stack, but don't remove it
		 
		 Access:
		  public
		 
		 Parameters:
		  count - _int_ (optional) return a number of models
		 
		 Returns:
		  _mixed_ if *count* is omitted a single object is returned. If *count*
		  is set, a collection is returned
		*/
		public function peekBack($count=0)
		{
			if( !$count ) {
				$i = count($this->itemStack);
				return $this->itemStack[($i-1)];
			}
			
			if( $count >= count($this->itemStack)) {
				return $this;
			}
			
			$out = new \foundry\model\collection;
			$len = count($this->itemStack);
			for( $i=0; $i<$count; $i++ ) {
				$out->unshift($this->itemStack[($count-($i-1))]);
			}
			
			return $out;
		}
		
		/*
		 Method: unshift
		  Push a foundry\model object into the front of the stack
		 
		 Access:
		  public
		 
		 Parameters:
		  obj - _object_ a foundry\model object
		 
		 Returns:
		  _void_
		*/
		public function unshift($obj)
		{
			array_unshift($this->itemStack, $obj);
		}
		
	
		/*
		 Method: shift
		  Remove and return the first item from the stack
		 
		 Access:
		  public
		 
		 Parameters:
		  count - _int_ (optional) return a number of objects
		 
		 Returns:
		  _mixed_ if *count* is omitted a single object is returned. If *count*
		  is set, a collection is returned
		*/
		public function shift($count=0)
		{
			if( $count == 0 ) {
				return array_shift($this->itemStack);
			}
	
			if( $count >= count($this->itemStack) ) {
				return $this;
			}
			
			$out = new self;
			for( $i=0; $i<$count; $i++ ) {
				$out->push(array_shift($this->itemStack));
			}
			
			return $out;
		}
	
		/*
		 Method: peekFront
		  Return, but don't remove, the first item on the stack
		 
		 Access:
		  public
		 
		 Parameters:
		  count - _int_ (optional) return a number of objects
		 
		 Returns:
		  _mixed_ if *count* is omitted a single object is returned. If *count*
		  is set, a collection is returned
		*/
		public function peekFront($count=0)
		{
			if( $count == 0 ) {
				return $this->itemStack[0];
			}
	
			if( $count >= count($this->itemStack) ) {
				return $this;
			}
			
			$out = new self;
			for( $i=0; $i<$count; $i++ ) {
				$out->push($this->itemStack[$i]);
			}
			
			return $out;
		}
	
		// Array and IteratorAggregate inplementation
		public function getIterator() {
			return new \ArrayObject($this->itemStack);
		}
		
		public function offsetGet($offset) {
			return $this->itemStack[$offset];
		}
		
		public function offsetExists($offset) {
			if( $this->itemStack[$offset] ) {
				return true;
			}
			
			return false;
		}

		public function offsetSet($offset, $val) {
			$this->itemStack[$offset] = $val;
		}
		
		public function offsetUnset($offset) {
			$this->itemStack[$offset] = false;
		}
	
		/*
		 Method: length
		  Return the number of objects in the stack. Can also be accessed
		  as a property
		 
		 Example:
		 > echo $collection->length(); // 3
		 > echo $collection->length; // 3
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _int_
		*/
		public function length() {
			return count($this->itemStack);
		}
		
		/*
		 Method: merge
		  Merges two collections together. If you pass a second argument
		  it attempts to sort it via that property (both models must have
		  that property).
		  
		 Example:
		  > $coll->merge($other, 'foo', \foundry\collection\SORT_REVERSE);
		 
		 Access:
		  public
		 
		 Parameters:
		  collection - _object_ the other collection
		  sort - _string_ (optional) property to sort by
		  sortFlag - _const_ (optional) set to SORT_REVERSE to sort... in
		  reverse
		 
		 Returns:
		  _object_ the merged collection
		*/
		public function merge( $collection, $sort = false, $sortFlag = false )
		{
			if( $sort == false ) {
				if( get_class($this->itemStack[0]) == 
					get_class($collection[0]) ) {

					// merged collections of the same class are filtered
					// for uniqueness
					$tmp = array();
					foreach( $this->itemStack as $i ) {
						$tmp[$i->uid] = $i;
					}
					foreach( $collection as $i ) {
						$tmp[$i->uid] = $i;
					}
					
					$this->itemStack = $tmp;
				} else {
					$this->itemStack = array_merge(
						$this->itemStack,
						$collection->toArray()
					);
				}
			} else {
				$temp = array();
				$all = array_merge(
					$this->itemStack,
					$collection->toArray()
				);
				
				foreach( $all as $obj ) {
					$temp[$obj->$sort] = $obj;
				}
				
				if( $sortFlag === true ) {
					krsort($temp);
				} else {
					ksort($temp);
				}
				
				$this->itemStack = array_values($temp);
			}
		}
		
		/*
		 Method: serialize
		  Serialize a object collection
		 
		 Access:
		  public
		 
		 Parameters:
		  fmt - _string_ the serialize format
		  recursive - _bool_ TRUE will serialize model associations
		 
		 Returns:
		  _String_
		*/
		public function serialize($fmt='json', $recursive=true)
		{
			$cls = sprintf('\\foundry\\model\\serializers\\%s', $fmt);
			
			return call_user_func(array($cls, 'serializeCollection'),
				$this->itemStack, $fmt, $recursive);
		}
		
		/*
		 Method: toArray
		  Return collection stack as array
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _array_
		*/
		public function toArray() {
			return $this->itemStack;
		}
		
		/*
		 Method: splat
		  Flattens the collection into a single property array
		  This is useful for returning elements for an auto complete search
		  system implemented via AJAX
		 
		 Example:
		 (start code)
		  $authors = _M( 'authors' )->find( ... );
		  return $authors->splat( 'name' ); 
		  // returns array( 'Author One', 'Author Two' );
		  
		  return $authors->splat('name', 'uid');
		  // array( '1234' => 'Author One', '5678' => 'Author Two');
		 (end)
		 
		 Access:
		  public
		 
		 Parameters:
		  property - _string_ property to extract
		  key - _string_ (optional) return array as k->v pair
		 
		 Returns:
		  _array_
		*/
		public function splat($property, $key=false)
		{
			$out = array();
			foreach( $this->itemStack as $item ) {
				if( $key ) {
					$out[$item->$key] = $item->$property;
				} else {
					$out[] = $item->$property;
				}
			}
			
			return $out;
		}
		
		/*
		 Method: grab
		  Grab all the models whose property matches the passed value
		  
		 Example:
		 (start code)
		  $authors = $allAuthors->grab('status', 1);
		  // grabs all the objects with a status of '1'
		 (end)
		 
		 Access:
		  public
		 
		 Parameters:
		  prop - _string_ property to checl
		  val - _mixed_ value to test against
		  greedy - _bool_ if TRUE, matched objects are removed from the stack
		 
		 Returns:
		  _object_ a collection of models
		*/
		public function grab($prop, $val, $greedy = false) {
			$out = new \foundry\model\collection;
			$c = count($this->itemStack);
			
			for( $i=0; $i<$c; $i++ ) {
				$item = array_shift($this->itemStack);
				$found = false;
				
				if( is_array($item->$prop) &&
					in_array($val, $item->$prop) ) {
					$out->push($item);
					$found = true;
				} elseif( $item->$prop == $val ) {
					$out->push($item);
					$found = true;
				}
				
				if( !$found || ($found && $greedy === false) ) {
					array_push($this->itemStack, $item);
				}
			}
			
			return $out;
		}
		
		public function __get($val) {
			switch( $val ) {
				case 'primaryKey':
					return $this->itemStack[0]->primaryKey;
					break;
				case 'myQuery':
					return $this->itemStack[0]->myQuery;
					break;
				case 'dbh':
					if( $this->itemStack[0]->dbh ) {
						return $this->itemStack[0]->dbh;
					} else {
						return \foundry\db::get('default');
					}
					break;
				case 'length':
				default:
					return $this->length();
					break;
			}
		}
	
		/*
		 Method: __call
		  Overloaded call supports the 'withAssnProperty' method.
		  Careful here, though. These can be pretty database intensive.
		  
		 Example:
		 (start code)
		  $collection = $otherCollection->withMediaFileName('foobar');
		 (end)
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _object_ collection of models
		*/
		public function __call($method, $args)
		{
			if( method_exists($this->itemStack[0], $method) ) {
				return call_user_func_array(array($this->itemStack[0], $method),
					$args);
			}
		
			// in the form of withTagName
			if( strpos($method, 'with') === false ) {
				return;
			}
			
			// convert withFooBar into with_foo_bar and explode it
			$parts = explode('_', Inflector::underscoreize($method));
			
			// we don't need the 'with'
			array_shift($parts);

			$assn = false;
			$property = false;
			$found = false;

			$assn = $parts[0];
			for( $i=1; $i<count($parts); $i++ ) {

				if( $this->itemStack[0]->hasAssociation($assn) ) {
					$found = $i;
					break;
				}
				$assn .= ucfirst($parts[$i]);
			}

			if( !$found ) {
				return false;
			}

			$key = $args[0];
			$greedy = $args[1];
			$namespace = $args[2];

			if( !$namespace ) {
				$namespace = Conf::get('namespace');
			}
			
			$cls = Inflector::classize($namespace, $assn);
			$tmp = new $cls($assn, $namespace);
			
			$property = implode('_', array_slice($parts, $found));

			// is a valid property
			if( !$tmp->isProperty($property) ) {
				return false;
			}

			// pull it all together
			$out = new self;
			$limit = count($this->itemStack);
			
			for( $i=0; $i<$limit; $i++ ) {
				$found = false;
				$item = $this->shift();
				
				// grabbing the assn, i.e $foo->tags
				$assns = $item->$assn;

				if( strpos(get_class($assns), 'collection') === false ) {
					if( $assns->$property == $key ) {
						$out->push($item);
						$found = true;
					}
				} elseif( !$assns->length ) {
					// no items, put it back
					$this->push($item);
					continue;
				}

				// does contain the item, add it to the stack
				if( strpos(get_class($assns), 'collection') !== false &&
					in_array($key, $assns->splat($property)) ) {
					$out->push($item);
				}
				
				if( ($found && !$greedy) || !$found ) {
					$this->push($item);
				}
			}
			
			return $out;
		}
	
	}
}

?>