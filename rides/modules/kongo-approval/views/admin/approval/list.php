<?php if(count($approvals) > 0): ?>
	<?php if(Auth::instance()->get_user()->has_role('admin')): ?>
		<table>
			<thead>
				<tr>
					<th>Member</th>
					<th>Model</th>
					<th>Action</th>
					<th>Created Date</th>
					<th>Details</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($approvals as $approval): ?>
				<tr>
					<td>
						<?php if($approval->user_id AND $user = ORM::factory('user', $approval->user_id)): ?>
						<a href="<?php echo Route::url('admin', array('resource'=>'member','action'=>'edit','id'=>$user->member->id))?>">
							<?php echo $user->member->first_name?> <?php echo $user->member->last_name?>
						</a>
						<?php endif ?>
					</td>
					<td><?php echo $approval->model_name?></td>
					<td><?php echo $approval->action?></td>
					<td><?php echo $approval->created_date?></td>
					<td>
						<a href="<?php echo Route::url('admin/approval',array('action'=>'view','id'=>$approval->id))?>">View</a>
					</td>
				</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php else: ?>
		<table>
			<thead>
				<tr>
					<th>Model</th>
					<th>Action</th>
					<th>Date Submitted</th>
					<th>Status</th>
					<th>Comment</th>
					<th>Details</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($approvals as $approval): ?>
				<tr>
					<td><?php echo $approval->model_name?></td>
					<td><?php echo $approval->action?></td>
					<td><?php echo $approval->created_date?></td>
					<td>
						<?php if($approval->approved_date): ?>
							Approved
						<?php elseif($approval->rejected_date): ?>
							Rejected
						<?php else: ?>
							Pending
						<?php endif ?>
					</td>
					<td><?php echo $approval->comment?></td>
					<td>
						<a href="<?php echo Route::url('admin/approval',array('action'=>'view','id'=>$approval->id))?>">View</a>
					</td>
				</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php endif ?>
	<?php echo  $pagination ?>
<?php else: ?>
<p>No changes waiting approval.</p>
<?php endif ?>