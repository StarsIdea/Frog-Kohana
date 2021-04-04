<?php if(Breadcrumbs::count() >= Breadcrumbs::$config['min_display']): ?>

<ul class="breadcrumbs">
	<?php foreach($breadcrumbs as $i => $crumb): ?>
	<li>
		<?php if($crumb['uri']): ?>
			<a href="<?php echo $crumb['uri']?>"><?php echo $crumb['title']?></a>
		<?php else: ?>
			<?php echo  $crumb['title'] ?>
		<?php endif ?>
			
		<?php if(count($breadcrumbs) > 1 AND count($breadcrumbs)-1 != $i): ?>
			<?php echo  Breadcrumbs::$config['separator'] ?>
		<?php endif ?>
	</li>
	<?php endforeach ?>
</ul>

<?php endif ?>