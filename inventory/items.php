<?php

// ─── Router ──────────────────────────────────────────────────────────────────
function items_page(): void
{
    wp_enqueue_media();

    if (isset($_GET['added'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Unit added successfully.</p></div>';
    }
    if (isset($_GET['updated'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Unit updated successfully.</p></div>';
    }

    $action_map = [
        'add'  => 'items_add_form',
        'edit' => 'items_edit_form',
    ];
    $action = $_GET['action'] ?? '';
    if (isset($action_map[$action])) {
        call_user_func($action_map[$action]);
    } else {
        items_list_view();
    }
}

// ─── List view ───────────────────────────────────────────────────────────────
function items_list_view(): void
{
    global $wpdb;

    $units_table       = $wpdb->prefix . 'mji_product_inventory_units';
    $brands_table      = $wpdb->prefix . 'mji_brands';
    $models_table      = $wpdb->prefix . 'mji_models';
    $locations_table   = $wpdb->prefix . 'mji_locations';
    $pc_table          = $wpdb->prefix . 'mji_products_collections';
    $collections_table = $wpdb->prefix . 'mji_collections';

    $search      = sanitize_text_field($_GET['search'] ?? '');
    $per_page    = max(1, absint($_GET['per_page'] ?? 10));
    $current_page = max(1, absint($_GET['paged'] ?? 1));
    $offset      = ($current_page - 1) * $per_page;

    $base_select = "
        SELECT DISTINCT piu.id, piu.sku, piu.name, piu.serial, piu.status, piu.created_date,
            b.name AS brand_name, m.name AS model_name, l.name AS location_name
        FROM {$units_table} piu
        LEFT JOIN {$brands_table}      b  ON b.id  = piu.brand_id
        LEFT JOIN {$models_table}      m  ON m.id  = piu.model_id
        LEFT JOIN {$locations_table}   l  ON l.id  = piu.location_id
        LEFT JOIN {$pc_table}          pc ON pc.inventory_unit_id = piu.id
        LEFT JOIN {$collections_table} c  ON c.id  = pc.collection_id
    ";

    if ($search !== '') {
        $like    = '%' . $wpdb->esc_like($search) . '%';
        $where   = "WHERE piu.sku LIKE %s OR piu.name LIKE %s OR m.name LIKE %s OR c.name LIKE %s OR piu.serial LIKE %s";
        $units   = $wpdb->get_results($wpdb->prepare(
            $base_select . $where . " GROUP BY piu.id ORDER BY piu.created_date DESC LIMIT %d OFFSET %d",
            $like, $like, $like, $like, $like, $per_page, $offset
        ));
        $total = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT piu.id) FROM {$units_table} piu
             LEFT JOIN {$models_table}      m  ON m.id  = piu.model_id
             LEFT JOIN {$pc_table}          pc ON pc.inventory_unit_id = piu.id
             LEFT JOIN {$collections_table} c  ON c.id  = pc.collection_id
             WHERE piu.sku LIKE %s OR piu.name LIKE %s OR m.name LIKE %s OR c.name LIKE %s OR piu.serial LIKE %s",
            $like, $like, $like, $like, $like
        ));
    } else {
        $units = $wpdb->get_results($wpdb->prepare(
            $base_select . "GROUP BY piu.id ORDER BY piu.created_date DESC LIMIT %d OFFSET %d",
            $per_page, $offset
        ));
        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$units_table}");
    }

    $total_pages = $per_page > 0 ? (int) ceil($total / $per_page) : 1;
    $base_args   = array_filter(['page' => 'items-management', 'search' => $search, 'per_page' => $per_page]);

    ?>
    <div class="wrap items-management">
        <h1 class="wp-heading-inline">Items</h1>
        <a href="<?= esc_url(admin_url('admin.php?page=items-management&action=add')) ?>" class="page-title-action">Add Unit</a>

        <form method="get" class="items-search-form">
            <input type="hidden" name="page" value="items-management">
            <div class="items-search-bar">
                <input type="text" name="search" value="<?= esc_attr($search) ?>" placeholder="Search SKU, name, model, collection, serial…" class="regular-text">
                <button type="submit" class="button">Search</button>
                <?php if ($search): ?>
                    <a href="<?= esc_url(admin_url('admin.php?page=items-management')) ?>" class="button">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (empty($units)): ?>
            <p><?= $search ? 'No units found for that search.' : 'No units yet. Add your first unit above.' ?></p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped items-table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Name</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Location</th>
                        <th>Serial</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($units as $unit): ?>
                        <tr>
                            <td><?= esc_html($unit->sku) ?></td>
                            <td><?= esc_html($unit->name ?? '—') ?></td>
                            <td><?= esc_html($unit->brand_name ?? '—') ?></td>
                            <td><?= esc_html($unit->model_name ?? '—') ?></td>
                            <td><?= esc_html($unit->location_name ?? '—') ?></td>
                            <td><?= esc_html($unit->serial ?? '—') ?></td>
                            <td><span class="items-status items-status--<?= esc_attr($unit->status) ?>"><?= esc_html($unit->status) ?></span></td>
                            <td class="items-actions">
                                <a href="<?= esc_url(admin_url("admin.php?page=items-management&action=edit&id={$unit->id}")) ?>" class="button button-small">Edit</a>
                                <a href="<?= esc_url(admin_url("admin.php?page=items-management&action=add&duplicate_from={$unit->id}")) ?>" class="button button-small">Duplicate</a>
                                <?php if ($unit->status !== 'sold'): ?>
                                    <button class="button button-small button-link-delete items-delete-btn" data-id="<?= esc_attr($unit->id) ?>" data-sku="<?= esc_attr($unit->sku) ?>">Delete</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1): ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <?= mji_customer_pagination($total_pages, $current_page, $base_args) ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}

// ─── Add form ────────────────────────────────────────────────────────────────
function items_add_form(): void
{
    global $wpdb;

    // Handle POST
    if (isset($_POST['items_add'])) {
        check_admin_referer('items_add', 'items_nonce');
        items_handle_insert();
        return;
    }

    // Pre-fill from duplicate source if provided
    $prefill = null;
    if (!empty($_GET['duplicate_from'])) {
        $prefill = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mji_product_inventory_units WHERE id = %d",
            absint($_GET['duplicate_from'])
        ));
    }

    $back_url = esc_url(admin_url('admin.php?page=items-management'));
    ?>
    <div class="wrap items-management">
        <h1><?= $prefill ? 'Duplicate Unit' : 'Add Unit' ?></h1>
        <a href="<?= $back_url ?>" class="button">&larr; Back to Items</a>

        <form method="post" class="items-form">
            <?php wp_nonce_field('items_add', 'items_nonce') ?>
            <input type="hidden" name="items_add" value="1">

            <?php items_render_form_fields($prefill, true) ?>

            <p class="submit">
                <?php submit_button($prefill ? 'Save Duplicate' : 'Add Unit', 'primary', 'submit', false) ?>
            </p>
        </form>
    </div>
    <?php
}

function items_handle_insert(): void
{
    global $wpdb;

    $data    = items_sanitize_form();
    $formats = items_formats($data);

    $result = $wpdb->insert("{$wpdb->prefix}mji_product_inventory_units", $data, $formats);
    if ($result === false) {
        custom_log('Items insert failed: ' . $wpdb->last_error);
        wp_die('Failed to save unit. Please go back and try again.');
    }

    $unit_id = $wpdb->insert_id;

    $wpdb->insert(
        "{$wpdb->prefix}mji_inventory_status_history",
        [
            'inventory_unit_id' => $unit_id,
            'from_status'       => null,
            'to_status'         => $data['status'],
            'notes'             => 'Unit created',
            'changed_by_user_id' => get_current_user_id(),
        ],
        ['%d', '%s', '%s', '%s', '%d']
    );

    wp_redirect(admin_url('admin.php?page=items-management&added=1'));
    exit;
}

// ─── Edit form ───────────────────────────────────────────────────────────────
function items_edit_form(): void
{
    global $wpdb;

    $id   = absint($_GET['id'] ?? 0);
    $unit = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}mji_product_inventory_units WHERE id = %d",
        $id
    ));

    if (!$unit) {
        wp_die('Unit not found.');
    }

    // Handle POST
    if (isset($_POST['items_edit'])) {
        check_admin_referer('items_edit', 'items_nonce');
        items_handle_update($id, $unit->status);
        return;
    }

    // Fetch status history
    $history = $wpdb->get_results($wpdb->prepare(
        "SELECT ish.*, u.display_name AS changed_by
         FROM {$wpdb->prefix}mji_inventory_status_history ish
         LEFT JOIN {$wpdb->users} u ON u.ID = ish.changed_by_user_id
         WHERE ish.inventory_unit_id = %d
         ORDER BY ish.created_at ASC",
        $id
    ));

    // Fetch WC product name if linked
    $wc_product_name = '';
    if ($unit->wc_product_id) {
        $product = wc_get_product($unit->wc_product_id);
        if ($product) {
            $wc_product_name = $product->get_name();
        }
    }

    $back_url = esc_url(admin_url('admin.php?page=items-management'));
    ?>
    <div class="wrap items-management">
        <h1>Edit Unit — <?= esc_html($unit->sku) ?></h1>
        <a href="<?= $back_url ?>" class="button">&larr; Back to Items</a>

        <form method="post" class="items-form">
            <?php wp_nonce_field('items_edit', 'items_nonce') ?>
            <input type="hidden" name="items_edit" value="1">

            <?php items_render_form_fields($unit, false, $wc_product_name) ?>

            <p class="submit">
                <?php submit_button('Save Changes', 'primary', 'submit', false) ?>
            </p>
        </form>

        <?php if (!empty($history)): ?>
            <h2>Status History</h2>
            <table class="wp-list-table widefat fixed striped items-history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Notes</th>
                        <th>Reference</th>
                        <th>Changed by</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td><?= esc_html(date('Y-m-d H:i', strtotime($h->created_at))) ?></td>
                            <td><?= esc_html($h->from_status ?? '—') ?></td>
                            <td><?= esc_html($h->to_status) ?></td>
                            <td><?= esc_html($h->notes ?? '—') ?></td>
                            <td>
                                <?php if ($h->reference_num): ?>
                                    <a href="<?= esc_url(admin_url("admin.php?page=invoice-management&reference_num={$h->reference_num}")) ?>">
                                        <?= esc_html($h->reference_num) ?>
                                    </a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?= esc_html($h->changed_by ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}

function items_handle_update(int $id, string $old_status): void
{
    global $wpdb;

    $data    = items_sanitize_form();
    $formats = items_formats($data);

    $result = $wpdb->update(
        "{$wpdb->prefix}mji_product_inventory_units",
        $data,
        ['id' => $id],
        $formats,
        ['%d']
    );

    if ($result === false) {
        custom_log('Items update failed for id ' . $id . ': ' . $wpdb->last_error);
        wp_die('Failed to update unit. Please go back and try again.');
    }

    // Log status change only if it changed
    if ($data['status'] !== $old_status) {
        $wpdb->insert(
            "{$wpdb->prefix}mji_inventory_status_history",
            [
                'inventory_unit_id'  => $id,
                'from_status'        => $old_status,
                'to_status'          => $data['status'],
                'notes'              => sanitize_text_field($_POST['status_note'] ?? ''),
                'changed_by_user_id' => get_current_user_id(),
            ],
            ['%d', '%s', '%s', '%s', '%d']
        );
    }

    wp_redirect(admin_url("admin.php?page=items-management&action=edit&id={$id}&updated=1"));
    exit;
}

// ─── Shared form rendering ───────────────────────────────────────────────────
function items_render_form_fields(?object $unit, bool $is_new, string $wc_product_name = ''): void
{
    $allowed_statuses = ['in_stock', 'missing', 'sold', 'damaged', 'rtv', 'dismantled'];

    $sku            = $is_new ? '' : esc_attr($unit->sku ?? '');
    $serial         = $is_new ? '' : esc_attr($unit->serial ?? '');
    $status         = $is_new ? 'in_stock' : esc_attr($unit->status ?? 'in_stock');
    $location_id    = esc_attr($unit->location_id ?? '');
    $brand_id       = esc_attr($unit->brand_id ?? '');
    $model_id       = esc_attr($unit->model_id ?? '');
    $cost_price     = esc_attr($unit->cost_price ?? '');
    $true_cost      = esc_attr($unit->true_cost ?? '');
    $retail_price   = esc_attr($unit->retail_price ?? '');
    $supplier_id    = esc_attr($unit->supplier_id ?? '');
    $invoice_number = esc_attr($unit->invoice_number ?? '');
    $invoice_date   = esc_attr($unit->created_date ? date('Y-m-d', strtotime($unit->created_date)) : date('Y-m-d'));
    $name           = esc_attr($unit->name ?? '');
    $description    = $unit->description ?? '';
    $image_id       = absint($unit->image_id ?? 0);
    $wc_product_id  = absint($unit->wc_product_id ?? 0);

    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';

    $locations = mji_get_locations();
    $brands    = mji_get_brands();
    $models    = mji_get_models();
    $suppliers = mji_get_suppliers();
    ?>
    <div class="items-form-grid">

        <div class="items-form-section">
            <h3>Identification</h3>

            <div class="form-field">
                <label for="sku">SKU <span class="required">*</span></label>
                <input type="text" id="sku" name="sku" value="<?= $sku ?>" class="regular-text" required>
            </div>

            <div class="form-field">
                <label for="serial">Serial Number</label>
                <input type="text" id="serial" name="serial" value="<?= $serial ?>" class="regular-text">
            </div>

            <div class="form-field">
                <label for="status">Status <span class="required">*</span></label>
                <select id="status" name="status" required>
                    <?php foreach ($allowed_statuses as $s): ?>
                        <option value="<?= esc_attr($s) ?>" <?= selected($s, $status, false) ?>><?= esc_html($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (!$is_new): ?>
                <div class="form-field">
                    <label for="status_note">Status Change Note</label>
                    <input type="text" id="status_note" name="status_note" class="regular-text" placeholder="Reason for status change (only saved if status changed)">
                </div>
            <?php endif; ?>

            <div class="form-field">
                <label for="invoice_date">Invoice Date <span class="required">*</span></label>
                <input type="date" id="invoice_date" name="invoice_date" value="<?= $invoice_date ?>" required>
            </div>
        </div>

        <div class="items-form-section">
            <h3>Classification</h3>

            <div class="form-field">
                <label for="location_id">Location <span class="required">*</span></label>
                <select id="location_id" name="location_id" required>
                    <option value="">Select location</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= esc_attr($loc->id) ?>" <?= selected($loc->id, $location_id, false) ?>><?= esc_html($loc->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label for="brand_id">Brand</label>
                <select id="brand_id" name="brand_id">
                    <option value="">Select brand</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= esc_attr($b->id) ?>" <?= selected($b->id, $brand_id, false) ?>><?= esc_html($b->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label for="model_id">Model</label>
                <select id="model_id" name="model_id">
                    <option value="">Select model</option>
                    <?php foreach ($models as $m): ?>
                        <option value="<?= esc_attr($m->id) ?>" <?= selected($m->id, $model_id, false) ?>><?= esc_html($m->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label for="supplier_id">Supplier</label>
                <select id="supplier_id" name="supplier_id">
                    <option value="">Select supplier</option>
                    <?php foreach ($suppliers as $s): ?>
                        <option value="<?= esc_attr($s->id) ?>" <?= selected($s->id, $supplier_id, false) ?>><?= esc_html($s->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label for="invoice_number">Invoice Number</label>
                <input type="text" id="invoice_number" name="invoice_number" value="<?= $invoice_number ?>" class="regular-text">
            </div>
        </div>

        <div class="items-form-section">
            <h3>Pricing</h3>

            <div class="form-field">
                <label for="cost_price">Cost Price <span class="required">*</span></label>
                <input type="number" id="cost_price" name="cost_price" value="<?= $cost_price ?>" step="0.01" min="0" class="regular-text" required>
            </div>

            <div class="form-field">
                <label for="true_cost">True Cost</label>
                <input type="number" id="true_cost" name="true_cost" value="<?= $true_cost ?>" step="0.01" min="0" class="regular-text">
            </div>

            <div class="form-field">
                <label for="retail_price">Retail Price <span class="required">*</span></label>
                <input type="number" id="retail_price" name="retail_price" value="<?= $retail_price ?>" step="0.01" min="0" class="regular-text" required>
            </div>
        </div>

        <div class="items-form-section">
            <h3>Details</h3>

            <div class="form-field">
                <label for="item_name">Name</label>
                <input type="text" id="item_name" name="item_name" value="<?= $name ?>" class="regular-text">
            </div>

            <div class="form-field">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" class="large-text"><?= wp_kses_post($description) ?></textarea>
            </div>

            <div class="form-field">
                <label>Image</label>
                <div class="items-image-picker">
                    <input type="hidden" id="image_id" name="image_id" value="<?= $image_id ?>">
                    <?php if ($image_url): ?>
                        <img id="items-image-preview" src="<?= esc_url($image_url) ?>" alt="Unit image">
                    <?php else: ?>
                        <img id="items-image-preview" src="" alt="" style="display:none;">
                    <?php endif; ?>
                    <button type="button" id="items-pick-image" class="button">Select Image</button>
                    <button type="button" id="items-remove-image" class="button" <?= $image_id ? '' : 'style="display:none;"' ?>>Remove</button>
                </div>
            </div>
        </div>

        <div class="items-form-section items-form-section--full">
            <h3>WooCommerce Product Link</h3>
            <p class="description">Optional. Link this unit to a WooCommerce product for storefront display.</p>

            <div class="form-field">
                <label for="wc_product_search">Product</label>
                <input type="hidden" id="wc_product_id" name="wc_product_id" value="<?= $wc_product_id ?>">
                <input type="text" id="wc_product_search" placeholder="Type to search products…" value="<?= esc_attr($wc_product_name) ?>" class="regular-text" autocomplete="off">
                <div id="wc-product-results" class="items-wc-results" style="display:none;"></div>
                <?php if ($wc_product_id): ?>
                    <button type="button" id="items-clear-wc" class="button">Unlink</button>
                <?php endif; ?>
            </div>
        </div>

    </div>
    <?php
}

// ─── Sanitize / format helpers ───────────────────────────────────────────────
function items_sanitize_form(): array
{
    $allowed_statuses = ['in_stock', 'missing', 'sold', 'damaged', 'rtv', 'dismantled'];
    $status           = in_array($_POST['status'] ?? '', $allowed_statuses) ? $_POST['status'] : 'in_stock';

    $date_raw     = sanitize_text_field($_POST['invoice_date'] ?? '');
    $created_date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_raw) ? $date_raw . ' 00:00:00' : current_time('mysql');

    $wc_product_id = absint($_POST['wc_product_id'] ?? 0);
    $image_id      = absint($_POST['image_id'] ?? 0);
    $supplier_id   = absint($_POST['supplier_id'] ?? 0);
    $brand_id      = absint($_POST['brand_id'] ?? 0);
    $model_id      = absint($_POST['model_id'] ?? 0);
    $location_id   = absint($_POST['location_id'] ?? 0);

    return [
        'sku'            => sanitize_text_field($_POST['sku'] ?? ''),
        'serial'         => sanitize_text_field($_POST['serial'] ?? '') ?: null,
        'status'         => $status,
        'location_id'    => $location_id ?: null,
        'brand_id'       => $brand_id ?: null,
        'model_id'       => $model_id ?: null,
        'cost_price'     => max(0, (float) ($_POST['cost_price'] ?? 0)),
        'true_cost'      => $_POST['true_cost'] !== '' ? max(0, (float) $_POST['true_cost']) : null,
        'retail_price'   => max(0, (float) ($_POST['retail_price'] ?? 0)),
        'supplier_id'    => $supplier_id ?: null,
        'invoice_number' => sanitize_text_field($_POST['invoice_number'] ?? '') ?: null,
        'created_date'   => $created_date,
        'name'           => sanitize_text_field($_POST['item_name'] ?? '') ?: null,
        'description'    => wp_kses_post($_POST['description'] ?? '') ?: null,
        'image_id'       => $image_id ?: null,
        'wc_product_id'  => $wc_product_id ?: null,
    ];
}

function items_formats(array $data): array
{
    return [
        '%s', // sku
        '%s', // serial
        '%s', // status
        '%d', // location_id
        '%d', // brand_id
        '%d', // model_id
        '%f', // cost_price
        '%f', // true_cost
        '%f', // retail_price
        '%d', // supplier_id
        '%s', // invoice_number
        '%s', // created_date
        '%s', // name
        '%s', // description
        '%d', // image_id
        '%d', // wc_product_id
    ];
}

// ─── AJAX: delete unit ───────────────────────────────────────────────────────
function items_ajax_delete(): void
{
    check_ajax_referer('mji_inventory_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorised']);
    }

    global $wpdb;
    $id    = absint($_POST['id'] ?? 0);
    $table = $wpdb->prefix . 'mji_product_inventory_units';

    $status = $wpdb->get_var($wpdb->prepare("SELECT status FROM {$table} WHERE id = %d", $id));
    if ($status === null) {
        wp_send_json_error(['message' => 'Unit not found']);
    }
    if ($status === 'sold') {
        wp_send_json_error(['message' => 'Cannot delete a sold unit']);
    }

    $result = $wpdb->delete($table, ['id' => $id], ['%d']);
    if ($result === false) {
        wp_send_json_error(['message' => 'Delete failed']);
    }

    wp_send_json_success();
}
add_action('wp_ajax_mji_delete_item', 'items_ajax_delete');

// ─── AJAX: search WC products ────────────────────────────────────────────────
function items_ajax_search_wc_products(): void
{
    check_ajax_referer('mji_inventory_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error();
    }

    $term = sanitize_text_field($_GET['term'] ?? '');
    if (strlen($term) < 2) {
        wp_send_json_success([]);
    }

    $query = new WP_Query([
        's'              => $term,
        'post_type'      => ['product', 'product_variation'],
        'post_status'    => 'any',
        'posts_per_page' => 10,
        'fields'         => 'ids',
    ]);

    $results = [];
    foreach ($query->posts as $post_id) {
        $results[] = [
            'id'   => $post_id,
            'text' => get_the_title($post_id) . ' (#' . $post_id . ')',
        ];
    }

    wp_send_json_success($results);
}
add_action('wp_ajax_mji_search_wc_products', 'items_ajax_search_wc_products');
