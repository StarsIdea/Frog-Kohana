<h1>Register</h1>
<?php include Kohana::find_file('views','errors') ?>

<?php echo  Form::open(Request::initial()->uri()) ?>
<dl>
	<dt><?php echo  Form::label('username', 'Username') ?></dt>
	<dd><?php echo  Form::input('username', Arr::get($values, 'username')) ?></dd>

	<dt><?php echo  Form::label('password', 'Password') ?></dt>
	<dd><?php echo  Form::password('password', Arr::get($values, 'password')) ?></dd>

	<dt><?php echo  Form::label('password_confirm', 'Confirm Password') ?></dt>
	<dd><?php echo  Form::password('password_confirm', NULL) ?></dd>

	<dt><?php echo  Form::label('email', 'Email') ?></dt>
	<dd><?php echo  Form::input('email', Arr::get($values, 'email')) ?></dd>
<br/>
<?php echo  Form::submit('submit', 'Register') ?>
</dl>
<?php echo  Form::close() ?>