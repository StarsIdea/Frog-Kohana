<h2 class="print-only">Events</h2>
<h2><?php echo $title?></h2>

<?php if($ride): ?>
<div class="meta">
	<?php echo $ride['date']?>
	<?php echo $ride['city']?>
</div>
<?php endif ?>

<div>
<?php if($results_by_type): ?>
	<?php foreach($results_by_type as $type => $results): ?>
		<h3><?php echo $type?></h3>
		<?php echo  Table::factory(NULL, $results)
			->set_column_titles(Table::AUTO)
			->set_attributes('class', 'wide')
			->render() ?>
	<?php endforeach ?>
<?php else: ?>
	No event results yet.
<?php endif ?>
</div>
