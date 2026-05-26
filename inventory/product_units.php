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

function render_inventory_units_meta_box(object $post)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'mji_product_inventory_units';
    $sku_history_table = $wpdb->prefix . 'mji_product_sku_history';
    $status_history_table = $wpdb->prefix . 'mji_inventory_status_history';
    $payments_table = $wpdb->prefix . 'mji_payments';
    $customers_table = $wpdb->prefix . 'mji_customers';
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';
    $models_table = $wpdb->prefix . 'mji_models';
    $brands_table = $wpdb->prefix . 'mji_brands';
    $product_id = $post->ID;
    $is_variation = false;
    $location_name_by_id = [];
    $variation_name_by_id = [];
    $variation_select = '';
    $cost_price = 0;
    $retail_price = 0;

    $units = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT u.*, m.name AS model_name, b.name AS brand_name
             FROM $table_name u
             LEFT JOIN $models_table m ON m.id = u.model_id
             LEFT JOIN $brands_table b ON b.id = u.brand_id
             WHERE u.wc_product_id = %d",
            $product_id
        ),
        ARRAY_A
    );

    if (! empty($units)) {
        $unit_ids = wp_list_pluck($units, 'id');
        $ids_placeholder = implode(',', array_fill(0, count($unit_ids), '%d'));

        $sku_history = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $sku_history_table WHERE unit_id IN ($ids_placeholder) ORDER BY changed_date",
                ...$unit_ids
            ),
            ARRAY_A
        );

        $sku_by_unit = [];
        foreach ($sku_history as $sku) {
            $sku_by_unit[$sku['unit_id']][] = $sku;
        }

        $status_history = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $status_history_table sh
                 WHERE inventory_unit_id IN ($ids_placeholder) ORDER BY created_at",
                ...$unit_ids
            ),
            ARRAY_A
        );

        $reference_numbers = wp_list_pluck($status_history, 'reference_num');
        $reference_numbers = array_unique($reference_numbers);
        $reference_numbers = array_filter($reference_numbers);

        $payments = [];
        if (! empty($reference_numbers)) {
            $ref_placeholders = implode(',', array_fill(0, count($reference_numbers), '%s'));

            $payments = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT p.reference_num, c.first_name, c.last_name, s.first_name AS salesperson_first_name, s.last_name AS salesperson_last_name FROM $payments_table p
                    LEFT JOIN $customers_table c 
                        ON c.id = p.customer_id
                    LEFT JOIN $salespeople_table s
                        ON s.id = p.salesperson_id
                    WHERE reference_num IN ($ref_placeholders)",
                    ...$reference_numbers
                ),
                ARRAY_A
            );
        }

        $payments_by_ref = [];
        foreach ($payments as $payment) {
            $ref = $payment['reference_num'];
            if (! isset($payments_by_ref[$ref])) {
                $payments_by_ref[$ref] = [];
            }
            $payments_by_ref[$ref][] = $payment;
        }
        foreach ($status_history as &$status) {
            $ref = $status['reference_num'];
            $status['payments'] = $payments_by_ref[$ref] ?? [];
        }
        unset($status);

        $status_by_unit = [];
        foreach ($status_history as $status) {
            $status_by_unit[$status['inventory_unit_id']][] = $status;
        }

        foreach ($units as &$unit) {
            $unit['sku_history'] = $sku_by_unit[$unit['id']] ?? [];
            $unit['status_history'] = $status_by_unit[$unit['id']] ?? [];
        }
        unset($unit);
    }

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

    <p>Manage individual inventory units for this product.</p>
    <button id="open_add_modal" class="button-primary">Add Unit</button>

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
            <?php foreach ($units as $unit):
                $old_sku_el = '';

                if (!empty($unit["sku_history"])) {
                    foreach ($unit["sku_history"] as $history) {
                        $old_sku_el .= $history["old_sku"] . "->";
                    }
                }
                $jsonHistory = json_encode($unit["status_history"]);
                $escapedJson = htmlspecialchars($jsonHistory, ENT_QUOTES, 'UTF-8');

                $unit_image_url = mji_get_unit_image_url($unit, 'medium');
                $unit_desc = $unit['description'] ? str_replace('•', '<br />', $unit['description']) : '';
            ?>
                <tr class="unit-row"
                    data-product-id="<?= esc_attr($product_id) ?>"
                    data-unit-id="<?= esc_attr($unit['id']) ?>"
                    data-sku="<?= esc_attr($unit['sku']) ?>"
                    data-status="<?= esc_attr($unit['status']) ?>"
                    data-variant="<?= esc_attr($unit['wc_product_variant_id']) ?>"
                    data-serial="<?= esc_attr($unit['serial']) ?>"
                    data-location="<?= esc_attr($unit['location_id']) ?>"
                    data-supplier="<?= esc_attr($unit['supplier_id'] ?? '') ?>"
                    data-invoice-number="<?= esc_attr($unit['invoice_number'] ?? '') ?>"
                    data-invoice-date="<?= esc_attr(date('Y-m-d', strtotime($unit['created_date'])) ?? '') ?>"
                    data-cost-price="<?= esc_attr($unit['cost_price']) ?>"
                    data-true-cost="<?= esc_attr($unit['true_cost']) ?>"
                    data-retail-price="<?= esc_attr($unit['retail_price']) ?>"
                    data-notes="<?= esc_attr($unit['notes'] ?? '') ?>"
                    data-history="<?= $escapedJson ?>"
                    data-model-name="<?= esc_attr($unit['model_name'] ?? '') ?>"
                    data-brand-name="<?= esc_attr($unit['brand_name'] ?? '') ?>"
                    data-spec-1="<?= esc_attr($unit['spec_1'] ?? '') ?>"
                    data-spec-2="<?= esc_attr($unit['spec_2'] ?? '') ?>"
                    data-image-url="<?= esc_attr($unit_image_url) ?>"
                    data-description="<?= esc_attr($unit_desc) ?>">

                    <td data-field="sku" data-value="<?= esc_html($unit['sku']) ?>" class="editable-cell">
                        <?= $old_sku_el ?><?= esc_html($unit['sku']) ?></td>
                    <td data-field="status" data-value="<?= esc_html($unit['status']) ?>" class="editable-cell">
                        <?= esc_html($unit['status']) ?></td>
                    <?php if ($is_variation): ?>
                        <td data-field="variant" data-value=" <?= esc_html($unit['wc_product_variant_id']) ?>" class="editable-cell">
                            <?= esc_html($variation_name_by_id[$unit['wc_product_variant_id']]) ?>
                        </td>
                    <?php endif; ?>
                    <td data-field="serial" data-value=" <?= esc_html($unit['serial']) ?>" class="editable-cell">
                        <?= esc_html($unit['serial']) ?>
                    </td>
                    <td data-field="location" data-value="<?= esc_html($unit['location_id']) ?>" class="editable-cell">
                        <?= esc_html($location_name_by_id[$unit['location_id']]) ?></td>
                    <td>
                        <?= $unit['status'] !== 'sold' ? '<button class="edit-unit button">Edit</button>' : '' ?>
                        <?= $unit['status'] !== 'sold' ? '<button class="edit-status button">Change Status</button>' : '' ?>
                        <button class="view-history button">View History</button>
                        <div class="print-dropdown" style="display:inline-block;position:relative;">
                            <button type="button" class="button print-dropdown-toggle">Print &#9660;</button>
                            <div class="print-dropdown-menu" hidden style="position:absolute;z-index:100;background:#fff;border:1px solid #ccc;min-width:110px;">
                                <button type="button" class="button print-zebra-tag" style="display:block;width:100%;text-align:left;">Zebra Tag</button>
                                <button type="button" class="button print-card" style="display:block;width:100%;text-align:left;">Card</button>
                            </div>
                        </div>
                        <button class="delete-unit button">Delete</button>
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
                        <?php mji_suppliers_dropdown(false) ?>
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
                    <th><label for="modal_spec_1">Spec 1</label></th>
                    <td><input type="text" id="modal_spec_1" name="spec_1" /></td>
                </tr>

                <tr>
                    <th><label for="modal_spec_2">Spec 2</label></th>
                    <td><input type="text" id="modal_spec_2" name="spec_2" /></td>
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

    <div id="edit-status-modal" class="return-container hidden">
        <div class="return">
            <h2 class="form-title">Update Item Status for <span id="sku"></span></h2>
            <form id="statusForm">

                <input type="hidden" id="unit-id" name="unit-id" value="" />
                <input type="hidden" name="action" value="update_unit_status" />
                <input type="hidden" id="product-id" name="product-id" value="" />

                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" required>
                        <option value="in_stock">In Stock</option>
                        <option value="damaged">Damaged</option>
                        <option value="dismantled">Dismantled</option>
                        <option value="missing">Missing</option>
                        <option value="rtv">Return to Vendor</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="updateDate">Date of Status Change:</label>
                    <input
                        type="date"
                        id="updateDate"
                        name="updateDate"
                        required />
                </div>

                <div class="form-group">
                    <label for="notes">Notes:</label>
                    <textarea
                        id="notes"
                        name="notes"></textarea>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password" />
                </div>

                <p id="error" class="alert hidden">Sorry there was an issue, Please try again later!!</p>
                <div class="btn-container">
                    <button id="confirm" class="button-primary" type="submit">Update Status</button>
                    <button id="cancel" class="button">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="view-history-modal" class="return-container hidden">
        <div class="view-history-container">
            <button type="button" class="close-history">&#10005;</button>
            <h2 class="form-title">Item History for <span id="sku"></span></h2>

            <div class="item-status-container">
            </div>
        </div>
    </div>
    <?php
}

function generate_variation_dropdown(WC_Product_Variable $product, array &$variation_name_by_id): array
{

    $available_variations = $product->get_available_variations();
    $first_retail_price   = 0;
    $first_cost_price     = 0;

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

function delete_inventory_unit()
{
    check_ajax_referer('mji_inventory_nonce', 'nonce');

    global $wpdb;
    $unit_id = isset($_POST['unit_id']) ? intval($_POST['unit_id']) : null;
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;
    $variant_id = isset($_POST['variant_id']) ? intval($_POST['variant_id']) : null;

    if (!$unit_id) $errors[] = wp_send_json_error([
        'message' => 'Unit ID required!!',
    ]);
    if (!$product_id) $errors[] = wp_send_json_error([
        'message' => 'Product ID required!!',
    ]);

    $table_name = $wpdb->prefix . 'mji_product_inventory_units';
    $order_items_table = $wpdb->prefix . 'mji_order_items';
    $return_items_table = $wpdb->prefix . 'mji_return_items';

    $wpdb->query('START TRANSACTION');
    try {
        if ($unit_id) {

            // Check order items table
            $order_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $order_items_table WHERE product_inventory_unit_id = %d",
                $unit_id
            ));
            if ($order_count > 0) {
                wp_send_json_error(
                    [
                        'message' => 'Unable to delete as the unit has already been sold!!',
                    ]
                );
            }
            $order_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $return_items_table WHERE product_inventory_unit_id = %d",
                $unit_id
            ));

            if ($order_count > 0) {
                wp_send_json_error(
                    [
                        'message' => 'Unable to delete as the unit has already been sold and returned!!',
                    ]
                );
            }

            // Read status before deleting — only in_stock units contribute to WC stock
            $unit_status = $wpdb->get_var($wpdb->prepare(
                "SELECT status FROM {$table_name} WHERE id = %d",
                $unit_id
            ));

            // Deletes the product_collections_table and inventory history with the inventory unit as its a cascade
            $result = $wpdb->delete(
                $table_name,
                ['id' => $unit_id],
            );

            if ($result === false) {
                throw new Exception($wpdb->last_error);
            }

            // Non-in_stock statuses already decremented WC stock when status changed;
            // decrementing again on delete would make stock go negative
            if ($unit_status === 'in_stock') {
                wc_update_product_stock($variant_id ?: $product_id, 1, 'decrease');
            }

            // CASCADE on products_collections handles cleanup when the unit is deleted

            $wpdb->query('COMMIT');
            wp_send_json_success($result);
        }
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        custom_log("Error " . $e->getMessage());
        wp_send_json_error(['message' => 'Error deleting inventory unit: ' . $e->getMessage()]);
    }
}
add_action('wp_ajax_delete_inventory_unit', 'delete_inventory_unit');

function create_inventory_units()
{
    check_ajax_referer('mji_inventory_nonce', 'nonce');

    global $wpdb;

    // Check required fields
    $errors = [];

    $unit_id = isset($_POST['unit_id']) ? intval($_POST['unit_id']) : null;
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;
    $variation_id = isset($_POST['variationID']) ? intval($_POST['variationID']) : null;
    $sku = isset($_POST['sku']) ? strtoupper(sanitize_text_field($_POST['sku'])) : null;
    $serial_number = (isset($_POST['serial']) && !empty($_POST['serial'])) ? $_POST['serial'] : null;
    $location_id = isset($_POST['location']) ? intval($_POST['location']) : null;
    $invoice_number = isset($_POST['invoice_number']) ? sanitize_text_field($_POST['invoice_number']) : null;
    $invoice_date = isset($_POST['invoice_date']) ? sanitize_text_field($_POST['invoice_date']) : null;
    if ($invoice_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $invoice_date)) {
        $invoice_date = null; // invalid date
    }
    $true_cost = isset($_POST['true_cost']) ? floatval($_POST['true_cost']) : null;
    $cost_price = isset($_POST['cost_price']) ? floatval($_POST['cost_price']) : null;
    $retail_price = isset($_POST['retail_price']) ? floatval($_POST['retail_price']) : null;
    $notes   = isset($_POST['notes'])  ? sanitize_textarea_field($_POST['notes'])  : null;
    $spec_1  = isset($_POST['spec_1']) ? sanitize_text_field($_POST['spec_1'])    : null;
    $spec_2  = isset($_POST['spec_2']) ? sanitize_text_field($_POST['spec_2'])    : null;
    $supplier = isset($_POST['supplier']) ? sanitize_text_field($_POST['supplier']) : null;

    if (!$product_id)
        $errors[] = "Product ID is required.";
    if (!$sku)
        $errors[] = "SKU is required.";
    if (!$location_id)
        $errors[] = "Location is required.";
    if (!$supplier)
        $errors[] = "Supplier is required.";
    if (!$invoice_number)
        $errors[] = "Invoice number is required.";
    if (!$invoice_date)
        $errors[] = "Invoice date is required.";
    if (!$true_cost)
        $errors[] = "True cost is required.";
    if (!$cost_price)
        $errors[] = "Cost price is required.";
    if (!$retail_price)
        $errors[] = "Retail price is required.";

    // If errors exist, return them
    if (!empty($errors)) {
        wp_send_json_error([
            'message' => 'Please fix the following errors:',
            'errors' => $errors
        ]);
    }

    $product = wc_get_product($product_id);
    $variation = $variation_id ? wc_get_product($variation_id) : null;
    $brand_id = get_post_meta($product_id, 'rank_math_primary_product_brand', true);

    $brand = '';
    if ($brand_id != 0) {
        $brand = get_term($brand_id, 'product_brand')->name;
    }
    $model = "";

    $table_name = $wpdb->prefix . 'mji_product_inventory_units';
    $models_table = $wpdb->prefix . 'mji_models';
    $brands_table = $wpdb->prefix . 'mji_brands';
    $suppliers_table = $wpdb->prefix . 'mji_suppliers';

    // Grab the model number
    if ($variation) {
        $model = $variation->get_sku();
        if (empty($model)) {
            $model = $product->get_sku();
        }
    } else {
        $model = $product->get_sku();
    }

    $model_id = get_brand_model_id($models_table, $model);
    if ($model_id === false) {
        $wpdb->query('ROLLBACK');
        wp_send_json_error(['message' => 'Failed to find or create model.']);
    }
    $brand_id = $brand_id != 0 ? get_brand_model_id($brands_table, $brand) : NULL;

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

    // Resolve image: variant thumbnail → product thumbnail → null
    $image_id = null;
    if ($variation_id) {
        $image_id = (int) get_post_thumbnail_id($variation_id) ?: null;
    }
    if (!$image_id) {
        $image_id = (int) get_post_thumbnail_id($product_id) ?: null;
    }

    try {
        $wpdb->query('START TRANSACTION');
        // If unit id present then update else need to create product unit
        if ($unit_id) {
            $current_status = $wpdb->get_var($wpdb->prepare(
                "SELECT status FROM $table_name WHERE id = %d", $unit_id
            ));
            if ($current_status === 'sold') {
                wp_send_json_error(['message' => 'Sold units cannot be edited.']);
            }

            update_unit_sku($unit_id, $sku);

            $old_variant_id = $wpdb->get_var(
                $wpdb->prepare("SELECT wc_product_variant_id FROM $table_name WHERE id = %d", $unit_id)
            );

            $result = $wpdb->update(
                $table_name,
                [
                    'wc_product_id'        => $product_id,
                    'wc_product_variant_id' => $variation_id,
                    'sku'                  => $sku,
                    'serial'               => $serial_number,
                    'location_id'          => $location_id,
                    'invoice_number'       => $invoice_number,
                    'created_date'         => $invoice_date,
                    'true_cost'            => $true_cost,
                    'cost_price'           => $cost_price,
                    'retail_price'         => $retail_price,
                    'model_id'             => $model_id,
                    'brand_id'             => $brand_id,
                    'supplier_id'          => $supplier_id,
                    'notes'                => $notes,
                    'spec_1'               => $spec_1 ?: null,
                    'spec_2'               => $spec_2 ?: null,
                    'image_id'             => $image_id,
                ],
                ['id' => $unit_id],
            );

            if ($result === false) {
                custom_log('Database error: ' . $wpdb->last_error);
                $wpdb->query('ROLLBACK');
                wp_send_json_error(
                    [
                        'message' => 'Please fix the following errors:',
                        'errors' => 'Database error: ' . $wpdb->last_error
                    ]
                );
            }
            // if price changed, need to change woocommerce products price and quantity change as well
            if ($variation) {
                $wc_retail_price = $variation->get_price();
                $wc_cost_price = get_post_meta($variation_id, '_cost_price', true);

                if ($wc_retail_price !== $retail_price) {
                    update_post_meta($variation_id, '_regular_price', $retail_price);
                    update_post_meta($variation_id, '_price', $retail_price);
                    WC_Product_Variable::sync($product_id);
                }

                if ($wc_cost_price !== $cost_price) {
                    update_post_meta($variation_id, '_cost_price', $cost_price);
                }

                if ($old_variant_id !== $variation_id) {
                    wc_update_product_stock($variation_id, 1, 'increase');
                    wc_update_product_stock($old_variant_id, 1, 'decrease');
                }

                // Deleting stale data so customer gets correct info
                wc_delete_product_transients($variation_id);
                wc_delete_product_transients($product_id);
            } else {
                $wc_retail_price = $product->get_price();
                $wc_cost_price = get_post_meta($product_id, '_cost_price', true);

                if ($wc_retail_price !== $retail_price) {
                    update_post_meta($product_id, '_regular_price', $retail_price);
                    update_post_meta($product_id, '_price', $retail_price);
                }

                if ($wc_cost_price !== $cost_price) {
                    update_post_meta($product_id, '_cost_price', $cost_price);
                }

                wc_delete_product_transients($product_id);
            }

            $wpdb->query('COMMIT');
            wp_send_json_success($result);
        } else {
            sku_exists_anywhere($sku);

            $result = $wpdb->insert(
                $table_name,
                [
                    'wc_product_id'        => $product_id,
                    'wc_product_variant_id' => $variation_id,
                    'sku'                  => $sku,
                    'serial'               => $serial_number,
                    'status'               => 'in_stock',
                    'location_id'          => $location_id,
                    'invoice_number'       => $invoice_number,
                    'created_date'         => $invoice_date,
                    'true_cost'            => $true_cost,
                    'cost_price'           => $cost_price,
                    'retail_price'         => $retail_price,
                    'model_id'             => $model_id,
                    'brand_id'             => $brand_id,
                    'supplier_id'          => $supplier_id,
                    'notes'                => $notes,
                    'spec_1'               => $spec_1 ?: null,
                    'spec_2'               => $spec_2 ?: null,
                    'image_id'             => $image_id,
                ]
            );

            if ($result === false) {
                custom_log('Database error: ' . $wpdb->last_error);
                $wpdb->query('ROLLBACK');
                wp_send_json_error(
                    [
                        'message' => 'Please fix the following errors:',
                        'errors' => 'Database error: ' . $wpdb->last_error
                    ]
                );
            }

            $inventory_unit_id = $wpdb->insert_id;

            if (!mji_insert_unit_history($inventory_unit_id, null, 'in_stock', 'Initial status on creation', $invoice_date)) {
                custom_log('Status history insert error: ' . $wpdb->last_error);
                $wpdb->query('ROLLBACK');
                wp_send_json_error([
                    'message' => 'Inventory inserted but failed to create status history',
                    'errors'  => 'Database error: ' . $wpdb->last_error,
                ]);
            }
            // if price changed, need to change woocommerce products price and quantity change as well
            if ($variation) {
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
            }
            $product_collection = get_the_terms($product_id, 'collection');
            if ($product_collection && !is_wp_error($product_collection)) {
                $product_collection_names = wp_list_pluck($product_collection, 'name');
                sync_product_collections($inventory_unit_id, $product_collection_names);
            } else {
                sync_product_collections($inventory_unit_id, array());
            }
            $wpdb->query('COMMIT');
            wp_send_json_success($result);
        }
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        custom_log("Error " . $e->getMessage());
        wp_send_json_error(['message' => 'Error creating inventory unit: ' . $e->getMessage()]);
    }
}

add_action('wp_ajax_create_inventory_units', 'create_inventory_units');

// Grab the brand and model id
function get_brand_model_id($table_name, $value)
{
    global $wpdb;

    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    if (empty($value)) return null;

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
            return false;
        } else {
            $id = $wpdb->insert_id;
        }
    }

    return $id;
}

// to check if the sku exists in the two table before creating
function sku_exists_anywhere($new_sku)
{
    global $wpdb;

    $units_table = $wpdb->prefix . 'mji_product_inventory_units';
    $history_table = $wpdb->prefix . 'mji_product_sku_history';

    // Check current units table
    $exists_units = $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM $units_table WHERE sku = %s", $new_sku)
    );

    // Check old SKUs history
    $exists_history = $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM $history_table WHERE old_sku = %s", $new_sku)
    );

    if ($exists_units > 0 || $exists_history > 0) {
        wp_send_json_error(
            [
                'message' => 'Please fix the following errors:',
                'errors' => 'The SKU exists already'
            ]
        );
    };
}

function update_unit_sku($unit_id, $new_sku)
{
    global $wpdb;

    $units_table = $wpdb->prefix . 'mji_product_inventory_units';
    $history_table = $wpdb->prefix . 'mji_product_sku_history';

    // Fetch current SKU
    $current_sku = $wpdb->get_var(
        $wpdb->prepare("SELECT sku FROM $units_table WHERE id = %d", $unit_id)
    );

    // If same SKU, do nothing
    if ($current_sku === $new_sku) {
        return true;
    }

    sku_exists_anywhere($new_sku);

    $wpdb->insert($history_table, [
        'unit_id' => $unit_id,
        'old_sku' => $current_sku
    ]);
}

// Save for the normal product
add_action('save_post_product', 'watch_simple_product_changes', 20, 3);
function watch_simple_product_changes($post_id, $post, $update)
{
    if (!$update || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return;

    // RANK MATH is not setting the primary category after the plugin update so created our own save as we need it to determine the brands
    if (isset($_POST['rank_math_primary_product_cat'])) {
        $primary_cat_id = absint($_POST['rank_math_primary_product_cat']);
        update_post_meta($post_id, 'rank_math_primary_product_cat', $primary_cat_id);
    }

    // RANK MATH is not setting the primary brand
    if (isset($_POST['rank_math_primary_product_brand'])) {
        $primary_brand_id = absint($_POST['rank_math_primary_product_brand']);
        update_post_meta($post_id, 'rank_math_primary_product_brand', $primary_brand_id);
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'mji_product_inventory_units';

    $exists_units = $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE wc_product_id = %s", $post_id)
    );

    if (!$exists_units) return;

    if (isset($_POST['rank_math_primary_product_brand']) && $_POST['rank_math_primary_product_brand']) {
        // Brand explicitly set in the WC form
        $new_primary_brand = intval($_POST['rank_math_primary_product_brand']);
        $brand = get_term($new_primary_brand, 'product_brand');
        if ($brand && !is_wp_error($brand)) {
            $brand_name = $brand->name;
            $brands_table = $wpdb->prefix . 'mji_brands';
            $brand_id = get_brand_model_id($brands_table, $brand_name);
            if ($brand_id === false) return;
            $wpdb->query($wpdb->prepare(
                "UPDATE {$table_name}
                 SET brand_id = %d
                 WHERE wc_product_id = %d
                   AND status NOT IN ('sold','rtv')",
                $brand_id, $post_id
            ));
        }
    } elseif (isset($_POST['rank_math_primary_product_brand'])) {
        // Brand explicitly cleared in the WC form (field present but empty)
        $wpdb->query($wpdb->prepare(
            "UPDATE {$table_name}
             SET brand_id = NULL
             WHERE wc_product_id = %d
               AND status NOT IN ('sold','rtv')",
            $post_id
        ));
    }
    // If $_POST has no brand key at all it's a programmatic save — don't touch brand

    // Update prices for simple products only
    $product = wc_get_product($post_id);

    if ($product->is_type('variable') || $product->is_type('variation')) {
        return;
    }

    // Post meta is already updated at priority 20 — can't use it as the "old"
    // value. Compare the incoming form value against what we have stored in the
    // inventory DB so the comparison is meaningful.
    $new_price = (isset($_POST['_regular_price']) && $_POST['_regular_price'] !== '')
        ? (float) sanitize_text_field($_POST['_regular_price'])
        : null;
    $new_model = isset($_POST['_sku']) ? sanitize_text_field($_POST['_sku']) : null;

    if ($new_price !== null) {
        $current_price = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT retail_price FROM {$table_name}
             WHERE wc_product_id = %d
               AND status NOT IN ('sold','rtv')
             LIMIT 1",
            $post_id
        ));
        if ($new_price != $current_price) {
            $result = $wpdb->query($wpdb->prepare(
                "UPDATE {$table_name}
                 SET retail_price = %f
                 WHERE wc_product_id = %d
                   AND status NOT IN ('sold','rtv')",
                $new_price, $post_id
            ));
            if ($result === false) {
                mji_log_admin_error('Error updating price: ' . $wpdb->last_error);
            }
        }
    }

    if ($new_model === null) {
        // SKU field absent from POST — programmatic save, don't touch model
    } elseif ($new_model === '') {
        // SKU explicitly cleared — remove model link from all active units
        $wpdb->query($wpdb->prepare(
            "UPDATE {$table_name} SET model_id = NULL
             WHERE wc_product_id = %d
               AND status NOT IN ('sold','rtv')",
            $post_id
        ));
    } else {
        $models_table = $wpdb->prefix . 'mji_models';
        $model_id = $wpdb->get_var($wpdb->prepare(
            "SELECT model_id FROM {$table_name}
             WHERE wc_product_id = %d
               AND status NOT IN ('sold','rtv')
             LIMIT 1",
            $post_id
        ));

        if (!$model_id) {
            // No active unit has a model linked — find or create by SKU name and link all active units
            $model_id = get_brand_model_id($models_table, $new_model);
            if ($model_id) {
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$table_name} SET model_id = %d
                     WHERE wc_product_id = %d
                       AND status NOT IN ('sold','rtv')",
                    $model_id, $post_id
                ));
                delete_transient('mji_models');
            }
            return;
        }

        $current_model = $wpdb->get_var($wpdb->prepare(
            "SELECT name FROM {$models_table} WHERE id = %d",
            $model_id
        ));

        if ($current_model != $new_model) {
            $result = $wpdb->update(
                $models_table,
                ['name' => $new_model],
                ['id' => $model_id],
                ['%s'],
                ['%d']
            );
            if ($result === false) {
                mji_log_admin_error("Failed to update model name for model ID $model_id: " . $wpdb->last_error);
            } else {
                delete_transient('mji_models');
            }
        }
    }
}

// Save for the variant product
add_action('woocommerce_save_product_variation', 'watch_variation_retail_price', 5, 2);
function watch_variation_retail_price($variation_id, $i)
{

    global $wpdb;
    $table_name = $wpdb->prefix . "mji_product_inventory_units";

    $exists_units = $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE wc_product_variant_id = %s", $variation_id)
    );

    if (!$exists_units) return;

    // Compare incoming form values against inventory DB — post meta is already
    // updated at the time this hook fires, so it can't serve as the "old" value
    $new_price = (isset($_POST['variable_regular_price'][$i]) && $_POST['variable_regular_price'][$i] !== '')
        ? (float) sanitize_text_field($_POST['variable_regular_price'][$i])
        : null;

    $is_human_save = isset($_POST['variable_sku'][$i]);
    $new_model = $is_human_save
        ? sanitize_text_field($_POST['variable_sku'][$i])
        : null;

    // Fall back to parent SKU when variant SKU is empty — whether absent or cleared
    // Only on human save; '' after fallback means both are empty so model should be cleared
    if (empty($new_model)) {
        $parent_id = wp_get_post_parent_id($variation_id);
        $new_model = get_post_meta($parent_id, '_sku', true) ?: ($is_human_save ? '' : null);
    }

    if ($new_price !== null) {
        $current_price = (float) $wpdb->get_var($wpdb->prepare(
            "SELECT retail_price FROM {$table_name}
             WHERE wc_product_variant_id = %d
               AND status NOT IN ('sold','rtv')
             LIMIT 1",
            $variation_id
        ));
        if ($new_price != $current_price) {
            $result = $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}mji_product_inventory_units
                 SET retail_price = %f
                 WHERE wc_product_variant_id = %d
                   AND status NOT IN ('sold','rtv')",
                $new_price, $variation_id
            ));
            if ($result === false) {
                mji_log_admin_error('Error updating price: ' . $wpdb->last_error);
            }
        }
    }

    if ($new_model === null) {
        // Field absent and no parent SKU — programmatic save, don't touch model
    } elseif ($new_model === '') {
        // Explicitly cleared — remove model link from all active units for this variation
        $wpdb->query($wpdb->prepare(
            "UPDATE {$table_name} SET model_id = NULL
             WHERE wc_product_variant_id = %d
               AND status NOT IN ('sold','rtv')",
            $variation_id
        ));
    } else {
        $models_table = $wpdb->prefix . 'mji_models';
        $current_model_id = $wpdb->get_var($wpdb->prepare(
            "SELECT model_id FROM {$table_name}
             WHERE wc_product_variant_id = %d
               AND status NOT IN ('sold','rtv')
             LIMIT 1",
            $variation_id
        ));

        // Always find-or-create the target model by name — never rename in place.
        // Renaming would affect all other units sharing the same model row (e.g. the
        // other variant that still uses the parent SKU).
        $new_model_id = get_brand_model_id($models_table, $new_model);
        if ($new_model_id === false) return;

        if ($new_model_id != $current_model_id) {
            $wpdb->query($wpdb->prepare(
                "UPDATE {$table_name} SET model_id = %d
                 WHERE wc_product_variant_id = %d
                   AND status NOT IN ('sold','rtv')",
                $new_model_id, $variation_id
            ));
            delete_transient('mji_models');
        }
    }
}

// Change SKU label to Model in WooCommerce admin
add_action('admin_footer', function () {
    global $pagenow, $post_type;

    // Only run on product edit pages
    if ($pagenow === 'post.php' || $pagenow === 'post-new.php') {
        if ($post_type === 'product') {
    ?>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    // Change the SKU label text
                    $('label[for="_sku"] abbr').text('Model');
                    $('label[for="_sku"] abbr').attr('title', 'Model'); // optional: change tooltip too
                });
            </script>
<?php
        }
    }
});

function change_status()
{
    check_ajax_referer('mji_inventory_nonce', 'nonce');

    $product_id = absint($_POST['product-id'] ?? 0);
    $unit_id    = absint($_POST['unit-id'] ?? 0);
    $status     = isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : '';
    $date       = isset($_POST['updateDate']) ? sanitize_text_field(wp_unslash($_POST['updateDate'])) : '';
    $notes      = isset($_POST['notes']) ? sanitize_textarea_field(wp_unslash($_POST['notes'])) : '';
    $password   = isset($_POST['password']) ? wp_unslash($_POST['password']) : '';

    if (empty($unit_id)) {
        wp_send_json_error(['message' => 'Unit ID is required.']);
    }

    $allowed_statuses = ['in_stock', 'damaged', 'dismantled', 'missing', 'rtv'];
    if (!in_array($status, $allowed_statuses, true)) {
        wp_send_json_error('Invalid status selected.');
    }

    if (!empty($date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        wp_send_json_error('Invalid date format.');
    }

    $admin_email  = get_option('admin_email');
    $current_user = get_user_by('email', $admin_email);
    if (!$current_user || !wp_check_password($password, $current_user->user_pass, $current_user->ID)) {
        wp_send_json_error('Incorrect password.');
    }

    global $wpdb;

    $inventory_unit_table = $wpdb->prefix . "mji_product_inventory_units";

    $existing_unit = $wpdb->get_row($wpdb->prepare(
        "SELECT id, status FROM {$inventory_unit_table} WHERE id = %d",
        $unit_id
    ));

    if (!$existing_unit) {
        wp_send_json_error('Unit not found.');
    }

    if ($existing_unit->status === 'sold') {
        wp_send_json_error('Cannot change status of a sold unit.');
    }

    if ($existing_unit->status === $status) {
        wp_send_json_success('Status did not change.');
    }

    // Start transaction only when we know we will write
    $wpdb->query('START TRANSACTION');

    try {
        $existing_unit = $wpdb->get_row($wpdb->prepare(
            "SELECT id, status FROM {$inventory_unit_table} WHERE id = %d FOR UPDATE",
            $unit_id
        ));

        if (!$existing_unit || $existing_unit->status === 'sold') {
            throw new Exception('Unit not found or already sold.');
        }

        if ($existing_unit->status === $status) {
            $wpdb->query('ROLLBACK');
            wp_send_json_success('Status did not change.');
        }

        if (!mji_insert_unit_history($unit_id, $existing_unit->status, $status, $notes ?? null, $date)) {
            throw new Exception('Failed to insert status history: ' . $wpdb->last_error);
        }

        $updated_result = $wpdb->update(
            $inventory_unit_table,
            [
                'status' => $status
            ],
            [
                'id' => $unit_id
            ],
            [
                '%s'
            ],
            [
                '%d'
            ]
        );

        if ($updated_result === false) {
            throw new Exception('Database update failed: ' . $wpdb->last_error);
        }

        // If product_id not provided in POST, look it up from the unit row
        if (!$product_id) {
            $product_id = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT wc_product_id FROM {$inventory_unit_table} WHERE id = %d",
                $unit_id
            ));
        }

        $product = $product_id ? wc_get_product($product_id) : null;
        if ($product) {
            handle_stock_adjustment_based_on_status_change($existing_unit->status, $status, $product_id);
        }
        $wpdb->query('COMMIT');
        wp_send_json_success(['message' => 'Status updated successfully.']);
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        wp_send_json_error('Error: ' . $e->getMessage());
    }
}
add_action('wp_ajax_update_unit_status', 'change_status');

function is_stock_affecting_status($status)
{
    return $status === 'in_stock';
}

function handle_stock_adjustment_based_on_status_change($old, $new, $product_id)
{
    $was_in_stock = is_stock_affecting_status($old);
    $is_in_stock  = is_stock_affecting_status($new);

    if ($was_in_stock && !$is_in_stock) {
        wc_update_product_stock($product_id, 1, 'decrease');
    } elseif (!$was_in_stock && $is_in_stock) {
        wc_update_product_stock($product_id, 1, 'increase');
    }
}

function block_product_deletion_if_in_inventory($post_id)
{
    if (get_post_type($post_id) !== 'product') return;

    global $wpdb;
    $table_name = $wpdb->prefix . 'mji_product_inventory_units';

    $exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE wc_product_id = %d",
            $post_id
        )
    );

    if ($exists > 0) {
        $redirect_url = add_query_arg(
            'delete_blocked',
            $post_id,
            admin_url('edit.php?post_type=product')
        );
        wp_safe_redirect($redirect_url);
        exit;
    }
}
add_action('wp_trash_post', 'block_product_deletion_if_in_inventory');

add_action('set_object_terms', 'watch_product_collection_changes', 10, 6);
function watch_product_collection_changes($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids)
{

    if ($taxonomy !== 'collection') {
        return;
    }

    if (get_post_type($object_id) !== 'product') {
        return;
    }

    if ($taxonomy == 'collection' && ($terms != $old_tt_ids)) {
        global $wpdb;
        $units_table = $wpdb->prefix . 'mji_product_inventory_units';
        $unit_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT id FROM {$units_table}
             WHERE wc_product_id = %d
               AND status NOT IN ('sold','rtv')",
            $object_id
        ));

        $product_collection = get_the_terms($object_id, 'collection');
        $collection_names = ($product_collection && !is_wp_error($product_collection))
            ? wp_list_pluck($product_collection, 'name')
            : [];

        foreach ($unit_ids as $unit_id) {
            sync_product_collections((int) $unit_id, $collection_names);
        }
    }
}

function sync_product_collections($unit_id, $collection_names)
{
    global $wpdb;

    $collections_table = $wpdb->prefix . 'mji_collections';
    $product_collections_table = $wpdb->prefix . 'mji_products_collections';

    // Clean input
    $collection_names = array_unique(array_filter(array_map('trim', $collection_names)));

    // GET CURRENT COLLECTION IDS
    $existing_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT collection_id
             FROM {$product_collections_table}
             WHERE inventory_unit_id = %d",
            $unit_id
        )
    );

    $existing_ids = array_map('intval', $existing_ids);

    // ENSURE COLLECTIONS EXIST
    if ($collection_names) {

        $placeholders = implode(',', array_fill(0, count($collection_names), '(%s)'));

        $wpdb->query(
            $wpdb->prepare(
                "INSERT IGNORE INTO {$collections_table} (name)
                 VALUES {$placeholders}",
                $collection_names
            )
        );

        // GET IDS FOR PROVIDED NAMES
        $in = implode(',', array_fill(0, count($collection_names), '%s'));

        $new_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT id
                 FROM {$collections_table}
                 WHERE name IN ($in)",
                $collection_names
            )
        );
    } else {
        $new_ids = [];
    }

    $new_ids = array_map('intval', $new_ids);

    //  DETERMINE DIFF
    $to_add = array_diff($new_ids, $existing_ids);
    $to_remove = array_diff($existing_ids, $new_ids);

    // DELETE REMOVED RELATIONS
    if ($to_remove) {
        $placeholders = implode(',', array_fill(0, count($to_remove), '%d'));

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$product_collections_table}
                 WHERE inventory_unit_id = %d
                 AND collection_id IN ($placeholders)",
                array_merge([$unit_id], $to_remove)
            )
        );
    }

    // INSERT NEW RELATIONS
    if ($to_add) {
        $values = [];
        $placeholders = [];

        foreach ($to_add as $id) {
            $placeholders[] = '(%d,%d)';
            $values[] = $unit_id;
            $values[] = $id;
        }

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$product_collections_table}
                 (inventory_unit_id, collection_id)
                 VALUES " . implode(',', $placeholders),
                $values
            )
        );
    }

    return $new_ids;
}

function migrate_product_categories_batch()
{
    // At the VERY start of the function:
    $paged = (int) get_option('brand_migration_page', 1);
    custom_log("=== Migration Started. Current Page: $paged ===");

    $batch_size = 200;

    $brand_terms = [
        'OMEGA',
        'MIDO',
        'Longines',
        'Glashütte Original',
        'Girard-Perregaux',
        'Faberge',
        'Corum',
        'Breguet',
        'Blancpain',
        'Bell & Ross',
        'Rolex',
        'Roger Dubuis',
        'Harry Winston',
        'Greubel Forsey',
        'Roberto Coin',
        'Pomellato',
        'Montecristo',
        'Mikimoto',
        'Messika',
        'Cammilli Firenze',
        'Wellendorff',
        'Piero Milano',
        'Peroni & Parise',
        'Gismondi',
        'Gioielliamo',
        'Fullord',
        'Bulgari'
    ];

    $query = new WP_Query([
        'post_type' => 'product',
        'posts_per_page' => $batch_size,
        'paged' => $paged,
        'fields' => 'ids',
        'no_found_rows' => true,
        'orderby' => 'ID',
        'order' => 'ASC',
        'post_status' => 'any'
    ]);

    if (!$query->posts) {
        delete_option('brand_migration_page');
        custom_log('Brand migration completed.');
        return;
    }

    foreach ($query->posts as $product_id) {

        $categories = wp_get_post_terms($product_id, 'product_cat');

        $brands = [];
        $collections = [];

        foreach ($categories as $cat) {

            // Check ancestors
            $ancestors = get_ancestors($cat->term_id, 'product_cat');
            $is_parent_jewellery = false;
            if (!empty($ancestors)) {
                foreach ($ancestors as $ancestor_id) {
                    $ancestor = get_term($ancestor_id, 'product_cat');
                    if ($ancestor && strtolower($ancestor->slug) === 'jewellery') {
                        $is_parent_jewellery = true;
                        break;
                    }
                }
            }

            if ($is_parent_jewellery) continue;
            $cat_name = trim($cat->name);
            $cat_name = html_entity_decode($cat_name, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            if ($cat_name === 'Montecristo JWL' || $cat_name === 'OCTOCRAFT' || $cat_name === 'TOSCO') {
                $cat_name = 'Montecristo';
            }

            if ($cat_name == 'Uncategorized' || $cat_name == 'Fine Jewellery' || $cat_name == 'Jewellery' || $cat_name == 'Watches') {
                continue;
            }

            if (in_array($cat_name, $brand_terms)) {

                $brand = get_term_by('name', $cat_name, 'product_brand');

                if (!$brand) {
                    $brand = wp_insert_term($cat_name, 'product_brand');

                    if (!is_wp_error($brand)) {
                        $brand_id = $brand['term_id'];
                    }
                } else {
                    $brand_id = $brand->term_id;
                }

                if (!empty($brand_id)) {
                    $brands[] = (int) $brand_id;
                }
            } else {

                $collection = term_exists($cat_name, 'collection');

                if (!$collection) {
                    $collection = wp_insert_term($cat_name, 'collection');
                    if (!is_wp_error($collection)) {
                        $collection_id = (int) $collection['term_id'];
                    }
                } else {
                    $collection_id = (int) $collection['term_id'];
                }

                if (!empty($collection_id)) {
                    $collections[] = $collection_id;
                }
            }
        }

        // IF we want to assign empty brands
        // if (empty($brands)) {

        //     $default_brand = term_exists('Montecristo', 'product_brand');

        //     if (!$default_brand) {
        //         $default_brand = wp_insert_term('Montecristo', 'product_brand');
        //         if (!is_wp_error($default_brand)) {
        //             $default_brand_id = (int) $default_brand['term_id'];
        //         }
        //     } else {
        //         // term_exists returns array
        //         $default_brand_id = (int) $collection['term_id'];
        //     }

        //     if (!empty($default_brand_id)) {
        //         $brands[] = $default_brand_id;
        //     }
        // }

        if (!empty($brands)) {

            wp_set_object_terms($product_id, $brands, 'product_brand');

            update_post_meta(
                $product_id,
                'rank_math_primary_product_brand',
                $brands[0]
            );

            if (count($brands) > 1) {
                custom_log('Multiple brands on product: ' . $product_id);
            }
        }

        if (!empty($collections)) {
            wp_set_object_terms($product_id, $collections, 'collection');
        }
    }

    $next_page = $paged + 1;

    update_option('brand_migration_page', $next_page, false);

    custom_log('Brand migration batch completed. Page: ' . $paged);
}

add_action('init', function () {

    if (!isset($_GET['run_brand_migration'])) {
        return;
    }

    migrate_product_categories_batch();

    echo "Batch processed. Refresh page to continue.";
    exit;
}, 11);

function migrate_product_collections()
{
    global $wpdb;

    $batch_size = 200;
    $table_name = "{$wpdb->prefix}mji_product_inventory_units";
    $brands_table = "{$wpdb->prefix}mji_brands";

    // At the VERY start of the function:
    $paged = (int) get_option('brand_migration_page', 1);
    custom_log("=== Migration Started. Current Page: $paged ===");

    $query = new WP_Query([
        'post_type' => 'product',
        'posts_per_page' => $batch_size,
        'paged' => $paged,
        'fields' => 'ids',
        'no_found_rows' => true,
        'orderby' => 'ID',
        'order' => 'ASC',
        'post_status' => 'any'
    ]);

    if (!$query->posts) {
        delete_option('brand_migration_page');
        custom_log('Collection migration completed.');
        return;
    }

    foreach ($query->posts as $product_id) {
        $brand_id = get_post_meta($product_id, 'rank_math_primary_product_brand', true);
        $brand = get_term($brand_id, 'product_brand');
        $brand = $brand->name;
        $brand_id = get_brand_model_id($brands_table, $brand);
        if ($brand_id === false) continue;

        try {
            $wpdb->update(
                $table_name,
                [
                    'brand_id' => $brand_id,
                ],
                ['wc_product_id' => $product_id],
                ['%d'],
                ['%d']
            );
        } catch (Exception $e) {
            custom_log("Error " . $e->getMessage());
        }
    }

    $next_page = $paged + 1;
    update_option('brand_migration_page', $next_page, false);
    custom_log('Collection batch completed. Page: ' . $paged);
}

add_action('init', function () {

    if (!isset($_GET['run_brand_db_migration'])) {
        return;
    }

    migrate_product_collections();

    echo "Batch processed. Refresh page to continue.";
    exit;
}, 11);
