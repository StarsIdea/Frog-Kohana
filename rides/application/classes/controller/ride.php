<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Ride extends Controller_Template {

	public function action_index()
	{
		$count = Model::factory('ride')->fetch_rides(NULL, NULL, $_GET)->count();
		$pagination = new Pagination(array('total_items'=>$count));
		$results = ORM::factory('ride')->fetch_rides($pagination->items_per_page, $pagination->offset, $_GET);
		$results = $results->as_array();

		foreach($results as & $item) {
			$item['name'] = Html::anchor(Route::get('rides')->uri(array('action'=>'view', 'id'=>$item['id'])), $item['name']);
			unset($item['id']);
		}

		$this->template->content = View::factory('ride/index', array(
			'title' => 'Events',
			'search' => View::factory('_search'),
			'results' => $results,
			'pagination' => $pagination
		));	
	}

	public function action_view()
	{
		Breadcrumbs::add('Back to Events', Route::url('rides'));
		$ride = ORM::factory('ride', array('id' => $this->request->param('id')));

		if( ! $ride->loaded()) {
			Message::warning('crud.item.invalid', array(':id' => $ride->id,':item' => 'Ride'));
			$this->request->redirect(Route::get('rides')->uri());
		}

		$results_by_type = array();
		$event_results = $ride->event_results();
		
		foreach($event_results as $item) {
			$event_type = trim($item['event_type']);
			unset($item['event_type']);

			if( ! empty($item['member']))
				$item['member'] = Html::anchor(Route::get('members')->uri(array('action'=>'view','id'=>$item['member_#'])), $item['member']);

			if( ! empty($item['equine']))
				$item['equine'] = Html::anchor(Route::get('equines')->uri(array('action'=>'view','id'=>$item['equine_#'])), $item['equine']);

			$results_by_type[$event_type][] = $item;
		}

		$this->template->content = View::factory('ride/view', array(
			'title' => $ride->name,
			'ride' => $ride->details(),
			'results_by_type' => $results_by_type
		));
	}
	
}
