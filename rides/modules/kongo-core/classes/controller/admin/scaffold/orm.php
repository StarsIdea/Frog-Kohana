<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Scaffold_ORM extends Controller_Admin_Template {

	public function before()
	{
		parent::before();
		if( ! isset($this->_resource)) {
			$this->_resource = $this->request->param('resource')
				? $this->request->param('resource')
				: $this->request->controller();
		}
		$this->_model_name = ucwords(Inflector::humanize(Inflector::singular($this->_resource)));
		View::set_global('_model_name', $this->_model_name);
		View::set_global('_resource', $this->_resource);
		
		if($this->request->action() != 'list')
			Breadcrumbs::add($this->_model_name, Route::url('admin', array('resource' => $this->_resource, 'action' => 'list')));
	}

	public function action_list()
	{
		Breadcrumbs::add($this->_model_name);

		$model = ORM::factory($this->_resource);

		$count_filtered = $model->fetch_all(NULL, NULL, $this->request->query())->count();
		$count_total = $model->fetch_all()->count();

		$items_per_page = (int)Arr::get($_GET, 'items_per_page', 50);

		if (array_key_exists('show_all', $_GET)) {
			$items_per_page = $count_filtered;
		}
		
		$pagination = Pagination::factory(array(
			'total_items' => $count_filtered,
			'items_per_page' => $items_per_page,
		));

		$this->handle_bulk_modify();
		
		$records = $model->fetch_all($pagination->items_per_page, $pagination->offset, $_GET);

		$table_columns = $model->display_columns($records);
		$foreign_column_titles = $model->foreign_column_titles($records);
		$table_column_titles = $model->table_column_titles();
		if (empty($table_column_titles)) {
			$table_column_titles = $table_columns;
		}

		//@todo Select all checkboxes when header checkbox is selected
		$this->template->content = $this->_load_view('list')
			->set('uri', $this->request->uri())
			->set('table_columns', $table_columns)
			->set('table_column_titles', $table_column_titles)
			->set('table_column_widths', $model->table_column_widths())
			->set('foreign_column_titles', $foreign_column_titles)
			->set('records', $records)
			->set('pagination', $pagination->render())
			->set('total_filtered_items', $count_filtered)
			->set('total_items', $count_total)
			->set('items_per_page', $items_per_page)
			->set('show_checkboxes', FALSE)
			->set('filters', $model->available_filters);
	}

	protected function handle_bulk_modify()
	{
		if($this->request->method() == HTTP_Request::POST)
		{
			// Checkbox actions
			switch($this->request->post('action')) {
				case 'delete':
					foreach($this->request->post('columns') as $id) {
						if(Auth::instance()->get_user()->has_role('admin')) {
							ORM::factory($this->_resource, $id)->delete();
						}
					}
					Message::success('scaffold.list.deleted', array(':item'=>$this->_resource, ':count'=>count($this->request->post('columns'))));
					break;
				default:
					Message::warning('No action selected.');
			}
		}
	}

	public function action_add()
	{
		Breadcrumbs::add('Add New '.$this->_model_name);
		$model = ORM::factory($this->_resource)->values($this->request->post());

		if($this->request->method() == HTTP_Request::POST)
		{
			try
			{
				if(Auth::instance()->get_user()->has_role('admin'))
				{
					$model->save();
					Message::success('scaffold.created',array(':item'=>$this->_model_name));

					$action = key($this->request->post('save'));
					$this->request->redirect(Route::get('admin')->uri(array(
						'resource' => $this->_resource,
						'action' => $action,
						'id' => $action=='edit' ? $model->id : NULL,
					)));
				}
				else
				{
					$model->values($this->request->post())->check();
					$this->submit_for_approval(NULL, $model);
				}
			}
			catch(ORM_Validation_Exception $e)
			{
				$errors = $e->errors('models');
			}
		}
		
		$this->template->content = $this->_load_view('add-edit')
			->bind('model', $model)
			->bind('errors', $errors);
	}

	public function action_edit()
	{
		$model = $this->_get_model_or_redirect();

		Breadcrumbs::add($model->{$model->tk()});
		$model->values($this->request->post());

		if($this->request->method() == HTTP_Request::POST)
		{
			try
			{
				$previous_model = clone $model;
				
				if(Auth::instance()->get_user()->has_role('admin'))
				{
					$model->set_related($this->request->post());
					$model->save();
					
					Message::success('scaffold.modified',array(':item'=>$this->_model_name));
					
					if($this->request->post('unpublish'))
					{
						$model->unpublish();
					}
					elseif($this->request->post('publish'))
					{
						$model->publish();
					}
					else
					{
						$action = key($this->request->post('save'));

						if($referrer = Arr::get($_GET, 'ref') and $action != 'edit') {
							$this->request->redirect($referrer);
						}

						$this->request->redirect(Route::get('admin')->uri(array(
							'resource' => $this->_resource,
							'action'=>$action,
							'id'=>$action=='edit' ? $model->id : NULL,
						)) . Url::query()); // not sure if this should stay here or not
					}
				}
				else
				{
					$model->values($this->request->post())->check();
					$this->submit_for_approval($previous_model, $model);
				}

			}
			catch(ORM_Validation_Exception $e)
			{
				$errors = $e->errors('models');
			}
		}

		$this->template->content = $this->_load_view('add-edit')
			->set('is_publishable', $model instanceof Model_Publishable)
			->bind('model', $model)
			->bind('errors', $errors);
	}

	public function action_delete()
	{
		$model = $this->_get_model_or_redirect();
		Breadcrumbs::add($model->{$model->tk()}, Route::url('admin', array('resource'=>$this->_resource, 'action'=>'edit', 'id'=>$this->request->param('id'))));
		Breadcrumbs::add('Delete');
		
		$this->template->content = $this->_load_view('delete')
			->set('record', $model->as_array());

		if($this->request->method() == HTTP_Request::POST)
		{
			if(Auth::instance()->get_user()->has_role('admin'))
			{
				$model->delete();
				Message::success('scaffold.deleted',array(':item'=>$this->_model_name));

				if($referrer = Arr::get($_GET, 'ref')) {
					$this->request->redirect($referrer);
				}

				$this->request->redirect(Route::get('admin')->uri(array('resource' => $this->_resource)));
			}
			else
			{
				$this->submit_for_approval(NULL, $model);
			}
		}
	}
	
	public function submit_for_approval($original = NULL, $new_or_modified)
	{
		$approval = ORM::factory('approval')
			->values(array(
				'user_id' => $this->auth->get_user()->id,
				'model_name' => $this->_resource,
				'model_id' => $original ? $original->id : $new_or_modified->id,
				'action' => $this->request->action(),
				'original' => $original ? $original->serialize() : NULL,
				'modified' => $new_or_modified->serialize(),
			))
			->save();
		Message::success('approval.pending.'.$this->request->action(), array(':item' => $this->_model_name));
		$this->request->redirect(Route::get('admin')->uri(array('resource'=>$this->_resource,'action'=>'list')));
	}

	public function action_install()
	{	
		try
		{
			$model = ORM::factory($this->_resource)->install();
			Message::success(NULL, ucfirst($this->_resource).' successfully installed!');
		}
		catch(Database_Exception $e)
		{
			if($e->getCode() != 1050)
				throw $e;
			Message::warning(':resource already installed.', array(':resource'=>ucfirst($this->_resource)));
		}
		$this->request->redirect($this->request->route()->uri(array('resource'=>$this->_resource,'action'=>'list')));
	}

	protected function _get_model_or_redirect()
	{
		$model = ORM::factory($this->_resource, $this->request->param('id'));
		if( ! $model->loaded())
			$this->request->redirect(Route::get('admin')->uri(array('resource' => $this->_resource)));
		return $model;
	}

	protected function _load_view($view_name)
	{
		$view_override = 'admin/'.$this->_resource.'/'.$view_name;
		$view = Kohana::find_file('views',$view_override);
		return $view ? View::factory($view_override) : View::factory('admin/scaffold/'.$view_name);
	}

}
