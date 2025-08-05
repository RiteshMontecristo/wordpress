<?php

/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package storefront
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main col-full" role="main">

        <h1><?php single_cat_title(); ?></h1>
        <p><?php echo category_description(); ?></p>
        <?php

        if (have_posts()) :

            while (have_posts()) :
                the_post();
        ?>

                <article class="blog-article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                    <!-- Display Featured Image -->
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="post-thumbnail blog-thumbnail">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium');
                                ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="blog-content">
                        <!-- Display Post Title -->
                        <h2 class="post-title">
                            <?php the_title(); ?>
                        </h2>
                        <!-- Display Excerpt or Content -->
                        <div class="post-excerpt">
                            <?php the_excerpt();
                            ?>
                        </div>
                    </div>
                </article>

        <?php
            endwhile;

        else :
            echo '<p>No blog has been posted yet </p>';

        endif;
        ?>

    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
