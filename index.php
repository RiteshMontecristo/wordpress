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
    <main id="main" class="site-main" role="main">
        <header>
            <h1>Blogs</h1>
            <p>Welcome to the Montecristo Jewellers Blog, your ultimate destination for insights, tips, and updates on luxury jewelry and fine timepieces. Whether you're exploring our signature collection, looking for expert advice on choosing the perfect gift, or staying updated on new releases, you've come to the right place.</p>
        </header>
        <div class="col-full">
            <?php
            const POSTS_PER_PAGE = 7;
            $args = array(
                'post_type' => 'post',
                'posts_per_page' => POSTS_PER_PAGE
            );

            $blog_query = new WP_Query($args);
            if ($blog_query->have_posts()) :
                $total_posts = $blog_query->found_posts;
                echo "<section class='blog-container'>";
                while ($blog_query->have_posts()) : $blog_query->the_post();
            ?>

                    <article class="blog-article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

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

                            <a href="<?php echo get_permalink() ?>" class="btn">Read more</a>
                        </div>

                        <!-- Display Featured Image -->
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="post-thumbnail blog-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium');
                                    ?>
                                </a>
                            </div>
                        <?php endif; ?>

                    </article>

            <?php

                endwhile;
                wp_reset_postdata();
                echo "</section>";
                if ($total_posts > POSTS_PER_PAGE) {
                    echo "<button class='load-more-blogs btn' id='loadMoreBlogs'>Load More </button>";
                } else {
                }
            else :
                echo '<p>No blog has been posted yet </p>';

            endif;
            ?>
        </div>
    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
