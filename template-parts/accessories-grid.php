<?php

$rolex_accessories = new WP_Query(array(
    'post_type' => 'mc-rolex',
    'posts_per_page' => -1,
    'tax_query' => array(
        array(
            'taxonomy' => 'rolex_category',
            'field'    => 'slug',
            'terms'    => 'accessories'
        ),
    ),
    'orderby' => 'ID',
    'order' => 'asc'
));

if ($rolex_accessories->have_posts()) {

    $total_product = $rolex_accessories->found_posts;
?>
    <div class="grid-nospace watch-grid f9-background-container remove-padding-top">

        <div id="watchesContainer" class="watches-container">
            <?php
            while ($rolex_accessories->have_posts()) {
                $rolex_accessories->the_post();

                $model_name = get_field('model_name');
                $spec_material1 = get_field('spec_material1');
                $rmc_number = get_field('rmc');
                $alt = "Rolex " . $model_name . " in " .  $spec_material1 . " " .  $rmc_number . "- Montecristo Jewellers";

            ?>
                <a class="f4-background-container" href="<?= esc_url(get_permalink()) ?>">
                    <div class="height"></div>
                    <div class="height-container">

                        <?php
                        $desktop_image_url = '/wp-content/uploads/rolex/rolex-cufflink-assets/rolex-cufflink-asset-packshot/rolex-accessories-' . $rmc_number . '-packshot.webp';
                        $mobile_image_url = '/wp-content/uploads/rolex/rolex-cufflink-assets/rolex-cufflink-asset-packshot/rolex-accessories-' . $rmc_number . '-packshot.webp';
                        $alt_text = $alt;
                        echo do_shortcode('[responsive_image desktop_image_url="' . esc_url($desktop_image_url) . '" mobile_image_url="' . esc_url($mobile_image_url) . '" alt_text="' . esc_attr($alt_text) . '"]');
                        ?>
                    </div>

                    <div class="watch-info">
                        <p class="body24Bold brown" body24Bold><?php echo $model_name ?></p>
                        <p class="legend16Light"><?php echo $spec_material1 ?></p>
                    </div>
                </a>
            <?php } ?>
        </div>
    </div>

<?php }

// Restore original Post Data.
wp_reset_postdata();
