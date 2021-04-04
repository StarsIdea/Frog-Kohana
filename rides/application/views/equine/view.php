<h2 class="print-only">Horses</h2>
<h2><?php echo $equine['name']?></h2>
<span class="meta"><?php echo $details['owner'] ?></span>

<p>
<strong>YTD Miles:</strong> <?php echo Arr::get($details,'YTD_mileage')?><br/>
<strong>Lifetime Miles:</strong> <?php echo Arr::get($details,'lifetime_mileage')?><br/>
<strong>Sex:</strong> <?php echo $details['sex']?><br/>
<strong>Foal Date:</strong> <?php echo $details['foal_date']?><br/>
<strong>Breed:</strong> <?php echo $details['breed']?><br/>
<strong>Color:</strong> <?php echo $details['color']?><br/>
</p>

<div>
<h3>Ridden By</h3>
<?php if($ridden_by): ?>
	<?php echo  Table::factory(NULL, $ridden_by)
		->set_column_titles(Table::AUTO)
		->render() ?>
<?php else: ?>
	No members have ridden this equine.
<?php endif ?>
</div>


<div id='rides'>
<h3>Rides</h3>
<?php if($rides_by_type): ?>
	<?php foreach($rides_by_type as $type => $results): ?>
		<h4><?php echo $type?></h4>
		<?php echo  Table::factory(NULL, $results)
			->set_column_titles(Table::AUTO)
			->set_attributes('class', 'wide')
			->render() ?>
	<?php endforeach ?>
<?php else: ?>
	No rides yet.
<?php endif ?>
</div>
