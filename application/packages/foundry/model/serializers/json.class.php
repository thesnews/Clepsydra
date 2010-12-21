<?php
/*
 File: json
  Provides \foundry\model\serializers\json class
  
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\model\serializers;
use foundry\event as Event;

/*
 Class: json
  Provides serializers for converting a model into a JSON string
 
 Namespace:
  \foundry\model\serializers\json
*/
class json {

	private $object = false;
	private $recursive = true;

	/*
	 Method: serializeCollection
	  Handles collection serialization
	 
	 Access:
	  public
	 
	 Parameters:
	  items - _array_ items to serialize
	  fmt - _string_ format
	  recursive - _bool_ recursive flag
	 
	 Returns:
	  _string_
	*/
	public static function serializeCollection($items, $fmt, $recursive) {
		$props = array();
		foreach( $items as $obj ) {
			$props[] = $obj->serialize($fmt, $recursive);
		}

		$string = '['.implode( ",\n", $props ).']';

		return $string;
	}

	/*
	 Method: constrictor
	 
	 Access:
	  public
	 
	 Parameters:
	  obj - _object_ model
	  recursive - _bool_ (optional)
	 
	 Returns:
	  _object_
	*/
	public function __construct($obj, $recursive=true) {
		$this->object = $obj;
		$this->recursive = $recursive;
	}
	
	/*
	 Method: toString
	  Converts model to string when printed
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function __toString() {
		$this->object->fetchAllAssociations();
		return $this->serialize($this->object->getProperties());
	}
	
	/*
	 Method: serialize
	  Serialize the model into a JSON string
	 
	 Access:
	  public
	 
	 Parameters:
	  data - _mixed_
	 
	 Returns:
	  _string_
	*/
	public function serialize($data) {
		$out = array();
		foreach( $data as $k => $v ) {
			$out[] = sprintf('"%s": %s', $k, $this->clean($v));
		}
		
		if( is_object($data) && $data instanceof \foundry\model\standardType ) {
			$str = '"%s": %s';
			// we use reflection so we don't have to hard code any changes
			// to the interface
			$rc = new \ReflectionClass('\\foundry\\model\\standardType');
			foreach( $rc->getMethods() as $m ) {
				$out[] = sprintf($str, (string) $m->name, $this->clean(
					call_user_func(array($data, (string) $m->name))));
			}
			
		}
		
		return '{'.implode(",\n", $out).'}';
	}
	
	/*
	 Method: clean
	  Clean and properly format the data before pushing it onto the string
	 
	 Access:
	  public
	 
	 Parameters:
	  data - _mixed_
	 
	 Returns:
	  _string_
	*/
	public function clean($data) {
		if( is_null($data) || $data === false ) {
			return 'false';
		} elseif( $data === true ) {
			return 'true';
		} elseif( is_numeric($data) ) {
			return $data;
		} elseif( is_string($data) ) {
			return '"'.rawurlencode($data).'"';
		} elseif( is_array($data) ) {
			return $this->serialize($data);
		} elseif( $this->recursive && is_object($data) ) {
			if( is_subclass_of($data, '\\foundry\\model') ) {
				Event::fire($data, 'beforeSerialize', array(
					'format' => 'json'
				), false);
			}
			return $this->serialize($data);
		}
		
		return 'false';
	}

}
?>