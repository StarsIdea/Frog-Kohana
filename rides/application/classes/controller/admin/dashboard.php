<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Dashboard extends Controller_Admin_Template {

	public function action_index()
	{
		$record_counts = array();
		$groups = array(
			'Rides' => array('ride','event_result'),
			'Members' => array('member','reclaim','equine'),
			'Reference' => array('event_type','member_type','equine_sex', 'federation'),
		);
		
		if(Auth::instance()->get_user()->has_role('admin'))
		{
			$approvals = Request::factory(Route::get('admin/approval')->uri())->execute();
			foreach($groups as $group => $resources) {
				foreach($resources as $resource) {
					$record_counts[$resource] = ORM::factory($resource)->find_all()->count();
				}
			}
		}
		else
		{
			$approvals = Request::factory(Route::get('admin/approval')->uri())->execute();
			foreach($groups as $group => $resources) {
				foreach($resources as $i => $resource) {
					$record_counts[$resource] = ORM::factory($resource)->find_all()->count();
					if( ! $this->acl->is_allowed($this->auth->get_user(), $resource, 'list')) {
						unset($groups[$group][$i]);
						if(empty($groups[$group])) {
							unset($groups[$group]);
						}
					}
				}
			}
		}
		
		# Create User
		# username, password, email, member
		# send user an email with password and a confirmation link
		
		
		// Update event type names from other fields
//		foreach(ORM::factory('event_type')->find_all() as $event_type) {
//			$event_type->save();
//		}
		
		// Change members birthdate from timestamp to date format
//		foreach(ORM::factory('member')->find_all() as $member) {
//			if( ! empty($member->birthdate)) {
//				$member->birth_date = date('Y-m-d',$member->birthdate);
//				$member->save();
//			}
//		}
		
		$quick_links = array(
			array(
				'url' => Route::url('admin', array('resource' => 'member')),
				'label' => 'Riders',
			),
			array(
				'url' => Route::url('admin', array('resource' => 'equine')),
				'label' => 'Horses',
				'class' => 'horse',
			),
			array(
				'url' => Route::url('admin', array('resource' => 'event_result')),
				'label' => 'Events',
				'class' => 'event',
			),
			array(
				'url' => Route::url('admin', array('resource' => 'member')).Url::query(array('active'=>1,'show_all'=>1)),
				'label' => 'Active Members',
			),
			array(
				'url' => Route::url('admin', array('resource' => 'equine')).Url::query(array('active'=>1,'show_all'=>1)),
				'label' => 'Active Horses',
				'class' => 'horse',
			),
			array(
				'url' => Route::url('admin/member', array('action' => 'reset_active')),
				'label' => 'Reset Active Members',
				'class' => 'reset-active-members'
			)
		);

		$this->template->content = View::factory('admin/main')
			->bind('approvals', $approvals)
			->set('groups', $groups)
			->set('record_counts', $record_counts)
			->set('resources', Kongo::get_resource_names())
			->set('quick_links', $quick_links);
	}

}
