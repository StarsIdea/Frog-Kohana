<div class="search">
<?php echo  Form::open(Request::initial()->uri().Url::query(), array('id' => 'site-search', 'method' => 'get')) ?>
	<input style="width: 270px" name="q" type="text" placeholder="Search" value="<?php echo  Html::chars(Arr::get($_GET, 'q'))?>"/>
	<input type="submit" value="Go"/>

	<?if(isset($_GET['q']) AND ! empty($_GET['q'])):?>
	<span class="site-search-results">
		<?php echo  '&nbsp;'.$total_filtered_items .'&nbsp;'. ($total_filtered_items == 1 ? 'result' : 'results') . ''?>
		( <?php echo  Html::anchor(Request::initial()->uri(), $total_items.' total') ?> )
	</span>
	<?endif?>
<?php echo  Form::close() ?>
</div>
