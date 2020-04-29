<?php
/**
 * Template part for displaying testimonial content
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 * @since Portfolio Way 1.0.0
 */
?>

<?php
$posts_per_page_count = photoway_get_option( 'testimonial_posts_number' );

if( !photoway_get_option( 'disable_testimonial' ) ):
	$testimonial_ids = photoway_get_ids( 'testimonial_page' );

	if( !empty( $testimonial_ids ) && is_array( $testimonial_ids ) && count( $testimonial_ids ) > 0 ):

		$query = new WP_Query( apply_filters( 'photoway_testimonial_args', array( 
			'post_type'      => 'page',
			'post__in'       => $testimonial_ids,
			'posts_per_page' => $posts_per_page_count,
			'orderby'        => 'post__in'
		)));

		if( $query->have_posts() ):
?>
			<section id="block-testimonial" class="block-testimonial">
				<div class="container">
					<div class="testimonial-content-wrap">
						<div class="row">
							<div class="col-lg-4 col-md-5">
								<?php if( photoway_get_option( 'testimonial_section_title' ) ): ?>
									<div class="section-title-group">
										<h2 class="section-title"><?php echo wp_kses_post( photoway_get_option( 'testimonial_section_title' ) ); ?></h2>
									</div>
								<?php endif; ?>
								<div class="controls"></div>
							</div>
							<div class="col-lg-8 col-md-7">
								<div class="content-inner">
									<div class="owl-carousel testimonial-carousel">
										<?php 
											while ( $query->have_posts() ):
												$query->the_post(); 
												$image = photoway_get_thumbnail_url( array(
													'size' => 'thumbnail'
												));
										?>
											    <div class="slide-item">
											    	<article class="testi-content">
											    		<div class="author-content">
										    				<div class="text">
										    					<?php the_content(); 	
										    					?>
										    				</div>
														</div>
											    		<header class="header-content">
											    			<figure class="author">
											    				<img src="<?php echo esc_url( $image ); ?>">
											    			</figure>
											    			<div class="testi-title-wrap">
											    				<h3 class="author-name"><?php the_title(); ?></h3>
											    			</div>
											    		</header>
											    	</article>
												</div>
										<?php
											endwhile; 
											wp_reset_postdata();
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
<?php
		endif;
	endif;
endif;