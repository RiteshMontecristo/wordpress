<?php

function filter_jewellery_a_to_default_brand($query)
{
    $default_brand_slug = 'montecristo';
    $current_term = get_queried_object();

    if (!$current_term) {
        return;
    }

    $is_parent_jewellery = false;

    if (!empty($current_term->parent) && $current_term->parent > 0) {
        $parent_term = get_term($current_term->parent, 'product_cat');

        if ($parent_term && $parent_term->slug === 'jewellery') {
            $is_parent_jewellery = true;
        }
    }

    if (!is_admin() && $query->is_main_query() && ($current_term->slug === 'designer' || $current_term->slug === 'watches' || $current_term->slug === 'jewellery' || $is_parent_jewellery)) {

        if ($current_term->slug === 'watches') {
            $default_brand_slug = 'bellross';
        }

        $tax_query = array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $default_brand_slug,
            )
        );
        $query->set('tax_query', $tax_query);
    }
}

add_action('pre_get_posts', 'filter_jewellery_a_to_default_brand');

function add_opengraph_tags_for_category()
{
    if (is_product_category()) {
        // Get the current category object
        $current_category = get_queried_object();

        // Get the category thumbnail (if using a plugin like "Category Images")
        $thumbnail_id = get_term_meta($current_category->term_id, 'thumbnail_id', true);
        $image_url = wp_get_attachment_image_url($thumbnail_id, 'full');

        // Fallback to a default image if no thumbnail is set
        if ($image_url) {
            echo '<meta property="og:image" content="' . esc_url($image_url) . '" />' . "\n";
        }
    }
}
add_action('wp_head', 'add_opengraph_tags_for_category');

function add_body_class($classes)
{
    if (is_product_category()) {

        $current_category = get_queried_object();
        $children = get_terms(array(
            'taxonomy' => 'product_cat',
            'parent' => $current_category->term_id,
            'hide_empty' => false,
        ));
        $parent_category = get_term($current_category->parent, 'product_cat');

        if ($current_category->parent === 0) {
            $parent_category = $current_category->name;
        } else {
            $parent_category_name = $parent_category->name;
        }

        if (!(!empty($children) || $parent_category_name == "Jewellery")) {
            $classes[] = 'product_width';
        }
    }
    return $classes;
}

// adding this as when the product is only one and there is no sidebar the items is small
add_filter('body_class', "add_body_class");

// add_filter('woocommerce_default_catalog_orderby', 'custom_default_catalog_orderby');
function custom_default_catalog_orderby()
{
    return 'price'; // Set default to sort by price
}

function add_banner()
{
    $current_category = get_queried_object();
    $search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

    if ($search_term) {
        echo "<h1> Seach result for " . $search_term . "</h1>";
    }

    if ($current_category && !is_wp_error($current_category) && isset($current_category->term_id)) {
        // Get the category thumbnail ID
        $thumbnail_id = get_term_meta($current_category->term_id, 'thumbnail_id', true);
        $image_data = wp_get_attachment_image_src($thumbnail_id, 'full');

        if (!empty($image_data)) {
            $thumbnail_url = $image_data[0]; // Image URL
            $image_width = $image_data[1];   // Image Width
            $image_height = $image_data[2];  // Image Height
            $mobile_image = get_field('mobile_image', 'product_cat_' . get_queried_object_id());
        }

        // Display the category name and thumbnail
        echo '<div class="category-thumbnail">';
        if (!empty($thumbnail_url)) {
?>
            <picture>
                <?php if ($mobile_image) {
                    $mobile_image_url = $mobile_image['url'];
                    $mobile_image_width = $mobile_image['width'];
                    $mobile_image_height = $mobile_image['height'];

                    echo '<source 
                            srcset="' . esc_url($mobile_image_url) . '" 
                            media="(max-width: 767px)" 
                            width="' . esc_attr($mobile_image_width) . '" 
                            height="' . esc_attr($mobile_image_height) . '" 
                        />';
                } ?>
                <img loading="eager" width="<?php echo $image_width ?>" height="<?php echo $image_height ?>" fetchpriority="high"
                    src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php esc_attr($current_category->name); ?>" />
            </picture>

    <?php
        }

        woocommerce_breadcrumb();

        echo '<div class="category-description col-full">';
        $data_attributes = "data-brand='{$current_category->slug}'";

        echo "<h1 id='data-selector' $data_attributes>" . $current_category->name . "</h1>";
        echo "<div>";
        echo apply_filters('the_content', $current_category->description);
        echo "</div>";
        echo '</div>';
        echo '</div>';
    }
}
add_action("woocommerce_before_main_content", "add_banner", 11);

// Changing the breadcrumb priority when the product has two catgories i.e. designer and jewellery category
add_filter('woocommerce_breadcrumb_main_term', 'custom_breadcrumb_designer_priority', 10, 2);
function custom_breadcrumb_designer_priority($main_term, $terms)
{
    // Define the Designer category slug and all its child categories
    $designer_category = 'designer';

    $priority_term = null;

    // Loop through the product's categories
    foreach ($terms as $term) {
        // Skip the Uncategorized category
        if ($term->slug === 'uncategorized') {
            continue;
        }

        // Check if the term is the Designer category or one of its child categories
        if ($term->slug === $designer_category || term_is_ancestor_of(get_term_by('slug', $designer_category, 'product_cat')->term_id, $term->term_id, 'product_cat')) {
            $priority_term = $term; // Set the designer category or its child category as priority
            break; // Exit the loop once we find the designer category or its child
        }
    }

    // If no designer category is found, fall back to the main term
    return $priority_term ? $priority_term : $main_term;
}

// Creating breadcrumb and sort be side by side
function custom_breadcrumb_with_sort()
{
    echo "<div class='breadcrumb-container col-full'>";
    // Display the original breadcrumbs
    $current_category = get_queried_object();

    $parent_category = get_term($current_category->parent, 'product_cat');
    ?>


    <div class="mobile-filter-button">
        <button id="mobile-filter" class="mobile-filter btn">Filter
            <svg width="16px" height="16px" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink" fill="#ffffff">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                    <title>Filter</title>
                    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <g id="Filter">
                            <rect id="Rectangle" fill-rule="nonzero" x="0" y="0" width="24" height="24"> </rect>
                            <line x1="4" y1="5" x2="16" y2="5" id="Path" stroke="#ffffff" stroke-width="2"
                                stroke-linecap="round"> </line>
                            <line x1="4" y1="12" x2="10" y2="12" id="Path" stroke="#ffffff" stroke-width="2"
                                stroke-linecap="round"> </line>
                            <line x1="14" y1="12" x2="20" y2="12" id="Path" stroke="#ffffff" stroke-width="2"
                                stroke-linecap="round"> </line>
                            <line x1="8" y1="19" x2="20" y2="19" id="Path" stroke="#ffffff" stroke-width="2"
                                stroke-linecap="round"> </line>
                            <circle id="Oval" stroke="#ffffff" stroke-width="2" stroke-linecap="round" cx="18" cy="5" r="2">
                            </circle>
                            <circle id="Oval" stroke="#ffffff" stroke-width="2" stroke-linecap="round" cx="12" cy="12"
                                r="2"> </circle>
                            <circle id="Oval" stroke="#ffffff" stroke-width="2" stroke-linecap="round" cx="6" cy="19" r="2">
                            </circle>
                        </g>
                    </g>
                </g>
            </svg>
        </button>
    </div>

    <?php
    // if ($parent_category_name != "Jewellery Catalog") {
    // }
    // Now add your Sort By dropdown
    if (is_shop() || is_product_category() || is_product_tag()) {
    ?>
        <div class="sort-by-dropdown">
            <!-- <label for="sortby" style="margin-right: 10px;">Sort By:</label> -->
            <select id="sortby" style="border: none; font-size:0.875em; width:140px">
                <option value="" disabled selected>Sort by Options</option>
                <option value="date">Sort by Latest</option>
                <option value="price">Sort by Low to High</option>
                <option value="price-desc">Sort by High to Low</option>
                <option value="popularity">Sort by Popularity</option>
            </select>
        </div>
    <?php

        echo "</div>";
    }
}

// add breadcrumb
function check_product_taxonomy()
{
    if (is_product_tag() || is_product_category()) {
        add_action('woocommerce_before_main_content', 'custom_breadcrumb_with_sort', 12);
    }
}
add_action('wp', 'check_product_taxonomy');

// Removing the title and the description of categories
remove_action('woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header');

function add_products_contaienr()
{
    echo "<div class='products-container col-full'>";
}
add_action("woocommerce_before_shop_loop", "add_products_contaienr", 40);

function add_sidebar_for_products()
{
    require_once get_stylesheet_directory() . '/template-parts/products_sidebar.php';
}
add_action("woocommerce_before_shop_loop", "add_sidebar_for_products", 45);

// Prepend brand name to product title on shop/category pages only
// add_filter('the_title', 'prepend_brand_to_product_title', 10, 2);
function prepend_brand_to_product_title($title, $id)
{
    // Only on frontend shop/category pages, and only for products
    if (! is_admin() && in_the_loop() && is_shop() || is_product_category()) {
        $terms = wp_get_post_terms($id, 'product_cat');
        if (!empty($terms)) {
            foreach ($terms as $term) {
                $has_children = get_term_children($term->term_id, 'product_cat');
                // Check if the category has children and is not the main category then its the brand
                if ($has_children && $term->slug != "watches" && $term->slug != "jewellery" && $term->slug != "designer") {
                    $brand = $term->name;
                    if ($brand && strpos($title, $brand) !== 0) {
                        $title = $brand . ' ' . $title;
                    }
                }
            }
        }
    }

    return str_replace("Watch", "", $title);
}

// Display model number below product title on shop/category pages
add_action('woocommerce_after_shop_loop_item_title', 'display_product_model_number', 5);
function display_product_model_number()
{
    global $product;

    $model_number = $product->get_sku();
    if ($model_number) {
        echo '<div class="product-model-number">' . esc_html($model_number) . '</div>';
    }
}

// removing add to cart button
remove_action("woocommerce_after_shop_loop_item", "woocommerce_template_loop_add_to_cart", 10);

function close_products_contaienr()
{
    echo "</div>";
}
add_action("woocommerce_shop_loop_header", "close_products_contaienr", 5);

// Remove Storefront sorting bar from storefront-sorting div to fix the seo issues of inline links to page 1,2 etc.
add_action('template_redirect', function () {
    ob_start('custom_remove_pagination_markup');
});

function custom_remove_pagination_markup($content)
{
    // Remove entire pagination nav block
    return preg_replace('#<nav class="woocommerce-pagination".*?</nav>#s', '', $content);
}

// Removing next and previous links from Rank Math SEO plugin to solve SEO issues with inline links to page 1,2 etc.
add_filter('rank_math/frontend/next_rel_link', '__return_false');
add_filter('rank_math/frontend/prev_rel_link', '__return_false');

function add_load_more()
{
    global $wp_query; // Access the global wp_query variable

    if ($wp_query->max_num_pages > 1) {
    ?>
        <div id="load-more-container">
            <button id="load-more" class="load-more-products btn">Load More</button>
        </div>
<?php
    }
}

add_action("woocommerce_after_main_content", "add_load_more", 10);

function get_products_info()
{

    $brand_collection = array("jewellery", "rings", "bracelets", "earrings", "engagement-rings", "pendants-necklaces", "wedding-bands");

    if (isset($_GET['brand']) && !empty($_GET['brand'])) {
        $brand = $_GET['brand'];
    }

    if (isset($_GET['brands']) && !empty($_GET['brands'])) {
        $brands = explode(',', $_GET['brands']);
    }

    if (isset($_GET['type']) && !empty($_GET['type'])) {
        $type = explode(',', $_GET['type']);
    }

    if (isset($_GET['targetGroup']) && !empty($_GET['targetGroup'])) {
        $targetGroup = explode(',', $_GET['targetGroup']);
    }

    if (isset($_GET['materials']) && !empty($_GET['materials'])) {
        $materials = explode(',', $_GET['materials']);
    }

    if (isset($_GET['gemstone']) && !empty($_GET['gemstone'])) {
        $gemstone = explode(',', $_GET['gemstone']);
    }

    if (isset($_GET['gift']) && !empty($_GET['gift'])) {
        $gift = explode(',', $_GET['gift']);
    }
    if (isset($_GET['s']) && !empty($_GET['s'])) {
        $searchTerm = $_GET['s'];
    }

    $minPrice = isset($_GET['min_price']) ? $_GET['min_price'] : 0;
    $maxPrice = isset($_GET['max_price']) ? $_GET['max_price'] : PHP_INT_MAX;
    $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'menu_order';
    $page = isset($_GET['page']) ? $_GET['page'] : 0;
    $posts_per_page = 12;
    $offset = $page * $posts_per_page;

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => $posts_per_page,
        'offset' => $offset,
        'meta_query' => array(
            array(
                'key' => '_price',
                'value' => array($minPrice, $maxPrice),
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC',
            )
        ),
        'orderby' => $orderby,
        'order' => 'ASC',
        'post_status' => 'publish'
    );

    if ($orderby === 'price') {
        $args['meta_key'] = '_price';
        $args['orderby'] = array(
            'meta_value_num' => 'ASC',
            'ID' => 'ASC',
        );
    } elseif ($orderby === 'price-desc') {
        $args['meta_key'] = '_price';
        $args['orderby'] = array(
            'meta_value_num' => 'DESC',
            'ID' => 'ASC',
        );
    } elseif ($orderby === 'popularity') {
        $args['meta_key'] = 'total_sales';
        $args['orderby'] = array(
            'meta_value_num' => 'DESC',
            'ID' => 'ASC',
        );
    } elseif ($orderby === 'menu_order') {
        // Manual drag-and-drop order
        $args['orderby'] = array(
            'menu_order' => 'ASC',
            'ID' => 'ASC'
        );
    } else {
        // Fallback to date if nothing matches
        $args['orderby'] = array(
            'date' => 'DESC',
            'ID' => 'ASC'
        );
    }

    // Initialize the tax_query array if brands or taxonomy are provided
    $tax_query = array(); // Start with an empty array
    if (!empty($searchTerm)) {
        $args['s'] = $searchTerm;
    }
    if (!empty($brand)) {
        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => $brand,
            'operator' => 'IN'
        );

        // For the Jewellery type page
        if (in_array($brand, $brand_collection)) {
            $tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => 'montecristo',
                'operator' => 'IN'
            );
        }
    }

    // Handle brand filtering (taxonomy query)
    if (!empty($brands)) {
        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => $brands,
            'operator' => 'IN'
        );
    }
    if (!empty($type)) {
        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => $type,
            'operator' => 'IN'
        );
    }

    // Handle taxonomy (tag) filtering
    if (!empty($tags)) {
        $tax_query[] = array(
            'taxonomy' => 'product_tag',
            'field' => 'slug',
            'terms' => $tags,
            'operator' => 'IN'
        );
    }
    if (!empty($targetGroup)) {
        $tax_query[] = array(
            'taxonomy' => 'product_tag',
            'field' => 'slug',
            'terms' => $targetGroup,
            'operator' => 'IN'
        );
    }
    if (!empty($materials)) {
        $tax_query[] = array(
            'taxonomy' => 'product_tag',
            'field' => 'slug',
            'terms' => $materials,
            'operator' => 'IN'
        );
    }
    if (!empty($gemstone)) {
        $tax_query[] = array(
            'taxonomy' => 'product_tag',
            'field' => 'slug',
            'terms' => $gemstone,
            'operator' => 'IN'
        );
    }
    if (!empty($gift)) {
        $tax_query[] = array(
            'taxonomy' => 'product_tag',
            'field' => 'slug',
            'terms' => $gift,
            'operator' => 'IN'
        );
    }
    if (!empty($tax_query)) {
        $args['tax_query'] = array(
            'relation' => 'AND',
            ...$tax_query
        );
    }

    // Query WooCommerce products
    $products = new WP_Query($args);

    return $products;
}

function filter_products()
{
    if (!isset($_GET['orderby']) && isset($_GET['brand']) && !empty($_GET['brand']) && $_GET['brand'] == "blancpain") {
        blancpain_products();
        wp_die();
    } else {
        // Query WooCommerce products
        $products = get_products_info();

        // Get the total number of products found
        $total_products = $products->found_posts;

        // Prepare HTML response
        ob_start();
        if ($products->have_posts()) {
            while ($products->have_posts()) {
                $products->the_post();
                wc_get_template_part('content', 'product'); // Default WooCommerce product template
            }
        } else {
            echo "<h3 class='filter-result'>No products found</h3>";
        }
        $html = ob_get_clean();

        // Return the filtered products as JSON
        wp_send_json(array('html' => $html, 'total_products' => $total_products));

        wp_die(); // End AJAX request
    }
}

add_action('wp_ajax_filter_products', 'filter_products'); // For logged-in users
add_action('wp_ajax_nopriv_filter_products', 'filter_products'); // For non-logged-in users

function load_more()
{

    if (!isset($_GET['orderby']) && isset($_GET['brand']) && !empty($_GET['brand']) && $_GET['brand'] == "blancpain") {
        blancpain_products();
        wp_die();
    } else {

        // Query WooCommerce products
        $products = get_products_info();

        // Get the total number of products found
        $total_products = $products->found_posts;

        // Prepare HTML response
        ob_start();
        if ($products->have_posts()) {
            while ($products->have_posts()) {
                $products->the_post();
                wc_get_template_part('content', 'product'); // Default WooCommerce product template
            }
        }
        $html = ob_get_clean();

        // Return the filtered products as JSON
        wp_send_json(array('html' => $html, 'total_products' => $total_products));

        wp_die(); // End AJAX request
    }
}

add_action('wp_ajax_load_more', 'load_more'); // For logged-in users
add_action('wp_ajax_nopriv_load_more', 'load_more'); // For non-logged-in users

function blancpain_products()
{
    if (isset($_GET['brands']) && !empty($_GET['brands'])) {
        $brands = explode(',', $_GET['brands']);
    }

    if (empty($brands)) {
        $brands = ['fifty-fathoms', 'villeret', 'ladybird-blancpain', 'air-command'];
    }

    if (isset($_GET['targetGroup']) && !empty($_GET['targetGroup'])) {
        $targetGroup = explode(',', $_GET['targetGroup']);
    }

    if (isset($_GET['materials']) && !empty($_GET['materials'])) {
        $materials = explode(',', $_GET['materials']);
    }

    $minPrice = isset($_GET['min_price']) ? $_GET['min_price'] : 0;
    $maxPrice = isset($_GET['max_price']) ? $_GET['max_price'] : PHP_INT_MAX;
    $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'price';

    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $posts_per_page = 12;
    $offset = $page * $posts_per_page;
    $remaining = $posts_per_page;

    $children = get_terms([
        'taxonomy' => 'product_cat',
        'parent' => 481, // Blancpain parent
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'hide_empty' => false,
    ]);

    ob_start();
    $found_products = 0;

    foreach ($children as $child) {

        // If filtering by brands (slugs), and this brand is not included, skip
        if (!in_array($child->slug, $brands)) {
            continue;
        }

        // Count products in this category matching filters
        $count_args = [
            'post_type' => 'product',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $child->term_id,
                ]
            ],
            'meta_query' => [
                [
                    'key' => '_price',
                    'value' => [$minPrice, $maxPrice],
                    'compare' => 'BETWEEN',
                    'type' => 'NUMERIC'
                ]
            ],
            'post_status' => 'publish',
        ];

        if (!empty($targetGroup)) {
            $count_args['tax_query'][] = [
                'taxonomy' => 'product_tag',
                'field' => 'slug',
                'terms' => $targetGroup,
            ];
        }
        if (!empty($materials)) {
            $count_args['tax_query'][] = [
                'taxonomy' => 'product_tag',
                'field' => 'slug',
                'terms' => $materials,
            ];
        }

        $query = new WP_Query($count_args);
        $product_ids = $query->posts;
        $product_count = count($product_ids);

        wp_reset_postdata();

        if ($offset >= $product_count) {
            // Skip this entire category
            $offset -= $product_count;
            continue;
        }

        // Pull the slice we need
        $sliced_ids = array_slice($product_ids, $offset, $remaining);

        if (!empty($sliced_ids)) {
            $product_args = [
                'post_type' => 'product',
                'post__in' => $sliced_ids,
                'orderby' => $orderby,
                'posts_per_page' => count($sliced_ids),
                'post_status' => 'publish'
            ];

            $product_query = new WP_Query($product_args);

            while ($product_query->have_posts()) {
                $product_query->the_post();
                wc_get_template_part('content', 'product');
                $found_products++;
            }

            wp_reset_postdata();
        }

        $remaining -= count($sliced_ids);
        $offset = 0; // Reset offset after first applicable category

        if ($remaining <= 0) {
            break;
        }
    }

    $count_args = [
        'post_type' => 'product',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tax_query' => [
            [
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $brands,
                'operator' => 'IN'
            ]
        ],
        'meta_query' => [
            [
                'key' => '_price',
                'value' => [$minPrice, $maxPrice],
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC'
            ]
        ],
        'post_status' => 'publish',
    ];

    if (!empty($targetGroup)) {
        $count_args['tax_query'][] = [
            'taxonomy' => 'product_tag',
            'field' => 'slug',
            'terms' => $targetGroup,
        ];
    }
    if (!empty($materials)) {
        $count_args['tax_query'][] = [
            'taxonomy' => 'product_tag',
            'field' => 'slug',
            'terms' => $materials,
        ];
    }

    $query = new WP_Query($count_args);
    $total_products = $query->post_count;

    if ($total_products === 0) {
        echo "<h3 style='grid-column:1/-1;'>No products found</h3>";
    }

    $html = ob_get_clean();

    wp_send_json(['html' => $html, 'total_products' => $total_products]);
    wp_die();
}
