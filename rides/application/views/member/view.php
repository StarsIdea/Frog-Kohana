<h2 class="print-only">Riders</h2>
<h2><?php echo $member['first_name']?> <?php echo Arr::get($member,'last_name')?></h2>
<span class="meta"><?php echo Arr::get($member, 'address')?></span>

<p>
<strong>ERA #:</strong> <?php echo $member['id']?><br/>
<strong>YTD Miles:</strong> <?php echo Arr::get($member,'YTD_mileage')?><br/>
<strong>Lifetime Miles:</strong> <?php echo Arr::get($member,'lifetime_mileage')?><br/>
<strong>Member Type:</strong> <?php echo Arr::get($member,'member_type')?><br/>
<strong>Status:</strong> <?php echo Arr::get($member,'active')?><br/>
</p>

<div id='equines-ridden'>
<h3>Horses Ridden</h3>
<?php if($equines): ?>
	<?php echo  Table::factory(NULL, $equines)
		->set_column_titles(Table::AUTO)
		->render() ?>
<?php else: ?>
No horses ridden yet.
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
