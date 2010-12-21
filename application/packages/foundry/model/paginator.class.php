<?php
/*
 File: paginator.class.php
  Provides the foundry\model\paginator and foundry\model\paginator\page classes
  
 Version:
  2010.06.29
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\model {
	use foundry\request\url as URL;
	
	/*
	 Class: paginator
	  The model paginator allows you to generate pagination links directly
	  from a model collection object.
	  
	 Example:
	 (start code)
	  $page = $this->request->get('page', 'num');
	  $articles = M::init('article')
			->order('self:created desc')
			->limit($start.', '.$limit)
			->find();
	  
	  $pag = new \foundry\model\paginator($articles, $page, $limit);
	  $pag->setURL('admin:article', array(
		'page' => '%PAGE%'
	  ));
	 (end)
	 
	  Paginator will automatically convert '%PAGE%' to the proper page number.
	  
	  Paginator also implements ArrayAccess and IteratorAggregate so you 
	  can loop over the paginator thusly:
	  
	 (start code)
	  $pag = new \foundry\model\paginator($articles, $page, $limit);
	  $pag->setURL('admin:article', array(
		'page' => '%PAGE%'
	  ));
	  
	  return array('pagination' => $pag);
	  
	  ...
	  LATER IN YOUR TEMPLATE
	  ...
	  
	  {% for page in pagination %}
	    <a href="{{ page.url() }}">{{ page.label() }}</a>
	  {% endfor %}
	 (end)
	 
	 Namespace:
	  foundry\model
	*/
	class paginator implements \ArrayAccess, \IteratorAggregate {
		
		private $url = false;
		private $model = false;
		private $object = false;
		private $limit = 50;
		private $perPage = 10;
		private $current = 0;
		
		private $total = 0;
		
		private $pages = array();
		
		private $previous = false;
		private $next = false;
		
		/*
		 Method: constructor
		  object constructor
		 
		 Access:
		  public
		  
		 Parameters:
  		  obj - _object_ the collection to paginate
		  current - _signed int_ the current page
		  limit - _signed int_ the maximum number of items per page
		  perPage - _signed int_ the maximum number of page links to show		  
		  binds - _array_ the bound vars from the original query (if any)
		 
		 Returns:
		  _object_
		*/
		public function __construct($obj, $current, $limit=50, $perPage=10,
			$binds = null) {
			
			$this->object = $obj;
			$this->model = get_class($obj);
			$this->limit = $limit;
			$this->perPage = $perPage;
			$this->current  = $current;
			
			$pk = $this->object->primaryKey;
			
			$q = array();
			$last = $this->object->myQuery;

			// so we want to build the last query with the where and order
			// constraints but sans the limit to get the total available.
			// we run this as a raw query only returing the primary key
			// because it's much faster this way.
			$q['where'] = $last['where'];
			$q['order'] = $last['order'];
			
			if( $last['join'] ) {
				$q['join'] = $last['join'];
			}

			$cols = 'count(self:'.$pk.')';

			if( $last['find'] ) {
				$cols = $last['find'];
			}

			$q = $this->object->convertTableRef($this->object->compileQuery($q,
				$cols));

			$stmt = $this->object->dbh->prepare($q);
			$stmt->execute($binds);
			$row = $stmt->fetch();

			$this->total = $row[0];
			
		}
		
		/*
		 Method: setURL
		  Set the url for the page links
		 
		 Access:
		  public
		 
		 Parameters:
		  url - _string_ the base url
		  data - _array_ GET data to be added
		 
		 Returns:
		  _void_
		*/
		public function setURL($url, $data=array()) {
			$this->url = array($url, $data);
			
			foreach($this->pages as $p ) {
				$p->setUrl($this->url);
			}
		}
		
		/*
		 Method: getPrevious
		  Get the 'Previous page' page object
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _mixed_ returns FALSE if there is no previous page
		*/
		public function getPrevious() {
			if( $this->previous ) {
				return $this->previous;
			}
			
			if( $this->current > 0 ) {
				$this->previous = new \foundry\model\paginator\page(
					($this->current-1), $this->url);
			}
			
			return $this->previous;
		}
		
		/*
		 Method: getNext
		  Get the 'Next page' page object
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _mixed_ returns FALSE if there is no next page
		*/
		public function getNext() {
			if( $this->next ) {
				return $this->next;
			}

			$maxPages = ceil($this->total/$this->limit);

			if( $this->current < $maxPages ) {
				$this->next = new \foundry\model\paginator\page(
					($this->current+1), $this->url);
			}

			return $this->next;
		}
		
		/*
		 Method: getPages
		  Build and return all the page links (except the Next and Previous
		  objects)
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _array_
		*/
		public function getPages() {
			if( count($this->pages) ) {
				return $this->pages;
			}
			
			$start = 0;
			if( $this->current >= $this->perPage ) {
				$start = $this->current - ceil($this->perPage/2);
			}

			$maxPages = ceil($this->total/$this->limit);
			
			for( $i=$start; $i<($start+$this->perPage); $i++ ) {
				if( $i >= $maxPages ) {
					break;
				}
				
				$pg = new \foundry\model\paginator\page($i, $this->url);
				if( $i == $this->current ) {
					$pg->setCurrent(true);
				}
				$this->pages[] = $pg;
				
			}
			
			return $this->pages;
		}


		// Array and IteratorAggregate inplementation
		public function getIterator() {
			$a = $this->getPages();

			$p = $this->getPrevious();
			if( $p ) {
				$p->setLabel('&#171; Prev');
				array_unshift($a, $p);
			}

			$n = $this->getNext();
			if( $n ) {
				$n->setLabel('Next &#187;');
				array_push($a, $n);
			}
			
			return new \ArrayObject($a);
		}
		
		public function offsetGet($offset) {
			return $this->pages[$offset];
		}
		
		public function offsetExists($offset) {
			if( $this->pages[$offset] ) {
				return true;
			}
			
			return false;
		}

		public function offsetSet($offset, $val) {
			$this->pages[$offset] = $val;
		}
		
		public function offsetUnset($offset) {
			$this->pages[$offset] = false;
		}
	
	}
}

namespace foundry\model\paginator {
	use foundry\request\url as URL;

	/*
	 Class: page
	  The foundry\model\paginator\page object holds the link and label
	  for each page
	 
	 Namespace:
	  \foundry\model\paginator
	*/
	class page implements \ArrayAccess, \IteratorAggregate {
		
		private $num = 0;
		private $url = false;
		
		private $current = false;
		
		private $label = false;
		
		/*
		 Method: constructor
		  Page objects are created directly by the Paginator object.
		 
		 Access:
		  public
		 
		 Parameters:
		  num - _signed int_ the page number
		  url - _array_ the url info
		 
		 Returns:
		  _object_
		*/
		public function __construct($num, $url) {
			$this->num = $num;
			$this->url = $url;
		}
		
		/*
		 Method: url
		  Parse and return the URL for the page
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _string_
		*/
		public function url() {
			$url = URL::build(URL::linkTo($this->url[0]), $this->url[1]);
		
			$filter = array(
				'%2525PAGE%2525' => $this->num,
				'%2525LABEL%2525' => $this->__toString()
			);
		
			return str_replace(
				array_keys($filter),
				$filter,
				$url);
		}
		
		/*
		 Method: label
		  Parse and return the label for the link
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _string_
		*/
		public function label() {
			if( $this->label ) {
				return $this->label;
			}			
			return $this->__toString();
		}
		
		/*
		 Method: setLabel
		  The default label is always the page number, you can change it by
		  passing a string to setLabel()
		 
		 Access:
		  public
		 
		 Parameters:
		  str - _string_
		 
		 Returns:
		  _void_
		*/
		public function setLabel($str) {
			$this->label = $str;
		}
		
		public function __toString() {
			return (string) ($this->num+1);
		}
		
		/*
		 Method: setCurrent
		  Sets the 'current' flag. This is called by the Paginator object and
		  should never need to be called directly.
		 
		 Access:
		  public
		 
		 Parameters:
		  flg - _bool_
		 
		 Returns:
		  _void_
		*/
		public function setCurrent($flg) {
			$this->current = $flg;
		}
		
		/*
		 Method: isCurrent
		  Is TRUE if this page object is the current page. This allows you to
		  style it differently, if you choose.
		 
		 Access:
		  public
		 
		 Parameters:
		  _void_
		 
		 Returns:
		  _bool_
		*/
		public function isCurrent() {
			return $this->current;
		}

		/*
		 Method: setURL
		  Allows you to set the page url after-the-fact.
		 
		 Access:
		  public
		 
		 Parameters:
		  url - _array_
		 
		 Returns:
		  _void_
		*/
		public function setURL($url) {
			$this->url = $url;
		}

		// Array and IteratorAggregate inplementation
		public function getIterator() {
			return new \ArrayObject(array(
				'label' => $this->label(),
				'url' => $this->url(),
				'isCurrent' => $this->isCurrent()
			));
		}
		
		public function offsetGet($offset) {
			switch($offset) {
				case 'label':
					return $this->label();
					break;
				case 'url':
					return $this->url();
					break;
				case 'isCurrent':
					return $this->isCurrent();
					break;
				default:
					return false;
					break;
			}
		}
		
		public function offsetExists($offset) {
			if( $this->offsetGet($offset) ) {
				return true;
			}
			
			return false;
		}

		public function offsetSet($offset, $val) {}
		
		public function offsetUnset($offset) {}
		
	}

}

?>