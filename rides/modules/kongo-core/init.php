<?php

include_once Kohana::find_file('vendor', 'markdown/markdown');

Route::set('media', 'media(/<file>)', array('file'=>'.+'))
	->defaults(array(
		'controller' => 'media',
		'action'     => 'file',
		'file'		 => NULL,
	));

Route::set('admin/register', 'admin/register')
	->defaults(array(
		'controller' => 'user',
		'action'     => 'register',
	));

Route::set('admin/login', 'admin/login')
	->defaults(array(
		'controller' => 'user',
		'action'     => 'login',
	));

Route::set('admin/logout', 'admin/logout')
	->defaults(array(
		'controller' => 'user',
		'action'     => 'logout',
	));

Route::set('admin/dashboard', 'admin')
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'dashboard',
		'action'     => 'index',
	));

Route::set('admin/import', 'admin/import(/<action>(/<id>))')
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'import',
		'action'     => 'index',
	));

Route::set('admin/help', 'admin/help(/<topic>)')
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'help',
		'action'     => 'index',
	));

Route::set('admin', 'admin(/<resource>(/<action>(/<id>)))', array(
	'action' => '(list|add|edit|delete|install)'
))
	->defaults(array(
		'directory'  => 'admin/scaffold',
		'controller' => 'orm',
		'action'     => 'list',
	));
