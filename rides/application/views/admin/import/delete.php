<h1>Delete <?php echo  $_model_name ?></h1>
<?php echo  Html::anchor(Route::get('admin')->uri(array('resource'=>$_resource)), 'Back to List') ?>
<p>Are you sure you want to delete import <strong>"<?php echo  $record['name'] ?>"</strong>?</p>
<p>This will delete:
	<ul class="delete">
		<li>1 ride (<?php echo $ride['name']?>)</li>
		<li><?php echo  $count ?> event_results</li>
	</ul>
</p>
<?php echo  Form::open(Request::initial()->uri() . Url::query()) ?>
<?php echo  Form::submit('confirm', "Yes, I'm Sure") ?>
<?php echo  Form::close() ?>
