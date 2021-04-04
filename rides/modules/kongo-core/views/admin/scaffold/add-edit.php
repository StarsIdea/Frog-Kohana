<h1><?php echo  Request::initial()->action() == 'add' ? 'Add' : 'Edit' ?> <?php echo  ucwords(Inflector::humanize($_resource)) ?></h1>

<?php include Kohana::find_file('views','errors') ?>

<?php echo  Form::open(Request::current()->uri() . Url::query()) ?>
<dl>
	<?php foreach($model->inputs() as $label => $input): ?>
	<?php if(strstr($input, 'hidden') == FALSE): ?>
	<dt><?php echo  ucwords(Inflector::humanize($label)) ?></dt>
	<?php endif ?>
	<dd><?php echo  $input ?></dd>
	<?php endforeach ?>
</dl>
	<?php include Kohana::find_file('views/admin', 'controls') ?>

<?php echo  Form::close() ?>