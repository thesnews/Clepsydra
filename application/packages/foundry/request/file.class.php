<?php
/*
 File: file
  Provides \foundry\request\file class
  
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\request;
use foundry\fs\file as F;

/*
 Class: file
  Provides and standardized interface for dealing with uploaded files
 
 Example:
 (start code)
  $files = \foundry\request::file();
  
  foreach( $files as $file ) {
  	if( $file->isError() ) : continue;
  	
  	... sanity checks ...
  	
  	$file->move('/path/to/new/file.ext');
  }
 (end)
 
 Namespace:
  \foundry\request
*/
class file {

	private $key = false;
	private $data = array();
	
	/*
	 Method: load
	  Load the _FILES array into the \foundry\request\file object
	  
	 Examples:
	 > \foundry\request\file::load($_FILES);
	 
	 Access:
	  public
	 
	 Parameters:
	  arr - _array_ the _FILES global array
	 
	 Returns:
	  _array_ standardized file info array
	*/
	public static function load($arr) {
		$out = array();
		
		if( !is_array($arr) ) {
			return array();
		}
		
		$top = current($arr);
		if( is_array($top['name']) ) {
			// _FILES[inputname[][name][inputname]
			$temp = array();
			foreach( $top as $k => $v ) {
				$key = $k;
				foreach( $v as $id => $value ) {
					if( !is_array($temp[$id]) ) {
						$temp[$id] = array();
					}
					
					$temp[$id][$key] = $value;
				}
			}
			$_FILES = $temp;
		}
		
		foreach( $_FILES as $id => $data ) {
			$out[] = new self($id, $data);
		}
		
		return $out;
	}
	
	/*
	 Method: constructor
	 
	 Access:
	  public
	 
	 Parameters:
	  k - _string_ file input name
	  d - _array_ file upload information
	 
	 Returns:
	  _object_
	*/
	public function __construct($k, $d) {
		$this->key = $k;
		$this->data = $d;
	}
	
	/*
	 Method: move
	  Move uploaded file to new destination
	 
	 Access:
	  public
	 
	 Parameters:
	  to - _string_ new destination
	 
	 Returns:
	  _bool_
	*/
	public function move($to) {
		if( $this->data['type'] == 'directory' ) {
			return rename($this->data['tmp_name'], $to);
		}
		
		// deals with imported files, or files that aren't uploaded
		if( !move_uploaded_file($this->data['tmp_name'], $to) ) {
			return rename($this->data['tmp_name'], $to);
		}
	}
	
	/*
	 Method: getName
	  Determine name of uploaded file
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function getName() {
		return F::rootName($this->data['name']);
	}
	
	/*
	 Method: getExtension
	  Determine uploaded file's extension
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function getExtension() {
		return F::extension($this->data['name']);
	}
	
	/*
	 Method: getType
	  Determine uploaded file's MIME type
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function getType() {
		if( $this->data['type'] == 'directory' ) {
			return 'directory';
		}
		return F::type($this->data['tmp_name']);
	}
	
	/*
	 Method: getSize
	  Determine uploaded file's size (in bytes)
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _int_
	*/
	public function getSize() {
		return $this->data['size'];
	}
	
	/*
	 Method: getTempPath
	  Determine uploaded file's temporary file name
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function getTempPath() {
		return $this->data['tmp_name'];
	}
	
	/*
	 Method: isError
	  Check for upload error
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_ or FALSE if no error
	*/
	public function isError() {
		return $this->data['error'];
	}
	
}
?>