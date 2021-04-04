<h1>Import Ride Results</h2>
	
<?php if($errors): ?>
<ul class="error">
	<?php foreach($errors as $error): ?>
	<li><?php echo  $error ?></li>
	<?php endforeach ?>
</ul>
<?php endif ?>

<form action="<?php echo  Route::url('admin/import', array('action' => 'new')) ?>" method="post" enctype="multipart/form-data">
	<input type="file" name="xls_spreadsheet" />
	<input type="submit" value="Import">
</form>
