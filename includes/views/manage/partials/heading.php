<!-- Content Header (Page header) -->
<section class="content-header">
	<div class="container-fluid">
        <div class="row mb-2">
			<div class="col-sm-6">
				<h1><?php echo $Site->get_page_heading(); ?></h1>
			</div>
			<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="<?php echo $Site->get_manage_page_url(); ?>">Manage</a></li>
					<?php if ( $Site->is_manage_endpoint() ) : ?>
					<li class="breadcrumb-item"><a href="<?php echo $Site->get_current_endpoint_url(); ?>"><?php the_title(); ?></a></li>
					<li class="breadcrumb-item active"><?php echo $Site->get_page_heading(); ?> - <?php echo $Site->get_current_endpoint_var(); ?></li>
					<?php ; else : ?>
					<li class="breadcrumb-item active"><?php the_title(); ?></li>
					<?php endif; ?>
				</ol>
			</div>
        </div>
	</div><!-- /.container-fluid -->
</section>