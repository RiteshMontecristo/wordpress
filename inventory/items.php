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
            $like,
            $like,
            $like,
            $like,
            $like,
            $per_page,
            $offset
        ));
        $total = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT piu.id) FROM {$units_table} piu
             LEFT JOIN {$models_table}      m  ON m.id  = piu.model_id
             LEFT JOIN {$pc_table}          pc ON pc.inventory_unit_id = piu.id
             LEFT JOIN {$collections_table} c  ON c.id  = pc.collection_id
             WHERE piu.sku LIKE %s OR piu.name LIKE %s OR m.name LIKE %s OR c.name LIKE %s OR piu.serial LIKE %s",
            $like,
            $like,
            $like,
            $like,
            $like
        ));
    } else {
        $units = $wpdb->get_results($wpdb->prepare(
            $base_select . "GROUP BY piu.id ORDER BY piu.created_date DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
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

    mji_insert_unit_history($unit_id, null, $data['status'], 'Unit created');

    $wc_product_id = (int) ($data['wc_product_id'] ?? 0);

    $collection_names = array_map('sanitize_text_field', (array) ($_POST['collections'] ?? []));
    sync_product_collections($unit_id, $collection_names);
    items_sync_wc_taxonomy($wc_product_id, $collection_names);
    items_sync_wc_product($wc_product_id, $data['name'], $data['description'], $data['retail_price'] ?? null, $data['model_id'] ?? null, $data['brand_id'] ?? null);
    items_sync_sibling_units($unit_id, $wc_product_id, $data, $collection_names);

    if ($wc_product_id) {
        wc_update_product_stock($wc_product_id, 1, 'increase');
    }

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
                <button type="button" id="items-change-status-btn" class="button" data-unit-id="<?= esc_attr($id) ?>">Change Status</button>
            </p>
        </form>

        <div id="items-status-modal" class="items-modal" style="display:none;">
            <div class="items-modal-inner">
                <h2>Change Status</h2>
                <label>New Status
                    <select id="items-modal-status">
                        <option value="in_stock">In Stock</option>
                        <option value="missing">Missing</option>
                        <option value="damaged">Damaged</option>
                        <option value="rtv">RTV</option>
                        <option value="dismantled">Dismantled</option>
                    </select>
                </label>
                <label>Date <input type="date" id="items-modal-date" value="<?= esc_attr(date('Y-m-d')) ?>"></label>
                <label>Notes <textarea id="items-modal-notes"></textarea></label>
                <label>Admin Password <input type="password" id="items-modal-password" autocomplete="off"></label>
                <p id="items-modal-error" style="color:red;display:none;"></p>
                <div class="items-modal-actions">
                    <button type="button" id="items-modal-confirm" class="button button-primary">Confirm</button>
                    <button type="button" id="items-modal-cancel" class="button">Cancel</button>
                </div>
            </div>
        </div>

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

    $data            = items_sanitize_form();
    $data['status']  = $old_status; // status only changes via the Change Status modal
    $formats         = items_formats($data);

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

    $wc_variant_id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT wc_product_variant_id FROM {$wpdb->prefix}mji_product_inventory_units WHERE id = %d",
        $id
    )) ?: null;

    $collection_names = array_map('sanitize_text_field', (array) ($_POST['collections'] ?? []));
    sync_product_collections($id, $collection_names);
    items_sync_wc_taxonomy((int) ($data['wc_product_id'] ?? 0), $collection_names);
    items_sync_wc_product((int) ($data['wc_product_id'] ?? 0), $data['name'], $data['description'], $data['retail_price'] ?? null, $data['model_id'] ?? null, $data['brand_id'] ?? null, $wc_variant_id);
    items_sync_sibling_units($id, (int) ($data['wc_product_id'] ?? 0), $data, $collection_names);

    if (!empty($data['wc_product_id']) && !empty($data['image_id'])) {
        update_post_meta((int) $data['wc_product_id'], '_thumbnail_id', (int) $data['image_id']);
    }

    wp_redirect(admin_url("admin.php?page=items-management&action=edit&id={$id}&updated=1"));
    exit;
}

// ─── Shared form rendering ───────────────────────────────────────────────────
function items_render_form_fields(?object $unit, bool $is_new, string $wc_product_name = ''): void
{
    $sku            = $is_new ? '' : esc_attr($unit->sku ?? '');
    $serial         = $is_new ? '' : esc_attr($unit->serial ?? '');
    $status         = $is_new ? 'in_stock' : ($unit->status ?? 'in_stock');
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
                <label>Status</label>
                <?php if ($is_new): ?>
                    <input type="hidden" name="status" value="in_stock">
                    <span class="items-status items-status--in_stock">in_stock</span>
                <?php else: ?>
                    <span class="items-status items-status--<?= esc_attr($status) ?>"><?= esc_html($status) ?></span>
                    <p class="description" style="margin-top:4px;">Use "Change Status" to update.</p>
                <?php endif; ?>
            </div>

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
                <select id="brand_id" name="brand_id" class="brand-select">
                    <option value="">— select or type to create —</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= esc_attr($b->id) ?>" <?= selected($b->id, $brand_id, false) ?>><?= esc_html($b->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label for="model_id">Model</label>
                <select id="model_id" name="model_id" class="model-select">
                    <option value="">— select or type to create —</option>
                    <?php foreach ($models as $m): ?>
                        <option value="<?= esc_attr($m->id) ?>" <?= selected($m->id, $model_id, false) ?>><?= esc_html($m->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label for="supplier_id">Supplier</label>
                <?php mji_suppliers_dropdown(false, (int) ($unit->supplier_id ?? 0)) ?>
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
                <?php wp_editor($description, 'description', [
                    'textarea_name' => 'description',
                    'textarea_rows' => 10,
                    'media_buttons' => false,
                ]); ?>
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

        <div class="items-form-section items-form-section--full">
            <h3>Collections</h3>
            <?php
            global $wpdb;
            $all_collections = $wpdb->get_results(
                "SELECT id, name FROM {$wpdb->prefix}mji_collections ORDER BY name"
            );
            $current_collections = [];
            if ($unit && !empty($unit->id)) {
                $rows = $wpdb->get_results($wpdb->prepare(
                    "SELECT c.name FROM {$wpdb->prefix}mji_products_collections pc
                     JOIN {$wpdb->prefix}mji_collections c ON c.id = pc.collection_id
                     WHERE pc.inventory_unit_id = %d",
                    $unit->id
                ));
                $current_collections = wp_list_pluck($rows, 'name');
            }
            ?>
            <div class="form-field">
                <label for="collections">Collections</label>
                <select name="collections[]" id="collections" multiple class="items-select2-multi">
                    <?php foreach ($all_collections as $col): ?>
                        <option value="<?= esc_attr($col->name) ?>"
                            <?= in_array($col->name, $current_collections, true) ? 'selected' : '' ?>>
                            <?= esc_html($col->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

    </div>

<?php
}

// ─── Sibling unit sync ───────────────────────────────────────────────────────

function items_sync_sibling_units(int $unit_id, int $wc_product_id, array $data, array $collection_names): void
{
    if (!$wc_product_id) return;

    global $wpdb;

    $table = $wpdb->prefix . 'mji_product_inventory_units';

    // Only sync active units — sold/rtv are frozen records
    $sibling_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$table}
         WHERE wc_product_id = %d AND id != %d
           AND status NOT IN ('sold', 'rtv')",
        $wc_product_id,
        $unit_id
    ));

    if (!$sibling_ids) return;

    // ── Field update ─────────────────────────────────────────────────────────
    $shared = [
        'name'         => $data['name'],
        'description'  => $data['description'],
        'retail_price' => $data['retail_price'],
        'brand_id'     => $data['brand_id'],
        'model_id'     => $data['model_id'],
        'image_id'     => $data['image_id'],
    ];

    $set_parts = [];
    $values    = [];

    foreach ($shared as $col => $val) {
        if ($val === null) {
            $set_parts[] = "`{$col}` = NULL";
        } elseif ($col === 'retail_price') {
            $set_parts[] = "`{$col}` = %f";
            $values[]    = $val;
        } elseif (in_array($col, ['brand_id', 'model_id', 'image_id'], true)) {
            $set_parts[] = "`{$col}` = %d";
            $values[]    = $val;
        } else {
            $set_parts[] = "`{$col}` = %s";
            $values[]    = $val;
        }
    }

    if ($set_parts) {
        $id_placeholders = implode(',', array_fill(0, count($sibling_ids), '%d'));
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$table}
                 SET " . implode(', ', $set_parts) . "
                 WHERE id IN ({$id_placeholders})",
                array_merge($values, $sibling_ids)
            )
        );
    }

    // ── Collection sync ───────────────────────────────────────────────────────
    foreach ($sibling_ids as $sibling_id) {
        sync_product_collections((int) $sibling_id, $collection_names);
    }
}

// ─── WC product sync (items → WC) ────────────────────────────────────────────

function items_sync_wc_taxonomy(int $wc_product_id, array $collection_names): void
{
    if (!$wc_product_id) {
        return;
    }

    $collection_names = array_unique(array_filter(array_map('trim', $collection_names)));
    $term_ids = [];

    foreach ($collection_names as $name) {
        $existing = term_exists($name, 'collection');
        if ($existing) {
            $term_ids[] = (int) $existing['term_id'];
        } else {
            $result = wp_insert_term($name, 'collection');
            if (!is_wp_error($result)) {
                $term_ids[] = (int) $result['term_id'];
            }
        }
    }

    // Always call — passing [] clears all terms when every collection is removed
    wp_set_object_terms($wc_product_id, $term_ids, 'collection');
    wc_delete_product_transients($wc_product_id);
}

function items_sync_wc_product(
    int $wc_product_id,
    ?string $name,
    ?string $description,
    ?float $retail_price,
    ?int $model_id,
    ?int $brand_id,
    ?int $wc_variant_id = null
): void {
    if (!$wc_product_id) return;

    $effective_id = ($wc_variant_id && $wc_variant_id > 0) ? $wc_variant_id : $wc_product_id;
    $product = wc_get_product($effective_id);
    if (!$product) return;

    $is_variation = $product->get_type() === 'variation';

    global $wpdb;

    // Model name becomes the WC SKU
    $model_name = $model_id
        ? $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}mji_models WHERE id = %d", $model_id))
        : null;

    // Resolve brand to a product_brand term ID
    $brand_term_id = null;
    if ($brand_id) {
        $brand_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}mji_brands WHERE id = %d", $brand_id));
        if ($brand_name) {
            $existing = term_exists($brand_name, 'product_brand');
            if ($existing) {
                $brand_term_id = (int) $existing['term_id'];
            } else {
                $result = wp_insert_term($brand_name, 'product_brand');
                if (!is_wp_error($result)) {
                    $brand_term_id = (int) $result['term_id'];
                }
            }
        }
    }

    // Unhook everything that could corrupt data on a programmatic save
    remove_action('woocommerce_update_product',           'items_sync_wc_to_units');
    remove_action('woocommerce_update_product_variation', 'items_sync_wc_to_units');
    remove_action('save_post_product',                    'watch_simple_product_changes', 20);

    try {
        if ($is_variation) {
            if ($model_name !== null) {
                $parent_id  = $product->get_parent_id();
                $parent     = $parent_id ? wc_get_product($parent_id) : null;
                $parent_sku = $parent ? $parent->get_sku() : '';
                if ($model_name !== $parent_sku) {
                    $product->set_sku($model_name);
                }
            }
            if ($retail_price !== null) $product->set_regular_price((string) $retail_price);
            $product->save();
            // Write after save so WC doesn't overwrite it during the save lifecycle
            update_post_meta($wc_product_id, '_variation_description', $description ?? '');

            // Brand lives on the parent product, not the variation
            $parent_id = $product->get_parent_id();
            if ($parent_id && $brand_term_id) {
                wp_set_object_terms($parent_id, [$brand_term_id], 'product_brand');
                update_post_meta($parent_id, 'rank_math_primary_product_brand', $brand_term_id);
                wc_delete_product_transients($parent_id);
            }
        } else {
            if ($name !== null) $product->set_name($name);
            if ($description !== null) $product->set_short_description($description);
            if ($retail_price !== null) $product->set_regular_price((string) $retail_price);
            if ($model_name !== null) $product->set_sku($model_name);
            $product->save();

            if ($brand_term_id) {
                wp_set_object_terms($wc_product_id, [$brand_term_id], 'product_brand');
                update_post_meta($wc_product_id, 'rank_math_primary_product_brand', $brand_term_id);
                wc_delete_product_transients($wc_product_id);
            }
        }
    } finally {
        add_action('woocommerce_update_product',           'items_sync_wc_to_units');
        add_action('woocommerce_update_product_variation', 'items_sync_wc_to_units');
        add_action('save_post_product',                    'watch_simple_product_changes', 20, 3);
    }
}

// ─── Sanitize / format helpers ───────────────────────────────────────────────

// If $raw_value is a numeric string, returns that ID. If it's a plain name,
// inserts a new record in the given table and returns the new ID.
function items_resolve_or_create(string $table_suffix, string $raw_value): ?int
{
    global $wpdb;
    $val = sanitize_text_field($raw_value);
    if ($val === '') return null;
    if (is_numeric($val)) return absint($val) ?: null;
    $wpdb->insert($wpdb->prefix . 'mji_' . $table_suffix, ['name' => $val], ['%s']);
    delete_transient('mji_' . $table_suffix);
    return $wpdb->insert_id ?: null;
}

function items_sanitize_form(): array
{
    $allowed_statuses = ['in_stock', 'missing', 'sold', 'damaged', 'rtv', 'dismantled'];
    $status           = in_array($_POST['status'] ?? '', $allowed_statuses) ? $_POST['status'] : 'in_stock';

    $date_raw     = sanitize_text_field($_POST['invoice_date'] ?? '');
    $created_date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_raw) ? $date_raw . ' 00:00:00' : current_time('mysql');

    $wc_product_id = absint($_POST['wc_product_id'] ?? 0);
    $image_id      = absint($_POST['image_id'] ?? 0);
    $location_id   = absint($_POST['location_id'] ?? 0);

    return [
        'sku'            => sanitize_text_field($_POST['sku'] ?? ''),
        'serial'         => sanitize_text_field($_POST['serial'] ?? '') ?: null,
        'status'         => $status,
        'location_id'    => $location_id ?: null,
        'brand_id'       => items_resolve_or_create('brands', $_POST['brand_id'] ?? ''),
        'model_id'       => items_resolve_or_create('models', $_POST['model_id'] ?? ''),
        'cost_price'     => max(0, (float) ($_POST['cost_price'] ?? 0)),
        'true_cost'      => $_POST['true_cost'] !== '' ? max(0, (float) $_POST['true_cost']) : null,
        'retail_price'   => max(0, (float) ($_POST['retail_price'] ?? 0)),
        'supplier_id'    => items_resolve_or_create('suppliers', $_POST['supplier_id'] ?? ''),
        'invoice_number' => sanitize_text_field($_POST['invoice_number'] ?? '') ?: null,
        'created_date'   => $created_date,
        'name'           => sanitize_text_field(wp_unslash($_POST['item_name'] ?? '')) ?: null,
        'description'    => wp_kses_post(wp_unslash($_POST['description'] ?? '')) ?: null,
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

    $unit = $wpdb->get_row($wpdb->prepare(
        "SELECT status, wc_product_id, wc_product_variant_id FROM {$table} WHERE id = %d",
        $id
    ));
    if ($unit === null) {
        wp_send_json_error(['message' => 'Unit not found']);
    }
    if ($unit->status === 'sold') {
        wp_send_json_error(['message' => 'Cannot delete a sold unit']);
    }

    $result = $wpdb->delete($table, ['id' => $id], ['%d']);
    if ($result === false) {
        wp_send_json_error(['message' => 'Delete failed']);
    }

    if ($unit->status === 'in_stock' && $unit->wc_product_id) {
        wc_update_product_stock((int) ($unit->wc_product_variant_id ?: $unit->wc_product_id), 1, 'decrease');
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

// ─── WC → units sync ─────────────────────────────────────────────────────────
function items_sync_wc_to_units(int $product_id): void
{
    global $wpdb;

    $product = wc_get_product($product_id);
    if (!$product) return;

    $is_variation = $product->get_type() === 'variation';

    // Build SET clause dynamically — skip any field that has no value so we
    // never coerce null to '' / 0.00 / 0 via prepare() placeholders.
    $set_parts = [];
    $values    = [];

    $name = $product->get_name() ?: null;
    if ($name !== null) {
        $set_parts[] = '`name` = %s';
        $values[]    = $name;
    }

    $description = $is_variation
        ? (get_post_meta($product_id, '_variation_description', true) ?: null)
        : ($product->get_short_description() ?: null);
    if ($description !== null) {
        $set_parts[] = '`description` = %s';
        $values[]    = $description;
    }

    $price_raw = $product->get_regular_price();
    if ($price_raw !== '' && $price_raw !== null && (float) $price_raw > 0) {
        $set_parts[] = '`retail_price` = %f';
        $values[]    = (float) $price_raw;
    }

    $image_id = (int) get_post_thumbnail_id($product_id) ?: null;
    if ($image_id !== null) {
        $set_parts[] = '`image_id` = %d';
        $values[]    = $image_id;
    } else {
        $set_parts[] = '`image_id` = NULL';
    }

    if (!$set_parts) return;

    // Only sold and rtv are frozen — all other statuses stay in sync with WC
    $values[] = $product_id;
    $where_col = $is_variation ? 'wc_product_variant_id' : 'wc_product_id';
    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->prefix}mji_product_inventory_units
             SET " . implode(', ', $set_parts) . "
             WHERE {$where_col} = %d
               AND status NOT IN ('sold', 'rtv')",
            $values
        )
    );
}
add_action('woocommerce_update_product',           'items_sync_wc_to_units');
add_action('woocommerce_update_product_variation', 'items_sync_wc_to_units');
