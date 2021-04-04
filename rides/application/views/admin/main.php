<div class="column resources">
	<h1>Site admin</h1>
	<?php foreach($groups as $group => $resources): ?>
	<table width="100%">
		<thead>
			<th colspan="3"><?php echo  ($group) ?></th>
		</thead>
		<tbody>
		<?php foreach($resources as $i => $resource): ?>
		<tr>
			<td width="80%">
				<?php echo Html::anchor(Route::get('admin')->uri(array('resource' => $resource)), ucwords(Inflector::humanize($resource)))?>
			</td>
			<td width="10%">
				<?php echo Html::anchor(Route::get('admin')->uri(array('resource' => $resource, 'action' => 'add')), 'Add')?>
			</td>
			<td width="10%">
				<?php echo Arr::get($record_counts, $resource)?>
			</td>
		</tr>
		<?php endforeach ?>
		</tbody>
	</table>
	<?php endforeach ?>
</div>

<div class="column wide clearfix">

	<div id="quick-links">
		<h1>Quick Links</h1>
		<ul class="inline">
			<?php foreach($quick_links as $item): ?>
			<li><a href="<?php echo $item['url']?>" class="<?php echo Arr::get($item, 'class')?>"><?php echo $item['label']?></a></li>
			<?php endforeach ?>
		</ul>
	</div>

	<div class='clearfix'>&nbsp;</div>

	<?php if($approvals): ?>
	<div id="approvals">
		<h1>Approvals</h1>
		<?php echo  $approvals ?>
	</div>
	<?php endif ?>

	<div id="imports">
		<?php echo  Request::factory(Route::get('admin/import')->uri())->execute()->body() ?>
	</div>

</div>

<div class="clearfix"></div>

<?/*<ul class="modules">
<?php foreach($resources as $resource): ?>
	<li><a href="<?php echo Route::url('admin', array('resource'=>$resource))?>"><?php echo  ucwords($resource) ?></a></li>
<?php endforeach ?>
</ul>*/
