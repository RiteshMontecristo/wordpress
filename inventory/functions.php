<?php
require_once get_stylesheet_directory() . '/inventory/print.php';
require_once get_stylesheet_directory() . '/inventory/sales.php';
require_once get_stylesheet_directory() . '/inventory/customer.php';
require_once get_stylesheet_directory() . '/inventory/salespeople.php';
require_once get_stylesheet_directory() . '/inventory/product_units.php';
require_once get_stylesheet_directory() . '/inventory/reports.php';

// Create the table when theme activated
function mji_create_all_tables()
{
    // Define table creation order — PARENTS FIRST, CHILDREN LAST
    $tables = [
        'customers' => 'create_customers_table',
        'salespeople' => 'create_salespeople_table',
        'locations' => 'create_locations_table',
        'brands' => 'create_brands_table',
        'models' => 'create_models_table',
        'product_inventory_units' => 'create_product_inventory_units_table',
        'orders' => 'create_orders_table',
        'order_items' => 'create_order_items_table',
        'payments' => 'create_payments_table',
        'services' => 'create_services_table',
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

function create_customers_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'mji_customers';

    // Define character set and collation
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE,
        phone VARCHAR(15) UNIQUE CHECK (CHAR_LENGTH(phone) >= 10),    
        street_address VARCHAR(255),
        city VARCHAR(100),
        province VARCHAR(100),
        postal_code CHAR(7),
        country VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FULLTEXT (
            first_name, last_name, phone,
            street_address, city, province, postal_code, country
        )
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

    // Define table name with WordPress prefix
    $table_name = $wpdb->prefix . 'mji_orders';
    $customers_table = $wpdb->prefix . 'mji_customers';
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';

    // Define character set and collation
    $charset_collate = $wpdb->get_charset_collate();

    // Define the SQL query to create the table
    $sql = "CREATE TABLE $table_name (
        id BIGINT PRIMARY KEY AUTO_INCREMENT,
        customer_id BIGINT NOT NULL,
        salesperson_id BIGINT NOT NULL,
        status ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'completed',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        reference_num VARCHAR(50) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        gst_total DECIMAL(10,2) NOT NULL,
        pst_total DECIMAL(10,2) NOT NULL,
        total DECIMAL(10,2) NOT NULL,
       
        UNIQUE KEY unique_reference_num (reference_num),
        FOREIGN KEY (customer_id) REFERENCES $customers_table(id),
        FOREIGN KEY (salesperson_id) REFERENCES $salespeople_table(id)
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

    // Define table name with WordPress prefix
    $table_name = $wpdb->prefix . 'mji_payments';
    $customers_table = $wpdb->prefix . 'mji_customers';
    $orders_table = $wpdb->prefix . 'mji_orders';
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';

    // Define character set and collation
    $charset_collate = $wpdb->get_charset_collate();

    // Define the SQL query to create the table
    $sql = "CREATE TABLE $table_name (
        id BIGINT PRIMARY KEY AUTO_INCREMENT,
        customer_id BIGINT NOT NULL,
        salesperson_id BIGINT NOT NULL,
        order_id BIGINT NULL,         
        layaway_id BIGINT NULL,
        reference_num VARCHAR(50),
        method ENUM('cash', 'cheque','debit', 'visa', 'master_card', 'amex', 'discover', 'travel_cheque', 'cup', 'alipay', 'layaway', 'gift_card') NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        transaction_type ENUM('purchase', 'layaway_deposit', 'layaway_redemption', 'refund') NOT NULL DEFAULT 'purchase',
        payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        notes TEXT,

        FOREIGN KEY (customer_id) REFERENCES $customers_table(id),
        FOREIGN KEY (order_id) REFERENCES $orders_table(id),
        FOREIGN KEY (salesperson_id) REFERENCES $salespeople_table(id)
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

        $wpdb->insert($table_name, ['name' => 'Downtown']);
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

function create_brands_table()
{
    global $wpdb;

    // Define table name with WordPress prefix
    $table_name = $wpdb->prefix . 'mji_brands';

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
        `id` BIGINT NOT NULL AUTO_INCREMENT,
        `wc_product_id` BIGINT NOT NULL,
        `wc_product_variant_id` BIGINT NULL,
        `location_id` BIGINT NOT NULL,
        `model_id` BIGINT NOT NULL,
        `brand_id` BIGINT NOT NULL,
        `sku` VARCHAR(50) NOT NULL,
        `serial` VARCHAR(50),
        `status` ENUM('in_stock', 'reserved', 'sold', 'damaged') NOT NULL,
        `created_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `sold_date` DATETIME DEFAULT NULL,
        `cost_price` DECIMAL(10,2) NOT NULL,
        `retail_price` DECIMAL(10,2) NOT NULL,
        `notes` TEXT,

        PRIMARY KEY (`id`),
        UNIQUE KEY `sku` (`sku`),
        UNIQUE KEY `serial` (`serial`),
        CONSTRAINT `fk_location` FOREIGN KEY (`location_id`) REFERENCES `{$locations_table}`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_brand` FOREIGN KEY (`brand_id`) REFERENCES `{$brands_table}`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_model` FOREIGN KEY (`model_id`) REFERENCES `{$models_table}`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
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

    // Define character set and collation
    $charset_collate = $wpdb->get_charset_collate();

    // Define the SQL query to create the table
    $sql = "CREATE TABLE $table_name (
        id BIGINT PRIMARY KEY AUTO_INCREMENT,
        order_id BIGINT NOT NULL,
        category ENUM('watch_service', 'jewellery_service', 'shipping')  NOT NULL,
        description TEXT,
        cost_price DECIMAL(10,2) NOT NULL,
        sold_price DECIMAL(10,2) NOT NULL,

        FOREIGN KEY (order_id) REFERENCES $orders_table(id)
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
        'Manage Sales', // Menu Title
        'manage_options', // Capability
        'inventory-management', // Menu Slug
        'inventory_page', // Callback Function
        'dashicons-id', // Icon
        25 // Position
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
        $search_term = esc_sql($_GET['s']);
        $custom_table = $wpdb->prefix . 'mji_product_inventory_units';
        // // Modifying the SQL WHERE clause to include the repeatable SKU field
        // $where .= " OR EXISTS (
        //                SELECT 1 FROM wp_product_skus ps
        //                 WHERE ps.product_id = {$wpdb->posts}.ID
        //                 AND ps.sku_text = '{$search_term}'
        // )";
        $where .= " OR EXISTS (
            SELECT 1
            FROM $custom_table
            WHERE $custom_table.wc_product_id = {$wpdb->posts}.ID
              AND $custom_table.sku LIKE '$search_term'
        )";
    }

    return $where;
}

add_filter('posts_where', 'custom_woocommerce_admin_search', 10, 2);

// ORDER ITEM REDUCTION
add_action('woocommerce_checkout_order_processed', 'adjust_stock_after_order', 10, 3);

function adjust_stock_after_order($order_id, $posted_data, $order)
{
    global $wpdb;
    $sku_error = array();

    foreach ($order->get_items() as $item_id => $item) {

        // Get product details
        $product_id = $item->get_product_id();       // Main product ID (parent ID for variations)
        $variation_id = $item->get_variation_id();   // Variation ID (0 if it's not a variation)

        // SKU INFORMATION ABOUT THE PRODUCTS
        $sku_data_array = get_post_meta($product_id, 'new_repeatable_sku_field', true);
        $remove_sku = "";

        if ($variation_id) {

            // loop thorugh the sku to remove the first sku matched of the variation.
            foreach ($sku_data_array as $sky_data) {
                if ($sky_data["sku_variation"] == $variation_id) {
                    $remove_sku = $sky_data["sku_text"];
                    break;
                }
            }

            // removing the first matched variaton from the array
            $filtered_sku_data = array_filter($sku_data_array, function ($sku) use ($remove_sku) {
                return $sku["sku_text"] != $remove_sku;
            });

            // saving the length to ensure if sku was removed or not
            $sku_data_len = count($sku_data_array);
            $filtered_sku_data_len = count($filtered_sku_data);

            // if not removed need to store it to the sku_error to manually delete it 
            if ($sku_data_len == $filtered_sku_data_len) {
                array_push($sku_error, $product_id);
            } else {
                update_post_meta($product_id, 'new_repeatable_sku_field', array_values($filtered_sku_data));
                $wpdb->delete(
                    'wp_product_skus',
                    array(
                        'sku_text' => $remove_sku,
                    ),
                    array(
                        '%s',
                    )
                );
            }
        } else {

            // storing the current len before fitlering
            $sku_data_len = count($sku_data_array);

            // removing the last sku
            $remove_sku = array_pop($sku_data_array);

            // storing the current len after fitlering
            $filtered_sku_data_len = count($sku_data_array);

            // if not removed then save the error to send a manual email
            if ($sku_data_len == $filtered_sku_data_len) {
                array_push($sku_error, $product_id);
            } else {
                update_post_meta($product_id, 'new_repeatable_sku_field', $sku_data_array);
            }
        }
    }

    if (count($sku_error) > 0) {
        // Send email
        $to = 'rm@montecristo1978.com';
        $subject = 'Online order error';
        $message = "There was an issue in regards to removing SKU for this online order " . $order_id . ". These are the product effected.";

        // Loop through the $sku_error array and append each item to the message
        foreach ($sku_error as $item_id) {
            $message .= "Product ID: " . $item_id . ", ";
        }

        $message = rtrim($message, ", ");
        $message .= ".";

        $headers = array('Content-Type: text/plain; charset=UTF-8');

        $max_retries = 3;
        $retry_delay = 5; // seconds

        for ($i = 0; $i < $max_retries; $i++) {
            $mail_sent = wp_mail($to, $subject, $message, $headers);
            if ($mail_sent) {
                break;
            }
            sleep($retry_delay);
        }

        if (!$mail_sent) {
            $order->add_order_note("Failed to send email to $to after $max_retries attempts. Subject: $subject");
        }
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
function save_cost_price_field($post_id)
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
});

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
