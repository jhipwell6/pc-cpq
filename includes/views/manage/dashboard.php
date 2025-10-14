<div class="row">
	<div class="col-md-3 col-sm-6 col-12">
		<div class="info-box">
			<span class="info-box-icon bg-success"><i class="fas fa-file-invoice-dollar"></i></span>
			<div class="info-box-content">
                <span class="info-box-text">Leads</span>
                <span class="info-box-number"><?php echo wp_count_posts('lead')->publish; ?></span>
			</div>
			<!-- /.info-box-content -->
		</div>
		<!-- /.info-box -->
		<div class="info-box">
			<span class="info-box-icon bg-info"><i class="fas fa-address-book"></i></span>
			<div class="info-box-content">
                <span class="info-box-text">Customers</span>
                <span class="info-box-number"><?php echo wp_count_posts('customer')->publish; ?></span>
			</div>
			<!-- /.info-box-content -->
		</div>
		<!-- /.info-box -->
	</div>
	<!-- /.col -->
</div>