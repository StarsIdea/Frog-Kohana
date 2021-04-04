<?php

class Assert_UserMember implements Acl_Assert_Interface {
	
	public function assert(Acl $acl, $role = null, $resource = null, $privilege = null)
	{
		if($privilege == 'edit' AND $role->member->id == $resource->id)
		{
			return TRUE;
		}
	}
	
}