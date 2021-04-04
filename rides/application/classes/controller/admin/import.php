<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Import extends Controller_Admin_Template {

	public function action_index()
	{
		$imports = ORM::factory('import')->where('id','>',1)->order_by('date_created', 'DESC')->find_all()->as_array();
		$this->response->body(
			View::factory('admin/import/index', array(
				'imports' => $imports
			))
		);
	}

	public function action_delete()
	{
		$id = $this->request->param('id');
		if($id == 1) 
			$this->request->redirect(Route::get('admin/import')->uri());

		$import = ORM::factory('import', $id);
		$ride = ORM::factory('ride')->where('import_id','=',$id)->find();

		if($this->request->method() == HTTP_Request::POST)
		{
			if(Auth::instance()->get_user()->has_role('admin'))
			{
				$ride->loaded() AND $ride->delete();
				$import->delete();
				Message::success('scaffold.deleted',array(':item'=>'Import'));
				$this->request->redirect(Route::get('admin/dashboard')->uri());
			}
		}

		$count = ORM::factory('event_result')->where('import_id','=',$id)->find_all()->count();
		$this->response->body(
			View::factory('admin/import/delete', array(
				'_model_name' => 'Import',
				'_resource' => 'import',
				'record' => $import->as_array(),
				'ride' => $ride->as_array(),
				'count' => $count
			))
		);
	}
	
	public function action_new()
	{
		if($this->request->method() == Request::POST)
		{
			$upload = Validation::factory($_FILES)
				->rule('xls_spreadsheet', 'Upload::not_empty')
				->rule('xls_spreadsheet', 'Upload::valid')
				->rule('xls_spreadsheet', 'Upload::type', array(':value', array('xls')));

			$file = $_FILES['xls_spreadsheet'];
			
			if($upload->check())
			{
				$reader = new Spreadsheet_Excel_Reader();
				$reader->setOutputEncoding('CP1251');
				$reader->read($file['tmp_name']);

				$import_parser = new Import_RideResults_Parser($reader, $file);
				$import_parser->parse();

				$import_validator = new Import_RideResults_Validator($import_parser);
				$import_mapper = new Import_RideResults_Mapper($import_parser, $file);

				if($import_validator->check() AND $import_mapper->save())
				{
					$saved_items = '<br> - '.implode('<br> - ', $import_mapper->saved);
					Message::success("Data imported successfully! $saved_items");

					$this->request->redirect(Route::get('admin/dashboard')->uri());
				}
				else
				{
					$errors = $import_validator->errors;
				}
			}
			else
			{
				$errors = $upload->errors('import');
			}
		}

		$view = View::factory('admin/import/view')
			->bind('errors', $errors);

		$this->response->body($view);
	}

}
