<?php
/*
 File: zip.class.php
  Provides \foundry\fs\zip class
  
 Version:
  2010.08/09
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\fs;
use foundry\fs\path as P;

/*
 Class: zip
  Provides a simple wrapper for the builtin ZipArchive class.
  
 Example:
 (start code)
  $z = new \foundry\fs\zip('path/to/archive.zip');
  
  if( $z->fileExists('foo/bar.baz') ) {
  	$z->extractTo('./');
  }
  
  foreach( $z as $name => $file ) {
  	echo $name."\n\n";
  }
 (end)
 
 Namespace:
  \foundry\fs\zip
*/
class zip implements \ArrayAccess, \IteratorAggregate {

	private $handle = false;
	
	private $fileStack = array();
	
	/*
	 Method: constructor
	 
	 Access:
	  public
	 
	 Parameters:
	  path - _string_ path to zip
	 
	 Returns:
	  _object_
	*/
	public function __construct($path) {
		$this->handle = new \ZipArchive;
		$this->handle->open($path);

		$this->fileStack = array();
		for( $i=0; $i<$this->handle->numFiles; $i++ ) {
			$name = $this->handle->getNameIndex($i);
			$this->fileStack[$name] = $this->handle->getFromIndex($i);
		}
	}
	
	/*
	 Method: fileExists
	  Determine if file exists at given path
	 
	 Access:
	  public
	 
	 Parameters:
	  path - _string_
	 
	 Returns:
	  _bool_
	*/
	public function fileExists($path) {
		return isset($this->fileStack[$path]);
	}
	
	/*
	 Method: getFile
	  Get contents of file with given name.
	 
	 Access:
	  public
	 
	 Parameters:
	  name - _string_
	 
	 Returns:
	  _mixed_ contents of file
	*/
	public function getFile($name) {
		return $this->fileStack[$name];
	}
	
	public function save() {
	
	}
	
	/*
	 Method: extractTo
	  Extract the archive to given location
	 
	 Access:
	  public
	 
	 Parameters:
	  path - _string_
	 
	 Returns:
	  _void_
	*/
	public function extractTo($path) {
		$this->handle->extractTo($path);
	}
	
	// Iterator implementation
	public function offsetSet($offset, $val) {
		return $this->fileStack[$offset] = $val;
	}
	
	public function offsetGet($offset) {
		return $this->fileStack[$offset];
	}
	
	public function offsetExists($offset) {
		if( isset($this->fileStack[$offset]) ) {
			return true;
		}
	}
	
	public function offsetUnset($offset) {
		$this->fileStack[$offset] = false;
		$this->handle->deleteFromName($offset);
	}
	
	public function getIterator() {
		return new \ArrayObject($this->fileStack);
	}
}

?>