<?php
/*
 File: helperNode
  Provides \foundry\view\twig\helperNode class
  
 Version:
  2010.06.09
  
 Copyright:
  2004-2010 The State News, Inc
  
 Author:
  Mike Joseph <josephm5@msu.edu>
  
 License:
  GNU GPL 2.0 - http://opensource.org/licenses/gpl-2.0.php
*/
namespace foundry\view\twig;

/*
 Class: helperNode
  Performs the compilation of the 'helper' tag
 
 Namespace:
  \foundry\view\twig
*/
class helperNode extends \Twig_Node {
	
	public function __construct($target, $alias, $lineNo) {
		if( !$alias ) {
			$alias = $target;
		};
	
		parent::__construct(array(), array(
			'target' => $target,
			'alias' => $alias
		), $lineNo);

	}
	
	public function compile($c) {
		$handler = sprintf('$context[\'%s\'] = \\foundry\\view\\helper\fetch("%s")', 
			$this['alias'], $this['target']);
	
		$c->addDebugInfo($this)->
			write($handler)->
			raw(";\n");
	}

}
?>