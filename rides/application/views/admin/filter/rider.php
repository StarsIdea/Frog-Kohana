<form action="<?php echo  Url::base() . $uri ?>" method="get">
	<div class="list">Riders:
		<label for="member_id">Id:
			<input type="text" name="member_id" value="<?php echo Arr::get($_GET, 'member_id')?>">
		</label>
		<label for="rider_name">Name: 
			<input type="text" name="rider_name" placeholder='e.g. %Badger%' value="<?php echo Arr::get($_GET, 'rider_name')?>">
		</label>
		<button type="submit">Apply</button>
	</div>
</form>