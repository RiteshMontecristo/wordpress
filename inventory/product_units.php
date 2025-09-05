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

    $units = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE wc_product_id = %d ",
            $product_id
        )
    );
    $product = wc_get_product($product_id);

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
    $status = sanitize_text_field($_POST['status']);
    $location_id = intval($_POST['locationID']);
    $variation_id = isset($_POST['variationID']) ? intval($_POST['variationID']) : null;
    $product = wc_get_product($product_id);
    $cost_price = 0;
    $retail_price = 0;
    $table_name = $wpdb->prefix . 'mji_product_inventory_units';

    if ($product_id <= 0 || empty($sku) || $location_id <= 0) {
        wp_send_json_error('Invalid product ID, SKU or location ID.');
        return;
    }


    if ($variation_id) {
        $variation = wc_get_product($variation_id);
        $retail_price = $variation->get_price();
        $cost_price = get_post_meta($variation_id, '_cost_price', true);
    } else {
        $retail_price = $product->get_price();
        $cost_price = get_post_meta($product_id, '_cost_price', true);
    }

    try {
        $result = $wpdb->insert(
            $table_name,
            [
                'wc_product_id' => $product_id,
                'wc_product_variant_id' => $variation_id,
                'sku' => $sku,
                'status' => $status,
                'location_id' => $location_id,
                'cost_price' => $cost_price,
                'retail_price' => $retail_price
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


    if ($unit_id <= 0 || $product_id <= 0 || empty($sku)) {
        wp_send_json_error('Invalid unit or product ID or no SKU provided.');
        return;
    }

    $table_name = $wpdb->prefix . 'mji_product_inventory_units';

    try {
        $result = $wpdb->update(
            $table_name,
            [
                'wc_product_id' => $product_id,
                'wc_product_variant_id' => $variation_id,
                'sku' => $sku,
                'status' => $status,
                'location_id' => $location_id
            ],
            ['id' => $unit_id],
            [
                '%d', // wc_product_id
                '%d', // wc_product_variant_id
                '%s', // sku
                '%s', // status
                '%d'  // location_id
            ]
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
