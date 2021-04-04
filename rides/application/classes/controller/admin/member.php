<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Member extends Controller_Admin_Template {

	public function action_reset_active()
	{
		$member = new Model_Member();
		try {
			$count = $member->set_all_inactive();
			Message::success("Success! $count members set to inactive!");
		} catch (Exception $e) {
			Message::warning('There was a problem setting all members to inactive.');
		}
		$this->request->redirect(Route::get('admin/dashboard')->uri());
	}

}
