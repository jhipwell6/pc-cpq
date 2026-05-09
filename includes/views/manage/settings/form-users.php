<?php
$Workspace_Users = isset( $Workspace_Users ) ? $Workspace_Users : PC_CPQ()->Workspace_Users();
$users = $Workspace_Users->get_workspace_users();
$role_options = $Workspace_Users->get_role_options();
$seat_limit = $Workspace_Users->get_seat_limit();
$seat_count = $Workspace_Users->get_workspace_user_count();
$remaining_seats = $Workspace_Users->get_remaining_seats();
$has_available_seat = $Workspace_Users->has_available_seat();
?>
<div id="edit-settings-users">
	<div class="row">
		<div class="col-md-8">
			<?php if ( ! empty( $message ) ) : ?>
			<div class="alert alert-success">
				<?php echo esc_html( $message ); ?>
			</div>
			<?php endif; ?>
			<div class="card">
				<div class="card-header">
					<h3 class="card-title">Workspace Users</h3>
					<div class="card-tools">
						<span class="badge badge-<?php echo $remaining_seats > 0 ? 'primary' : 'warning'; ?>">
							<?php echo esc_html( $seat_count . ' of ' . $seat_limit . ' seats used' ); ?>
						</span>
					</div>
				</div>
				<div class="card-body p-0">
					<?php if ( empty( $users ) ) : ?>
					<div class="p-3 text-muted">No workspace users have been added yet.</div>
					<?php else : ?>
					<table class="table table-striped mb-0">
						<thead>
							<tr>
								<th>User</th>
								<th>Email</th>
								<th style="width: 220px;">Role</th>
								<th style="width: 120px;"></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $users as $workspace_user ) : ?>
							<tr>
								<td>
									<strong><?php echo esc_html( $workspace_user['name'] ? $workspace_user['name'] : $workspace_user['email'] ); ?></strong>
									<?php if ( $workspace_user['is_current_user'] ) : ?>
									<span class="badge badge-light ml-1">You</span>
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( $workspace_user['email'] ); ?></td>
								<td>
									<?php if ( $workspace_user['can_edit'] ) : ?>
									<form action="" method="post" class="js-update-workspace-user-role-form mb-0">
										<input type="hidden" name="user_id" value="<?php echo esc_attr( $workspace_user['id'] ); ?>">
										<div class="input-group input-group-sm">
											<select name="role" class="form-control">
												<?php foreach ( $role_options as $role => $label ) : ?>
												<option value="<?php echo esc_attr( $role ); ?>"<?php selected( $workspace_user['role'], $role ); ?>><?php echo esc_html( $label ); ?></option>
												<?php endforeach; ?>
											</select>
											<div class="input-group-append">
												<button type="submit" class="btn btn-outline-primary">Save</button>
											</div>
										</div>
										<?php wp_nonce_field( 'update_workspace_user_role', 'update_workspace_user_role_nonce' ); ?>
									</form>
									<?php else : ?>
									<span><?php echo esc_html( $workspace_user['role_label'] ); ?></span>
									<?php endif; ?>
								</td>
								<td class="text-right">
									<?php if ( $workspace_user['can_remove'] ) : ?>
									<form action="" method="post" class="js-remove-workspace-user-form d-inline">
										<input type="hidden" name="user_id" value="<?php echo esc_attr( $workspace_user['id'] ); ?>">
										<?php wp_nonce_field( 'remove_workspace_user', 'remove_workspace_user_nonce' ); ?>
										<button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
									</form>
									<?php else : ?>
									<span class="text-muted small">Managed elsewhere</span>
									<?php endif; ?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-header">
					<h3 class="card-title">Add User</h3>
				</div>
				<div class="card-body">
					<p class="text-muted">Your workspace currently includes up to <?php echo esc_html( $seat_limit ); ?> users. Add someone by email and choose whether they should manage the workspace or just quotes and customers.</p>
					<?php if ( ! $has_available_seat ) : ?>
					<div class="alert alert-warning">
						All seats are currently in use. Remove a user before adding another.
					</div>
					<?php endif; ?>
					<form action="" method="post" class="js-add-workspace-user-form">
						<div class="form-group">
							<label for="workspace-user-first-name">First Name</label>
							<input type="text" id="workspace-user-first-name" name="first_name" class="form-control" <?php disabled( ! $has_available_seat ); ?>>
						</div>
						<div class="form-group">
							<label for="workspace-user-last-name">Last Name</label>
							<input type="text" id="workspace-user-last-name" name="last_name" class="form-control" <?php disabled( ! $has_available_seat ); ?>>
						</div>
						<div class="form-group">
							<label for="workspace-user-email">Email</label>
							<input type="email" id="workspace-user-email" name="email" class="form-control" required <?php disabled( ! $has_available_seat ); ?>>
						</div>
						<div class="form-group">
							<label for="workspace-user-role">Role</label>
							<select id="workspace-user-role" name="role" class="form-control" <?php disabled( ! $has_available_seat ); ?>>
								<?php foreach ( $role_options as $role => $label ) : ?>
								<option value="<?php echo esc_attr( $role ); ?>"><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<?php wp_nonce_field( 'add_workspace_user', 'add_workspace_user_nonce' ); ?>
						<button type="submit" class="btn btn-primary float-right" <?php disabled( ! $has_available_seat ); ?>>Add User</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
