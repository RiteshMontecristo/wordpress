<?php

add_action('add_meta_boxes', 'add_product_print_section');

//  Print tag
function add_product_print_section()
{
    add_meta_box(
        'product_print_section', // Meta box ID
        'Print Product Information', // Meta box title
        'render_product_print_section', // Callback function to render the meta box
        'product', // Post type (WooCommerce products)
        'side', // Context (side, normal, advanced)
        'default' // Priority
    );
}

function render_product_print_section($post)
{
    // Get the product object
    $product = wc_get_product($post->ID);

    // Get product data
    $product_title = $product->get_title();
    $product_price = $product->get_price();
    $product_sku = $product->get_sku();

    // Get custom SKUs (if stored as variations or custom meta)
    $custom_skus = get_post_meta($post->ID, '_custom_skus', true); // Example: Array of custom SKUs
?>
    <div id="print-section">
        <p><strong>Title:</strong> <?php echo esc_html($product_title); ?></p>
        <p><strong>Price:</strong> <?php echo wc_price($product_price); ?></p>

        <label for="custom-sku-select"><strong>Select Custom SKU:</strong></label>
        <select id="custom-sku-select">
            <?php
            if (is_array($custom_skus)) {
                foreach ($custom_skus as $sku) {
                    echo '<option value="' . esc_attr($sku) . '">' . esc_html($sku) . '</option>';
                }
            } else {
                echo '<option value="' . esc_attr($product_sku) . '">' . esc_html($product_sku) . '</option>';
            }
            ?>
        </select>

        <button type="button" id="print-button">Print</button>
    </div>

<?php
}

// Print Card 
add_action('add_meta_boxes', 'add_product_card_print_section');

function add_product_card_print_section()
{
    add_meta_box(
        'product_card_print_section', // Meta box ID
        'Print Product Card', // Meta box title
        'render_product_card_print_section', // Callback function to render the meta box
        'product', // Post type (WooCommerce products)
        'normal', // Context (side, normal, advanced)
        'default' // Priority
    );
}

function render_product_card_print_section($post)
{
    global $wpdb;
    // Get the product information
    $product_id = $post->ID;
    $inventory_units_table = "{$wpdb->prefix}mji_product_inventory_units";

    $sql_query = $wpdb->prepare(
        "SELECT wc_product_variant_id, sku, serial, retail_price 
        FROM $inventory_units_table
        WHERE wc_product_id = %d",
        $product_id
    );

    try {
        $results = $wpdb->get_results($sql_query);

        if (!$results) {
            throw new RuntimeException($wpdb->last_error);
        }

        $sku_options = '<select id="sku_option" name="sku_text">';
        $image_url = [];
        $first_sku_data = null;

        foreach ($results as $index => $row) {
            $id = $row->wc_product_variant_id ?: $product_id;

            if (!isset($image_url[$id])) {
                $product = wc_get_product($id);
                $image_id = $product->get_image_id();
                $desc = $row->wc_product_variant_id ? $product->get_description() : $product->get_short_description();
                $image_url[$id] = ["img" =>  $image_id ? wp_get_attachment_image_url($image_id, 'medium') : wc_placeholder_img_src(), "desc" => $desc];
            }

            $image_src =  $image_url[$id]["img"];
            $desc =  str_replace('•', '<br />', $image_url[$id]["desc"]);
            $sku = $row->sku;
            $serial = $row->serial;
            $retail_price = $row->retail_price;

            if ($index === 0) {
                $first_sku_data = [
                    'img' => $image_src,
                    'desc' => $desc,
                    'price' => $retail_price,
                    'sku' => $sku,
                    'serial' => $serial
                ];
            }

            $sku_options .= '<option data-serial="' . $serial . '" data-price="' . $retail_price . '" data-desc="' . $desc . '" data-img-src="' . $image_src . '" value="' . $sku . '">' . $sku . '</option>';
        }
        $sku_options .= '</select>';
    } catch (Exception $err) {
        custom_log($err->getMessage());
    }

?>
    <div id="print-card-section">
        <img id="print-product-image" src="<?php echo esc_url($first_sku_data['img']); ?>" />
        <div id="print-desc"><?php echo wp_kses_post($first_sku_data['desc']); ?></div>
        <div id="print-sku">SKU <?php echo esc_html($first_sku_data['sku']); ?></div>
        <div id="print-serial">Serial no. <?php echo esc_html($first_sku_data['serial']); ?></div>
        <div id="print-price">Price <?php echo wc_price($first_sku_data['price']); ?></div>
    </div>
    <div><?= $sku_options ?></div>
    <button class="hiddenddd" id="card-print">Print</button>
<?php
}
