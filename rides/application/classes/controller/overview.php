<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Overview extends Controller_Template {

	public function action_index()
	{
		$this->template->content = View::factory('overview', array(
			'recent_events' => Model::factory('ride')->fetch_recent(5),
			//'top_horses' => Model::factory('equine')->fetch_top_ranking(),
			//'top_riders_junior' => Model::factory('member')->fetch_top_ranking('Junior'),
			//'top_riders_senior' => Model::factory('member')->fetch_top_ranking('Senior')
		));
	}
	
}
