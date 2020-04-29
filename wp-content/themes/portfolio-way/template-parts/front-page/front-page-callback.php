<?php
/**
 * Template part for displaying callback content
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 * @since Portfolio Way 1.0.0
 */
?>

<?php
if( !photoway_get_option( 'disable_callback' ) && photoway_get_option( 'callback_page' ) ):
	$callback_id = photoway_get_option( 'callback_page' );
	if( $callback_id ):
		$query = new WP_Query( apply_filters( 'photoway_callback_page_args',  array(
			'post_type'  => 'page',
			'p'          => $callback_id,
	)));
	while( $query->have_posts() ):
		$query->the_post();
		$image = photoway_get_thumbnail_url( array(
			'size' => 'photoway-1920-850'
		));
	?>
	<section id="block-callback" class="block-callback" style="background-image: url(<?php echo esc_url( $image ); ?>)">
		<div class="callback-container">
			<div class="container">
				<div class="row align-items-center">
					<div class="col-12 col-lg-12">
						<div class="callback-content-wrap">
							<div class="callback-content">
								<?php if( !photoway_get_option( 'disable_callback_title' ) ): ?>
									<div class="section-title-group">
										<h2 class="section-title">
											<?php the_title(); ?>
										</h2>
									</div>
								<?php endif;

									$excerpt_length = photoway_get_option( 'callback_excerpt_lenth' );
									if( !photoway_get_option( 'disable_callback_content' ) ){
										photoway_excerpt( $excerpt_length , true );
									}
									
										if( !photoway_get_option( 'disable_callback_button' ) ):
									?>

									<div class="button-container">
										<a href="<?php the_permalink(); ?>" class="button-outline">
											<?php
												echo photoway_get_option( 'callback_button_text' );
											?>
										</a>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="overlay"></div>
		</div>
	</section>
	<?php 
		endwhile;
		wp_reset_postdata();
		endif;
endif;