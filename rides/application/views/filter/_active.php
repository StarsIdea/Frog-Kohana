<ul class="filter boolean">
	<?php foreach($filters as $filter): ?>
	<li><a href="<?php echo Url::base().$filter['url']?>" class="<?php echo $filter['class']?>"><?php echo $filter['name']?></a></li>
	<?php endforeach ?>
</ul>
