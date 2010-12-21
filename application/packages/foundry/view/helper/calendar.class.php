<?php
/*
 Title: helper\calendar

 Group: Helpers
 
 File: calendar.class.php
  Provides calendar view helper class
  
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
use foundry\view\helper\calendarWeek as Week;

/*
 Class: calendar
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
      <td>{{ day.date }}</td>
    {% endfor %}
    </tr>
  {% endfor %}
  </table>
 (end)
 
 Namespace:
  \foundry\view\helper
*/
class calendar {

	private $time = false;
	
	private $firstDay = false;
	
	private $lastDay = false;

	private $dayStack = array();
	private $weekStack = array();

	public function __construct() {
	}
	
	/*
	 Method: setTime
	  Set the calendar time, otherwise the calendar defaults to today.
	 
	 Access:
	  public
	 
	 Parameters:
	  time - _signed int_ a unix timestamp
	 
	 Returns:
	  _void_
	*/
	public function setTime($time = false) {
		$this->time = $time;
		if( !$time ) {
			$this->time = time();
		}
		
		$this->firstDay = strtotime(date('m/1/Y', $this->time ).' 00:00:00');
		$this->lastDay = strtotime(date('m/t/Y', $this->time ).' 23:59:00');
	
	}

	/*
	 Method: getNow
	  Returns the current calendar timestamp.
	  
	 Alias: _{{ calendar.now }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _signed int_
	*/
	public function getNow() {
		return $this->time;
	}
	
	/*
	 Method: getFirstDay
	  Returns the timestamp for the first day of the month.
	  
	 Alias: _{{ calendar.firstDay }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _signed int_
	*/
	public function getFirstDay() {
		return $this->firstDay;
	}
	
	/*
	 Method: getLastDay
	  Returns the timestamp for the last day of the month.
	  
	 Alias: _{{ calendar.lastDay }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _signed int_
	*/
	public function getLastDay() {
		return $this->lastDay;
	}
	
	/*
	 Method: getName
	  Return the month name as a string, i.e. 'January'
	  
	 Alias: _{{ calendar.name }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _string_
	*/
	public function getName() {
		return date('F', $this->time);
	}

	/*
	 Method: getMonth
	  Return month as padded int, i.e. '01'.
	  
	 Alias: _{{ calendar.month }}_
	 
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
	  Return the full, 4 digit, year.
	  
	 Alias: _{{ calendar.year }}_
	 
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
	  
	 Alias: _{{ calendar.time }}_
	 
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
	
	public function __toString() {
		return $this->getTime();
	}

	/*
	 Method: getNext
	  Initialize and return the next month object.
	  
	 Alias: _{{ calendar.next }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _object_ - instance of \foundry\view\helper\calendar
	*/
	public function getNext() {
		return new self($this->getNextTime());
	}
	
	/*
	 Method: getPrevious
	  Initialize and return the previous month object.
	  
	 Alias: _{{ calendar.previous }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _object_ - instance of \foundry\view\helper\calendar
	*/
	public function getPrevious() {
		return new self($this->getPreviousTime());
	}

	/*
	 Method: getNextTime
	  Return the timestamp for the next month.
	  
	 Alias: _{{ calendar.nextTime }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _signed int_
	*/
	public function getNextTime() {
		return strtotime('+1 month', $this->firstDay);
	}
	
	/*
	 Method: getPreviousTime
	  Return the previous month timestamp.
	  
	 Alias: _{{ calendar.previousTime }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _signed int_
	*/
	public function getPreviousTime() {
		return strtotime('-1 month', $this->firstDay);
	}

	/*
	 Method: getDays
	  Return an array of all of the days in the month.
	  
	 Alias: _{{ calendar.days }}_
	 
	 Access:
	  publiv
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _array_ - containing instances of \foundry\view\helper\calendarDay
	*/
	public function getDays() {
		if( !empty($this->dayStack) ) {
			return $this->dayStack;
		}
		
		$this->dayStack = array();
		
		$length = date('t', $this->time);
		$start = $this->firstDay;
		
		for( $i=0; $i<$length; $i++ ) {
			$this->dayStack[] = new Day($start);
			$start = strtotime('+1 day', $start);
		}
		
		return $this->dayStack;
	
	}
	
	/*
	 Method: getDays
	  Return an array of all the weeks in this month (may include days not
	  part of this month).
	  
	 Alias: _{{ calendar.weeks }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _array_ containing instances of \foundry\view\helper\calendarWeek
	*/
	public function getWeeks() {
		if( !empty($this->weekStack) ) {
			return $this->weekStack;
		}
		
		$this->weekStack = array();
		$start = $this->firstDay;
		
		$thisMonth = $this->getMonth();
		for(;;) {
			$week = new Week($start);

			$week->setCalendarTime($this->time);

			$this->weekStack[] = $week;
			
			$days = $week->getDays();
			if( $days[6]->getMonth() != $thisMonth ) {
				break;
			}
			
			$start = $week->getNextTime();
		}

		return $this->weekStack;
	}
	
	/*
	 Method: getEmptyDaysBefore
	  Return array containing the ending days of the previous month begining on
	  Sunday.
	  
	 Alias: _{{ calendar.emptyDaysBefore }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _array - containing instances of \foundry\view\helper\calendarDay
	*/
	public function getEmptyDaysBefore() {
		$count = date('w', $this->firstDay);
		$start = strtotime(sprintf('-%d days', $count), $this->firstDay);
		
		$days = array();
		
		if( $count > 0 ) {
			for( $i=0; $i<$count; $i++ ) {
				$days[] = new Day($start);
				$start += 86400;
			}
		}
		
		return $days;
	}

	/*
	 Method: getEmptyDaysAfter
	  Return an array containing the begininig days of the following month, ends
	  Saturday.
	  
	 Alias: _{{ calendar.emptyDaysAfter }}_
	 
	 Access:
	  publiv
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _array_ containing instances of \foundry\view\helper\calendarDays
	*/
	public function getEmptyDaysAfter()	{
		$count = date('w', $this->lastDay);
		$start = strtotime('+1 day', $this->lastDay);
		$days = array();
		
		if( $count < 6 ) {
			for( $i=$count; $i<6; $i++ ) {
				$days[] = new Day($start);
				$start += 86400;
			}
		}
		
		return $days;
	
	}

	/*
	 Method: getAllDays
	  Combines emptyDaysBefore, days and emptyDaysAfter for you.
	  
	 Alias: _{{ calendar.allDays }}_
	 
	 Access:
	  public
	 
	 Parameters:
	  _void_
	 
	 Returns:
	  _array_ containing instances of \foundry\view\helper\calendarDay
	*/
	public function getAllDays() {
		$pre = $this->getEmptyDaysBefore();
		$post = $this->getEmptyDaysAfter();
		$all = array_merge($pre, $this->getDays(), $post);
		
		return $all;
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