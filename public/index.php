<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
date_default_timezone_set ( 'Europe/Paris' );
chdir ( dirname ( __DIR__ ) );
define('APPLICATION_PATH', dirname(__DIR__));
set_time_limit ( 10 );
ignore_user_abort ( false );
function exception_error_handler($errno, $errstr, $errfile, $errline) {
	throw new ErrorException ( $errstr, 0, $errno, $errfile, $errline );
	return false;
}
set_error_handler ( 'exception_error_handler' );
// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name () === 'cli-server' && is_file ( __DIR__ . parse_url ( $_SERVER ['REQUEST_URI'], PHP_URL_PATH ) )) {
	return false;
}

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init ( require 'config/application.config.php' )->run ();
