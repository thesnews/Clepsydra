<?php
/*
 File: yaml
  Provides \foundry\model\serializers\yaml class
  
 Version:
  2010.06.08
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\model\serializers;
use foundry\event as Event;

require_once 'vendor/spyc.php';

/*
 Class: yaml
  Provides methods for converting a model to YAML
 
 Namespace:
  \foundry\model\serializers\yaml
*/
class yaml {
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

		$string = \Spyc::YAMLDump($props);

		return $string;
	}

	/*
	 Method: constructor
	 
	 Access:
	  public
	 
	 Parameters:
	  object - _object_ model
	  recursive - _bool_ (optional)
	 
	 Returns:
	  _object_
	*/
	public function __construct($object, $recursive=true) {
		$this->object = $object;
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

		return \Spyc::YAMLDump($this->serialize($this->object->getProperties()));
	}
	
	/*
	 Method: serialize
	  Serialize the model into a YAML string
	 
	 Access:
	  public
	 
	 Parameters:
	  data - _mixed_
	 
	 Returns:
	  _string_
	*/
	public function serialize($data) {
		$out = array();

		foreach( $data as $k=>$v ) {
			if( is_string($v) ) {
				$v = urlencode($v);
			} elseif( $this->recursive && is_object($v) ) {
				if( is_subclass_of($v, '\\foundry\\model') ) {
					Event::fire($v, 'beforeSerialize', array(
						'format' => 'yaml'
					), false);
				}
				$v = $this->serialize($v);
			}
			
			$out[$k] = $v;
		}

		return $out;
	}
}
?>