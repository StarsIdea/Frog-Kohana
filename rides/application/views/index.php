<div id="list-header">
	<h2><?php echo $title?></h2>

	<?php echo View::factory('filter/_active')->render()?>
	<div class="clear"></div>

	<?php echo View::factory('_search')->render()?>
	<div class="clear"></div>
	
	<?php echo $alphabet?>
</div>
<?php echo $results ? $results : 'No results yet.'?>

<?php echo $pagination?>