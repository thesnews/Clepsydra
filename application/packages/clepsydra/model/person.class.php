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

 Attributes:
 
  - uid - _int_
  - name - _string_
  - email - _string_
  - password - _string_
  - salt - _string_
  - pin - _int_ (UNUSED)
  - phone - _string_
  - status - _boolean_ 0 = clocked out, 1 = clocked in
  - active - _boolean_ 0 = past employee, 1 = current employee
  - is_admin - _boolean_
  - track - _boolean_ 0 = do not track time, 1 = track time
  - locked - _boolean_ locked account flag
  - attempts - _int_ invalid login attempts
  - url - _string_ READONLY person's infocard URL
  - in - _boolean_ READONLY 'TRUE' if clocked in
  - out - _boolean_ READONLY 'TRUE' if clocked out
  
 Associations:
 
  - card - 1:M
 
 Namespace:
  \clepsydra\model
*/
class person extends \foundry\model {

	protected $hasMany = array(
		'card' => array(
			'namespace' => 'clepsydra',
			'order' => 'self:in desc',
			'limit' => 100
		)
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

	public function __in() {
		return ($this->status == 1);
	}
	
	public function __out() {
		return ($this->status == 0);
	}

//	This is an example of an overloaded property
	public function __url() {
		return URL::linkTo(URL::build('some:path/foo', array(
			'var' => 'val'
		)), true);
	}
}

?>