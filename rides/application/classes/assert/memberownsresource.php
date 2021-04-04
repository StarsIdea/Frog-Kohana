<?php

class Assert_MemberOwnsResource implements Acl_Assert_Interface {
	
	public function assert(Acl $acl, $role = null, $resource = null, $privilege = null)
	{
		if($role->member->id == $resource->member_id)
		{
			return TRUE;
		}
	}
	
}