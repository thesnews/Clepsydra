<?php
/*
 File: generic
  Provides \foundry\model\generic class
  
 Version:
  2010.06.07
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\model;
use foundry\utility as Util;
use foundry\event as Event;

/*
 Class: generic
  A generic model, useful for serializing arbitrary data.
 
 Namespace:
  \foundry\model\generic
*/
class generic extends \foundry\model {

	/*
	 Method: constructor
	 
	 Access:
	  public
	 
	 Parameters:
	  self - _string_ (optional) class mame
	  ns - _string_  (optional) force a namespace
	  handler - _object_ (optional) force a database handle
	 
	 Returns:
	  _object_
	*/
	public function __construct($self=false, $ns=false, $handler=false) {
		parent::__construct($self, $ns, $handler);
	}

	/*
	 Method: save
	  Simply returns false
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  FALSE
	*/
	public function save() {
		return false;
	}
}

?>