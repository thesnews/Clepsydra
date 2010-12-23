<?php
/*
 Title: model\person

 Group: Models
 
 File: person.class.php
  Provides Person model class
  
 Version:
  2010.12.23
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace clepsydra\model;
use foundry\event as Event;
use foundry\filter as Filter;
use foundry\request\url as URL;

/*
 Class: person
  Person class
 
 Namespace:
  \clepsydra\model
*/
class person extends \foundry\model {

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
		'name',
		'email',
		'salt',
		'password',
		'pin',
		'phone',
		'active',
		'status',
		'is_admin',
		'track',
		'locked',
		'attempts'

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