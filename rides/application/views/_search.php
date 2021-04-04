<form method="GET" action="<?php echo Url::base().Request::current()->uri()?>" id="search">
	<input type="text" name="search" placeholder="Search" value="<?php echo Arr::get($_GET, 'search')?>"/>
	<input type="submit" value="Go"/>
	<?php if(Arr::get($_GET, 'search')):?>
		<a href="<?php echo Url::base().Request::current()->uri()?>">Clear</a>
	<?php endif ?>
</form>
