<?php
const GST_RATE = 0.05;
const PST_RATE = 0.07;
define('MJI_DB_VERSION', '1.1');

require_once get_stylesheet_directory() . '/inventory/print.php';
require_once get_stylesheet_directory() . '/inventory/sales.php';
require_once get_stylesheet_directory() . '/inventory/customer.php';
require_once get_stylesheet_directory() . '/inventory/salespeople.php';
require_once get_stylesheet_directory() . '/inventory/product_units.php';
require_once get_stylesheet_directory() . '/inventory/find.php';
require_once get_stylesheet_directory() . '/inventory/reports.php';
require_once get_stylesheet_directory() . '/inventory/items.php';

// Create the table when theme activated
function mji_create_all_tables()
{
    // Define table creation order — PARENTS FIRST, CHILDREN LAST
    $tables = [
        'salespeople'              => 'create_salespeople_table',
        'customers'                => 'create_customers_table',
        'locations'                => 'create_locations_table',
        'brands'                   => 'create_brands_table',
        'collections'              => 'create_collections_table',
        'models'                   => 'create_models_table',
        'suppliers'                => 'create_suppliers_table',
        'product_inventory_units'  => 'create_product_inventory_units_table',
        'orders'                   => 'create_orders_table',
        'order_items'              => 'create_order_items_table',
        'services'                 => 'create_services_table',
        'layaways'                 => 'create_layaways_table',
        'credits'                  => 'create_credits_table',
        'payments'                 => 'create_payments_table',
        'returns'                  => 'create_returns_table',
        'return_items'             => 'create_return_items_table',
        'return_services'          => 'create_return_services_table',
        'inventory_status_history' => 'create_inventory_status_history_table',
        'product_sku_history'      => 'create_sku_history_table',
        'products_collections'     => 'create_products_collections_table',
    ];

    foreach ($tables as $slug => $func_name) {
        if (!function_exists($func_name)) {
            custom_log("Function missing: {$func_name}");
            continue;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'mji_' . $slug;

        $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));

        if ($exists === $table_name) {
            custom_log("✅ Table already exists: {$table_name} — skipping creation");
        } else {
            custom_log("🆕 Creating table: {$table_name}");
            $func_name();
        }
    }
}

add_action('after_switch_theme', 'mji_create_all_tables');

function mji_run_migrations()
{
    $installed = get_option('mji_db_version', '0');
    if (version_compare($installed, MJI_DB_VERSION, '>=')) {
        return;
    }

    global $wpdb;

    if (version_compare($installed, '1.1', '<')) {
        mji_migrate_1_1($wpdb);
    }

    update_option('mji_db_version', MJI_DB_VERSION);
}
add_action('init', 'mji_run_migrations');

function mji_migrate_1_1($wpdb)
{
    $units_table = $wpdb->prefix . 'mji_product_inventory_units';
    $pc_table    = $wpdb->prefix . 'mji_products_collections';

    // --- product_inventory_units ---

    $wpdb->query("ALTER TABLE `{$units_table}`
        MODIFY COLUMN `wc_product_id` BIGINT DEFAULT NULL");
    if ($wpdb->last_error) {
        custom_log('❌ Migration 1.1 step 1 failed: ' . $wpdb->last_error);
        return;
    }

    $wpdb->query("ALTER TABLE `{$units_table}`
        ADD COLUMN IF NOT EXISTS `name`        VARCHAR(255) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS `description` TEXT DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS `image_id`    BIGINT DEFAULT NULL");
    if ($wpdb->last_error) {
        custom_log('❌ Migration 1.1 step 2 failed: ' . $wpdb->last_error);
        return;
    }

    // --- backfill name, description, image_id from WooCommerce ---
    $units = $wpdb->get_results(
        "SELECT id, wc_product_id, wc_product_variant_id
         FROM `{$units_table}`
         WHERE wc_product_id IS NOT NULL AND name IS NULL"
    );
    foreach ($units as $unit) {
        $product = wc_get_product($unit->wc_product_id);
        if (!$product) {
            continue;
        }
        $name = $product->get_name();
        if ($unit->wc_product_variant_id) {
            $description = (string) get_post_meta($unit->wc_product_variant_id, '_variation_description', true);
            $raw_image   = (int) get_post_meta($unit->wc_product_variant_id, '_thumbnail_id', true);
            $image_id    = $raw_image ?: null;
        } else {
            $description = $product->get_short_description();
            $raw_image   = (int) get_post_thumbnail_id($unit->wc_product_id);
            $image_id    = $raw_image ?: null;
        }
        $wpdb->update(
            $units_table,
            [
                'name'        => $name,
                'description' => $description ?: null,
                'image_id'    => $image_id,
            ],
            ['id' => $unit->id],
            ['%s', '%s', $image_id ? '%d' : '%s'],
            ['%d']
        );
        if ($wpdb->last_error) {
            custom_log('❌ Migration 1.1 backfill failed for unit ' . $unit->id . ': ' . $wpdb->last_error);
        }
    }

    // --- products_collections ---
    // Re-point from wc_product_id to product_inventory_units.id

    // 1. Make product_id nullable so we can INSERT rows without it during backfill
    $wpdb->query("ALTER TABLE `{$pc_table}`
        MODIFY COLUMN `product_id` BIGINT DEFAULT NULL");
    if ($wpdb->last_error) {
        custom_log('❌ Migration 1.1 step 3 failed: ' . $wpdb->last_error);
        return;
    }

    // 2. Add inventory_unit_id column
    $wpdb->query("ALTER TABLE `{$pc_table}`
        ADD COLUMN IF NOT EXISTS `inventory_unit_id` BIGINT DEFAULT NULL");
    if ($wpdb->last_error) {
        custom_log('❌ Migration 1.1 step 4 failed: ' . $wpdb->last_error);
        return;
    }

    // 3. Drop old unique key so we can add the new one
    $old_key = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND INDEX_NAME = 'product_collection_id_unique'",
        $pc_table
    ));
    if ($old_key) {
        $wpdb->query("ALTER TABLE `{$pc_table}` DROP INDEX `product_collection_id_unique`");
        if ($wpdb->last_error) {
            custom_log('❌ Migration 1.1 step 5 failed: ' . $wpdb->last_error);
            return;
        }
    }

    // 4. Add new unique key (inventory_unit_id is all NULL right now — MySQL allows that)
    $new_key = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND INDEX_NAME = 'unit_collection_unique'",
        $pc_table
    ));
    if (!$new_key) {
        $wpdb->query("ALTER TABLE `{$pc_table}`
            ADD UNIQUE KEY `unit_collection_unique` (`inventory_unit_id`, `collection_id`)");
        if ($wpdb->last_error) {
            custom_log('❌ Migration 1.1 step 6 failed: ' . $wpdb->last_error);
            return;
        }
    }

    // 5. Backfill: one row per (unit, collection) — expands many-units-per-WC-product correctly
    $wpdb->query("INSERT IGNORE INTO `{$pc_table}` (`inventory_unit_id`, `collection_id`)
        SELECT piu.id, pc.collection_id
        FROM `{$pc_table}` pc
        JOIN `{$units_table}` piu ON piu.wc_product_id = pc.product_id
        WHERE pc.product_id IS NOT NULL");
    if ($wpdb->last_error) {
        custom_log('❌ Migration 1.1 step 7 failed: ' . $wpdb->last_error);
        return;
    }

    // 6. Delete the old product_id-based rows (those never got inventory_unit_id set)
    $wpdb->query("DELETE FROM `{$pc_table}` WHERE `inventory_unit_id` IS NULL");
    if ($wpdb->last_error) {
        custom_log('❌ Migration 1.1 step 8 failed: ' . $wpdb->last_error);
        return;
    }

    // 7. Add FK on inventory_unit_id
    $fk_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
         WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = %s AND CONSTRAINT_NAME = 'fk_inventory_unit'",
        $pc_table
    ));
    if (!$fk_exists) {
        $wpdb->query("ALTER TABLE `{$pc_table}`
            ADD CONSTRAINT `fk_inventory_unit`
            FOREIGN KEY (`inventory_unit_id`) REFERENCES `{$units_table}`(`id`) ON DELETE CASCADE");
        if ($wpdb->last_error) {
            custom_log('❌ Migration 1.1 step 9 failed: ' . $wpdb->last_error);
            return;
        }
    }

    // 8. Drop old product_id column
    $col_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND COLUMN_NAME = 'product_id'",
        $pc_table
    ));
    if ($col_exists) {
        $wpdb->query("ALTER TABLE `{$pc_table}` DROP COLUMN `product_id`");
        if ($wpdb->last_error) {
            custom_log('❌ Migration 1.1 step 10 failed: ' . $wpdb->last_error);
            return;
        }
    }

    custom_log('✅ Migration 1.1 completed.');
}

function create_customers_table()
{
    global $wpdb;
    $table_name        = $wpdb->prefix . 'mji_customers';
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';
    $charset_collate   = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id               BIGINT AUTO_INCREMENT PRIMARY KEY,
        first_name       VARCHAR(255) NOT NULL,
        last_name        VARCHAR(255) NOT NULL,
        email            VARCHAR(255) DEFAULT NULL,
        street_address   VARCHAR(255) DEFAULT NULL,
        city             VARCHAR(100) DEFAULT NULL,
        province         VARCHAR(100) DEFAULT NULL,
        postal_code      VARCHAR(20) DEFAULT NULL,
        country          VARCHAR(100) DEFAULT NULL,
        created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        notes            TEXT DEFAULT NULL,
        primary_phone    VARCHAR(20) DEFAULT NULL,
        secondary_phone  VARCHAR(20) DEFAULT NULL,
        salesperson_id   BIGINT DEFAULT NULL,
        prefix           VARCHAR(10) DEFAULT NULL,
        UNIQUE KEY email (email),
        KEY fk_salesperson (salesperson_id),
        FULLTEXT KEY fulltext_index_name (
            first_name, last_name,
            street_address, city, province, postal_code, country,
            primary_phone, secondary_phone
        ),
        FOREIGN KEY (salesperson_id) REFERENCES $salespeople_table(id) ON DELETE SET NULL ON UPDATE CASCADE
    ) $charset_collate;";

    $result = $wpdb->query($sql);

    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");
    }
}

function create_salespeople_table()
{
    global $wpdb;

    // Define table name with WordPress prefix
    $table_name = $wpdb->prefix . 'mji_salespeople';

    // Define character set and collation
    $charset_collate = $wpdb->get_charset_collate();

    // Define the SQL query to create the table
    $sql = "CREATE TABLE $table_name (
        id BIGINT PRIMARY KEY AUTO_INCREMENT,
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL
        ) $charset_collate;";

    $result = $wpdb->query($sql);

    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");
    }
}

function create_orders_table()
{
    global $wpdb;

    $table_name        = $wpdb->prefix . 'mji_orders';
    $customers_table   = $wpdb->prefix . 'mji_customers';
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';
    $locations_table   = $wpdb->prefix . 'mji_locations';
    $charset_collate   = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id             BIGINT PRIMARY KEY AUTO_INCREMENT,
        customer_id    BIGINT NOT NULL,
        salesperson_id BIGINT NOT NULL,
        location_id    BIGINT DEFAULT NULL,
        status         ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'completed',
        created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        reference_num  VARCHAR(50) NOT NULL,
        subtotal       DECIMAL(10,2) NOT NULL,
        gst_total      DECIMAL(10,2) NOT NULL,
        pst_total      DECIMAL(10,2) NOT NULL,
        total          DECIMAL(10,2) NOT NULL,
        notes          TEXT DEFAULT NULL,

        UNIQUE KEY unique_reference_num (reference_num),
        KEY customer_id (customer_id),
        KEY salesperson_id (salesperson_id),
        KEY created_at_index (created_at),
        KEY wp_mji_orders_ibfk_3 (location_id),
        KEY created_at_and_location_index (created_at, location_id),
        FOREIGN KEY (customer_id)    REFERENCES $customers_table(id),
        FOREIGN KEY (salesperson_id) REFERENCES $salespeople_table(id),
        FOREIGN KEY (location_id)    REFERENCES $locations_table(id)
        ) $charset_collate;";

    $result = $wpdb->query($sql);

    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");
    }
}

function create_payments_table()
{
    global $wpdb;

    $table_name       = $wpdb->prefix . 'mji_payments';
    $customers_table  = $wpdb->prefix . 'mji_customers';
    $orders_table     = $wpdb->prefix . 'mji_orders';
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';
    $locations_table  = $wpdb->prefix . 'mji_locations';
    $layaways_table   = $wpdb->prefix . 'mji_layaways';
    $credits_table    = $wpdb->prefix . 'mji_credits';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT PRIMARY KEY AUTO_INCREMENT,
        customer_id BIGINT NOT NULL,
        salesperson_id BIGINT NOT NULL,
        location_id BIGINT NULL,
        order_id BIGINT NULL,
        layaway_id BIGINT NULL,
        credit_id BIGINT NULL,
        reference_num VARCHAR(50),
        method ENUM('cash','cheque','debit','visa','master_card','amex','bank_draft','cup','alipay','layaway','gift_card','credit','wire') NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        transaction_type ENUM('purchase','layaway_deposit','layaway_redemption','credit_deposit','credit_redemption','refund') NOT NULL DEFAULT 'purchase',
        payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        notes TEXT,
        layaway_id_uc BIGINT GENERATED ALWAYS AS (IFNULL(layaway_id, 0)) STORED,
        credit_id_uc  BIGINT GENERATED ALWAYS AS (IFNULL(credit_id, 0)) STORED,

        UNIQUE KEY reference_num_unique (customer_id, reference_num, method, transaction_type, layaway_id_uc, credit_id_uc),
        KEY customer_id (customer_id),
        KEY order_id (order_id),
        KEY salesperson_id (salesperson_id),
        KEY location_id (location_id),
        KEY layaway_id (layaway_id),
        KEY credit_id (credit_id),
        KEY reference_num_index (reference_num),
        KEY payment_date_index (payment_date),
        FOREIGN KEY (customer_id)    REFERENCES $customers_table(id)  ON DELETE RESTRICT,
        FOREIGN KEY (salesperson_id) REFERENCES $salespeople_table(id) ON DELETE RESTRICT,
        FOREIGN KEY (location_id)    REFERENCES $locations_table(id)   ON DELETE RESTRICT,
        FOREIGN KEY (order_id)       REFERENCES $orders_table(id)      ON DELETE RESTRICT,
        FOREIGN KEY (layaway_id)     REFERENCES $layaways_table(id)    ON DELETE RESTRICT,
        FOREIGN KEY (credit_id)      REFERENCES $credits_table(id)     ON DELETE RESTRICT
        ) $charset_collate;";

    $result = $wpdb->query($sql);

    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");
    }
}

function create_locations_table()
{
    global $wpdb;

    // Define table name with WordPress prefix
    $table_name = $wpdb->prefix . 'mji_locations';

    // Define character set and collation
    $charset_collate = $wpdb->get_charset_collate();

    // Define the SQL query to create the table
    $sql = "CREATE TABLE $table_name (
        id BIGINT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50)
        ) $charset_collate;";

    $result = $wpdb->query($sql);

    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");

        $wpdb->insert($table_name, ['name' => 'Hastings']);
        $wpdb->insert($table_name, ['name' => 'Richmond']);
        $wpdb->insert($table_name, ['name' => 'Metrotown']);
    }
}

function create_models_table()
{
    global $wpdb;

    // Define table name with WordPress prefix
    $table_name = $wpdb->prefix . 'mji_models';

    // Define character set and collation
    $charset_collate = $wpdb->get_charset_collate();

    // Define the SQL query to create the table
    $sql = "CREATE TABLE $table_name (
        id   BIGINT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) DEFAULT NULL,
        KEY name_index (name)
        ) $charset_collate;";

    $result = $wpdb->query($sql);

    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");
    }
}

function create_brands_table()
{
    global $wpdb;

    // Define table name with WordPress prefix
    $table_name = $wpdb->prefix . 'mji_brands';

    // Define character set and collation
    $charset_collate = $wpdb->get_charset_collate();

    // Define the SQL query to create the table
    $sql = "CREATE TABLE $table_name (
        id   BIGINT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) DEFAULT NULL,
        UNIQUE KEY name_index (name)
        ) $charset_collate;";

    $result = $wpdb->query($sql);

    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");
    }
}

function create_product_inventory_units_table()
{
    global $wpdb;

    // Define table name with WordPress prefix
    $table_name = $wpdb->prefix . 'mji_product_inventory_units';
    $locations_table = $wpdb->prefix . 'mji_locations';
    $brands_table = $wpdb->prefix . 'mji_brands';
    $models_table = $wpdb->prefix . 'mji_models';

    // Define character set and collation
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE `{$table_name}` (
        `id`                   BIGINT NOT NULL AUTO_INCREMENT,
        `wc_product_id`        BIGINT DEFAULT NULL,
        `wc_product_variant_id` BIGINT DEFAULT NULL,
        `location_id`          BIGINT NOT NULL,
        `model_id`             BIGINT DEFAULT NULL,
        `brand_id`             BIGINT DEFAULT NULL,
        `sku`                  VARCHAR(50) NOT NULL,
        `serial`               VARCHAR(50) DEFAULT NULL,
        `status`               ENUM('in_stock','missing','sold','damaged','rtv','dismantled') NOT NULL,
        `created_date`         DATETIME DEFAULT CURRENT_TIMESTAMP,
        `sold_date`            DATETIME DEFAULT NULL,
        `status_updated_date`  DATETIME DEFAULT NULL,
        `cost_price`           DECIMAL(10,2) NOT NULL,
        `retail_price`         DECIMAL(10,2) NOT NULL,
        `notes`                TEXT DEFAULT NULL,
        `supplier_id`          BIGINT DEFAULT NULL,
        `invoice_number`       VARCHAR(100) DEFAULT NULL,
        `true_cost`            DECIMAL(10,2) DEFAULT NULL,
        `name`                 VARCHAR(255) DEFAULT NULL,
        `description`          TEXT DEFAULT NULL,
        `image_id`             BIGINT DEFAULT NULL,

        PRIMARY KEY (`id`),
        UNIQUE KEY `sku` (`sku`),
        UNIQUE KEY `serial` (`serial`),
        KEY `idx_inventory_filter` (`location_id`, `status`, `created_date`, `sold_date`),
        KEY `product_id_index` (`wc_product_id`),
        CONSTRAINT `fk_location` FOREIGN KEY (`location_id`) REFERENCES `{$locations_table}`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_brand`    FOREIGN KEY (`brand_id`)    REFERENCES `{$brands_table}`(`id`)    ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_model`    FOREIGN KEY (`model_id`)    REFERENCES `{$models_table}`(`id`)    ON DELETE CASCADE ON UPDATE CASCADE
    ) {$charset_collate};";

    $result = $wpdb->query($sql);

    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");
    }
}

function create_order_items_table()
{
    global $wpdb;

    // Define table name with WordPress prefix
    $table_name = $wpdb->prefix . 'mji_order_items';
    $orders_table = $wpdb->prefix . 'mji_orders';
    $product_inventory_table = $wpdb->prefix . 'mji_product_inventory_units';

    // Define character set and collation
    $charset_collate = $wpdb->get_charset_collate();

    // Define the SQL query to create the table
    $sql = "CREATE TABLE $table_name (
        id BIGINT PRIMARY KEY AUTO_INCREMENT,
        order_id BIGINT NOT NULL,
        product_inventory_unit_id BIGINT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        sale_price DECIMAL(10,2) NOT NULL,
        discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        total DECIMAL(10,2) NOT NULL,

        KEY order_id (order_id),
        KEY product_inventory_unit_id (product_inventory_unit_id),
        FOREIGN KEY (order_id) REFERENCES $orders_table(id),
        FOREIGN KEY (product_inventory_unit_id) REFERENCES $product_inventory_table(id)
        ) $charset_collate;";

    $result = $wpdb->query($sql);

    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");
    }
}

function create_services_table()
{
    global $wpdb;

    // Define table name with WordPress prefix
    $table_name = $wpdb->prefix . 'mji_services';
    $orders_table = $wpdb->prefix . 'mji_orders';
    $locations_table = $wpdb->prefix . 'mji_locations';

    // Define character set and collation
    $charset_collate = $wpdb->get_charset_collate();

    // Define the SQL query to create the table
    $sql = "CREATE TABLE $table_name (
        id BIGINT PRIMARY KEY AUTO_INCREMENT,
        order_id BIGINT NOT NULL,
        location_id BIGINT NOT NULL,
        category ENUM('watch_service', 'jewellery_service', 'shipping')  NOT NULL,
        description TEXT,
        cost_price DECIMAL(10,2) NOT NULL,
        sold_price DECIMAL(10,2) NOT NULL,

        FOREIGN KEY (order_id) REFERENCES $orders_table(id),
        FOREIGN KEY (location_id) REFERENCES $locations_table(id)
        ) $charset_collate;";

    $result = $wpdb->query($sql);

    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");
    }
}

function create_sku_history_table()
{
    global $wpdb;

    // Define table name with WordPress prefix
    $table_name = $wpdb->prefix . 'mji_product_sku_history';
    $product_inventory_table = $wpdb->prefix . 'mji_product_inventory_units';

    $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));

    if ($exists === $table_name) {
        custom_log("✅ Table already exists: {$table_name} — skipping creation");
    } else {
        // Define character set and collation
        $charset_collate = $wpdb->get_charset_collate();

        // Define the SQL query to create the table
        $sql = "CREATE TABLE $table_name (
        `id` BIGINT NOT NULL AUTO_INCREMENT,
        `unit_id` BIGINT NOT NULL,
        `old_sku` VARCHAR(50) NOT NULL,
        `changed_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `old_sku` (`old_sku`),
        FOREIGN KEY (`unit_id`) REFERENCES `$product_inventory_table`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
        ) $charset_collate;";

        $result = $wpdb->query($sql);

        if ($result === false) {
            custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
        } else {
            custom_log("✅ Successfully created {$table_name}");
        }
    }
}

function create_suppliers_table()
{
    global $wpdb;

    // Define table name with WordPress prefix
    $table_name = $wpdb->prefix . 'mji_suppliers';

    $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));

    if ($exists === $table_name) {
        custom_log("✅ Table already exists: {$table_name} — skipping creation");
    } else {
        // Define character set and collation
        $charset_collate = $wpdb->get_charset_collate();

        // Define the SQL query to create the table
        $sql = "CREATE TABLE $table_name (
        id BIGINT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50)
        ) $charset_collate;";

        $result = $wpdb->query($sql);

        if ($result === false) {
            custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
        } else {
            custom_log("✅ Successfully created {$table_name}");
        }
    }
}

function create_collections_table()
{
    global $wpdb;
    $table_name      = $wpdb->prefix . 'mji_collections';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id   BIGINT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL,
        UNIQUE KEY name_unique (name)
        ) $charset_collate;";
    $result = $wpdb->query($sql);
    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");
    }
}

function create_layaways_table()
{
    global $wpdb;
    $table_name      = $wpdb->prefix . 'mji_layaways';
    $customers_table = $wpdb->prefix . 'mji_customers';
    $locations_table = $wpdb->prefix . 'mji_locations';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id               BIGINT PRIMARY KEY AUTO_INCREMENT,
        customer_id      BIGINT NOT NULL,
        location_id      BIGINT NOT NULL,
        reference_num    VARCHAR(50) NOT NULL,
        total_amount     DECIMAL(10,2) NOT NULL,
        remaining_amount DECIMAL(10,2) NOT NULL,
        status           ENUM('active','redeemed','expired','cancelled') DEFAULT 'active',
        created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY reference_num (reference_num),
        FOREIGN KEY (customer_id) REFERENCES $customers_table(id) ON DELETE RESTRICT,
        FOREIGN KEY (location_id) REFERENCES $locations_table(id) ON DELETE RESTRICT
        ) $charset_collate;";
    $result = $wpdb->query($sql);
    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");
    }
}

function create_credits_table()
{
    global $wpdb;
    $table_name      = $wpdb->prefix . 'mji_credits';
    $customers_table = $wpdb->prefix . 'mji_customers';
    $locations_table = $wpdb->prefix . 'mji_locations';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id               BIGINT PRIMARY KEY AUTO_INCREMENT,
        customer_id      BIGINT NOT NULL,
        location_id      BIGINT NOT NULL,
        reference_num    VARCHAR(50) DEFAULT NULL,
        total_amount     DECIMAL(10,2) NOT NULL,
        remaining_amount DECIMAL(10,2) NOT NULL,
        status           ENUM('active','redeemed','expired','cancelled') DEFAULT 'active',
        created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY reference_num (reference_num),
        FOREIGN KEY (customer_id) REFERENCES $customers_table(id) ON DELETE RESTRICT,
        FOREIGN KEY (location_id) REFERENCES $locations_table(id) ON DELETE RESTRICT
        ) $charset_collate;";
    $result = $wpdb->query($sql);
    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");
    }
}

function create_returns_table()
{
    global $wpdb;
    $table_name      = $wpdb->prefix . 'mji_returns';
    $orders_table    = $wpdb->prefix . 'mji_orders';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id            BIGINT PRIMARY KEY AUTO_INCREMENT,
        order_id      BIGINT NOT NULL,
        reference_num VARCHAR(50) DEFAULT NULL,
        return_date   DATE NOT NULL DEFAULT (CURDATE()),
        reason        TEXT,
        subtotal      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        gst_total     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        pst_total     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        total         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        UNIQUE KEY reference_num_index (reference_num),
        KEY created_at_index (return_date),
        FOREIGN KEY (order_id) REFERENCES $orders_table(id) ON DELETE RESTRICT
        ) $charset_collate;";
    $result = $wpdb->query($sql);
    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");
    }
}

function create_return_items_table()
{
    global $wpdb;
    $table_name       = $wpdb->prefix . 'mji_return_items';
    $returns_table    = $wpdb->prefix . 'mji_returns';
    $units_table      = $wpdb->prefix . 'mji_product_inventory_units';
    $charset_collate  = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id                        BIGINT PRIMARY KEY AUTO_INCREMENT,
        return_id                 BIGINT NOT NULL,
        order_item_id             BIGINT NOT NULL,
        product_inventory_unit_id BIGINT NOT NULL,
        unit_price                DECIMAL(10,2) NOT NULL,
        created_at                DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (return_id)                 REFERENCES $returns_table(id) ON DELETE RESTRICT,
        FOREIGN KEY (product_inventory_unit_id) REFERENCES $units_table(id)   ON DELETE RESTRICT
        ) $charset_collate;";
    $result = $wpdb->query($sql);
    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");
    }
}

function create_return_services_table()
{
    global $wpdb;
    $table_name      = $wpdb->prefix . 'mji_return_services';
    $returns_table   = $wpdb->prefix . 'mji_returns';
    $services_table  = $wpdb->prefix . 'mji_services';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id         BIGINT PRIMARY KEY AUTO_INCREMENT,
        return_id  BIGINT NOT NULL,
        service_id BIGINT NOT NULL,
        price      DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (return_id)  REFERENCES $returns_table(id)  ON DELETE RESTRICT,
        FOREIGN KEY (service_id) REFERENCES $services_table(id) ON DELETE RESTRICT
        ) $charset_collate;";
    $result = $wpdb->query($sql);
    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");
    }
}

function create_inventory_status_history_table()
{
    global $wpdb;
    $table_name      = $wpdb->prefix . 'mji_inventory_status_history';
    $units_table     = $wpdb->prefix . 'mji_product_inventory_units';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id                  BIGINT PRIMARY KEY AUTO_INCREMENT,
        inventory_unit_id   BIGINT NOT NULL,
        from_status         VARCHAR(50) DEFAULT NULL,
        to_status           VARCHAR(50) NOT NULL,
        notes               TEXT DEFAULT NULL,
        changed_by_user_id  BIGINT DEFAULT NULL,
        reference_num       VARCHAR(50) DEFAULT NULL,
        created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY created_at_index (created_at),
        KEY inventory_unit_id_index (inventory_unit_id),
        KEY idx_to_status (to_status),
        KEY idx_unit_id (inventory_unit_id, id),
        FOREIGN KEY (inventory_unit_id) REFERENCES $units_table(id) ON DELETE CASCADE
        ) $charset_collate;";
    $result = $wpdb->query($sql);
    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");
    }
}

function create_products_collections_table()
{
    global $wpdb;
    $table_name        = $wpdb->prefix . 'mji_products_collections';
    $collections_table = $wpdb->prefix . 'mji_collections';
    $charset_collate   = $wpdb->get_charset_collate();
    $units_table = $wpdb->prefix . 'mji_product_inventory_units';
    $sql = "CREATE TABLE $table_name (
        id                  BIGINT PRIMARY KEY AUTO_INCREMENT,
        inventory_unit_id   BIGINT DEFAULT NULL,
        collection_id       BIGINT NOT NULL,
        UNIQUE KEY unit_collection_unique (inventory_unit_id, collection_id),
        KEY collection_id_index (collection_id),
        KEY inventory_unit_id_index (inventory_unit_id),
        FOREIGN KEY (collection_id) REFERENCES $collections_table(id) ON DELETE RESTRICT,
        CONSTRAINT fk_inventory_unit FOREIGN KEY (inventory_unit_id) REFERENCES $units_table(id) ON DELETE CASCADE
        ) $charset_collate;";
    $result = $wpdb->query($sql);
    if ($result === false) {
        custom_log("❌ Failed to create {$table_name}: " . $wpdb->last_error);
    } else {
        custom_log("✅ Successfully created {$table_name}");
    }
}

// Add menu page in WordPress admin
function create_inventory_menu()
{
    add_menu_page(
        'Inventory Management', // Page Title
        'Sales', // Menu Title
        'manage_options', // Capability
        'inventory-management', // Menu Slug
        'inventory_page', // Callback Function
        'dashicons-id', // Icon
        25 // Position
    );

    // Submenu: Items
    add_submenu_page(
        'inventory-management',
        'Items',
        'Items',
        'manage_options',
        'items-management',
        'items_page'
    );

    // Submenu: Add Customer
    add_submenu_page(
        'inventory-management', // Parent slug
        'Customer',        // Page title
        'Customer',        // Menu title
        'manage_options',      // Capability
        'customer-management',        // Menu slug
        'customer_page' // Callback function
    );

    // Submenu: Add Salespeople
    add_submenu_page(
        'inventory-management', // Parent slug
        'Salespeople',        // Page title
        'Salespeople',        // Menu title
        'manage_options',      // Capability
        'salespeople-management',        // Menu slug
        'salespeople_page' // Callback function
    );

    // Submenu: Add Find
    add_submenu_page(
        'inventory-management', // Parent slug
        'Find',        // Page title
        'Find',        // Menu title
        'manage_options',      // Capability
        'invoice-management',        // Menu slug
        'find_page' // Callback function
    );

    // Submenu: Reports
    add_submenu_page(
        'inventory-management',
        'Reports',
        'Reports',
        'manage_options',
        'reports-management',
        'reports_page'
    );
}
add_action('admin_menu', 'create_inventory_menu');

// Searching on the backend of WordPress for products
function custom_woocommerce_admin_search($where, $wp_query)
{
    global $pagenow, $wpdb;

    // Check if we are on the WooCommerce product admin page
    if (is_admin() && 'edit.php' === $pagenow && isset($_GET['post_type']) && 'product' === $_GET['post_type'] && !empty($_GET['s'])) {

        // Get the search term
        $search_term = sanitize_text_field($_GET['s']);
        $custom_table = $wpdb->prefix . 'mji_product_inventory_units';
        $history_table = $wpdb->prefix . 'mji_product_sku_history';

        $where .= $wpdb->prepare("
            OR EXISTS (
                SELECT 1 FROM $custom_table
                WHERE $custom_table.wc_product_id = {$wpdb->posts}.ID
                AND (
                    $custom_table.sku = %s
                    OR $custom_table.serial = %s
                    OR EXISTS (
                        SELECT 1 FROM $history_table
                        WHERE $history_table.unit_id = $custom_table.id
                        AND $history_table.old_sku = %s
                    )
                )
            )
        ", $search_term, $search_term, $search_term);
    }

    return $where;
}
add_filter('posts_where', 'custom_woocommerce_admin_search', 10, 2);

// IMPORTING SELECT2 for select search HTML
function my_enqueue_scripts()
{
    wp_enqueue_script('select2', WC()->plugin_url() . '/assets/js/select2/select2.full.min.js', ['jquery'], WC()->version);
    wp_enqueue_style('select2-css', WC()->plugin_url() . '/assets/css/select2.css', [], WC()->version);
}
add_action('admin_enqueue_scripts', 'my_enqueue_scripts');


// ORDER ITEM REDUCTION
add_action('woocommerce_checkout_order_processed', 'adjust_stock_after_order', 10, 3);

// TODO: CHANGE THE RECENT SKU TO SOLD WHEN ORDER PLACED ONLINE 
function adjust_stock_after_order($order_id, $posted_data, $order)
{
    global $wpdb;
    $sku_error = array();

    foreach ($order->get_items() as $item_id => $item) {

        // Get product details
        $product_id = $item->get_product_id();       // Main product ID (parent ID for variations)
        $variation_id = $item->get_variation_id();   // Variation ID (0 if it's not a variation)

    }
}

// Add Cost Price field to simple products
add_action('woocommerce_product_options_general_product_data', 'add_cost_price_field');
function add_cost_price_field()
{
    global $product_object;
    if ($product_object && $product_object->is_type('simple')) {
        woocommerce_wp_text_input(array(
            'id' => '_cost_price',
            'label' => 'Cost Price',
            'desc_tip' => true,
            'description' => 'Enter the product cost price.',
            'type' => 'number',
            'custom_attributes' => array(
                'step' => 'any',
                'min' => '0'
            )
        ));
    }
}

// Save Cost Price field
add_action('woocommerce_process_product_meta', 'save_cost_price_field');
function save_cost_price_field(int $post_id)
{
    if (isset($_POST['_cost_price'])) {
        update_post_meta($post_id, '_cost_price', sanitize_text_field($_POST['_cost_price']));
    }
}

// Add Cost Price field to product variations
add_action('woocommerce_variation_options_pricing', 'add_cost_price_to_variations_styled', 10, 3);
function add_cost_price_to_variations_styled($loop, $variation_data, $variation)
{
?>
    <div class="form-row form-row-first">
        <label><?php esc_html_e('Cost Price ($)', 'woocommerce'); ?></label>
        <input type="number" class="wc_input_price short" name="_cost_price[<?php echo esc_attr($loop); ?>]"
            value="<?php echo esc_attr(get_post_meta($variation->ID, '_cost_price', true)); ?>" placeholder="" step="any"
            min="0" />
    </div>
<?php
}

add_action('woocommerce_save_product_variation', 'save_cost_price_variation', 10, 2);
function save_cost_price_variation($variation_id, $i)
{
    if (isset($_POST['_cost_price'][$i])) {
        update_post_meta($variation_id, '_cost_price', sanitize_text_field($_POST['_cost_price'][$i]));
    }
}

function mji_get_salespeople()
{
    static $salespeople = null;

    if ($salespeople !== null) {
        return $salespeople;
    }

    // Try transient first
    $salespeople = get_transient('mji_salespeople');
    if ($salespeople !== false) {
        return $salespeople;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mji_salespeople';
    $results = $wpdb->get_results("SELECT id, first_name, last_name FROM $table_name");

    $salespeople = $results ?: [];
    set_transient('mji_salespeople', $salespeople, DAY_IN_SECONDS);

    return $salespeople;
}

function mji_salesperson_dropdown($required = true, $selected_id = '')
{
    $salespeople = mji_get_salespeople();
    $required_attr = $required ? 'required' : '';

    $html = "<select name='salesperson' id='salesperson' {$required_attr}>";
    $html .= '<option value="">Select Salesperson</option>';

    foreach ($salespeople as $s) {
        $selected = ($s->id == $selected_id) ? 'selected' : '';
        $html .= "<option value='{$s->id}' {$selected}>{$s->first_name} {$s->last_name}</option>";
    }

    $html .= '</select>';
    return $html;
}

function mji_get_locations()
{
    static $locations = null;

    if ($locations !== null) {
        return $locations;
    }

    $locations = get_transient('mji_locations');
    if ($locations !== false) {
        return $locations;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mji_locations';
    $results = $wpdb->get_results("SELECT id, name FROM $table_name");

    $locations = $results ?: [];
    set_transient('mji_locations', $locations, DAY_IN_SECONDS);

    return $locations;
}

function mji_store_dropdown($required = true, $selected_id = '')
{
    $locations = mji_get_locations();
    $required_attr = $required ? 'required' : '';

    $html = "<select name='location' id='location' {$required_attr}>";
    $html .= '<option value="">Select Location</option>';

    foreach ($locations as $l) {
        $selected = ($l->id == $selected_id) ? 'selected' : '';
        $html .= "<option value='{$l->id}' {$selected}>{$l->name}</option>";
    }

    $html .= '</select>';
    return $html;
}

function mji_get_brands()
{
    // Try transient first
    $brands = get_transient('mji_brands');
    if ($brands !== false) {
        return $brands;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mji_brands';
    $results = $wpdb->get_results("SELECT id, name FROM $table_name ORDER BY name ASC");

    $brands = $results ?: [];
    set_transient('mji_brands', $brands, DAY_IN_SECONDS);

    return $brands;
}

function mji_brands_dropdown($required = true, $selected_id = '')
{
    $brands = mji_get_brands();
    $required_attr = $required ? 'required' : '';

    $html = "<select name='brands' id='brands' {$required_attr}>";
    $html .= '<option value="">Select Brands</option>';

    foreach ($brands as $b) {
        $selected = ($b->id == $selected_id) ? 'selected' : '';
        $html .= "<option value='{$b->id}' {$selected}>{$b->name}</option>";
    }

    $html .= '</select>';
    return $html;
}

function mji_get_models(): array
{
    $cached = get_transient('mji_models');
    if ($cached !== false) return $cached;

    global $wpdb;
    $results = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}mji_models ORDER BY name ASC") ?: [];
    set_transient('mji_models', $results, DAY_IN_SECONDS);
    return $results;
}

function mji_get_suppliers()
{
    // Try transient first
    $brands = get_transient('mji_suppliers');
    if ($brands !== false) {
        return $brands;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mji_suppliers';
    $results = $wpdb->get_results("SELECT id, name FROM $table_name ORDER BY name ASC");

    $brands = $results ?: [];
    set_transient('mji_suppliers', $brands, DAY_IN_SECONDS);

    return $brands;
}

function mji_suppliers_dropdown(bool $required = true, int $selected = 0)
{
    $suppliers = mji_get_suppliers();
    $required_attr = $required ? 'required' : '';

?>
    <select id="supplierID" name="supplier_id" class="supplier-select" <?= $required_attr ?>>
        <option value="">Select or add supplier</option>
        <?php foreach ($suppliers as $supplier): ?>
            <option value="<?= $supplier->id ?>" <?= selected($supplier->id, $selected, false) ?>><?= esc_html($supplier->name) ?></option>
        <?php endforeach; ?>
    </select>

<?php
}
// To look at our categories and then make the primary category based on parent category
add_action('admin_menu', function () {
    add_submenu_page(
        'woocommerce',
        'Assign Primary Categories by Parent (Rank Math)',
        'Assign Primary Categories (Parent Rule)',
        'manage_options',
        'assign-primary-by-parent',
        'render_assign_primary_by_parent_page'
    );
});

function render_assign_primary_by_parent_page()
{
    echo '<div class="wrap">';
    echo '<h1>Assign Primary Category Based on Parent (Rank Math)</h1>';

    if (isset($_POST['assign_categories'])) {
        check_admin_referer('assign_primary_by_parent_nonce', 'assign_primary_by_parent_nonce');

        $count = assign_primary_category_by_parent();
        echo '<div class="notice notice-success"><p>✅ Successfully updated ' . esc_html($count) . ' products.</p></div>';
    }

?>
    <form method="post">
        <?php wp_nonce_field('assign_primary_by_parent_nonce', 'assign_primary_by_parent_nonce'); ?>
        <?php submit_button('Start Assigning Primary Categories', 'primary', 'assign_categories'); ?>
        <p><em>This will set the primary category to the deepest direct child of "Watches" or "Designers".</em></p>
        <p><strong>Example:</strong> If product is in <code>Submariner → Rolex → Watches</code>, then <code>Rolex</code> becomes primary.</p>
    </form>

    <hr />
    <h3>How it works:</h3>
    <ul>
        <li>Only considers categories directly under "Watches" or "Designers"</li>
        <li>Skips deeper children like "Submariner" or "Diamond Ring"</li>
        <li>Does not overwrite existing primary category if already set</li>
        <li>Uses term ID — compatible with Rank Math</li>
    </ul>
    </div>
<?php
}

function assign_primary_category_by_parent()
{
    $target_parent_slugs = ['watches', 'designer'];

    // Get all published products
    $args = [
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'fields'         => 'ids',
    ];

    $products = get_posts($args);
    $count = 0;

    foreach ($products as $product_id) {
        // Skip if already has a primary category set
        $existing = get_post_meta($product_id, 'rank_math_primary_product_cat', true);
        if (! empty($existing)) {
            continue;
        }

        // Get all assigned product categories (term IDs)
        $term_ids = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));

        if (empty($term_ids)) {
            custom_log("ℹ️ Product ID: $product_id → No categories assigned");
            continue;
        }

        $primary_term_id = null;

        // Loop through all assigned categories
        foreach ($term_ids as $term_id) {
            // Walk up the hierarchy until we find a direct child of target parent
            $ancestors = get_ancestors($term_id, 'product_cat', 'taxonomy');

            // Check if any ancestor is a direct child of target parent
            $found = false;
            $current_parent_id = null;

            // Get direct parent of current term
            $term = get_term($term_id, 'product_cat');
            if (! $term || is_wp_error($term)) continue;

            $parent_id = $term->parent;

            // If parent is one of our target parents (Watches/Designers), this is our candidate!
            if ($parent_id > 0) {
                $parent_term = get_term($parent_id, 'product_cat');
                if ($parent_term && ! is_wp_error($parent_term) && in_array($parent_term->slug, $target_parent_slugs)) {
                    $found = true;
                    $current_parent_id = $term_id; // This is the "brand" level
                }
            }

            // If found, pick it as primary (we'll use first valid one)
            if ($found) {
                $primary_term_id = $current_parent_id;
                break; // Stop at first valid match — we want the first matching brand
            }
        }

        // If we found a valid brand-level category, assign it
        if ($primary_term_id) {
            update_post_meta($product_id, 'rank_math_primary_product_cat', $primary_term_id);
            $count++;
        } else {
            custom_log("ℹ️ Product ID: $product_id → No suitable parent category found (under Watches/Designers)");
        }
    }

    return $count;
}

// Show admin notice when error occurs on our inventory system
function mji_log_admin_error($message)
{
    $errors = get_transient('mji_global_admin_errors');

    if (!is_array($errors)) {
        $errors = [];
    }

    $errors[] = $message;

    set_transient('mji_global_admin_errors', $errors);
}

add_action('admin_notices', function () {
    $errors = get_transient('mji_global_admin_errors');

    if (is_array($errors) && !empty($errors)) {
        foreach ($errors as $error) {
            echo '<div class="notice notice-error is-dismissible">
                    <p><strong>Error:</strong> ' . esc_html($error) . '</p>
                  </div>';
        }
        delete_transient('mji_global_admin_errors');
    }
    if (isset($_GET['delete_blocked'])) {
        $post_id = intval($_GET['delete_blocked']);
        echo '<div class="notice notice-error is-dismissible">
                <p><strong>Deletion blocked:</strong> Product ID ' . esc_html($post_id) . ' cannot be deleted because it exists in inventory history.</p>
              </div>';
    }
});

// ─── Shared unit-history helper ──────────────────────────────────────────────
function mji_insert_unit_history(
    int $unit_id,
    ?string $from_status,
    string $to_status,
    ?string $notes = null,
    ?string $created_at = null,
    ?int $changed_by_user_id = null
): bool {
    global $wpdb;
    return $wpdb->insert(
        $wpdb->prefix . 'mji_inventory_status_history',
        [
            'inventory_unit_id'  => $unit_id,
            'from_status'        => $from_status,
            'to_status'          => $to_status,
            'notes'              => $notes,
            'created_at'         => $created_at ?? current_time('mysql'),
            'changed_by_user_id' => $changed_by_user_id ?? get_current_user_id(),
        ],
        ['%d', '%s', '%s', '%s', '%s', '%d']
    ) !== false;
}

function mji_enqueue_admin_scripts(string $hook): void {
    $dir  = get_stylesheet_directory_uri();
    $path = get_stylesheet_directory();

    // WC product edit page — only needs admin.css + inventory_unit.css
    if (in_array($hook, ['post.php', 'post-new.php'], true)) {
        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'product') {
            wp_enqueue_script('admin-script', $dir . '/inventory/scripts/index.js', [], filemtime($path . '/inventory/scripts/index.js'), true);
            wp_localize_script('admin-script', 'ajax_inventory', [
                'ajax_url'            => admin_url('admin-ajax.php'),
                'nonce'               => wp_create_nonce('mji_inventory_nonce'),
                'placeholder_img_url' => wc_placeholder_img_src('thumbnail'),
            ]);
            wp_enqueue_style('admin-style',          $dir . '/inventory/styles/admin.css',          [], filemtime($path . '/inventory/styles/admin.css'));
            wp_enqueue_style('inventory-unit-style', $dir . '/inventory/styles/inventory_unit.css', [], filemtime($path . '/inventory/styles/inventory_unit.css'));
        }
        return;
    }

    $inventory_hooks = [
        'toplevel_page_inventory-management',
        'sales_page_items-management',
        'sales_page_customer-management',
        'sales_page_salespeople-management',
        'sales_page_invoice-management',
        'sales_page_reports-management',
    ];

    if (!in_array($hook, $inventory_hooks, true)) return;

    // JS — all inventory pages
    wp_enqueue_script('zebra-printer',   $dir . '/inventory/scripts/printer/BrowserPrint-3.1.250.min.js',       [], '3.7',    true);
    wp_enqueue_script('zebra-printer-2', $dir . '/inventory/scripts/printer/BrowserPrint-Zebra-1.1.250.min.js', [], '3.7',    true);
    wp_enqueue_script('sheetjs',         'https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js',   [], '0.18.7', true);
    wp_enqueue_script('admin-script',    $dir . '/inventory/scripts/index.js', ['zebra-printer', 'zebra-printer-2', 'sheetjs'], filemtime($path . '/inventory/scripts/index.js'), true);
    wp_localize_script('admin-script', 'ajax_inventory', [
        'ajax_url'             => admin_url('admin-ajax.php'),
        'nonce'                => wp_create_nonce('mji_inventory_nonce'),
        'sales_css_url'        => $dir . '/inventory/styles/sales.css',
        'find_invoice_css_url' => $dir . '/inventory/styles/find_invoice.css',
        'placeholder_img_url'  => wc_placeholder_img_src('thumbnail'),
    ]);

    // Shared CSS — all inventory pages
    wp_enqueue_style('admin-style', $dir . '/inventory/styles/admin.css', [], filemtime($path . '/inventory/styles/admin.css'));

    // Page-specific CSS
    $page_styles = [
        'toplevel_page_inventory-management' => ['sales-style',    'sales.css'],
        'sales_page_items-management'        => ['items-style',    'items.css'],
        'sales_page_customer-management'     => ['customer-style', 'customer.css'],
        'sales_page_invoice-management'      => ['find-style',     'find_invoice.css'],
        'sales_page_reports-management'      => ['reports-style',  'reports.css'],
    ];

    if (isset($page_styles[$hook])) {
        [$handle, $file] = $page_styles[$hook];
        wp_enqueue_style($handle, $dir . '/inventory/styles/' . $file, ['admin-style'], filemtime($path . '/inventory/styles/' . $file));
    }
}
add_action('admin_enqueue_scripts', 'mji_enqueue_admin_scripts');

function format_label($input)
{
    // Split by any non-alphanumeric characters
    $words = preg_split('/[^a-zA-Z0-9]+/', $input, -1, PREG_SPLIT_NO_EMPTY);

    // Capitalize first letter of each word
    $words = array_map(function ($word) {
        return ucfirst($word);
    }, $words);

    // Join with spaces
    return implode(' ', $words);
}
