<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Member extends Controller_Template {
	
	public function action_index()
	{
		$count = count(Model::factory('member')->fetch_riders(NULL, NULL, $_GET));
		$alphabet = new Alphabet(array('table' => 'members','column' => 'last_name'));
		$pagination = new Pagination(array('total_items'=>$count));
		$results = ORM::factory('member')->fetch_riders($pagination->items_per_page, $pagination->offset, $_GET);

		foreach($results as & $item) {
			Arr::unshift($item, 'ERA #', $item['id']);
			$item['name'] = Html::anchor(Route::get('members')->uri(array('action'=>'view', 'id'=>$item['id'])), $item['name']);
			unset($item['id']);
		}

		$this->template->content = View::factory('member/index', array(
			'title' => 'Riders',
			'search' => View::factory('_search'),
			'filter_active' => View::factory('filter/_active', array(
				'filters' => $this->filteractive()
			)),
			'results' => $results,
			'pagination' => $pagination,
			'alphabet' => $alphabet
		));	
	}

	public function action_view()
	{
		$member = ORM::factory('member', array('id' => $this->request->param('id')));
		Breadcrumbs::add('Back to Riders',Route::url('members'));

		if( ! $member->loaded()) {
			Message::warning('crud.item.invalid', array(':id' => $member->id,':item' => 'Rider'));
			$this->request->redirect(Route::get('members')->uri());
		}

		$equines = $member->equines_ridden()->as_array();
		foreach($equines as & $item)
		{
			Arr::unshift($item, 'Equine #', $item['id']);
			$item['name'] = Html::anchor(
				Route::get('equines')->uri(array('action'=>'view', 'id'=>$item['id'])),
				$item['name']
			);
			unset($item['id']);
		}

		$details = $member->details();
		if(isset($details['city']))
		{
			$details['address'] = $details['city'];
			if($prov = $details['state_prov'])
				$details['address'] .= ', '.$prov;
		}
		$details['active'] = $details['active'] == '1' ? 'Active' : 'Inactive';

		$rides_by_type = array();
		$rides = $member->event_results()->as_array();
		foreach($rides as & $item) {
			$year = date('Y', strtotime($item['ride_date']));
			//$event_type = trim($item['event_type']);
			//unset($item['event_type']);

			$item['ride_name'] = Html::anchor(
				Route::get('rides')->uri(array('action'=>'view','id'=>$item['ride_id'])),
				$item['ride_name']
			);
			unset($item['ride_id']);

			$item['equine'] = Html::anchor(
				Route::get('equines')->uri(array('action'=>'view','id'=>$item['equine_#'])),
				$item['equine']
			);
			$rides_by_type[$year][] = $item;
		}

		$this->template->content = View::factory('member/view', array(
			'title' => $member->first_name .' '. $member->last_name,
			'member' => $details,
			'equines' => $equines,
			'rides_by_type' => $rides_by_type,
		));
	}
	
}
