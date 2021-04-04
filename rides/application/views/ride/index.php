<div id="list-header">
<h2><?php echo $title?></h2>

	<?php echo $search?>
	<div class="clear"></div>
	
	<?php echo isset($alphabet) ? $alphabet : null ?>
</div>
<?php if($results): ?>
	<?php echo  Table::factory('era', $results)
			->set_column_titles(Table::AUTO)
			->set_attributes('class', 'wide')
			->render() ?>
<?php else: ?>
	<p>No rides yet.</p>
<?php endif ?>


<?php echo  $pagination ?>
