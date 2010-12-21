<?php
/*
 Title: helper\calendarWeek

 Group: Helpers
 
 File: calendarWeek.class.php
  Provides calendarWeek display helper class
  
 Version:
  2010.11.08
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\view\helper;
use foundry\view\helper\calendarDay as Day;

/*
 Class: calendarWeek
  CalendarWeek helper provides a simple interface for drawing calendars.
 
  Note that all the 'get*()' method are overloaded, so 'week.getTime()' will
  return the same as 'week.time'.
  
  Example:
  (begin code)
   {% helper calendar %}
   
   <table>
   {% for week in calendar.weeks %}
	 <tr{% if week.isCurrent() %} class="current"{% endif %}>
	 {% for day in week.days %}
	   <td>{{ day.date }}</td>
	 {% endfor %}
	 </tr>
   {% endfor %}
   </table>
  (end)

 Namespace:
  \foundry\view\helper
   
*/
class calendarWeek {

	private $time = false;
	private $calendarTime = false;
	private $dayStack = array();
	
	/*
	 Method: __construct
	  Constructor
	 
	 Access:
	  public
	 
	 Parameters:
	  time - _int_ (OPTIONAL)
	 
	 Returns:
	  _object_
	*/
	public function __construct($time = false)
	{
		$this->time = $time;
		
		if( $time === false ) {
			$this->time = time();
		}
		
		$now = date('w', $this->time);
		
		$this->time = strtotime(sprintf('-%d days 00:00:00', $now),
			$this->time);
			
		$this->calendarTime = $this->time;
	}
	
	/*
	 Method: setCalendarTime
	  Set the time in relation to the parent calendar month
	 
	 Access:
	  public
	 
	 Parameters:
	  t - _int_
	 
	 Returns:
	  _void_
	*/
	public function setCalendarTime($t) {
		$this->calendarTime = $t;
	}

	/*
	 Method: isCurrent
	  Determine if this week is the current week, based on parent month's time.
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _bool_
	*/
	public function isCurrent() {
		return (date('W', $this->calendarTime)-1 == date('W', $this->time));
	}
	
	/*
	 Method: getMonth
	  Return the current month as padded int, i.e. '01'.
	  
	 Alias: _{{ week.month }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function getMonth() {
		return date('m', $this->time);
	}
	
	/*
	 Method: getYear
	  Return the current, 4 digit, year.
	  
	 Alias: _{{ week.year }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _int_
	*/
	public function getYear() {
		return date('Y', $this->time);
	}

	/*
	 Method: getTime
	  Return the current timestamp.
	  
	 Alias: _{{ week.time }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _signed int_
	*/
	public function getTime() {
		return $this->time;	
	}
	
	/*
	 Method: getNext
	  Initializes and returns the next week object.
	  
	 Alias: _{{ week.next }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _object_ - instance of \foundry\view\helper\calendarWeek
	*/
	public function getNext() {
		return new self($this->nextTime());
	}
	
	/*
	 Method: getPrevious
	  Initialize and returnn the previous week object.
	  
	 Alias: _{{ week.previous }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _object_ - instance of \foundry\view\helper\calendarWeek
	*/
	public function getPrevious() {
		return new self($this->previousTime());
	}

	/*
	 Method: getNextTime
	  Return the next week's timestamp.
	  
	 Alias: _{{ week.nextTime }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _signed int_
	*/
	public function getNextTime() {
		return strtotime('+1 week', $this->time);
	}
	
	/*
	 Method: getPreviousTime
	  Return the previous week's timestamp.
	  
	 Alias: _{{ week.previousTime }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _signed int_
	*/
	public function getPreviousTime() {
		return strtotime('-1 week', $this->time);
	}

	/*
	 Method: getDays
	  Initialize and return an array of this week's day objects.
	  
	 Alias: _{{ week.days }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _array_ containing instances of \foundry\view\helper\calendarDay
	*/
	public function getDays() {
		if( !empty($this->dayStack) ) {
			return $this->dayStack;
		}
		
		$this->dayStack = array();

		$start = $this->time;
		for( $i=0; $i<7; $i++ ) {
			$day = new Day($start);
			$day->setCalendarTime($this->calendarTime);
			
			$this->dayStack[] = $day;
			
			$start = strtotime('+1 day', $start);
		}
		
		return $this->dayStack;
	}

	public function __toString() {
		return $this->getTime();
	}


	public function __get($k) {
		$method = sprintf('get%s', ucfirst($k));
		if( method_exists($this, $method) ) {
			return $this->$method();
		}
		
		return false;
	}
}

?>