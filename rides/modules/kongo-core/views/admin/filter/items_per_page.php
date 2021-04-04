<?php
$items_per_page_list = array(25=>25,50=>50,100=>100,150=>150);
?>
<div class="list">Items Per Page: <ul>
	<?php foreach($items_per_page_list as $value => $label): ?>
	<?php if($items_per_page == $value OR ($value == 'all' AND $total_items == $items_per_page)): ?>
	<li><strong><?php echo $label?></strong></li>
	<?php else: ?>
	<li><a href="<?php echo Url::query(array('items_per_page'=>$value))?>"><?php echo $label?></a></li>
	<?php endif ?>
	<?php endforeach ?>
	<li><a href="<?php echo Url::query(array('show_all'=>true))?>">Show All</a></li>
</ul>
</div>
