<?php defined('SYSPATH') or die('No direct access allowed.');

class Model_User extends Model_Auth_User implements Acl_Resource_Interface, Acl_Role_Interface {
	
	protected $_belongs_to = array(
		'member' => array(),
	);

	public function has_role($name)
	{
		return in_array($name, $this->roles->find_all()->as_array('id','name'));
	}	
	
	public function get_resource_id()
	{
		return 'user';
	}
	
	public function get_role_id()
	{
		return $this->roles->order_by('id','DESC')->find()->name;
	}

}