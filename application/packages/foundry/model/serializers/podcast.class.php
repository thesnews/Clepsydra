<?php
/*
 File: podcast.class.php
  Provides Podcast serializer class
  
 Version:
  2010.09.27
  
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
 Class: podcast
  Provides Podcast serialization for foundry\model instances
 
 Namespace:
  \foundry\model\serializers
*/
class podcast {

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
		require_once 'packages/ffmpeg/ffmpeg.class.php';

		
		$out = array(
			sprintf('<title>%s</title>', $data->getTitle()),
			sprintf('<link>%s</link>', $data->getURL()),
			sprintf('<guid>%s</guid>', $data->getURL()),
			sprintf('<pubDate>%s</pubDate>', date('r', $data->getModified())),
			sprintf('<description>%s</description>', 
				\foundry\filter::stripTagsStrict($data->getDescription())),
			sprintf('<itunes:summary>%s</itunes:summary>', 
				\foundry\filter::stripTagsStrict($data->getDescription())),
			sprintf('<itunes:duration>%s</itunes:duration>', 
				\ffmpeg\duration($data->pathOriginal))
		);

		$type = $data->fileType.'/'.$data->extension;
		$size = 0;
		if( file_exists($data->pathOriginal) ) {
			$size = filesize($data->pathOriginal);
		}
		
		$out[] = sprintf('<enclosure url="%s" type="%s" length="%d" />', 
				\foundry\request\url::urlFor($data->urlOriginal), $type, $size);
		

		$authors = array();
		if( is_array($data->getAuthor()) ) {
			foreach( $data->getAuthor() as $author ) {
				$authors[] = sprintf('<itunes:author>%s</itunes:author>',
					$author);
			}
			
			$out[] = implode("\n", $authors);
		}
		
		return "<item>\n".implode("\n", $out)."\n</item>";
	}

}
?>