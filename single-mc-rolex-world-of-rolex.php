<?php

/**
 * The template for displaying world-of-rolex post.
 *
 * @package storefront
 */

get_header(); ?>

<!-- Rolex Header -->
<?php get_template_part('template-parts/rolex-header') ?>


<main id="rolex-page" class="site-main rolex-main f9-background-container" role="main">
	<?php
	while (have_posts()) :
		the_post();

		the_content();

		// Get the pages with the category 'world-of-rolex'
		$world_of_rolex_pages = new WP_Query(array(
			'post_type' => 'mc-rolex',
			'posts_per_page' => -1, // Get all pages
			'tax_query' => array(
				array(
					'taxonomy' => 'rolex_category',
					'field'    => 'slug',
					'terms'    => 'world-of-rolex', // The slug of your category
				),
			),
		));

		if ($world_of_rolex_pages->have_posts()) {
			echo "<section class='blog-container grid-nospace'>";
			while ($world_of_rolex_pages->have_posts()) {
				$world_of_rolex_pages->the_post();

				// Check if ACF is installed and active
				if (function_exists('get_field')) {
					// Check if a specific field exists
					$desktop_url = get_field('desktop_url');
					$mobile_url = get_field('mobile_url');
					$image_alt = get_field('image_alt');
					$heading = get_field('world_of_rolex_title') ? get_field('world_of_rolex_title') : get_field('heading');
					$description = get_field('description');
					$published = get_field('published');

					if ($desktop_url && $mobile_url && $image_alt && $heading && $description && $published) {
	?>

						<div class="blog-push">
							<a aria-label="Learn more about <?php echo $heading ?>" href="<?php esc_url(the_permalink()) ?>">
								<?php echo do_shortcode('[responsive_image desktop_image_url="' . esc_url($desktop_url) . '" mobile_image_url="' . esc_url($mobile_url) . '" alt_text="' . esc_attr($image_alt) . '"]'); ?>
							</a>
							<div class="blog-text">
								<p class="fixed16"><?php echo $published ?></p>
								<h2 class="headline36"><?php echo $heading ?></h2>
								<p class="body20Light"><?php echo $description ?></p>
								<a aria-label="Learn more about <?php echo $heading ?>" href="<?php esc_url(the_permalink()) ?>" class="secondary-cta fixed14">Learn more<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="12px" height="12px" viewBox="0 0 12 12" version="1.1">
										<g>
											<path d="M 9.601562 6 L 8.558594 7.121094 L 3.679688 12 L 2.480469 10.800781 L 7.359375 5.921875 L 2.398438 1.121094 L 3.601562 0 Z M 9.601562 6 "></path>
										</g>
									</svg></a>
							</div>
						</div>
	<?php
					}
				} else {
					echo "<div>ACF is not installed or not active<div>";
				}
				wp_reset_postdata();
			}
		} else {
			echo "<div>No Posts with rolex-blog category</div>";
		}

		echo "</section>";

	endwhile; // End of the loop.
	?>

</main><!-- #main -->

<?php get_template_part('template-parts/keepexploring') ?>
<!-- Rolex Footer -->
<?php get_template_part('template-parts/rolex-footer') ?>

<?php
get_footer();
