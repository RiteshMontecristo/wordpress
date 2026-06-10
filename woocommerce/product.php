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
    echo "<h2 class='brand'>" . esc_html($brand_name) . "</h2>";

    if ($brand_name === 'Montecristo') {
        $sub_brand = get_montecristo_sub_brand();
        if ($sub_brand) {
            echo "<p class='montecristo-sub-brand'>" . esc_html($sub_brand) . "</p>";
        }
    }
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


function price_container()
{
    global $product;

    // Option A: Get from attribute 'pa_model'
    $model_number = $product->get_sku();
    $brand_name   = get_brand_name();
    if ($model_number && $brand_name !== 'Montecristo') {
        echo '<div class="product-model-number">' . esc_html($model_number) . '</div>';
    }
    echo "<div class='price_container'>";
}
add_action("woocommerce_single_product_summary", "price_container", 9);

function custom_price_zero_message($price, $product)
{
    $brand_name = get_brand_name();
    $raw_price  = $product->get_price();

    if ($brand_name === 'Montecristo' || $raw_price === '' || $raw_price == 0) {
        return is_product()
            ? '<span class="mji-price-enquire">Inquire for pricing</span>'
            : '';
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
    if (has_term('montecristo', 'product_cat', $product_id) || has_term('mikimoto', 'product_cat', $product_id)) {
        echo '</div>';
        return;
    }

    $product   = wc_get_product($product_id);
    $raw_price = $product ? $product->get_price() : '';

    if ($raw_price === '' || $raw_price == 0) {
        echo '</div>';
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
    <!-- <button data-product="<?php echo $product_id ?>" data-favourite="<?php echo $is_favourite ?>" id="wishlist">
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
    ?>
    <div class="product-cta-wrap">

        <button type="button" class="btn btn-contact open-contact-modal">Inquire</button>

        <div class="product-icon-links">

            <button type="button" class="product-icon-link open-call-modal">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.07 12 19.79 19.79 0 0 1 1 3.18 2 2 0 0 1 3 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 8.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16h1z"/>
                </svg>
                <span>Call Us</span>
            </button>

            <button type="button" class="product-icon-link open-appointment-modal">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <span>Book Appointment</span>
            </button>

            <?php if ($brand_name === 'Montecristo') : ?>
            <button type="button" class="product-icon-link open-contact-modal" data-modal-title="Handcraft Your Custom Jewellery" data-inquiry-type="custom_jewellery">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                </svg>
                <span>Customize</span>
            </button>
            <?php endif; ?>

        </div>
    </div>
    <?php
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

add_filter('woocommerce_available_variation', 'add_sku_to_variation_data');
function add_sku_to_variation_data($variation_data)
{
    $variation_data['sku'] = $variation_data['variation_id'] ? get_post_meta($variation_data['variation_id'], '_sku', true) : '';
    return $variation_data;
}

add_action('wp_footer', 'change_sku_number_for_variant');
function change_sku_number_for_variant()
{
    if (is_product()) {
        global $product;

        // Store original SKU and price HTML
        $original_sku   = $product->get_sku();
        $original_price = $product->get_price_html();
    ?>
        <script>
            jQuery(function($) {

                var originalSKU = '<?= esc_js($original_sku); ?>';
                var originalPrice = '<?= wp_json_encode($original_price); ?>';

                $('form.variations_form').on('found_variation', function(event, variation) {
                    $(".product-model-number").text(variation.sku);
                    $(".price_container .price").html(variation.price_html);
                });

                // When variations are reset (clear selection)
                $('form.variations_form .reset_variations').on('click', function() {
                    console.log("User clicked Clear button");
                    $(".product-model-number").text(originalSKU);
                    $(".price_container .price").html(originalPrice);
                });
            });
        </script>
<?php }
}

// Show a region notice when the brand is sellable online but this customer's country is restricted.
add_action('woocommerce_single_product_summary', function () {
    global $product;
    if (!$product) return;

    $product_id    = $product->get_parent_id() ?: $product->get_id();
    $brand_term_id = (int) get_post_meta($product_id, 'rank_math_primary_product_brand', true);
    if (!$brand_term_id) return;
    if (get_term_meta($brand_term_id, 'mji_sellable_online', true) !== '1') return;

    $allowed = mji_get_product_allowed_countries($product_id);
    if (empty($allowed)) return; // worldwide — no restriction

    $country = '';
    if (WC()->customer) {
        $country = WC()->customer->get_shipping_country()
            ?: WC()->customer->get_billing_country();
    }
    if (empty($country)) {
        $geo     = WC_Geolocation::geolocate_ip();
        $country = $geo['country'] ?? '';
    }

    if (empty($country) || in_array($country, $allowed, true)) return;

    echo '<p class="mji-region-notice">Currently unavailable in your region.</p>';
}, 28);

// When a product is not purchasable, WooCommerce skips its stock HTML entirely.
// Re-attach it just before the add-to-cart action so it always shows.
add_action('woocommerce_single_product_summary', function () {
    global $product;
    if ($product && !$product->is_purchasable()) {
        echo wp_kses_post(wc_get_stock_html($product));
    }
}, 29);

// Single product purchasability is controlled by the brand's "sellable online" flag.
// Managed under Products > Brands in WP admin. No brand assigned = not purchasable.
add_filter('woocommerce_is_purchasable', function (bool $purchasable, WC_Product $product): bool {
    if (!$purchasable) return false;

    $product_id    = $product->get_parent_id() ?: $product->get_id();
    $brand_term_id = (int) get_post_meta($product_id, 'rank_math_primary_product_brand', true);
    if (!$brand_term_id) return false;

    $sellable = get_term_meta($brand_term_id, 'mji_sellable_online', true);
    if ($sellable !== '1') return false;

    $allowed = mji_get_product_allowed_countries($product_id);
    if (empty($allowed)) return true; // worldwide — no restriction

    // Determine the visitor's country via WC customer session or geolocation.
    // Shipping country takes priority — restrictions are about where the item ships to.
    $country = '';
    if (WC()->customer) {
        $country = WC()->customer->get_shipping_country()
            ?: WC()->customer->get_billing_country();
    }
    if (empty($country)) {
        $geo     = WC_Geolocation::geolocate_ip();
        $country = $geo['country'] ?? '';
    }

    if (empty($country)) return true; // can't determine — don't silently block

    return in_array($country, $allowed, true);
}, 10, 2);
