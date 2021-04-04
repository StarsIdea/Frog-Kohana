<div id="auth-controls">
	<?php if(Auth::instance()->logged_in()): ?>
	<p>Logged in as <?php echo  Auth::instance()->get_user()->username ?> -
		<?/*<a href="<?php echo Route::url('admin', array(
			'resource' => 'user',
			'action' => 'edit',
			'id' => Auth::instance()->get_user()->id,
		))?>">Edit</a>,*/?>
		<a href="<?php echo Route::url('admin/logout', array('action' => 'logout'))?>">Logout</a></p>
	<?php else: ?>
	<p>Welcome guest, <a href="<?php echo Route::url('admin/login')?>">Login</a>,
	<a href="<?php echo Route::url('admin/register')?>">Register</a></p>
	<?php endif ?>
</div>