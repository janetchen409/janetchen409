<?php
/**
 * The template for displaying the footer
 * Contains the closing of the body tag and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 * @since Portfolio Way 1.0.0
 */
?>
	</div> <!-- site main end -->
	<footer id="colophon" class="site-footer site-footer-primary">
		<?php get_template_part('template-parts/footer/top', 'footer'); ?>
		<div class="bottom-footer">
			<?php if( photoway_get_option( 'footer_layout' ) == 'footer_one' ): ?>
				<div class="container">
					<div class="row">
						<div class="col-lg-4 col-md-5">
							<?php if( has_nav_menu( 'social' )): ?>
								<div class="socialgroup">
									<?php echo photoway_get_menu( 'social' ); ?>
								</div>
							<?php endif; ?>
						</div>
						<div class="col-lg-8 col-md-7 align-self-end">
							<div class="desc-menu-wrap">
								<?php get_template_part('template-parts/footer/site', 'info'); ?>
								<?php if ( has_nav_menu( 'footer' ) ): ?>
									<div class="footer-menu">
										<?php echo photoway_get_menu( 'footer' ); ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</footer>
	<?php wp_footer(); ?>
	</body>
</html>