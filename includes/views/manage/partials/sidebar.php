<?php
$current_page_id = get_queried_object_id();
$current_page_slug = $current_page_id ? get_post_field( 'post_name', $current_page_id ) : '';
$is_dashboard = $Site->is_manage_dashboard();
$is_leads = is_page( $Site->get_leads_page() );
$is_customers = is_page( $Site->get_customers_page() );
$is_reports = $Site->is_manage_reports();
$is_settings = $Site->is_manage_settings();
?>
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
					<a href="<?php echo $Site->get_manage_page_url(); ?>" class="nav-link<?php echo $is_dashboard ? ' active' : ''; ?>">
						<i class="nav-icon fas fa-tachometer-alt"></i>
						<p>Dashboard</p>
					</a>
				</li>
				<li class="nav-item" id="nav-item_leads">
					<a href="<?php echo $Site->get_leads_page_url(); ?>" class="nav-link<?php echo $is_leads ? ' active' : ''; ?>">
						<i class="nav-icon fas fa-file-invoice-dollar"></i>
						<p>Leads</p>
					</a>
				</li>
				<li class="nav-item" id="nav-item_customers">
					<a href="<?php echo $Site->get_customers_page_url(); ?>" class="nav-link<?php echo $is_customers ? ' active' : ''; ?>">
						<i class="nav-icon fas fa-address-book"></i>
						<p>Customers</p>
					</a>
				</li>
				<?php if ( $Site->get_reports_page_url() ) : ?>
				<li class="nav-item" id="nav-item_reports">
					<a href="<?php echo $Site->get_reports_page_url(); ?>" class="nav-link<?php echo $is_reports ? ' active' : ''; ?>">
						<i class="nav-icon fas fa-chart-bar"></i>
						<p>Reports</p>
					</a>
				</li>
				<?php endif; ?>
				<?php if ( PC_CPQ()->User()->can_manage_settings() ) : ?>
				<li class="nav-item has-treeview<?php echo $is_settings ? ' menu-open' : ''; ?>" id="nav-item_settings">
					<a href="#" class="nav-link<?php echo $is_settings ? ' active' : ''; ?>">
						<i class="nav-icon fas fa-cog"></i>
						<p>
							Settings
							<i class="fas fa-angle-left right"></i>
						</p>
					</a>
					<ul class="nav nav-treeview">
						<li class="nav-item" id="nav-item_settings_price">
							<a href="<?php echo $Site->get_settings_page_url( 'price' ); ?>" class="nav-link<?php echo 'price' === $current_page_slug ? ' active' : ''; ?>">
								<i class="fas fa-chevron-right nav-icon text-xs"></i>
								<p>Price Settings</p>
							</a>
						</li>
						<li class="nav-item" id="nav-item_settings_quotes">
							<a href="<?php echo $Site->get_settings_page_url( 'quotes' ); ?>" class="nav-link<?php echo 'quotes' === $current_page_slug ? ' active' : ''; ?>">
								<i class="fas fa-chevron-right nav-icon text-xs"></i>
								<p>Quote Settings</p>
							</a>
						</li>
						<li class="nav-item" id="nav-item_settings_integrations">
							<a href="<?php echo $Site->get_settings_page_url( 'integrations' ); ?>" class="nav-link<?php echo 'integrations' === $current_page_slug ? ' active' : ''; ?>">
								<i class="fas fa-chevron-right nav-icon text-xs"></i>
								<p>Integrations</p>
							</a>
						</li>
						<li class="nav-item" id="nav-item_settings_users">
							<a href="<?php echo $Site->get_settings_page_url( 'users' ); ?>" class="nav-link<?php echo 'users' === $current_page_slug ? ' active' : ''; ?>">
								<i class="fas fa-chevron-right nav-icon text-xs"></i>
								<p>Users</p>
							</a>
						</li>
						<li class="nav-item" id="nav-item_settings_plating">
							<a href="<?php echo $Site->get_settings_page_url( 'plating' ); ?>" class="nav-link<?php echo 'plating' === $current_page_slug ? ' active' : ''; ?>">
								<i class="fas fa-chevron-right nav-icon text-xs"></i>
								<p>Plating Settings</p>
							</a>
						</li>
						<li class="nav-item" id="nav-item_settings_processes">
							<a href="<?php echo $Site->get_settings_page_url( 'processes' ); ?>" class="nav-link<?php echo 'processes' === $current_page_slug ? ' active' : ''; ?>">
								<i class="fas fa-chevron-right nav-icon text-xs"></i>
								<p>Process Settings</p>
							</a>
						</li>
						<li class="nav-item" id="nav-item_settings_fees">
							<a href="<?php echo $Site->get_settings_page_url( 'fees' ); ?>" class="nav-link<?php echo 'fees' === $current_page_slug ? ' active' : ''; ?>">
								<i class="fas fa-chevron-right nav-icon text-xs"></i>
								<p>Fee Settings</p>
							</a>
						</li>
						<li class="nav-item" id="nav-item_settings_templates">
							<a href="<?php echo $Site->get_settings_page_url( 'templates' ); ?>" class="nav-link<?php echo 'templates' === $current_page_slug ? ' active' : ''; ?>">
								<i class="fas fa-chevron-right nav-icon text-xs"></i>
								<p>Templates</p>
							</a>
						</li>
					</ul>
				</li>
				<?php endif; ?>
			</ul>
		</nav>
		<!-- /.sidebar-menu -->
	</div>
	<!-- /.sidebar -->
</aside>
