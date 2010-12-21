<?php
/*
 File: modelHandler
  Provides \foundry\view\twig\modelHandler class
  
 Version:
  2010.06.03
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\view\twig;
use foundry\model as M;
use foundry\model\inflector as Inflector;

/*
 Class: modelHandler
  Provides the handler for the fetch tag (post compilation). This is what
  performs the actual data selection for the 'fetch' tag.
 
 Namespace:
  \foundry\view\twig
*/
class modelHandler extends \foundry\model\collection {
	
	protected $modelString = false;
	protected $def = false;
	
	public function __construct($model, $def) {
		$this->modelString = $model;
		$this->def = $def;
		
		$mod = M::init($this->modelString);
		
		$findBy = false;
		
		foreach( $this->def as $k => $v ) {
			if( $k == 'limit' ) {
				$mod->limit($v);
			} elseif( $k == 'order' ) {
				$mod->order($v);
			} elseif( $k == 'where' ) {
				$mod->where($v);
			} elseif( strpos($k, 'with') !== false ) {
				// something like withTags or withMedia
				$str = lcfirst(Inflector::singularize(substr($k, 4)));
				if( !$mod->hasAssociation($str) ) {
					continue;
				}
				
				$assn = M::init($str);
				if( !($prop = $assn->defaultSearchProperty) ) {
					continue;
				}
				
				$func = sprintf('findBy%s', ucfirst($prop));

				$findBy = call_user_func_array(array($assn, $func), $v);
			} elseif( $k == 'forceCache' ) {
				$mod->forceCache(true);
			}
		}
		
		if( $findBy ) {
			$this->itemStack = $mod->findBy($findBy[0]->modelString,
				array($findBy))->toArray();
		} else {
			$this->itemStack = $mod->find()->toArray();
		}
	}
	
}


?>