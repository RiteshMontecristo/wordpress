<?php

// Add opening div before the product summary.
function product_wrapper()
{
    echo "<div class='product-wrapper'>";
    woocommerce_breadcrumb();
    echo "<div class='col-full'>";
}

add_action("woocommerce_before_single_product_summary", "product_wrapper", 1);

function product_wrapper_closer()
{
    echo "</div>";
    echo "</div>";
}
add_action("woocommerce_single_product_summary", "product_wrapper_closer", 55);

function get_brand_name()
{
    static $brand_name = '';

    if ($brand_name) return $brand_name;

    $terms = wp_get_post_terms(get_the_ID(), 'product_cat');
    if (!empty($terms)) {
        foreach ($terms as $term) {
            // Check if the category has child categories
            $has_children = get_term_children($term->term_id, 'product_cat');
            if ($has_children && $term->slug != "watches" && $term->slug != "jewellery" && $term->slug != "designer") {
                $brand_name = $term->name;
            }
        }
    }
    return $brand_name;
}

function add_brand_name()
{
    $brand_name = get_brand_name();
    echo "<h2 class='brand'>$brand_name</h2>";
}
add_action("woocommerce_single_product_summary", "add_brand_name", 4);

function custom_single_product_title($title, $id)
{
    // Only on single product pages
    if (is_product() && get_the_ID() === $id) {
        // Modify the title as needed
        $brand_name = get_brand_name();

        $title = trim(str_replace($brand_name, "", $title));
    }
    return $title;
}
add_filter('the_title', 'custom_single_product_title', 10, 2);

// Remove quantity input field from product pages
add_filter('woocommerce_is_sold_individually', '__return_true');

function price_container()
{
    global $product;

    // Option A: Get from attribute 'pa_model'
    $model_number = $product->get_sku();
    if ($model_number) {
        echo '<div class="product-model-number">' . esc_html($model_number) . '</div>';
    }
    echo "<div class='price_container'>";
}
add_action("woocommerce_single_product_summary", "price_container", 9);

function custom_price_zero_message($price, $product)
{
    $brand_name = get_brand_name();
    if ($brand_name === 'Montecristo') {
        return;
    }
    if ($product->get_price() == 0) {
        return '<span class="price-upon-request"><i>Price upon request</i></span>';
    }
    // Remove the default currency symbol (e.g. $)
    $price = preg_replace('/<span class="woocommerce-Price-currencySymbol">.*?<\/span>/i', '', $price);

    // Append plain "CAD"
    $price .= ' CAD';
    return $price;
}

add_filter('woocommerce_get_price_html', 'custom_price_zero_message', 100, 2);

// Price container and add to favourites
function close_price_container()
{
    $product_id = "";
    $user_id = "";
    $is_favourite = "";

    $product_id = get_the_ID();
    // $user_id = get_current_user_id();
    // $is_favourite = "false";
    // $favourite = get_user_meta($user_id, 'wishlist', true) ?: [];

    // if (! in_array($product_id, $favourite)) {
    //     $is_favourite = "false";
    // } else {
    //     $is_favourite =  "true";
    // }
    if (has_term('montecristo', 'product_cat', $product_id)) {
?> </div>

    <?php
        return;
    }
    ?>
    <svg class="info-icon" width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="12" r="10" stroke="#1C274C" stroke-width="1.5" />
        <path d="M12 17V11" stroke="#1C274C" stroke-width="1.5" stroke-linecap="round" />
        <circle cx="1" cy="1" r="1" transform="matrix(1 0 0 -1 11 9)" fill="#1C274C" />
    </svg>
    <span class="info-desc">Suggested retail price exclusive of tax. The suggested retail price can be modified at any time
        without notice.</span>
    </div>
    <!-- <button data-product="<?php echo $product_id ?>" data-user="<?php echo $user_id ?>" data-favourite="<?php echo $is_favourite ?>" id="wishlist">
        <svg width="23" height="22" viewBox="0 0 23 22" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M6.775 1.25946C3.58562 1.25946 1 3.95087 1 7.27074C1 13.282 7.825 18.7468 11.5 20.0179C15.175 18.7468 22 13.282 22 7.27074C22 3.95087 19.4144 1.25946 16.225 1.25946C14.272 1.25946 12.5447 2.26881 11.5 3.81371C10.9675 3.02416 10.26 2.37979 9.43755 1.93518C8.61507 1.49056 7.70178 1.25878 6.775 1.25946Z" stroke="#555555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </button> -->

    <?php
}
add_action("woocommerce_single_product_summary", "close_price_container", 11);

function single_page_contact()
{
    $brand_name = get_brand_name();

    if ($brand_name == 'Omega') {
        $contact = "tel:+1-604-325-2116";
    } else {
        $contact = "tel:+1-604-263-3611";
    }
    if ($brand_name === 'Montecristo') {
    ?>
        <div class="montecristo-category">
            <a href="/customize-your-jewellery" class="btn btn-customize">Customize Jewellery</a>
            <a href="/contact" class="btn btn-contact">Contact Us</a>
        </div>
    <?php } else { ?>
        <a href="/contact#contactUs" class="btn btn-contact">Contact Us</a>
        <a href="<?= $contact ?>" class="btn btn-call">Call Us</a>
<?php }
}

add_action("woocommerce_single_product_summary", "single_page_contact", 31);

// Removing WOOCOMMERCE PRODUCTS SKU, tags and categories
remove_action("woocommerce_single_product_summary", "woocommerce_template_single_meta", 40);

// WOOCOMMERCE PRODUCTS DESCRIPTION TABS
remove_action("woocommerce_after_single_product_summary", "woocommerce_output_product_data_tabs", 10);

function product_description()
{
    require_once get_stylesheet_directory() . '/woocommerce/tabs/tabs.php';
}
add_action("woocommerce_after_single_product_summary", "product_description", 10);
