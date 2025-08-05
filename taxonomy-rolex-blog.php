<?php

/**
 * The template for displaying all single posts.
 *
 * @package storefront
 */

get_header(); ?>

<!-- Rolex Header -->
<?php get_template_part('template-parts/rolex-header') ?>


<main id="main" class="site-main rolex-main" role="main">

	<?php
	while (have_posts()) :
		the_post();

		the_content();

	endwhile; // End of the loop.
	?>

</main><!-- #main -->

<!-- Rolex Footer -->
<?php get_template_part('template-parts/rolex-footer') ?>

<?php
get_footer();
