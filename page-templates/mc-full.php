<?php

/**
 * Template Name: Full
 * 
 */

get_header(); ?>


<main id="primary" class="site-main">

    <?php
    echo get_the_post_thumbnail();

    $parent_id = wp_get_post_parent_id( get_the_ID() );

    if($parent_id){
        woocommerce_breadcrumb();
    }

    while (have_posts()) :
        the_post();

        the_content();

    endwhile; // End of the loop.
    ?>

</main><!-- #primary -->

<?php
get_footer();
