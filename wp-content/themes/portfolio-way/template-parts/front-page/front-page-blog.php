<?php
/**
 * Template part for displaying blog items
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 * @since Portfolio Way 1.0.0
 */
?>

<?php
$posts_per_page_count = photoway_get_option( 'blog_posts_number' );
$blog_category_id = photoway_get_option( 'blog_category' );

$args = array(
	'posts_per_page'      => $posts_per_page_count,
	'offset'              => 0,
	'category'            => $blog_category_id,
	'ignore_sticky_posts' => 1
	);
$posts_array = get_posts( $args );

if( count( $posts_array ) > 0 && !photoway_get_option( 'disable_blog' ) ):
	?>
	<section id="block-blog" class="block-blog">
		<div class="container">
			<?php if( photoway_get_option( 'blog_section_title' ) ): ?>
				<div class="section-title-group">
					<h2 class="section-title"><?php echo wp_kses_post( photoway_get_option( 'blog_section_title' ) ); ?></h2>
				</div>
			<?php endif; ?>
			<div class="block-blog-inner">
				<div class="row masonry-wrapper grid-post-wrap">
					<?php
					foreach ( $posts_array as $post ) : setup_postdata( $post );
					$image = photoway_get_thumbnail_url( array(
						'size' => 'photoway-1200-1600'
					));
					?>
					<div class="col-lg-4 col-sm-6 col-12 grid-post">
						<article class="post-container">
							<div class="line-div">
								<figure class="feature-image">
									<a href="<?php the_permalink(); ?>">
										<img src="<?php echo esc_url( $image );?>">
									</a>
								</figure>
								<?php if( !photoway_get_option( 'disable_blog_content' ) ): ?>
								<div class="post-content">
									<?php
										if( 'post' == get_post_type() && !photoway_get_option( 'disable_blog_category_title' ) ):
											$cat = photoway_get_the_category();
											if( $cat ):
												?>
											<span class="entrymeta-cat">
												<?php
												$term_link = get_category_link( $cat[ 0 ]->term_id );
												?>
												<a href="<?php echo esc_url( $term_link ); ?>">
													<?php echo esc_html( $cat[0]->name ); ?>
												</a>
											</span>
											<?php
											endif;
										endif;
									?>
									<?php if( !photoway_get_option( 'disable_blog_post_title' ) ): ?>
										<header class="entry-header">
											<h3 class="entry-title">
												<a href="<?php the_permalink(); ?>">
													<?php the_title(); ?>
												</a>
											</h3>
										</header>
									<?php endif; ?>
								</div>
								<?php endif; ?>
							</div>
						</article>
					</div>
					<?php
						endforeach;
						wp_reset_postdata(); 	
					?>
				</div>
			</div>
		</div>
	</section>
	
<?php endif;