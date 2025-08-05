<?php

/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package storefront
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <section id="image-carousel" class="splide" aria-label="Beautiful Images">
            <div class="splide__track">
                <ul class="splide__list">
                    <?php
                    $slider_entries = get_post_meta(get_the_ID(), 'mc_repeatable_slider', true);

                    if (!empty($slider_entries)) {
                        foreach ($slider_entries as $entry) {
                            $url = isset($entry['mc_repeatable_url']) ? esc_url($entry['mc_repeatable_url']) : '';

                            // grabbing the imgae url
                            $image = isset($entry['mc_repeatable_image']) ? esc_url($entry['mc_repeatable_image']) : '';
                            $mobileImage = isset($entry['mc_repeatable_mobile_image']) ? esc_url($entry['mc_repeatable_mobile_image']) : '';

                            // Retrieve the attachment ID from the URL for the alt tag
                            $image_id = attachment_url_to_postid($image);

                            // Retrieve the alt text
                            $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);

                            if ($image && $mobileImage) {
                    ?>
                                <li class="splide__slide">
                                    <a href="<?php echo $url; ?>">
                                        <picture>
                                            <source srcset="<?php echo $mobileImage; ?>" media="(max-width: 767px)">
                                            <source srcset="<?php echo $image; ?>" media="(min-width: 767px)">
                                            <img class="exclude-litespeed-lazyload" fetchpriority="high" loading="eager" fetchpriority="high" src="<?php echo $image; ?>" alt="<?php echo $image_alt; ?>" width="2000" height="920" title="<?php echo $image_alt; ?>">
                                        </picture>
                                    </a>
                                </li>
                    <?php }
                        }
                    }

                    ?>

                </ul>
            </div>
        </section>

        <?php
        while (have_posts()) :
            the_post();

            the_content();

        endwhile; // End of the loop.
        ?>

    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
