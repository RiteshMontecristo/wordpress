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
    $cost_price = 0;
    $retail_price = 0;
    $units = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE wc_product_id = %d ",
            $product_id
        )
    );
    $product = wc_get_product($product_id);

    if ($product && $product->is_type('variable')) {
        $is_variation = true;
        $variation_data = generate_variation_dropdown($product, $variation_name_by_id);
        $variation_select = $variation_data['html'];
        $cost_price = $variation_data['default_cost'];
        $retail_price = $variation_data['default_retail'];
    } else {
        $retail_price = $product->get_price();
        $cost_price = get_post_meta($product_id, '_cost_price', true);
    }

    $all_location = mji_get_locations();
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

        #normal-sortables .postbox .submit {
            float: none;
        }
    </style>

    <p>Manage individual inventory units for this product.</p>
    <button id="open_add_modal">Add Unit</button>

    <table id="inventory-units-table" width="100%">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Status</th>
                <?php if ($is_variation): ?>
                    <th>Variation</th>
                <?php endif; ?>
                <th>Serial</th>
                <th>Location</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($units as $unit): ?>
                <tr class="unit-row"
                    data-product-id="<?= esc_attr($product_id) ?>"
                    data-unit-id="<?= esc_attr($unit->id) ?>"
                    data-sku="<?= esc_attr($unit->sku) ?>"
                    data-status="<?= esc_attr($unit->status) ?>"
                    data-variant="<?= esc_attr($unit->wc_product_variant_id) ?>"
                    data-serial="<?= esc_attr($unit->serial) ?>"
                    data-location="<?= esc_attr($unit->location_id) ?>"
                    data-supplier="<?= esc_attr($unit->supplier_id ?? '') ?>"
                    data-invoice-number="<?= esc_attr($unit->invoice_number ?? '') ?>"
                    data-invoice-date="<?= esc_attr(date('Y-m-d', strtotime($unit->created_date)) ?? '') ?>"
                    data-cost-price="<?= esc_attr($unit->cost_price) ?>"
                    data-true-cost="<?= esc_attr($unit->true_cost) ?>"
                    data-retail-price="<?= esc_attr($unit->retail_price) ?>"
                    data-notes="<?= esc_attr($unit->notes ?? '') ?>">

                    <td data-field="sku" data-value="<?= esc_html($unit->sku) ?>" class="editable-cell">
                        <?= esc_html($unit->sku) ?></td>
                    <td data-field="status" data-value="<?= esc_html($unit->status) ?>" class="editable-cell">
                        <?= esc_html($status[$unit->status]) ?></td>
                    <?php if ($is_variation): ?>
                        <td data-field="variant" data-value=" <?= esc_html($unit->wc_product_variant_id) ?>" class="editable-cell">
                            <?= esc_html($variation_name_by_id[$unit->wc_product_variant_id]) ?>
                        </td>
                    <?php endif; ?>
                    <td data-field="serial" data-value=" <?= esc_html($unit->serial) ?>" class="editable-cell">
                        <?= esc_html($unit->serial) ?>
                    </td>
                    <td data-field="location" data-value="<?= esc_html($unit->location_id) ?>" class="editable-cell">
                        <?= esc_html($location_name_by_id[$unit->location_id]) ?></td>
                    <td>
                        <button class="edit-unit button">Edit</button>
                    </td>
                </tr>
            <?php endforeach; ?>

        </tbody>
    </table>

    <!-- Inventory Unit Modal -->
    <div id="inventory_unit_modal" style="display:none;">
        <form name="inventoryUnitForm" id="inventoryUnitForm">
            <!-- Error message container -->
            <div id="modal_error_message" style="color:red; margin-bottom:10px; display:none;"></div>

            <input type="hidden" name="unit_id" id="modal_unit_id" value="" />
            <input type="hidden" name="product_id" id="modal_product_id" value="<?= esc_attr($product_id) ?>" />

            <table class="form-table">
                <tr>
                    <th><label for="modal_sku">SKU</label></th>
                    <td><input type="text" id="modal_sku" name="sku" /></td>
                </tr>

                <tr>
                    <th><label for="modal_status">Status</label></th>
                    <td>
                        <select id="modal_status" name="status">
                            <option value="in_stock">In Stock</option>
                            <option value="reserved">Reserved</option>
                            <option value="sold">Sold</option>
                            <option value="damaged">Damaged</option>
                        </select>
                    </td>
                </tr>

                <?php if ($is_variation): ?>
                    <tr>
                        <th><label for="variationID">Variation</label></th>
                        <td><?= $variation_select ?></td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <th><label for="modal_serial">Serial</label></th>
                    <td><input type="text" id="modal_serial" name="serial" /></td>
                </tr>

                <tr>
                    <th><label for="location">Location</label></th>
                    <td><?= mji_store_dropdown(false) ?></td>
                </tr>

                <tr>
                    <th><label for="supplierID">Supplier</label></th>
                    <td>
                        <?= mji_suppliers_dropdown(false) ?>
                    </td>
                </tr>

                <tr>
                    <th><label for="modal_invoice_number">Invoice #</label></th>
                    <td><input type="text" id="modal_invoice_number" name="invoice_number" /></td>
                </tr>

                <tr>
                    <th><label for="modal_invoice_date">Invoice Date</label></th>
                    <td><input type="date" id="modal_invoice_date" name="invoice_date" value="<?= wp_date('Y-m-d', time()); ?>" /></td>
                </tr>

                <tr>
                    <th><label for="modal_true_cost">True\Landed Cost</label></th>
                    <td><input type="number" step="0.01" id="modal_true_cost" name="true_cost" value="<?= $cost_price ?>" /></td>
                </tr>

                <tr>
                    <th><label for="modal_cost_price">Cost Price</label></th>
                    <td><input type="number" step="0.01" id="modal_cost_price" name="cost_price" value="<?= $cost_price ?>" /></td>
                </tr>

                <tr>
                    <th><label for="modal_retail_price">Retail Price</label></th>
                    <td><input type="number" step="0.01" id="modal_retail_price" name="retail_price" value="<?= $retail_price ?>" /></td>
                </tr>

                <tr>
                    <th><label for="modal_notes">Notes</label></th>
                    <td><textarea id="modal_notes" name="notes"></textarea></td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" id="modal_save" class="button button-primary">Save Unit</button>
                <button type="button" id="modal_cancel" class="button">Cancel</button>
            </p>
        </form>
    </div>

<?php
}

function generate_variation_dropdown($product, &$variation_name_by_id)
{

    $available_variations = $product->get_available_variations();

    $html = '<select id="variationID" name="new_variation_id" required>';

    foreach ($available_variations as $index => $variation_data) {

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

        // Get retail and cost price
        $variation_obj = wc_get_product($variation_id);
        $retail_price = $variation_obj ? $variation_obj->get_price() : '';
        $cost_price = $variation_obj ? get_post_meta($variation_id, '_cost_price', true) : '';

        // Save first variation prices as defaults
        if ($index === 0) {
            $first_retail_price = $retail_price;
            $first_cost_price = $cost_price;
        }

        $html .= "<option 
                    value='{$variation_id}' 
                    data-retail='{$retail_price}' 
                    data-cost='{$cost_price}'>
                    {$attr_string}
                  </option>";
    }

    $html .= '</select>';
    // Return both HTML and default prices
    return [
        'html' => $html,
        'default_retail' => $first_retail_price,
        'default_cost' => $first_cost_price
    ];
}

function create_inventory_units()
{
    global $wpdb;
    // MTMI22-0270 price diff
    // 3175	3500

    // Check required fields
    $errors = [];

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;
    $sku = isset($_POST['sku']) ? sanitize_text_field($_POST['sku']) : null;
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : null;
    $serial_number = (isset($_POST['serial']) && !is_empty($_POST['serial'])) ? $_POST['serial'] : null;
    $location_id = isset($_POST['location']) ? intval($_POST['location']) : null;
    $variation_id = isset($_POST['variationID']) ? intval($_POST['variationID']) : null;
    $invoice_number = isset($_POST['invoice_number']) ? sanitize_text_field($_POST['invoice_number']) : null;
    $invoice_date = isset($_POST['invoice_date']) ? sanitize_text_field($_POST['invoice_date']) : null;
    if ($invoice_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $invoice_date)) {
        $invoice_date = null; // invalid date
    }
    $true_cost = isset($_POST['true_cost']) ? floatval($_POST['true_cost']) : null;
    $cost_price = isset($_POST['cost_price']) ? floatval($_POST['cost_price']) : null;
    $retail_price = isset($_POST['retail_price']) ? floatval($_POST['retail_price']) : null;
    $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : null; // nl2br(esc_html()) when outputting notes in HTML.
    $supplier = isset($_POST['supplier']) ? sanitize_text_field($_POST['supplier']) : null;

    if (!$product_id) $errors[] = "Product ID is required.";
    if (!$sku) $errors[] = "SKU is required.";
    if (!$status) $errors[] = "Status is required.";
    if (!$location_id) $errors[] = "Location is required.";
    if (!$supplier) $errors[] = "Supplier is required.";
    if (!$invoice_number) $errors[] = "Invoice number is required.";
    if (!$invoice_date) $errors[] = "Invoice date is required.";
    if (!$true_cost) $errors[] = "True cost is required.";
    if (!$cost_price) $errors[] = "Cost price is required.";
    if (!$retail_price) $errors[] = "Retail price is required.";

    // If errors exist, return them
    if (!empty($errors)) {
        wp_send_json_error([
            'message' => 'Please fix the following errors:',
            'errors' => $errors
        ]);
    }

    $product = wc_get_product($product_id);
    $category_id = get_post_meta($product_id, 'rank_math_primary_product_cat', true);
    if ($category_id == 0) {
        wp_send_json_error('No primary category set for this product.');
    }
    $brand = get_term($category_id, 'product_cat')->name;
    $model = "";

    $table_name = $wpdb->prefix . 'mji_product_inventory_units';
    $models_table = $wpdb->prefix . 'mji_models';
    $brands_table = $wpdb->prefix . 'mji_brands';
    $suppliers_table = $wpdb->prefix . 'mji_suppliers';

    // Grab the model number and also if price changed, need to change woocommerce products price and quantity change as well
    if ($variation_id) {
        $variation = wc_get_product($variation_id);
        $wc_retail_price = $variation->get_price();
        $wc_cost_price = get_post_meta($variation_id, '_cost_price', true);
        wc_update_product_stock($variation_id, 1, 'increase');
        WC_Product_Variable::sync_stock_status($variation->get_parent_id());

        if ($wc_retail_price !== $retail_price) {
            update_post_meta($variation_id, '_regular_price', $retail_price);
            update_post_meta($variation_id, '_price', $retail_price);
            WC_Product_Variable::sync($product_id);
        }

        if ($wc_cost_price !== $cost_price) {
            update_post_meta($variation_id, '_cost_price', $cost_price);
        }

        // Deleting stale data so customer gets correct info
        wc_delete_product_transients($variation_id);
        wc_delete_product_transients($product_id);
        $model = $variation->get_sku();
    } else {
        $wc_retail_price = $product->get_price();
        $wc_cost_price = get_post_meta($product_id, '_cost_price', true);
        wc_update_product_stock($product_id, 1, 'increase');

        if ($wc_retail_price !== $retail_price) {
            update_post_meta($product_id, '_regular_price', $retail_price);
            update_post_meta($product_id, '_price', $retail_price);
        }

        if ($wc_cost_price !== $cost_price) {
            update_post_meta($product_id, '_cost_price', $cost_price);
        }

        wc_delete_product_transients($product_id);
        $model = $product->get_sku();
    }

    $model_id = get_brand_model_id($models_table, $model);
    $brand_id = get_brand_model_id($brands_table, $brand);

    // If numeric, it is an existing supplier else create new
    if (ctype_digit($supplier)) {
        $supplier_id = intval($supplier);
    } else {
        $wpdb->insert($suppliers_table, [
            'name' => $supplier
        ]);

        $supplier_id = $wpdb->insert_id;
        delete_transient('mji_suppliers');
    }

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
                'invoice_number' => $invoice_number,
                'created_date' => $invoice_date,
                'true_cost' => $true_cost,
                'cost_price' => $cost_price,
                'retail_price' => $retail_price,
                'model_id' => $model_id,
                'brand_id' => $brand_id,
                'supplier_id' => $supplier_id,
                'notes' => $notes
            ]
        );

        if ($result === false) {
            custom_log('Database error: ' . $wpdb->last_error);
            wp_send_json_error(
                [
                    'message' => 'Please fix the following errors:',
                    'errors' => 'Database error: ' . $wpdb->last_error
                ]
            );
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
    $serial = isset($_POST['serialNum']) && !is_empty($_POST['serialNum']) ? sanitize_text_field($_POST['serialNum']) : null;

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

// Grab the brand and model id
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

// Save for the normal product
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

// Save for the variant product
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
