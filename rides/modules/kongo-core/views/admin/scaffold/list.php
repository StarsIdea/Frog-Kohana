<h1><?php echo  $_model_name ?></h1>

<?php if ($records): ?>
	<div class="filters">
		<?php include Kohana::find_file('views', 'admin/search') ?>
		<?php in_array('active', $filters) AND include Kohana::find_file('views', 'admin/filter/active') ?>
		<?php in_array('rider', $filters) AND include Kohana::find_file('views', 'admin/filter/rider') ?>

		<?php if( ! empty($_GET)): ?>
			<a href="<?php echo Url::base() . Request::current()->uri()?>">Clear Filters</a>
		<?php endif ?>

		<?php if($total_filtered_items > 25): ?>
		<?php include Kohana::find_file('views', 'admin/filter/items_per_page') ?>
		<?php endif ?>

		<?php echo  $pagination ?>
		<span id="toggle-whitespace">View Compact</span>
	</div>
	<div class="clear"></div>

	<?php echo Form::open(Request::initial()->uri())?>

	<table class="full-width">
		<?php foreach($table_columns as $column => $details): ?>
			<?php if($column == 'id'): ?>
				<col width="10" class="checkbox">
			<?php elseif(array_key_exists($column, $table_column_widths)): ?>
				<col width="<?php echo  $table_column_widths[$column] ?>">
			<?php else: ?>
				<col>
			<?php endif ?>
		<?php endforeach ?>
		<col width="80" class="actions">

		<thead>
			<tr>
				<?php if(in_array('id', $table_columns) AND Auth::instance()->get_user()->has_role('admin')): ?>
				<th class="checkbox"><?php echo Form::checkbox("columns", 'all')?></th>
				<?php endif ?>
				<th class="actions">Modify</th>

				<?php foreach($table_column_titles as $column): ?>
				<?php if($column != 'id'): ?>
					<th><?php echo  Helper_Table::get_sortable_header($column, Inflector::humanize($column)) ?></th>
				<?php endif ?>
				<?php endforeach ?>
			</tr>
		</thead>

		<tbody>
		<?php foreach($records as $record): ?>
			<tr>
				<?/*** CHECKBOX ***/?>
				<?php if(isset($record['id']) AND in_array('id', $table_columns) AND Auth::instance()->get_user()->has_role('admin')): ?>
				<td class="checkbox"><?php echo Form::checkbox("columns[]", $record['id'])?></td>
				<?php endif ?>
				<?/*** ACTIONS ***/?>
				<td class="actions">
					<a href="<?php echo Route::url('admin',array('resource'=>$_resource,'action'=>'edit','id'=>$record['id'])) . Url::query(array('ref' => Request::initial()->uri() . Url::query()))?>">
						<img alt="edit" src="<?php echo Url::base()?>media/img/edit3.png"/>
					</a>
					<a href="<?php echo Route::url('admin',array('resource'=>$_resource,'action'=>'delete','id'=>$record['id'])) . Url::query(array('ref' => Request::initial()->uri() . Url::query()))?>">
						<img alt="delete" src="<?php echo Url::base()?>media/img/delete3.png"/>
					</a>
				</td>
				<?/*** RECORDS ***/?>
				<?php foreach($table_columns as $column): ?>
				<?php if($column != 'id'): ?>
				<td>
					<?php if($column == 'last_published' AND !empty($record[$column])): ?>
						<?php echo  date('Y-m-d H:i:s', $record[$column]) ?>
					<?php elseif(array_key_exists($column, $foreign_column_titles)):?>
						<a href="<?php echo Route::url('admin', array('resource'=>str_replace('_id','',$column), 'action'=>'edit','id'=>$record[$column]))
							. Url::query(array('ref' => Request::initial()->uri() . Url::query()))?>"><?php echo Arr::get($foreign_column_titles[$column], $record[$column], $record[$column])?></a>
					<?php elseif(strpos($column,'_id') !== FALSE): ?>
						<a href="<?php echo Route::url('admin', array('resource'=>str_replace('_id','',$column), 'action'=>'edit','id'=>$record[$column]))?>"><?php echo $record[$column]?></a>
					<?php else: ?>
						<?php echo  $record[$column] ?>
					<?php endif ?>
				</td>
				<?php endif ?>
				<?php endforeach ?>
			</tr>
		<?php endforeach ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="<?php echo count($table_columns)+1?>">
					<?php echo  $total_filtered_items ?> records
				</td>
			</tr>
			<?php if(Auth::instance()->get_user()->has_role('admin')): ?>
			<tr class="checkbox">
				<td colspan="<?php echo count($table_columns)+1?>">
					<?php echo Form::select('action', array('-- Choose an action', 'delete'=>'Delete'))?>
					<?php echo Form::submit(NULL, 'Go')?>
				</td>
			</tr>
			<?php endif ?>
		</tfoot>
	</table>
	<?php echo  $pagination ?>
	<?php echo Form::close()?>
<?php else: ?>
	<p>No <?php echo  $_resource ?>.</p>
<?php endif ?>

<div class="controls-container">
<a class="control" href="<?php echo Route::url('admin', array('resource'=>$_resource,'action'=>'add'))?>">Add</a>
</div>
