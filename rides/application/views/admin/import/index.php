<h1>Imports</h1>
<a href="<?php echo  Route::url('admin/help', array('topic'=>'importer')) ?>">Help</a><br/>
<a class="control" href="<?php echo Route::url('admin/import', array('action'=>'new'))?>">New Import</a>
<?php if( ! empty($imports)): ?>
<table>
	<thead>
		<tr>
			<th>Filename</th>
			<th>Date Created</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($imports as $import): ?>
		<tr>
			<td><strong><?php echo  $import->name ?></strong></td>
			<td><?php echo  $import->date_created?></td>
				
			<td><a href="<?php echo Route::url('admin', array('resource'=>'event_result','action'=>'list')) . Url::query(array('import_id'=>$import->id))?>">View</a>,
				<a href="<?php echo Route::url('admin/import', array('action'=>'delete','id'=>$import->id))?>">Delete</a></td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
<?php else: ?>
<p>No imports yet.</p>
<?php endif ?>
