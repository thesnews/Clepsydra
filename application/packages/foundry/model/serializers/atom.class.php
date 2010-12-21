<?php
/*
 File: atom.class.php
  Provides ATOM serializer class
  
 Version:
  2010.09.15
  
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
 Class: atom
  Provides ATOM serialization for foundry\model instances
 
 Namespace:
  \foundry\model\serializers
*/
class atom {

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

		$string = implode("\n", $props);

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
//		$this->object->fetchAllAssociations();
		return $this->serialize($this->object);
	}
	
	/*
	 Method: serialize
	  Serialize the model into a ATOM Feed string
	 
	 Access:
	  public
	 
	 Parameters:
	  data - _mixed_
	 
	 Returns:
	  _string_
	*/
	public function serialize($data) {
		$out = array(
			sprintf('<title>%s</title>', $data->getTitle()),
			sprintf('<link href="%s" />', $data->getURL()),
			sprintf('<id>%s</id>', $data->getURL()),
			sprintf('<updated>%s</updated>', date('c', $data->getModified())),
			sprintf('<summary>%s</summary>', 
				\foundry\filter::stripTagsStrict($data->getDescription()))
		);

		$authors = array();
		if( is_array($data->getAuthor()) ) {
			foreach( $data->getAuthor() as $author ) {
				$authors[] = sprintf('<author><name>%s</name></author>',
					$author);
			}
			
			$out[] = implode("\n", $authors);
		}
		
		return "<entry>\n".implode("\n", $out)."\n</entry>";
	}

}
?>