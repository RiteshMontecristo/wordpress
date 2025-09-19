<?php

add_action('add_meta_boxes', 'add_inventory_units_meta_box');

function add_inventory_units_meta_box()
{
    add_meta_box(
        'inventory_units',
        'Inventory Units',
        'render_inventory_units_meta_box',
        'product',
        'normal',
        'default'
    );
}

function render_inventory_units_meta_box($post)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'mji_product_inventory_units';
    $product_id = $post->ID;
    $is_variation = false;
    $location_name_by_id = [];
    $variation_name_by_id = [];
    $status = [
        "in_stock" => "In Stock",
        "reserved" => "Reserved",
        "sold" => "Sold",
        "damaged" => "Damaged",
    ];
    $variation_select = '';
    $add_serial = false;
    $units = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE wc_product_id = %d ",
            $product_id
        )
    );
    $product = wc_get_product($product_id);

    $product_cats = wp_get_post_terms($product_id, 'product_cat');
    if (!empty($product_cats) && !is_wp_error($product_cats)) {
        foreach ($product_cats as $cat) {
            if ($cat->slug === 'designer') {
                $$add_serial = false;
                break;
            } else if ($cat->slug === 'watches') {
                $add_serial = true;
                break;
            }
        }
    }

    if ($product && $product->is_type('variable')) {
        $is_variation = true;
        $variation_select = generate_variation_dropdown($product, $variation_name_by_id);
    }

    $all_location = get_all_location("");
    foreach ($all_location as $location) {
        $location_name_by_id[$location->id] = $location->name;
    }

    wp_nonce_field('save_inventory_units', 'inventory_units_nonce');

?>
    <style>
        #inventory-units-table {

            td,
            th {
                border: 1px solid;
                margin: 0;
            }

            input {
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>

    <p>Manage individual inventory units for this product.</p>

    <table id="inventory-units-table" width="100%">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Status</th>
                <?php if ($is_variation): ?>
                    <th>Variation</th>
                <?php endif; ?>
                <?php if ($add_serial): ?>
                    <th>Serial</th>
                <?php endif; ?>
                <th>Location</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($units as $unit): ?>
                <tr class="unit-row" data-product-id="<?= $product_id ?>" data-unit-id="<?= esc_attr($unit->id) ?>">
                    <td data-field="sku" data-value="<?= esc_html($unit->sku) ?>" class="editable-cell">
                        <?= esc_html($unit->sku) ?></td>
                    <td data-field="status" data-value="<?= esc_html($unit->status) ?>" class="editable-cell">
                        <?= esc_html($status[$unit->status]) ?></td>
                    <?php if ($is_variation): ?>
                        <td data-field="variant" data-value=" <?= esc_html($unit->wc_product_variant_id) ?>" class="editable-cell">
                            <?= esc_html($variation_name_by_id[$unit->wc_product_variant_id]) ?>
                        </td>
                    <?php endif; ?>
                    <?php if ($add_serial): ?>
                        <td data-field="serial" data-value=" <?= esc_html($unit->serial) ?>" class="editable-cell">
                            <?= esc_html($unit->serial) ?>
                        </td>
                    <?php endif; ?>
                    <td data-field="location" data-value="<?= esc_html($unit->location_id) ?>" class="editable-cell">
                        <?= esc_html($location_name_by_id[$unit->location_id]) ?></td>
                    <td>
                        <button class="edit-unit button">Edit</button>
                        <button class="save-unit button button-primary" style="display:none;">Save</button>
                        <!-- <a href="#" class="delete-unit" data-id="<?= $unit->id ?>" data-product="<?= $product_id ?>">Delete</a> -->
                    </td>
                </tr>
            <?php endforeach; ?>

            <!-- New Unit Row -->
            <tr id="addNewInventoryUnit" data-product-id="<?= $product_id ?>" class="unit-row new-unit-row">
                <td><input type="text" id="sku" name="new_sku" placeholder="Enter SKU" /></td>
                <td>
                    <select id="status" name="new_status">
                        <option value="in_stock">In Stock</option>
                        <option value="reserved">Reserved</option>
                        <option value="sold">Sold</option>
                        <option value="damaged">Damaged</option>
                    </select>
                </td>
                <?php if ($is_variation): ?>
                    <td>
                        <?= $variation_select ?>
                    </td>
                <?php endif; ?>
                <?php if ($add_serial): ?>
                    <td><input type="text" id="serialNum" name="new_serial" placeholder="Enter serial" /></td>
                <?php endif; ?>
                <td>
                    <?= get_all_location("select") ?>
                </td>
                <td><button id="addUnit" type="submit" name="add_unit" value="1">Add Unit</button></td>
            </tr>
        </tbody>
    </table>
<?php
}

function get_all_location($return_type = "array")
{
    global $wpdb;
    // Safe static query – no prepare needed
    $query = "SELECT id, name FROM {$wpdb->prefix}mji_locations";
    $result = $wpdb->get_results($query);

    if ($return_type == "select") {
        $html = '<select id="locationID" name="new_location_id">';
        foreach ($result as $loc) {
            $html .= "<option value='{$loc->id}'>{$loc->name}</option>";
        }
        $html .= '</select>';
        return $html;
    } else {
        return $result;
    }
}

function generate_variation_dropdown($product, &$variation_name_by_id)
{

    $available_variations = $product->get_available_variations();

    $html = '<select id="variationID" name="new_variation_id">';

    foreach ($available_variations as $variation_data) {

        $variation_id = $variation_data['variation_id'];
        $attributes = $variation_data['attributes'];

        $values = [];

        foreach ($attributes as $attr_key => $attr_value) {
            if (!empty($attr_value)) {
                $values[] = $attr_value;
            }
        }
        $attr_string = implode(', ', $values);
        $variation_name_by_id[$variation_id] = $attr_string;
        $html .= "<option value='{$variation_id}'>{$attr_string}</option>";
    }

    $html .= '</select>';
    return $html;
}

function create_inventory_units()
{
    global $wpdb;

    $product_id = intval($_POST['product_id']);
    $sku = sanitize_text_field($_POST['sku']);
    $serial_number = isset($_POST['serialNum']) ? $_POST['serialNum'] : null;

    $status = sanitize_text_field($_POST['status']);
    $location_id = intval($_POST['locationID']);
    $variation_id = isset($_POST['variationID']) ? intval($_POST['variationID']) : null;
    $product = wc_get_product($product_id);
    $category_id = get_post_meta($product_id, 'rank_math_primary_product_cat', true);
    if ($category_id == 0) {
        wp_send_json_error('No primary category set for this product.');
    }
    $brand = get_term($category_id, 'product_cat')->name;
    $model = "";

    $cost_price = 0;
    $retail_price = 0;

    $table_name = $wpdb->prefix . 'mji_product_inventory_units';
    $models_table = $wpdb->prefix . 'mji_models';
    $brands_table = $wpdb->prefix . 'mji_brands';

    if ($product_id <= 0 || empty($sku) || $location_id <= 0) {
        wp_send_json_error('Invalid product ID, SKU or location ID.');
        return;
    }

    if ($variation_id) {
        $variation = wc_get_product($variation_id);
        $retail_price = $variation->get_price();
        $cost_price = get_post_meta($variation_id, '_cost_price', true);
        $model = $variation->get_sku();
    } else {
        $retail_price = $product->get_price();
        $cost_price = get_post_meta($product_id, '_cost_price', true);
        $model = $product->get_sku();
    }

    $model_id = get_brand_model_id($models_table, $model);
    $brand_id = get_brand_model_id($brands_table, $brand);

    try {
        $result = $wpdb->insert(
            $table_name,
            [
                'wc_product_id' => $product_id,
                'wc_product_variant_id' => $variation_id,
                'sku' => $sku,
                'serial' => $serial_number,
                'status' => $status,
                'location_id' => $location_id,
                'cost_price' => $cost_price,
                'retail_price' => $retail_price,
                'model_id' => $model_id,
                'brand_id' => $brand_id
            ]
        );

        if ($result === false) {
            custom_log('Database error: ' . $wpdb->last_error);
            wp_send_json_error('Database error: ' . $wpdb->last_error);
        }

        wp_send_json_success($result);
    } catch (Exception $e) {
        custom_log("Error " . $e->getMessage());
        wp_send_json_error(['message' => 'Error creating inventory unit: ' . $e->getMessage()]);
    }
}

add_action('wp_ajax_create_inventory_units', 'create_inventory_units');

function update_inventory_units()
{
    global $wpdb;

    $unit_id = intval($_POST['unitId']);
    $product_id = intval($_POST['productId']);
    $sku = sanitize_text_field($_POST['sku']);
    $status = sanitize_text_field($_POST['status']);
    $location_id = intval($_POST['locationID']);
    $variation_id = isset($_POST['variationID']) ? intval($_POST['variationID']) : null;
    $serial = isset($_POST['serialNum']) ? sanitize_text_field($_POST['serialNum']) : null;

    $data =   [
        'wc_product_id' => $product_id,
        'wc_product_variant_id' => $variation_id,
        'sku' => $sku,
        'status' => $status,
        'location_id' => $location_id,
        'serial' => $serial
    ];
    $format = [
        '%d', // wc_product_id
        '%d', // wc_product_variant_id
        '%s', // sku
        '%s', // status
        '%d',  // location_id
        '%s'  // serial
    ];

    if ($variation_id) {
        $variation = wc_get_product($variation_id);
        $retail_price = $variation->get_price();
        $cost_price = get_post_meta($variation_id, '_cost_price', true);
        $model = $variation->get_sku();
        $models_table = $wpdb->prefix . 'mji_models';

        $model_id = get_brand_model_id($models_table, $model);

        $data['cost_price'] = $cost_price;
        $data['retail_price'] = $retail_price;
        $data['model_id'] = $model_id;
        $format[] = '%f'; // cost_price
        $format[] = '%f'; // retail_price
        $format[] = '%d'; // model_id
    }

    if ($unit_id <= 0 || $product_id <= 0 || empty($sku)) {
        wp_send_json_error('Invalid unit or product ID or no SKU provided.');
        return;
    }

    $table_name = $wpdb->prefix . 'mji_product_inventory_units';

    try {
        $result = $wpdb->update(
            $table_name,
            $data,
            ['id' => $unit_id],
            $format
        );

        if ($result === false) {
            custom_log('Database error: ' . $wpdb->last_error);
            wp_send_json_error('Database error: ' . $wpdb->last_error);
        }

        wp_send_json_success($result);
    } catch (Exception $e) {
        custom_log("Error " . $e->getMessage());
        wp_send_json_error(['message' => 'Error updating inventory unit: ' . $e->getMessage()]);
    }
}

add_action('wp_ajax_update_inventory_units', 'update_inventory_units');

function get_brand_model_id($table_name, $value)
{
    global $wpdb;

    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Create a unique transient key for this table and value
    $transient_key = 'brand_model_' . md5($table_name . '|' . $value);

    $cached_id = get_transient($transient_key);
    if ($cached_id !== false) {
        return $cached_id;
    }

    $sql = $wpdb->prepare(
        "SELECT id FROM $table_name WHERE name = %s LIMIT 1",
        $value
    );
    $existing_id = $wpdb->get_var($sql);

    if ($existing_id) {
        $id = $existing_id;
    } else {
        $inserted = $wpdb->insert(
            $table_name,
            [
                'name' => $value
            ]
        );
        if ($inserted === false) {
            custom_log('Database error: ' . $wpdb->last_error);
            wp_send_json_error($table_name . ' could\'nt be inserted: ' . $wpdb->last_error);
        } else {
            $id = $wpdb->insert_id;
        }
    }

    // Storing it in transietn for 30 days
    set_transient($transient_key, $id, DAY_IN_SECONDS * 30);

    return $id;
}

add_action('save_post_product', 'watch_simple_product_changes', 20, 3);
function watch_simple_product_changes($post_id, $post, $update)
{
    if (!$update || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;

    global $wpdb;
    $table_name = $wpdb->prefix . 'mji_product_inventory_units';

    // Update brands for simple and variable products
    $old_primary_cat = get_post_meta($post_id, 'rank_math_primary_product_cat', true);
    $new_primary_cat = isset($_POST['rank_math_primary_product_cat']) ? intval($_POST['rank_math_primary_product_cat']) : $old_primary_cat;

    if ($old_primary_cat != $new_primary_cat) {
        $category = get_term($new_primary_cat, 'product_cat');

        if ($category && !is_wp_error($category)) {
            $brand = $category->name;
            $brands_table = $wpdb->prefix . 'mji_brands';
            $brand_id = get_brand_model_id($brands_table, $brand);
            try {
                $wpdb->update(
                    $table_name,
                    [
                        'brand_id' => $brand_id,
                    ],
                    ['wc_product_id' => $post_id],
                    ['%d'],
                    ['%d']
                );
            } catch (Exception $e) {
                custom_log("Error " . $e->getMessage());
            }
        }
    }

    // Update prices for simple products only
    $product = wc_get_product($post_id);

    if ($product->is_type('variable') || $product->is_type('variation')) {
        return;
    }

    $old_price = get_post_meta($post_id, '_price', true);
    $new_price = isset($_POST['_regular_price']) ? sanitize_text_field($_POST['_regular_price']) : $old_price;

    $old_model = get_post_meta($post_id, '_sku', true);
    $new_model = isset($_POST['_sku']) ?
        sanitize_text_field($_POST['_sku']) : $old_model;

    if ($old_price != $new_price) {
        $result = $wpdb->update(
            $table_name,
            [
                'retail_price' => $new_price,
            ],
            [
                'wc_product_id' => $post_id,
                'status' => 'in_stock'
            ],
            ['%f'],
            ['%d', '%s']
        );
        if ($result === false) {
            mji_log_admin_error('Error updating price' . $wpdb->last_error);
        }
    }
    if ($old_model != $new_model) {
        $model_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT model_id FROM {$wpdb->prefix}mji_product_inventory_units WHERE wc_product_id = %d LIMIT 1",
                $post_id
            )
        );

        if (!$model_id) {
            mji_log_admin_error("No model found for ID $post_id");
            return;
        }

        $result = $wpdb->update(
            $wpdb->prefix . 'mji_models',
            ['name' => $new_model],
            ['id' => $model_id],
            ['%s'],
            ['%d']
        );

        if ($result === false) {
            mji_log_admin_error("Failed to update model name for model ID $model_id: " . $wpdb->last_error);
        }
    }
}

add_action('woocommerce_save_product_variation', 'watch_variation_retail_price', 5, 2);
function watch_variation_retail_price($variation_id, $i)
{
    $old_price = get_post_meta($variation_id, '_price', true);

    $new_price = isset($_POST['variable_regular_price'][$i])
        ? sanitize_text_field($_POST['variable_regular_price'][$i])
        : $old_price;

    $old_model = get_post_meta($variation_id, '_sku', true);
    $new_model = isset($_POST['variable_sku'][$i]) ?
        sanitize_text_field($_POST['variable_sku'][$i]) : $old_model;

    global $wpdb;

    $result = $wpdb->update(
        $wpdb->prefix . 'mji_product_inventory_units',
        [
            'retail_price' => $new_price,
        ],
        [
            'wc_product_variant_id' => $variation_id,
            'status' => 'in_stock'
        ],
        ['%f'],
        ['%d', '%s']
    );

    if ($result === false) {
        // Save the error so we can show it later as an admin notice
        mji_log_admin_error('Error updating price' . $wpdb->last_error);
    }

    $model_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT model_id FROM {$wpdb->prefix}mji_product_inventory_units WHERE wc_product_variant_id = %d LIMIT 1",
            $variation_id
        )
    );

    if (!$model_id) {
        mji_log_admin_error("No model found for variation ID $variation_id");
        return;
    }

    $result = $wpdb->update(
        $wpdb->prefix . 'mji_models',
        ['name' => $new_model],
        ['id' => $model_id],
        ['%s'],
        ['%d']
    );

    if ($result === false) {
        mji_log_admin_error("Failed to update model name for model ID $model_id: " . $wpdb->last_error);
    }
}
