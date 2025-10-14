<nav class="navbar navbar-static-top">
	<div class="container">
		<div class="nav d-flex align-items-center justify-content-between w-100">
			<div class="nav-brand">
				<a href="<?php echo esc_url( $Site->get_website() ); ?>" class="navbar-brand">
					<?php if ( $Site->has_logo() ) : ?>
					<img src="<?php echo esc_url( $Site->get_logo('url') ); ?>" alt="<?php echo esc_attr( $Site->get_company() ); ?>">
					<?php ; else : ?>
					<span><?php echo $Site->get_company(); ?></span>
					<?php endif; ?>
				</a>
			</div>
			<div class="nav-phone">
				<a href="tel:<?php echo $Site->get_phone(); ?>"><?php echo $Site->get_phone(); ?></a>
			</div>
		</div>
	</div>
</nav>