<?php defined('SYSPATH') OR die('No direct access allowed.');

Route::set('admin/approval', 'admin/approval(/<action>(/<id>))')
	->defaults(array(
		'directory'  => 'admin',
		'controller' => 'approval',
		'action'     => 'list',
	));