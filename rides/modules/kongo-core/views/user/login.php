<div id="single-form">
<h1>Login</h1>
<?php include Kohana::find_file('views','errors') ?>
<?php echo  Form::open(Request::initial()->uri()) ?>
<dl>
	<dt><?php echo  Form::label('username') ?></dt>
	<dd><?php echo  Form::input('username', Arr::get($values, 'username')) ?></dd>
	
	<dt><?php echo  Form::label('password') ?></dt>
	<dd><?php echo  Form::password('password') ?></dd>
</dl>
<div class="controls-container">
	<?php echo  Form::submit('login', 'Login') ?>
</div>
</div>
<?php echo  Form::close() ?>