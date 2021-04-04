<div class="controls-container">
	<?php echo  Form::submit('save[list]', 'Save Changes') ?>
	<?php echo  Form::submit('save[edit]', 'Save and Continue Editing') ?>
	or <a href="<?php echo isset($_resource) ? Route::url(Route::name(Request::initial()->route()), array('resource'=>$_resource)) : Request::initial()->referrer()?>">Cancel</a>

	<span class="right">
	<?php if(Request::initial()->action() == 'edit' AND isset($is_publishable) AND $is_publishable): ?>
		<?php if(Arr::get($values, 'published') == 0): ?>
		<?php echo  Form::submit('publish', 'Publish', array('id'=>'publish')) ?>
		<?php else: ?>
		<?php echo  Form::submit('unpublish', 'Unpublish', array('id'=>'unpublish')) ?>
		<?php endif ?>
	<?php endif ?>
	<a class="control" href="<?php echo Route::url('admin',array('resource'=>$_resource,'action'=>'delete','id'=>Request::initial()->param('id')))?>">Delete</a>
	</span>
</div>