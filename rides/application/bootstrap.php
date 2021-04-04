<?php defined('SYSPATH') or die('No direct script access.');

ini_set('xdebug.var_display_max_depth', 8);
ini_set('allow_url_include', TRUE);

// -- Environment setup --------------------------------------------------------

// Load the core Kohana class
require SYSPATH.'classes/kohana/core'.EXT;

if (is_file(APPPATH.'classes/kohana'.EXT))
{
	// Application extends the core
	require APPPATH.'classes/kohana'.EXT;
}
else
{
	// Load empty core extension
	require SYSPATH.'classes/kohana'.EXT;
}

/**
 * Set the default time zone.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/timezones
 */
date_default_timezone_set('America/Chicago');

/**
 * Set the default locale.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/setlocale
 */
setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Enable the Kohana auto-loader.
 *
 * @see  http://kohanaframework.org/guide/using.autoloading
 * @see  http://php.net/spl_autoload_register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @see  http://php.net/spl_autoload_call
 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

// -- Configuration and initialization -----------------------------------------

/**
 * Set the default language
 */
I18n::lang('en-us');

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */
require APPPATH.'config/bootstrap'.EXT;

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 */
Kohana::init(Kohana::$config->load('init')->as_array());
Kohana::$config = $config_backup;

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Log_File(APPPATH.'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
//Kohana::$config->attach(new Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(Kohana::$config->load('modules')->as_array());

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */

Route::set('error', 'error/<action>(/<message>)', array('action' => '[0-9]++', 'message' => '.+'))
->defaults(array(
    'controller' => 'error_handler'
));

Route::set('members', '<page>(/<action>(/<id>))', array(
		'page' => '(riders|members)',
	))
	->defaults(array(
		'page'		 => 'riders',
		'controller' => 'member',
		'action'     => 'index',
	));

Route::set('equines', '<page>(/<action>(/<id>))', array(
		'page' => 'horses|equines',
	))
	->defaults(array(
		'page'		 => 'horses',
		'controller' => 'equine',
		'action'     => 'index',
	));

Route::set('rides', '<page>(/<action>(/<id>))', array(
		'page' => 'events',
	))
	->defaults(array(
		'page'		 => 'events',
		'controller' => 'ride',
		'action'     => 'index',
	));

Route::set('admin/member', 'admin/member(/<action>(/<id>))', array('action' => 'reset_active'))
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'member',
		'action'     => 'reset_active',
	));

Route::set('default', '(<controller>(/<action>(/<id>)))', array(
		'controller' => '(overview)'
	))
	->defaults(array(
		'controller' => 'overview',
		'action'     => 'index',
	));
