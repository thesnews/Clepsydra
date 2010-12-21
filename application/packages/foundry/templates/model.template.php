<?php
/*
 Title: <# subns #>\<# name #>

 Group: <# group #>
 
 File: <# name #>
  Provides <# class #> class
  
 Version:
  <# version #>
  
 Copyright:
  2004-<# year #> The State News, Inc
  
 Author:
  <# Your name #> <<# you #>@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace <# namespace #>\model;
use foundry\event as Event;
use foundry\filter as Filter;
use foundry\request\url as URL;

/*
 Class: <# name #>
  <# desc #>
 
 Namespace:
  <# namespace #>
*/
class <# name #> extends \foundry\model {

	protected $hasOne = array(
//		This is an example of an association
/*		'<# association #>' => array(
			'namespace' => '<# namespace #>',
			'order' => '<# order #>',
			'limit' => '<# limit #>'
		)
*/
	);
	
	protected $schema = array(
		'uid',
//		The rest of your table schema goes here
	);

	public function __construct($self=false, $ns=false, $handler=false) {
		parent::__construct($self, $ns, $handler);

//		This is an event callback
//		Event::bind($this, 'beforeSave', array($this, 'preSave'));

	}


	public function preSave() {
//		This is the action called by the above event callback
	}

//	This is an example of an overloaded property
	public function __url() {
		return URL::linkTo(URL::build('some:path/foo', array(
			'var' => 'val'
		)), true);
	}
}

?>