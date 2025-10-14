<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
	<!-- Brand Logo -->
	<a href="<?php echo $Site->get_manage_page_url(); ?>" class="brand-link">
		<img src="<?php echo PC_CPQ()->plugin_url() . '/assets/img/polycoat_cpq-color_white.svg'; ?>" alt="<?php echo PC_CPQ_NAME; ?> Logo" class="brand-image">
		<!--<span class="brand-text font-weight-light"><?php echo PC_CPQ_NAME; ?></span>-->
	</a>

	<!-- Sidebar -->
	<div class="sidebar">
		<?php // echo SPC()->view( 'manage/partials/sidebar/user', $data ); /* Don't need this right now */ ?>
		<?php // echo SPC()->view( 'manage/partials/sidebar/search', $data ); /* Don't need this right now */ ?>

		<!-- Sidebar Menu -->
		<nav class="mt-2">
			<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
				<li class="nav-item" id="nav-item_dashboard">
					<a href="<?php echo $Site->get_manage_page_url(); ?>" class="nav-link">
						<i class="nav-icon fas fa-tachometer-alt"></i>
						<p>Dashboard</p>
					</a>
				</li>
				<li class="nav-item" id="nav-item_leads">
					<a href="<?php echo $Site->get_leads_page_url(); ?>" class="nav-link">
						<i class="nav-icon fas fa-file-invoice-dollar"></i>
						<p>Leads</p>
					</a>
				</li>
				<li class="nav-item" id="nav-item_customers">
					<a href="<?php echo $Site->get_customers_page_url(); ?>" class="nav-link">
						<i class="nav-icon fas fa-address-book"></i>
						<p>Customers</p>
					</a>
				</li>
				<li class="nav-item" id="nav-item_settings">
					<a href="#" class="nav-link">
						<i class="nav-icon fas fa-cog"></i>
						<p>
							Settings
							<i class="fas fa-angle-left right"></i>
						</p>
					</a>
					<ul class="nav nav-treeview">
						<li class="nav-item">
							<a href="<?php echo $Site->get_settings_page_url( 'price' ); ?>" class="nav-link">
								<i class="far fa-circle nav-icon"></i>
								<p>Price Settings</p>
							</a>
						</li>
						<li class="nav-item">
							<a href="<?php echo $Site->get_settings_page_url( 'quotes' ); ?>" class="nav-link">
								<i class="far fa-circle nav-icon"></i>
								<p>Quote Settings</p>
							</a>
						</li>
						<li class="nav-item">
							<a href="<?php echo $Site->get_settings_page_url( 'plating' ); ?>" class="nav-link">
								<i class="far fa-circle nav-icon"></i>
								<p>Plating Settings</p>
							</a>
						</li>
						<li class="nav-item">
							<a href="<?php echo $Site->get_settings_page_url( 'processes' ); ?>" class="nav-link">
								<i class="far fa-circle nav-icon"></i>
								<p>Process Settings</p>
							</a>
						</li>
						<li class="nav-item">
							<a href="<?php echo $Site->get_settings_page_url( 'templates' ); ?>" class="nav-link">
								<i class="far fa-circle nav-icon"></i>
								<p>Templates</p>
							</a>
						</li>
					</ul>
				</li>
			</ul>
		</nav>
		<!-- /.sidebar-menu -->
	</div>
	<!-- /.sidebar -->
</aside>