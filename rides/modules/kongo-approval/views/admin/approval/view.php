<?php if(Auth::instance()->get_user()->has_role('admin')): ?>
	<h1>Approve <?php echo $approval->model_name?> <?php echo $approval->action?> made by
	<a href="<?php echo Route::url('admin',array('resource'=>'member','action'=>'edit','id'=>$member->id))?>"><?php echo  $member->first_name ?> <?php echo  $member->last_name ?></a>
	on <?php echo $approval->created_date?>?
	</h1>
	<br/>
<?php else: ?>
	<h1><?php echo $approval->model_name?> "<?php echo $approval->action?>" submitted for approval on <?php echo $approval->created_date?></h1>
<?php endif ?>

<?php if($modified): ?>
<table class="approval">
	<thead>
		<tr>
			<th>Column</th>
			<?if($original):?><th>Original</th><?endif?>
			<th><?php echo  $original ? 'Modified' : 'New' ?></th>
		</tr>
	</thead>
<?php foreach($modified->as_array() as $key => $val): ?>
	<?php if($val != NULL): ?>
	<tr>
		<td class="key"><strong><?php echo $key?>:</strong></td>
		<?if($original):?><td><?php echo $original->$key?></td><?endif?>
		<td<?php echo ($original->$key != $val)?' style="color:green;"':''?>><?php echo $val?></td>
	</tr>
	<?php endif ?>
<?php endforeach ?>
</table>
<?php endif ?>

<?php if( ! Auth::instance()->get_user()->has_role('admin')): ?>
	<h2>Status:
	<?php if($approval->approved_date): ?>
		<span style="color: green">Approved</span>
	<?php elseif($approval->rejected_date): ?>
		<span style="color: red">Rejected</span>
	<?php else: ?>
		Pending
	<?php endif ?>
	</h2>
	<?php if( ! empty($approval->comment) AND ($approval->approved_date OR $approval->rejected_date)): ?>
		"<?php echo  $approval->comment ?>" - Admin
	<?php endif ?>
<?php endif ?>

<?php if(Auth::instance()->get_user()->has_role('admin')): ?>
	<?php echo  Form::open(Request::initial()->uri()) ?>
	<h3><?php echo  Form::label('comment', 'Comment') ?></h3>
	<?php echo  Form::textarea('comment') ?>

	<div class="controls-container">
		<?php echo  Form::submit('approve', 'Approve') ?> or <a href="<?php echo Route::url('admin')?>">Cancel</a>
		<span class="right">
		<?php echo  Form::submit('reject', 'Reject') ?>
		</span>
	</div>
	<?php echo  Form::close() ?>
<?php endif ?>