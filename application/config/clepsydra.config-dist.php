<?php
namespace foundry\config;
/*
	This is the instance-wide config file for this application when it is
	in it's primary context.
*/
function defaults() {
	return array(
		// can be realtive to appRoot or absolute
		'templates'					=> array(
			'../templates'
		),

		'middleware'				=> array(
			'\\foundry\\middleware\\common'
		),
	
		'databases' => array(
			'default' => array(
				'host' => 'HOSTNAME',
				'user' => 'USERNAME',
				'password' => 'PASSWORD',
				'database' => 'DATABASE',
				'driver' => 'mysql'
			)
		),
		
		'cache' => false,
		
		'mail' => array(
			'transport' => '/usr/sbin/sendmail -bs',
			'defaultFrom' => array(
				'webmaster@statenews.com' => 'The State News'),
			'admin' => 'mike.joseph5@gmail.com',
			'limit' => 3,
			'interval' => 900
		),

		'debug'	=> true,
		'backtrace' => true,
		
		'auth' => array(
			'maxAttempts' => 3,
			'lockTimeout' => 1800
		),
		
		'log' => array(
			'minWarn' => 3
		),
		
		'env' => array(
			'php' => false,
			'hostname' => false
		)
	);
}

?>