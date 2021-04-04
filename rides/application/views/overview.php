<ul id="linklist">
	<li><a href="<?php echo Url::base()?>riders">Riders</a></li>
	<li><a href="<?php echo Url::base()?>horses" class="horse">Horses</a></li>
	<li><a href="<?php echo Url::base()?>events" class="event">Events</a></li>
</ul>

<div class="overview-table">
<h2>Recent Events</h2>
<table>
	<col width="320">
	<col width="100">
	<col width="100">
	<col width="50">
	<thead>
		<tr>
			<th>Name</th>
			<th>Date</th>
			<th>City</th>
			<th>Province</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($recent_events as $event): ?> 
		<tr>
			<td><a href="<?php echo Url::base()?>events/view/<?php echo $event['id']?>"><?php echo $event['name']?></a></td>
			<td><?php echo $event['date']?></td>
			<td><?php echo $event['city']?></td>
			<td><?php echo $event['province']?></td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
</div>
<div style="clear:both;"></div>

<?php if (isset($top_riders_junior)): ?>
<div class="overview-table">
<h4>Top Active Junior Riders</h4>
<table>
	<col width="140">
	<thead>
		<tr>
			<th>Name</th>
			<th>Total Points</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($top_riders_junior as $rider): ?> 
		<tr>
			<td><a href="<?php echo Url::base()?>riders/view/<?php echo $rider['id']?>"><?php echo $rider['name']?></a></td>
			<td><?php echo $rider['total_points']?></td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
</div>
<?php endif ?>

<?php if (isset($top_riders_senior)): ?>
<div class="overview-table">
<h4>Top Active Senior Riders</h4>
<table>
	<col width="140">
	<thead>
		<tr>
			<th>Name</th>
			<th>Total Points</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($top_riders_senior as $rider): ?> 
		<tr>
			<td><a href="<?php echo Url::base()?>riders/view/<?php echo $rider['id']?>"><?php echo $rider['name']?></a></td>
			<td><?php echo $rider['total_points']?></td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
</div>
<div class="overview-table">
<?php endif ?>

<?php if (isset($top_riders_senior)): ?>
<h4>Top Riders</h4>
<table>
	<col width="140">
	<thead>
		<tr>
			<th>Name</th>
			<th>Total Points</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($top_riders_senior as $rider): ?> 
		<tr>
			<td><a href="<?php echo Url::base()?>riders/view/<?php echo $rider['id']?>"><?php echo $rider['name']?></a></td>
			<td><?php echo $rider['total_points']?></td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
</div>
<?php endif ?>
