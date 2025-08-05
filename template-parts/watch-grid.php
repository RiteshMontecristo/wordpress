<?php

// get the url
$url = get_permalink();

// Split the url on "/"
$splitUrlArr = explode('/', $url);

// removing the first 3 and last element
$param = $splitUrlArr[sizeof($splitUrlArr) - 2];

// Get the pages with the category 'world-of-rolex'
$rolex_product = new WP_Query(array(
    'post_type' => 'mc-rolex-product',
    'posts_per_page' => 6, // Get all pages
    'tax_query' => array(
        array(
            'taxonomy' => 'rolex_product_category',
            'field'    => 'slug',
            'terms'    => $param
        ),
    ),
    'orderby' => 'ID',
    'order' => 'asc'
));

if ($rolex_product->have_posts()) {

    $total_product = $rolex_product->found_posts;
    $taxonomy_details = get_term_by('slug', $param, 'rolex_product_category');
    $text = $total_product === 1 ? 'model' : 'A selection of models';

?>
    <div class="grid-nospace watch-grid f9-background-container remove-padding-top">

        <div data-term="<?php echo $param ?>" id="watchesContainer" class="watches-container">
            <?php
            while ($rolex_product->have_posts()) {
                $rolex_product->the_post();
                // grabbing the ACF value
                $model_name = get_field('model_name');
                $spec_model_case = get_field('spec_model_case');
                $rmc_number = get_field('rmc_number');
                $spec_material = get_field('spec_material');
                $family_handle = get_field('family_handle');
                $alt = "Rolex " . $family_handle . " in " .  $spec_material . " " .  $rmc_number . "- Montecristo Jewellers";

            ?>
                <a class="f4-background-container" href="<?php esc_url(the_permalink()) ?>">
                    <div class="height"></div>

                    <div class="height-container">

                        <?php
                        $desktop_image_url = 'https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1/rolex/upright_watches_assets/upright_watch_assets/' . $rmc_number . '.webp';
                        $mobile_image_url = 'https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1/rolex/upright_watches_assets/upright_watch_assets/' . $rmc_number . '.webp';
                        $alt_text = $alt;
                        echo do_shortcode('[responsive_image desktop_image_url="' . esc_url($desktop_image_url) . '" mobile_image_url="' . esc_url($mobile_image_url) . '" alt_text="' . esc_attr($alt_text) . '"]');
                        ?>
                    </div>

                    <div class="watch-info">
                        <p class="legend16Bold brown">Rolex</p>
                        <p class="body24Bold brown" body24Bold><?php echo $model_name ?></p>
                        <p class="legend16Light"><?php echo $spec_model_case ?></p>
                    </div>
                </a>
            <?php } ?>
        </div>
        <?php
        // Only display load more button if we have more than 6 products
        if ($total_product > 6) {
            echo '<button id="loadMore" class="primary-cta">Load more</button>';
        }
        ?>

    </div>

<?php }

// Restore original Post Data.
wp_reset_postdata();
