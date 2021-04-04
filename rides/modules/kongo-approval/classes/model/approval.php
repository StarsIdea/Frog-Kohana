<?php

class Model_Approval extends ORM implements Acl_Resource_Interface {
	
	public function get_resource_id()
	{
		return 'approval';
	}

	public function save(Validation $validation=NULL)
	{
		$datetime = new DateTime;
		$this->created_date = $datetime->format('Y-m-d H:i:s');
		parent::save($validation);
	}

	public function fetch_approvals($limit = NULL, $offset = NULL, $user_id = NULL)
	{
		$query = $this->with('members');
		
		if($user_id)
		{
			$query->where('user_id','=',$user_id);
		}
		else
		{
			$query
				->where('approved_date','IS',NULL)
				->where('rejected_date','IS',NULL);
		}
		
		$limit AND $query->limit($limit);
		$offset AND $query->offset($offset);
		
		return $query
			->order_by('created_date','desc')
			->find_all();
	}

}