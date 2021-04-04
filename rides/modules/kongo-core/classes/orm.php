<?php

class ORM extends Kohana_ORM {

	//@todo Should meta be a property on the model or a function return an array of scaffolding rules?
	public $available_filters = array();
	protected $_meta = array();
	protected $_title_key;
	protected $_display_columns;
	protected $_search = array();
	
	public function filters() 
	{
		return array(
			TRUE => array(
				array('Format::empty_to_null', array(':value')),
			),
		);
	}
	
	public function set($column, $value)
	{
		
		parent::set($column, $value);
	}
	
	public function select_array()
	{
		return FALSE;
	}
	
	public function foreign_title()
	{
		return FALSE;
	}
	
	// Set to FALSE for serializing modifications to a model
	public function reload_on_wakeup($bool)
	{
		$this->_reload_on_wakeup = (bool)$bool;
	}
	
	public function display_columns(Database_Result $records)
	{
		$table_columns = array_keys($this->table_columns());
		if($row = $records->current()) {
			$table_columns = array_keys($row);
			if($this->_display_columns !== NULL)
				$table_columns = array_intersect($this->_display_columns, $table_columns);
		}
		return $table_columns;
	}

	public function table_column_titles()
	{
		return array();
	}

	public static function foreign_columns(array $table_columns)
	{
		$cols = array();
		foreach($table_columns as $column) {
			 if(strpos($column, '_id') !== FALSE) {
				 $cols[] = $column;
			 }
		}
		return $cols;
	}
	
	public function foreign_column_titles($records)
	{
		$table_columns = $this->display_columns($records);
		$foreign_columns = ORM::foreign_columns($table_columns);
		$models = array();
		$model_ids = array();
		$foreign_titles = array();
		
		foreach($foreign_columns as $column) {
			$models[$column] = str_replace('_id', '', $column);
		}
		foreach($records as $record) {
			foreach($models as $column => $model) {
				if(array_key_exists($column, $record)) {
					$model_ids[$model][] = $record[$column];
				}
			}
		}
		foreach($model_ids as $model => $ids) {
			try {
				$foreign_titles[$model.'_id'] = ORM::factory($model)->display_titles($ids);
			}
			catch(Exception $e) {
				// do nothing
			}
		}
		
		return $foreign_titles;
	}
	
	public function display_titles($ids)
	{
		return DB::select('id', array($this->foreign_title(), 'title'))
			->from($this->_table_name)
			->where('id','IN',$ids)
			->execute()
			->as_array('id','title');
	}

	protected function get_search_string($terms)
	{
		$matches = array();
		$wants_exact_match = (bool)preg_match('/^"(.*)"$/', $terms, $matches);
		$str = $wants_exact_match ? $matches[1] : "%$terms%";
		return $str;
	}

	protected function _fetch_all_query($limit = NULL, $offset = NULL, array $filters = array(), array $columns = array())
	{
		$query = DB::select()->from($this->_table_name);

		$columns AND $query->select($columns);
		
		if($search_terms = Arr::get($filters, 'q')) {
			$search_string = $this->get_search_string($search_terms);
			$this->_build_search($query, $this, $search_string);
		}
		
		$limit AND $query->limit($limit);
		$offset AND $query->offset($offset);

		$sort = Arr::get($filters, 'sort', 'date');
		$direction = Arr::get($filters, 'dir', 'desc');

		if( ! is_null($active = Arr::get($filters, 'active')) AND in_array('active', $this->available_filters)) {
			$query->where('active','=',(bool)$active);
		}

		foreach($filters as $key => $value)
		{
			if(array_key_exists($key, $this->_table_columns) and Valid::not_empty($value))
			{
				$query->where($this->_table_name . '.' .$key, 'LIKE', $value);
			}
		}

		if((array_key_exists($sort, $this->_table_columns) 
		OR ($this->_display_columns AND in_array($sort, $this->_display_columns)))
		AND in_array($direction, array('asc','desc')))
		{
			$query->order_by($sort, $direction);
		}

		return $query;
	}

	public function fetch_all($limit = NULL, $offset = NULL, array $filters = array(), array $columns = array())
	{
		$query = $this->_fetch_all_query($limit, $offset, $filters, $columns);
		return $query->execute();
	}

	protected function get_column_names()
	{
		$columns = array();

		foreach($this->table_columns() as $alias => $field) {
			// Not a related or id column
			if( ! empty($field['column_name'])) {
				$columns[] = $field['column_name'];
			}
		}
		return $columns;
	}
	
	public function _build_search(Database_Query_Builder_Select & $query, $model, $search_string)
	{
		$table = $model->table_name();
		$columns = empty($this->_search) ? $this->get_column_names() : $this->_search;

		if( ! empty($columns)) {
			// Isolate search from other query conditions
			$query->and_where_open();

			foreach($columns as $column) {
				if (is_array($column)) {
					$query->or_where($column[0].".".$column[1], 'LIKE', $search_string);
				} else {
					$query->or_where($table.".".$column, 'LIKE', $search_string);
				}
			}

			$query->and_where_close();
		}
	}

	public function table_column_widths()
	{
		return array();
	}

	public function tk()
	{
		return $this->_title_key ? $this->_title_key : $this->_primary_key;
	}

	public function set_related($post)
	{
		foreach($this->_has_many as $alias => $details)
		{
			if($related = Arr::get($post, $alias))
			{
				// Many-to-Many
				if($through_table = Arr::get($details, 'through'))
				{
					$current_related_values = Arr::get($post, $alias, array());
					$all_related_values = ORM::factory($details['model'])->find_all()->as_array(NULL, 'id');

					$add = array_intersect($all_related_values, $current_related_values);
					$remove = array_diff($all_related_values, $current_related_values);

					if($add == $remove)
					{
						$remove = array();
					}

					foreach($add as $id) {
						try {
							$this->add($alias, $id);
						} catch(Database_Exception $e){}
					}
					foreach($remove as $id) {
						try {
							$this->remove($alias, $id);
						} catch(Database_Exception $e){}
					}
				}
				else
				{
					// Delete all checked items
					if($delete_ids = Arr::get($related, 'delete'))
					{
						foreach($delete_ids as $related_id)
						{
							ORM::factory($details['model'], $related_id)->delete();
						}
						unset($related['delete']);
					}

					// Update items
					foreach($related as $related_id => $values)
					{
						$related_model = ORM::factory($details['model'], $related_id);
						if($related_model->loaded())
						{
							$related_model->values($values)->save();
						}
					}
				}
			}
		}
	}

	//@todo Refactor this to use some lightweight objects instead of arrays
	public function inputs($labels = TRUE, $include_context = FALSE)
	{
		$inputs = array();
		$columns = $this->list_columns($this->_table_name);
		array_shift($columns);
		$user = Auth::instance()->get_user();

		foreach($columns as $alias => $column)
		{
			$name = $include_context 
				? $this->_object_plural."[$this->id][".$column['column_name'].']'
				: $column['column_name'];
			$label_attr['class'] = ! $column['is_nullable']  ? 'required' : NULL;
			$labels
				? $label = Form::label($name, UTF8::ucwords($alias), $label_attr)
				: $label = $name;
			
			$value = $this->{$column['column_name']};
			$meta = Arr::get($this->_meta, $name, array());
			$attributes = Arr::get($meta, 'attributes', array());

			if(Arr::get($meta, 'hidden'))
				break;

			$column_name = $column['column_name'];
			
			switch($column['data_type'])
			{
				case 'int unsigned':
				case 'int' && (substr($column_name, strlen($column_name)-3, strlen($column_name)) == '_id'):
					if($column['key'])
					{
						$foreign_model_name = str_replace('_id', '', $column['column_name']);
						foreach($this->_belongs_to as $alias => $details)
						{
							# Automatically set the member_id for logged in users
							# [!!] Possible security issue here, should be enforced in controller and not
							#	entered as a hidden field.  Works for now using approval system.
							if( ! $user->has_role('admin')
							AND $foreign_model_name == 'member')
							{
								$inputs[$label] = Form::hidden($name, $user->member->id, $attributes);
							}
							else
							{
								if($details['model'] == $foreign_model_name AND ! $include_context)
								{
									$foreign_model = ORM::factory($foreign_model_name);
									if( ! $options = $foreign_model->select_array()) {
										$options = $foreign_model->find_all()->as_array('id',$foreign_model->tk());
									}

									if($column['is_nullable'] !== FALSE)
									{
										Arr::unshift($options, '', '-- '.__('None'));
									}
									$inputs[$label] = Form::select($name, $options, $value, $attributes);
								}
							}
						}
					}
					break;

				case 'tinyint':
					$inputs[$label] = Form::checkbox($name, 1, (bool)$value, $attributes);
					break;

				case 'text':
					$inputs[$label] = Form::textarea($name, $value, $attributes);
					break;
				
				case 'date':
					$inputs[$label] = Form::input($name, $value, array_merge($attributes, array('class'=>'datepicker')));
					break;

				default:
					if($choices = Arr::get($meta, 'enum'))
					{
						if($column['is_nullable'])
							Arr::unshift($choices, '', '-- '.__('None'));
						$inputs[$label] = Form::select($name, $choices, $value, $attributes);
						break;
					}
					$inputs[$label] = Form::input($name, $value, $attributes);
			}

			foreach($this->_has_many as $alias => $details)
			{
				$label = Form::label($alias, UTF8::ucwords($alias));
				$related_model_name = Arr::get($details, 'model');
				$related_model = ORM::factory($related_model_name);
				if($through_table_name = Arr::get($details, 'through'))
				{
					//@todo Make this not rely on id as only type of primary_key
					$choices = $related_model->find_all()->as_array('id', $related_model->tk());
					$selected = $this->$alias->find_all()->as_array(NULL, 'id');
					$inputs[$label] = Form::select($alias.'[]', $choices, $selected, array('multiple'=>'multiple'));
				}
				else
				{
					// form names as model[id][column]
					// request::factory to action_edit of resource and return result
					$select_columns = $related_model->table_columns();
					if(array_key_exists($details['foreign_key'], $select_columns))
					{
						unset($select_columns[$details['foreign_key']]);
					}
					$records = $this->$alias->find_all();
					$table_columns = array_keys($select_columns);
					array_shift($table_columns);
					$_object_plural = Inflector::plural($related_model_name);

					$inputs[$label] = View::factory('admin/scaffold/add-edit/has-many', array(
						'_object_plural' => $_object_plural,
						'table_columns' => $table_columns,
						'records' => $records,
					));
				}
			}
		}
		return $inputs;
	}

	public function install()
	{
		if( ! $file = Kohana::find_file(NULL, $this->_object_name, 'sql'))
			throw new Kohana_Exception('Install script does not exist for model :model', array(':model'=>$this->_object_name));

		$sql = file_get_contents($file);

		return DB::query(NULL, $sql)
			->execute();
	}
	
}
