<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Approval extends Controller_Admin_Template {
	
	protected $_resource_required = array('view');

	public function action_list()
	{
		$user_id = $this->user->has_role('admin') ? NULL : $this->user->id;
		$count = ORM::factory('approval')->fetch_approvals(NULL, NULL, $user_id)->count();
		$pagination = Pagination::factory(array(
			'total_items' => $count,
			'items_per_page' => 10,
		));
		$approvals = ORM::factory('approval')->fetch_approvals($pagination->items_per_page, $pagination->offset, $user_id);
		$this->response->body(View::factory('admin/approval/list')
			->set('approvals', $approvals)
			->set('pagination', $pagination));
	}

	public function action_view()
	{
		$approval = ORM::factory('approval',$this->request->param('id'));
		
		if( ! $approval->loaded())
			$this->request->redirect(Route::get('admin')->uri());

		$user = ORM::factory('user', $approval->user_id);
		$member = $user->member;
		
		$original = ORM::factory($approval->model_name, $approval->model_id);
		//$original->unserialize($approval->original); // ensure_reload_on_wakeup is true OR just load object
		
		$modified = ORM::factory($approval->model_name);
		$modified->reload_on_wakeup(FALSE);
		$modified->unserialize($approval->modified); // ensure reload_on_wakeup is false
		
		/**
		 * Idea: When a user logs on, output a queue of messages stored from admin approving their create/edits
		 * OR have a user status page where they can see the approval statuses of their changes
		 */
		if($this->request->method() == HTTP_Request::POST AND $this->user->has_role('admin'))
		{
			$datetime = new DateTime('now');
			
			if($this->request->post('approve'))
			{
				in_array($approval->action, array('add','register'))
					? $modified->create()
					: $modified->update();
				$result = 'approved';
				$approval->approved_date = $datetime->format('Y-m-d H:i:s');
				$approval->approved_by = Auth::instance()->get_user()->id;
			}
			elseif($this->request->post('reject'))
			{
				$result = 'rejected';
				$approval->rejected_date = $datetime->format('Y-m-d H:i:s');
				$approval->rejected_by = Auth::instance()->get_user()->id;
			}
			
			$approval->comment = $this->request->post('comment');
			$approval->save();
			
			Message::success('approval.'.$result.'.'.$approval->action, array(':item'=>$approval->model_name));
			$this->request->redirect(Route::get('admin')->uri());
		}
		
		$this->template->content = View::factory('admin/approval/view')
			->set('member', $member)
			->set('approval', $approval)
			->set('original', $original)
			->set('modified', $modified);
	}

}