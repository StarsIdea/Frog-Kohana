<!DOCTYPE html>
<html lang="en">
<head profile="http://gmpg.org/xfn/11">
	<title><?php echo  $title ?></title>
	<?php foreach($styles as $file => $type) { echo HTML::style($file, array('media' => $type)), "\n"; }?>
	<?php if(isset($scripts_top)) { foreach($scripts_top as $file) { echo HTML::script($file), "\n"; } }?>
</head>
<body>
	<div id="wrapper">
		<div id="container">

			<div id="breadcrumbs">
			<?php echo  Breadcrumbs::render() ?>
			<?php echo View::factory('admin/auth/controls') ?>
			</div>

			<?php if(isset($messages)): ?>
			<div id="messagelist">
				<?php echo  $messages ?>
			</div>
			<?php endif ?>
			
			<div id="content" class="clearfix">
				<?php echo  $content ?>
			</div>
		</div>
	</div>

	<?php if(Kohana::$environment == Kohana::DEVELOPMENT): ?>
	<?php echo  View::factory('profiler/stats') ?>
	<?php endif ?>
	
	<?php if(isset($scripts_bottom)) { foreach($scripts_bottom as $file) { echo HTML::script($file), "\n"; } }?>
</body>
</html>