<div id="admin-menu">
	<a href="<?php echo Route::url('admin/dashboard')?>">Dashboard</a>
	<span class="right">
	Logged in as <?php echo  Auth::instance()->get_user()->username ?>.
	<a href="<?php echo Route::url('admin/logout')?>">Logout</a>.
	</span>
</div>
<br/>