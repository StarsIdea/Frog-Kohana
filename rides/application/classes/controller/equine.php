<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Equine extends Controller_Template {

	public function action_index()
	{
		$count = ORM::factory('equine')->fetch_equines(NULL, NULL, $_GET)->count();
		$alphabet = new Alphabet(array('table' => 'equines','column' => 'name'));
		$pagination = new Pagination(array('total_items'=>$count));
		$results = ORM::factory('equine')->fetch_equines(
			$pagination->items_per_page, $pagination->offset, $_GET);

		$results = $results->as_array();
		foreach($results as & $item) {
			unset($item['owner']);
			$item['name'] = Html::anchor(Route::get('equines')->uri(array('action'=>'view', 'id'=>$item['id'])), $item['name']);
		}

		$this->template->content = View::factory('equine/index', array(
			'title' => 'Horses',
			'search' => View::factory('_search'),
			'results' => $results,
			'pagination' => $pagination,
			'alphabet' => $alphabet
		));	
	}

	public function action_view()
	{
		Breadcrumbs::add('Back to Horses', Route::url('equines'));
		$equine = ORM::factory('equine', array('id' => $this->request->param('id')));

		if( ! $equine->loaded()) {
			Message::warning('crud.item.invalid', array(':id' => $equine->id,':item' => 'Equine'));
			$this->request->redirect(Route::get('default')->uri(array('controller'=>'equine')));
		}

		$ridden_by = $equine->fetch_ridden_by();
		$ridden_by = $ridden_by->as_array();
		foreach($ridden_by as & $item)
		{
			$item['name'] = Html::anchor(
				Route::get('members')->uri(array('action'=>'view', 'id'=>$item['id'])),
				$item['name']
			);
		}

		// Set owner to whichever field is available
		$details = $equine->details();
		$details['owner'] = ! empty($details['member_name']) 
			? $details['member_name']
			: $details['owner_name'];

		$rides_by_type = array();
		$rides = $equine->event_results()->as_array();
		foreach($rides as & $item) {
			$year = date('Y', strtotime($item['ride_date']));
			$item['ride_name'] = Html::anchor(
				Route::get('rides')->uri(array('action'=>'view','id'=>$item['ride_id'])),
				$item['ride_name']
			);
			unset($item['ride_id']);

			$item['member'] = Html::anchor(
				Route::get('members')->uri(array('action'=>'view','id'=>$item['member_#'])),
				$item['member']
			);
			$rides_by_type[$year][] = $item;
		}

		$this->template->content = View::factory('equine/view', array(
			'equine' => $equine->as_array(),
			'ridden_by' => $ridden_by,
			'details' => $details,
			'rides_by_type' => $rides_by_type,
		));
	}

}
