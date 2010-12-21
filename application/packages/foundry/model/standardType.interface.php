<?php
/*
 File: standardType.interface.php
  Provides the model standard type interface
 
 Version:
  2010.07.01
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\model;

/*
 Interface: standardType
  Standard types allow aggregate methods and processes to work across a wide
  variety of model types and not have to worry about specific property names. 
 
 Namespace:
  \foundry\model
*/
interface standardType {

	/*
	 Method: getTitle
	  return a title
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function getTitle();
	
	/*
	 Method: getAuthor
	  Return an array of author names
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _array_ of _string_s
	*/
	public function getAuthor();
	
	/*
	 Method: getURL
	  Return a url
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function getURL();
	
	/*
	 Method: getDescription
	  Return a proper description
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function getDescription();
	
	/*
	 Method: getEditURL
	  Return direct gryphon edit url
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function getEditURL();
	
	/*
	 Method: getCreated
	  Return created timestamp
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _int_
	*/
	public function getCreated();
	
	/*
	 Method: getModified
	  Return modified timestamp
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _int_
	*/
	public function getModified();
	
	/*
	 Method: getTag
	  Return an array of Tag names
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _array_
	*/	
	public function getTag();
	
	// switch author to authors
	// media
	// parent (for blogs and images in a gallery)

}
?>