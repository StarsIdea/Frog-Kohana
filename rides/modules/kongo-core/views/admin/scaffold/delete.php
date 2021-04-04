<h1>Delete <?php echo  $_model_name ?></h1>
<?php echo  Html::anchor(Route::get('admin')->uri(array('resource'=>$_resource)), 'Back to List') ?>
<p>Are you sure you want to delete record "<?php echo  $record['id'] ?>"?</p>
<?php echo  Form::open(Request::initial()->uri() . Url::query()) ?>
<?php echo  Form::submit('confirm', "Yes, I'm Sure") ?>
<?php echo  Form::close() ?>