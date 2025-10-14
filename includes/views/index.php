<div id="page" class="site">
	<div id="content" class="site-content">
		<div id="primary" class="content-area">
			<header class="site-header">
				<?php echo PC_CPQ()->view( 'partials/navigation', $data ); ?>
			</header>
			<main id="main" class="site-main" role="main">
				<div class="container d-flex mih-inherit">
					<article>
						<div class="entry-content">
							<?php the_content(); ?>
						</div>
					</article>
				</div><!-- .container -->
			</main><!-- #main -->
			<?php echo PC_CPQ()->view( 'partials/footer', $data ); ?>
		</div><!-- #primary -->
	</div><!-- #content -->
</div><!-- #page -->