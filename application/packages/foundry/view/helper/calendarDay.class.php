<?php
/*
 Title: helper\calendarDay

 Group: Helpers
 
 File: calendarDay.class.php
  Provides calendarDay view helper class
  
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

/*
 Class: calendarDay
  Calendar helper provides a simple interface for drawing calendars.
  
  Note that all the 'get*()' method are overloaded, so 'calendar.getName()' will
  return the same as 'calendar.name'.
  
 Example:
 (begin code)
  {% helper calendar %}
  
  <table>
  {% for week in calendar.weeks %}
    <tr>
    {% for day in week.days %}
      <td{% if day.isCurrent() %} class="current"{% endif %}>{{ day.date }}</td>
    {% endfor %}
    </tr>
  {% endfor %}
  </table>
 (end)
 
 Namespace:
  \foundry\view\helper
*/
class calendarDay {

	private $time			= false;
	private $calendarTime = false;

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
	public function __construct($time = false) {
		$this->time = $time;
		
		if( $time === false ) {
			$this->time = time();
		}
		
		$this->time = strtotime('Today 00:00:00', $this->time);
		$this->calendarTime = $this->time;
	}
	
	/*
	 Method: setCalendarTime
	  Sets the time in relation to the parent calendar month/week.
	 
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
	  Determine if the this day is the current day based on parent calendar's
	  initial timestamp.
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _bool_
	*/
	public function isCurrent() {
		return (date('d', $this->calendarTime) == date('d', $this->time));
	}

	/*
	 Method: isCuurentMonth
	  Determine if this day is part of the currently selected month.
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _bool_
	*/
	public function isCurrentMonth() {
		return (date('m', $this->calendarTime) == date('m', $this->time));
	}

	/*
	 Method: getShortName
	  Return the day's short name, i.e. 'Mon'.
	  
	 Alias: _{{ day.shortName }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function getShortName() {
		return date('D', $this->time);
	}

	/*
	 Method: getName
	  Return the day's name, i.e. 'Monday'.
	  
	 Alias: _{{ day.name }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function getName() {
		return date( 'l', $this->time );
	}
	
	/*
	 Method: getDate
	  Return the day's padded date, i.e. '01'.
	  
	 Alias: _{{ day.date }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function getDate() {
		return date( 'd', $this->time );
	}
	
	/*
	 Method: getMonth
	  Return this day's parent month padded date, i.e. '03' or '12'.
	  
	 Alias: _{{ day.month }}_
	 
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
	  Return the day's 4 digit year.
	  
	 Alias: _{{ day.year }}_
	 
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
	  Return the day's timestamp.
	  
	 Alias: _{{ day.time }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _int_
	*/
	public function getTime() {
		return $this->time;	
	}
	
	/*
	 Method: getNext
	  Initialize and return the next day object.
	  
	 Alias: _{{ day.next }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _object_ instance of \foundry\view\helper\calendarDay
	*/
	public function getNext() {
		return new self($this->nextTime());
	}
	
	/*
	 Method: getPrevious
	  Initialize and return the previous day object.
	  
	 Alias: _{{ day.previous }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _object_ instance of \foundry\view\helper\calendarDay
	*/
	public function getPrevious() {
		return new self($this->previousTime());
	}

	/*
	 Method: getNextTime
	  Return the next day's timestamp.
	  
	 Alias: _{{ day.nextTime }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _int_
	*/
	public function getNextTime() {
		return strtotime('+1 day', $this->time);
	}
	
	/*
	 Method: getPreviousTime
	  Return the previous day's timestamp.
	  
	 Alias: _{{ day.previousTime }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _int_
	*/
	public function getPreviousTime() {
		return strtotime('-1 day', $this->time);
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