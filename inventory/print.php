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
    // Get the product information
    $product = wc_get_product($post->ID);
    $variable_product = $product->is_type('variable');
    $product_title = $product->get_title();
    $product_image = $product->get_image();
    $primary_category_id = get_post_meta($post->ID, 'rank_math_primary_product_cat', true);

    $category = get_term($primary_category_id, 'product_cat');

    // custom_log($category);
    if ($category) {
        echo "Primary category " . $category->name;
    }

    // Getting custom SKUs and related info
    $custom_skus = get_post_meta($post->ID, 'new_repeatable_sku_field', true);
    $sku_options = '';

    if ($custom_skus) {
        $sku_options = '<select name="sku_text" id="sku_text">';
        foreach ($custom_skus as $value) {
            if ($variable_product) {
                $sku_options .= '<option value="' . $value["sku_text"] . ' ' . $value['sku_variation'] . '">' . $value["sku_text"] . '</option>';
            } else {
                $sku_options .= '<option value="' . $value["sku_text"] . '">' . $value["sku_text"] . '</option>';
            }
        }
        $sku_options .= '</select>';
    }

    // getting regular attributes for both variant and non variants
    $attributes = $product->get_attributes();
    $regular_attributes = array();
    foreach ($attributes as $attribute) {
        if (!$attribute->get_variation()) {
            $regular_attributes[] = [
                $attribute['name'] => implode(", ", $attribute['options'])
            ];
        }
    }

    // DISPLAY THE VARIANTS information
    if ($variable_product) {
        $variations_id_array = $product->get_children();

        $variable_products_info = array();

        foreach ($variations_id_array as $id) {
            $variation = wc_get_product($id);
            $price = $variation->get_price();
            $attribute = $variation->get_attributes();
            $variable_product_info = ["id" => $id];;

            $variable_product_info["attributes"] = $attribute;
            $variable_product_info["price"] = $price;
            $variable_products_info[] = $variable_product_info;
        }

        $json = json_encode($variable_products_info, JSON_HEX_APOS | JSON_HEX_QUOT);
        echo "<input name='variable' id='variable' type='hidden' value='" . htmlspecialchars($json, ENT_QUOTES) . "' />";
    } else { // displaying price here cause vairants will have separate price and dont want to duplicate it.
        $price = $product->get_price();
        echo "<input name='price' id='price' type='hidden' value='" . $price . "' />";
    }
?>
    <div id="print-section">
        <?php echo $product_image ?>
        <br />
        <input name="title" id="title" type="hidden" value="<?php echo $product_title ?>" />
        <?php echo $sku_options ? $sku_options : '' ?>
        <br />
        <input name='regular-attribute' id='regular-attribute' type='hidden'
            value='<?php echo json_encode($regular_attributes) ?>' />
        <button class="hiddenddd" id="card-print">Print</button>
    </div>

<?php
}
