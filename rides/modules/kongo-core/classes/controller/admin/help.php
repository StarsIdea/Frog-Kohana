<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Help extends Controller_Admin_Template {

	public function before()
	{
		parent::before();
		Breadcrumbs::add('Help: ' . ucwords(Inflector::humanize($this->request->param('topic'))));
	}

	public function action_index()
	{
		$topic = $this->request->param('topic');
		if($file = Kohana::find_file('help', $topic, 'md'))
		{
			$markdown = file_get_contents($file);
			$html = Markdown($markdown);
			$this->response->body($html);
		}
		else
		{
			throw new HTTP_Exception_404('Help topic not found!');
		}
	}

}