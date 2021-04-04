<table>
	<thead>
		<tr>
			<?php foreach($table_columns as $column): ?>
				<th><?php echo  Helper_Table::get_sortable_header($column) ?></th>
			<?php endforeach ?>
			<th>Delete?</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($records as $record): ?>
		<tr>
			<?php foreach($record->inputs(FALSE, TRUE) as $column_name => $input): ?>
			<?php //if( ! in_array($column_name, $hidden)): ?>
			<td><?php echo  $input ?></td>
			<?php //endif ?>
			<?php endforeach ?>
			<td><?php echo Form::checkbox($_object_plural.'[delete][]', $record->id)?></td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
<!--<div class="controls-container">
<a class="control" href="<?//=Route::url('admin', array('resource'=>$_resource,'action'=>'add'))?>">Add</a>
</div>-->
