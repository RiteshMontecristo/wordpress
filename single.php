<?php

/**
 * The template for displaying all single posts.
 *
 * @package storefront
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <header>
            <h1><?php the_title() ?></h1>
            <span>Posted on <?php echo get_the_date(); ?>.</span>
        </header>
        <img class="thumbnail-banner" src="<?php the_post_thumbnail_url() ?>" alt="<?php the_title() ?>">
        <?php
        // Display Storefront breadcrumbs at the top of the single post page
        woocommerce_breadcrumb();
        ?>
        <?php
        while (have_posts()) :
            the_post();

        ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <?php
                the_content();
                ?>
            </article><!-- #post-## -->

        <?php

        endwhile; // End of the loop.
        ?>
        <div class="col-full">
            <div class="share-blog">
                <span>Share this Blog:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo get_permalink(); ?>" target="_blank">
                    <svg fill="#1778f2" width="32" height="32" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                        <path d="M12 2C6.5 2 2 6.5 2 12c0 5 3.7 9.1 8.4 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.3v7C18.3 21.1 22 17 22 12c0-5.5-4.5-10-10-10z"></path>
                    </svg>
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo get_permalink(); ?>&text=<?php echo get_the_title(); ?>" target="_blank">
                    <svg fill="#1da1f2" width="32" height="32" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                        <path d="M22.23,5.924c-0.736,0.326-1.527,0.547-2.357,0.646c0.847-0.508,1.498-1.312,1.804-2.27 c-0.793,0.47-1.671,0.812-2.606,0.996C18.324,4.498,17.257,4,16.077,4c-2.266,0-4.103,1.837-4.103,4.103 c0,0.322,0.036,0.635,0.106,0.935C8.67,8.867,5.647,7.234,3.623,4.751C3.27,5.357,3.067,6.062,3.067,6.814 c0,1.424,0.724,2.679,1.825,3.415c-0.673-0.021-1.305-0.206-1.859-0.513c0,0.017,0,0.034,0,0.052c0,1.988,1.414,3.647,3.292,4.023 c-0.344,0.094-0.707,0.144-1.081,0.144c-0.264,0-0.521-0.026-0.772-0.074c0.522,1.63,2.038,2.816,3.833,2.85 c-1.404,1.1-3.174,1.756-5.096,1.756c-0.331,0-0.658-0.019-0.979-0.057c1.816,1.164,3.973,1.843,6.29,1.843 c7.547,0,11.675-6.252,11.675-11.675c0-0.178-0.004-0.355-0.012-0.531C20.985,7.47,21.68,6.747,22.23,5.924z"></path>
                    </svg>
                </a>
                <a href="https://www.pinterest.com/pin/create/button/?url=<?php echo get_permalink(); ?>&media=<?php echo wp_get_attachment_url(get_post_thumbnail_id()); ?>&description=<?php echo get_the_title(); ?>" target="_blank">
                    <svg fill="#e60122" width="32" height="32" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                        <path d="M12.289,2C6.617,2,3.606,5.648,3.606,9.622c0,1.846,1.025,4.146,2.666,4.878c0.25,0.111,0.381,0.063,0.439-0.169 c0.044-0.175,0.267-1.029,0.365-1.428c0.032-0.128,0.017-0.237-0.091-0.362C6.445,11.911,6.01,10.75,6.01,9.668 c0-2.777,2.194-5.464,5.933-5.464c3.23,0,5.49,2.108,5.49,5.122c0,3.407-1.794,5.768-4.13,5.768c-1.291,0-2.257-1.021-1.948-2.277 c0.372-1.495,1.089-3.112,1.089-4.191c0-0.967-0.542-1.775-1.663-1.775c-1.319,0-2.379,1.309-2.379,3.059 c0,1.115,0.394,1.869,0.394,1.869s-1.302,5.279-1.54,6.261c-0.405,1.666,0.053,4.368,0.094,4.604 c0.021,0.126,0.167,0.169,0.25,0.063c0.129-0.165,1.699-2.419,2.142-4.051c0.158-0.59,0.817-2.995,0.817-2.995 c0.43,0.784,1.681,1.446,3.013,1.446c3.963,0,6.822-3.494,6.822-7.833C20.394,5.112,16.849,2,12.289,2"></path>
                    </svg>
                </a>
            </div>

            <div class="related-blogs-container">
                <h2>What to read next </h2>

                <div class="related-blogs">
                    <?php
                    $current_post_id = get_the_ID(); // Get the current post ID

                    $args = array(
                        'post_type' => 'post',
                        'posts_per_page' => 3,
                        'post_status' => 'publish',
                        'post__not_in' => array($current_post_id),
                        'orderby' => 'rand'
                    );

                    $related_posts = new WP_Query($args);


                    if ($related_posts->have_posts()) {
                        while ($related_posts->have_posts()) {
                            $related_posts->the_post();
                    ?>
                            <a href="<?php echo esc_url(get_permalink()) ?>">
                                <img src="<?php echo esc_url(get_the_post_thumbnail_url()); ?>" alt="test" />
                                <span> <?php echo get_the_date(); ?></span>
                                <h3><?php the_title(); ?></h3>
                            </a>
                    <?php
                        }
                    }
                    ?>

                </div>
            </div>

        </div>
    </main><!-- #main -->
</div><!-- #primary -->

</div>
<?php
do_action('storefront_sidebar');
get_footer();
