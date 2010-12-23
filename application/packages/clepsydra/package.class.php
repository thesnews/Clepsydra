<?php
namespace clepsydra;

class package {

	private static $myContext = 'secondary';

	public static function supportsContext($ctx) {
		return true;
	}

	public static function setContext($ctx) {
		self::$myContext = $ctx;
	}
	
	public static function urls() {
		if( self::$myContext == 'primary' ) {
			return array(
				'clepsydra:main' => 'main/:action',
				'clepsydra:person' => 
					'3cabfab8f977ae7d12a3773423acf849/:action',
				'clepsydra:admin' => 'null',
				'clepsydra:setting' => 'null',
				'clepsydra:export' => 'null'
			);
		}
		
		return array();
	}
	
	public static function defaults() {
		return array(
			'version'	=> '1.0Alpha (Build: 20101221)',
			'requiresAuth' => array(
				'clepsydra:person'
			)
		);
	}
	
	public static function middleware() {
		if( self::$myContext == 'primary' ) {
			return array(
				'\\clepsydra\\middleware\\auth',
			);
		}
		return array();
	}
	
	public static function helpers() {
		if( self::$myContext == 'primary' ) {
			return array(
			);
		}
		
		return array();
	}
}

?>