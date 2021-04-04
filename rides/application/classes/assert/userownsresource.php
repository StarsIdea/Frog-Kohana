<?php

class Assert_UserOwnsResource implements Acl_Assert_Interface {
	
	public function assert(Acl $acl, $role = null, $resource = null, $privilege = null)
	{
		if($role->id == $resource->user_id)
		{
			return TRUE;
		}
	}
	
}