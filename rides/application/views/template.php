<!--<link href="{{base?>assets/css/reset.css" media="screen, projection" rel="stylesheet" type="text/css" />-->
	<link href="http://enduranceridersofalberta.ca/public/themes/era/css/main.css" media="screen, projection" rel="stylesheet" type="text/css" />
	<link href="<?php echo Url::base()?>media/css/screen.css" media="screen, projection" rel="stylesheet" type="text/css" />
	<link href="<?php echo Url::base()?>media/css/print.css" media="print" rel="stylesheet" type="text/css" />
	<?php echo $header?>
		<div id="dbmenu">
			<ul>
				<?php foreach($navigation as $item): ?>
				<li class="<?php echo $item['classes']?>"><a href="<?php echo Url::base()?><?php echo $item['url']?>"><?php echo $item['title']?></a></li>
				<?php endforeach ?>
			</ul>
		</div>
		<div class="clear"></div>
		
		<?php echo $breadcrumbs?>
		<div class="clear"></div>

		<div id="messagelist">
			<?php echo $messages?>
		</div>
		<div class="clear"></div>

		<div id="content" class="page rides">
			<?php if (isset($message)): ?>
			<h2><?php echo $title?></h2>
			<div><?php echo $message?></div>
			<?php else: ?>
			<?php echo $content?>
			<?php endif ?>
		</div>
		<div class="clear"></div>
		<?php echo $profiler?>
		
		<div id="sidebar"></div>
	</div><!-- end wrap -->
	<?php echo $footer?>
