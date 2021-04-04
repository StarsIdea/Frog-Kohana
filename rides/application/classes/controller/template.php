<?php defined('SYSPATH') or die('No direct script access.');

abstract class Controller_Template extends Kohana_Controller_Template {

	public function before()
	{
		parent::before();
		//$this->request != Request::initial() AND $this->auto_render = FALSE;

		if($this->auto_render)
		{
			$this->template->title = 'Endurance Riders of Alberta';
			$this->template->styles = array();
			$this->template->scripts_top = array();
			$this->template->scripts_bottom = array();
			$this->template->messages = '';
		}
	}

	public function after()
	{
		$this->template->styles = array_merge($this->template->styles, array(
			'media/css/reset.css' => 'screen',
			'media/css/print.css' => 'print',
			'media/css/lib/smoothness/jquery-ui-1.8.12.custom.css' => 'screen',
			'media/css/screen.css' => 'screen',
		));

		$this->template->scripts_top = array_merge($this->template->scripts_top, array(
			'https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js',
			'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/jquery-ui.min.js',
		));
		
		$this->template->search = Arr::get($_GET,'search',NULL);
		$this->template->header = file_get_contents('http://enduranceridersofalberta.ca/templates/header.php');
		$this->template->footer = file_get_contents('http://enduranceridersofalberta.ca/templates/footer.php');
		$this->template->base = Url::base(false, true);
		$this->template->current = trim(URL::site(Request::initial()->uri()), '/');
		$this->template->breadcrumbs = Breadcrumbs::render();
		$this->template->messages = Message::render();
		$this->template->profiler = (Kohana::$environment == Kohana::DEVELOPMENT AND ! Kohana::$is_cli)
			? View::factory('profiler/stats')
			: null;
		$this->template->navigation = $this->navigation();

		parent::after();
	}

	public function navigation()
	{
		$items = array(
			array(
				'url' => Route::get('default')->uri(array('controller' => 'overview')),
				'title' => 'Overview',
			),
			array(
				'url' => Route::get('members')->uri(array('page' => 'riders')),
				'title' => 'Riders',
			),
			array(
				'url' => Route::get('equines')->uri(array('page' => 'horses')),
				'title' => 'Horses',
			),
			array(
				'url' => Route::get('rides')->uri(array('page' => 'events')),
				'title' => 'Events',
			),
			array(
				'url' => Route::get('admin/login')->uri(),
				'title' => 'Login',
				'classes' => array('right'),
			),
			array(
				'url' => Route::get('admin/register')->uri(),
				'title' => 'Register',
				'classes' => array('right'),
			),
		);

		// Set current item
		foreach($items as & $item)
		{
			$item['classes'][] = (Request::initial() AND strstr(Request::initial()->uri(), $item['url']) !== FALSE) ? 'current' : NULL;
			$item['classes'] = implode(' ', $item['classes']);
		}

		return $items;
	}

	public function filteractive()
	{
		$filters = array(
			array(
				'url' => Request::initial()->uri().Url::query(array('active' => 0)),
				'name' => 'Inactive',
				'class' => NULL,
			),
			array(
				'url' => Request::initial()->uri().Url::query(array('active' => 1)),
				'name' => 'Active',
				'class' => NULL,
			),
			array(
				'url' => Request::initial()->uri(),
				'name' => 'All',
				'class' => NULL,
			),
		);
		
		$selected = Arr::get($_GET, 'active', 2);
		if(Arr::get($filters, $selected))
			$filters[$selected]['class'] = 'current';

		return $filters;
	}
	
}
