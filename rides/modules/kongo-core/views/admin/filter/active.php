<?php 
$options = array(
	array('url'=>Url::base().Request::initial()->uri(), 'label'=>'All', 'value'=>-1),
	array('url'=>Url::query(array('active'=>1)), 'label'=>'Active', 'value'=>1),
	array('url'=>Url::query(array('active'=>0)), 'label'=>'Inactive', 'value'=>0),
);
?>
<div class="list">Active: 
<ul>
	<?php foreach($options as $option): ?>
		<li>
			<?php if((int)Arr::get($_GET, 'active', -1) === $option['value']): ?>
			<strong><?php echo $option['label']?></strong>
			<?php else: ?>
			<a href="<?php echo $option['url']?>"><?php echo $option['label']?></a>
			<?php endif ?>
		</li>
	<?php endforeach ?>
</ul>
</div>