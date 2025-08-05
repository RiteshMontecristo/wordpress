<?php

/**
 * The template for displaying all single rolex posts.
 *
 * @package storefront
 */

get_header(); ?>

<!-- Rolex Header -->
<?php

get_template_part('template-parts/rolex-header');

global $post;


// Get the categories of the current post by ID
$categories = wp_get_post_terms($post->ID, 'rolex_category');
$family = '';
if (! empty($categories)) {
	foreach ($categories as $category) {
		if (esc_html($category->slug) == "new-watches" || esc_html($category->slug) == "watches") {
			$family = 'data-pagetype="Family"';
		}
	}
}

if (has_term('world-of-rolex', 'rolex_category')) {
	$family = 'data-pagetype="article"';
}


?>

<main id="rolex-page" <?php echo $family ?> class="site-main rolex-main" role="main">

	<?php

	// DISCOVER ROLEX SECTION
	if ($post->post_name == "rolex") {
		get_template_part('template-parts/discover-rolex');
	}

	if (has_term('world-of-rolex', 'rolex_category')) {
		echo '<article class="f9-background-container">';
	}


	// Check if ACF is installed and active
	if (function_exists('get_field')) {

		// THIS IS FOR ROLEX BLOG PAGES
		$heading = get_field('heading');
		$description = get_field('description');
		$description_light = get_field('description_light');
		$published = get_field('published');
		$article_excerpt = get_field('article_excerpt');

		if ($heading && $description && $published) {

			// Display the field value
			echo '<div class="grid-nospace"><div class="article-intro"><h1 class="headline50">' . $heading . '</h1>';
			if ($article_excerpt) {
				echo '<p><span class="body20Bold">' . $article_excerpt . '</span>';
			} else {
				echo '<p><span class="body20Bold">' . $description . '</span>';
				if ($description_light) {
					echo '<span class="body20Light">' . $description_light . '</span>';
				}
			}
			echo '</p><p class="body20Bold">' . $published . '</p></div></div>';
		}
	}
	?>

	<?php
	while (have_posts()) :
		the_post();

		the_content();

	endwhile; // End of the loop.

	// Only for rolex blogs
	if (has_term('world-of-rolex', 'rolex_category')) {
		echo '</article>';
	}


	?>


</main><!-- #main -->

<?php
global $post;

// Dont want the keep exploring section on rolex contact-us form
if ($post->ID !== 6542)	get_template_part('template-parts/keepexploring');

?>
<!-- Rolex Footer -->
<?php get_template_part('template-parts/rolex-footer') ?>

<?php
get_footer();
