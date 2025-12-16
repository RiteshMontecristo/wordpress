<?php

function customer_page()
{
    if (isset($_GET['added']) && $_GET['added'] === '1') {
        echo '<div class="updated notice is-dismissible"><p>Customer added successfully!</p></div>';
    }
?>
    <div class="wrap">
        <h1>Customer Management</h1>

        <!-- Search Form -->
        <form method="get">
            <input type="hidden" name="page" value="customer-management">
            <input type="text" name="search" placeholder="Search by Name or Phone">
            <button type="submit" class="button">Search</button>
        </form>

        <!-- Add Customer Button -->
        <a href="?page=customer-management&action=add" class="button button-primary">Add New Customer</a>

        <?php
        $action_map = [
            'view' => 'view_customer_page',
            'add' => 'add_customer_form',
            'edit' => 'edit_customer_form',
            'delete' => 'delete_customer_form'
        ];

        // Handle actions
        if (isset($_GET['action']) && isset($action_map[$_GET['action']])) {
            call_user_func($action_map[$_GET['action']]);
        } else {
            $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
            $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;
            $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
            $output = customer_table("customer", $search_query, $per_page, $current_page);
            echo $output;
        }
        ?>
    </div>
<?php
}

// Display the custmer table
function customer_table($context = "customer", $search_query = "", $per_page = 20, $current_page = 1, $location_id = null)
{
    global $wpdb;
    ob_start();
    $table_name = $wpdb->prefix . 'mji_customers';

    // Pagination settings
    $offset = ($current_page - 1) * $per_page;
    $where = 'WHERE 1=1';

    // ONLY USE THIS IF WE HAVE OVER 50K CUSTOMERS
    // if (!empty($search_query)) {
    //     $search = $search_query;

    //     $where .= " AND (
    //                     MATCH(first_name, last_name, street_address, city, province, postal_code, country, primary_phone, secondary_phone)
    //                     AGAINST('" . esc_sql($search) . "' IN NATURAL LANGUAGE MODE)
    //                 )";
    // }
    if (!empty($search_query)) {
        $search = trim($search_query);
        $like = '%' . $wpdb->esc_like($search) . '%';

        $where .= $wpdb->prepare(
            " AND (
            first_name LIKE %s OR
            last_name LIKE %s OR
            CONCAT(first_name, ' ', last_name) LIKE %s OR
            CONCAT(last_name, ' ', first_name) LIKE %s OR
            street_address LIKE %s OR
            city LIKE %s OR
            province LIKE %s OR
            postal_code LIKE %s OR
            country LIKE %s OR
            primary_phone LIKE %s OR
            secondary_phone LIKE %s
        )",
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like,
            $like
        );
    }
    // Main query with join and pagination
    $query = $wpdb->prepare("
        SELECT *
        FROM $table_name
        $where
        ORDER BY created_at DESC
        LIMIT %d OFFSET %d
    ", $per_page, $offset);

    $customers = $wpdb->get_results($query);

    if (empty($customers)) {
        echo '<div class="notice notice-info is-dismissible"><p>No customers found!</p></div>';
        return ob_get_clean();
    }

    // Get total number of customers
    $total_customers = $wpdb->get_var("
                                        SELECT COUNT(DISTINCT c.id)
                                        FROM $table_name c
                                        $where
                                    ");

    // Pagination calculation
    $total_pages = ceil($total_customers / $per_page);
    $salespeople_array = mji_get_salespeople();

?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Primary Phone</th>
                <th>Secondary Phone</th>
                <th>Email</th>
                <th>Address</th>
                <?php if ($context === "customer") : ?>
                    <th>Salesperson</th>
                <?php else: ?>
                    <th>Layaway/Credit</th>
                <?php endif; ?>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php

            foreach ($customers as $customer) {

                $customer_id = $customer->id;
                $salesperson_id = $customer->salesperson_id;
                $primary_phone =  $customer->primary_phone;
                $secondary_phone =  $customer->secondary_phone;

                $address = $customer->street_address . "<br />" . $customer->city . " " . $customer->province . " " . "<br />" . $customer->postal_code;
                $salesperson = current(array_filter($salespeople_array, function ($sp) use ($salesperson_id) {
                    return $sp->id === $salesperson_id;
                }));
                if ($context === "customer") {
                    $saleperson_html = is_empty($salesperson) ? '<td></td>' : "<td id='salesperson'>{$salesperson->first_name} {$salesperson->last_name}</td>";
                    $actionMethod = "
            {$saleperson_html}
            <td>
                <a href='?page=customer-management&action=view&id={$customer_id}' class='button'>View</a>
                <a href='?page=customer-management&action=edit&id={$customer_id}' class='button'>Edit</a>
            </td>";
                } else {
                    $layaway_credit = get_layaway_sum($customer_id, $location_id);
                    $layaway = $layaway_credit["layaway"] ? "Layaway: " . $layaway_credit["layaway"] : '';
                    $credit = $layaway_credit["credit"] ? "<br />Credit: " . $layaway_credit["credit"] : '';
                    $actionMethod = '
                            <td>' . $layaway  . $credit . '</td>
                            <td>
                                <button class="select-customer button" data-customerid="' . $customer_id . '">Select</button>
                            </td>
                    ';
                }

                echo "<tr>
                <td id='firstName'>{$customer->first_name}</td>
                <td id='lastName'>{$customer->last_name}</td>
                <td id='primaryPhone'>{$primary_phone}</td>
                <td id='secondaryPhone'>{$secondary_phone}</td>
                <td id='email'>{$customer->email}</td>
                <td id='address'>$address</td>
                $actionMethod
              </tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="tablenav">
        <div class="tablenav-pages">
            <?php
            if ($total_pages > 1) {
                for ($i = 1; $i <= $total_pages; $i++) {
                    $active = ($i === $current_page) ? 'current' : '';
                    echo "<a class='page-numbers $active' href='?page=customer-management&paged=$i&per_page=$per_page'>$i</a> ";
                }
            }
            ?>

            <form method="GET">
                <input type="hidden" name="page" value="customer-management">
                <input type="hidden" name="search" value="<?= esc_attr($search_query) ?>">
                <select name="per_page" onchange="this.form.submit();">
                    <?php
                    $options = [10, 20, 50, 100];
                    foreach ($options as $option) {
                        $selected = ($per_page == $option) ? 'selected' : '';
                        echo "<option value='$option' $selected>$option per page</option>";
                    }
                    ?>
                </select>
            </form>
        </div>
    </div>
<?php
    return ob_get_clean();
}

function add_customer_form()
{
?>
    <h2>Add Customer</h2>
    <form name="customer" method="post" class="customer-grid-form">
        <?php wp_nonce_field('add_customer_action', 'add_customer_nonce'); ?>
        <input type="hidden" name="add_customer" value="1">

        <div class="customer-form-grid">
            <!-- First Name -->
            <div class="form-group">
                <label for="firstName" class="form-label">First Name</label>
                <input id="firstName" type="text" name="firstName" class="regular-text" required>
            </div>

            <!-- Last Name -->
            <div class="form-group">
                <label for="lastName" class="form-label">Last Name</label>
                <input id="lastName" type="text" name="lastName" class="regular-text" required>
            </div>

            <!-- Primary Phone -->
            <div class="form-group">
                <label for="primaryPhone" class="form-label">Primary Phone</label>
                <input id="primaryPhone" type="tel" name="primaryPhone" class="regular-text">
            </div>

            <!-- Secondary Phone -->
            <div class="form-group">
                <label for="secondaryPhone" class="form-label">Secondary Phone</label>
                <input id="secondaryPhone" type="tel" name="secondaryPhone" class="regular-text">
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" class="regular-text">
            </div>

            <!-- Salesperson -->
            <div class="form-group">
                <label for="salesperson" class="form-label">Salesperson</label>
                <?= mji_salesperson_dropdown(false) ?>
            </div>

            <!-- Address -->
            <div class="form-group full-width">
                <label for="address" class="form-label">Street Address</label>
                <input id="address" type="text" name="address" class="regular-text">
            </div>

            <!-- City -->
            <div class="form-group">
                <label for="city" class="form-label">City</label>
                <input id="city" type="text" name="city" class="regular-text">
            </div>

            <!-- Province -->
            <div class="form-group">
                <label for="province" class="form-label">Province</label>
                <input id="province" type="text" name="province" class="regular-text">
            </div>

            <!-- Postal Code -->
            <div class="form-group">
                <label for="postalCode" class="form-label">Postal Code</label>
                <input id="postalCode" type="text" name="postalCode" class="regular-text">
            </div>

            <!-- Country -->
            <div class="form-group">
                <label for="country" class="form-label">Country</label>
                <?php countrySelector("Canada"); ?>
            </div>

            <!-- Notes -->
            <div class="form-group full-width">
                <label for="notes" class="form-label">Notes</label>
                <textarea id="notes" name="notes" rows="3" class="large-text"></textarea>
            </div>
        </div>

        <?php submit_button('Save Customer', 'primary', 'customer_cta', false); ?>
    </form>
    <?php
    // Handle form submission
    if (isset($_POST['add_customer'])) {
        // Verify the nonce
        if (!isset($_POST['add_customer_nonce']) || !wp_verify_nonce($_POST['add_customer_nonce'], 'add_customer_action')) {
            die('Security check failed!');
        }
        global $wpdb;

        $table_name = $wpdb->prefix . 'mji_customers';
        $firstName = sanitize_text_field(stripslashes($_POST['firstName']));
        $lastName = sanitize_text_field(stripslashes($_POST['lastName']));
        $email = sanitize_email($_POST['email']);
        $salesperson = sanitize_text_field($_POST['salesperson']);
        $primary_phone = sanitize_text_field($_POST['primaryPhone']);
        $secondary_phone = sanitize_text_field($_POST['secondaryPhone']);
        $address = sanitize_text_field(stripslashes($_POST['address']));
        $city = sanitize_text_field($_POST['city']);
        $province = sanitize_text_field($_POST['province']);
        $postalCode = sanitize_text_field($_POST['postalCode']);
        $country = sanitize_text_field($_POST['country']);
        $notes = sanitize_textarea_field($_POST['notes']);
        $errors = [];

        // Validate required fields
        if (empty($firstName))
            $errors[] = 'First name is required';
        if (empty($lastName))
            $errors[] = 'Last name is required';

        try {
            $inserted = $wpdb->insert($table_name, [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'primary_phone' => $primary_phone,
                'secondary_phone' => $secondary_phone,
                'email' => !empty($email) ? $email : null,
                'salesperson_id' => $salesperson ? $salesperson : null,
                'street_address' => $address,
                'city' => $city,
                'province' => $province,
                'postal_code' => $postalCode,
                'country' => $country,
                'notes' => $notes,
            ]);
            if (!$inserted) {
                echo '<div class="notice notice-error is-dismissible"><p>' . $wpdb->last_error . '</p></div>';
            }
            $redirect_url = add_query_arg(
                ['page' => 'customer-management', 'added' => '1'],
                admin_url('admin.php')
            );
            wp_redirect($redirect_url);
            exit;
        } catch (Exception $e) {
            custom_log($wpdb->last_error);
        }
    }
}

function delete_customer_form()
{
    if (!isset($_GET['id'])) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mji_customers';
    $customer_id = intval($_GET['id']);

    $customer = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $customer_id");

    if (!$customer) {
        echo '<div class="notice notice-error is-dismissible"><p>Customer not found!</p></div>';
        return;
    }

    $deleted = $wpdb->delete($table_name, ['id' => $customer_id]);

    if ($deleted) {
        echo '<div class="updated"><p>Customer deleted successfully!</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . $wpdb->last_error . '</p></div>';
    }

    $result = customer_table();
    echo $result;
}

function edit_customer_form()
{
    if (!isset($_GET['id'])) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mji_customers';
    $phones_table = $wpdb->prefix . 'mji_customer_phones';
    $customer_id = intval($_GET['id']);

    // Handle form submission
    if (isset($_POST['edit_customer'])) {
        // Verify the nonce
        if (!isset($_POST['edit_customer_nonce']) || !wp_verify_nonce($_POST['edit_customer_nonce'], 'edit_customer_action')) {
            die('Security check failed!');
        }
        global $wpdb;

        $firstName = sanitize_text_field(stripslashes($_POST['firstName']));
        $lastName = sanitize_text_field(stripslashes($_POST['lastName']));
        $email = sanitize_email($_POST['email']);
        $salesperson = sanitize_text_field(stripslashes($_POST['salesperson']));
        $primary_phone = sanitize_text_field($_POST['primaryPhone']);
        $secondary_phone = sanitize_text_field($_POST['secondaryPhone']);
        $address = sanitize_text_field(stripslashes($_POST['address']));
        $city = sanitize_text_field($_POST['city']);
        $province = sanitize_text_field($_POST['province']);
        $postalCode = sanitize_text_field($_POST['postalCode']);
        $country = sanitize_text_field($_POST['country']);
        $notes = sanitize_textarea_field($_POST['notes']);

        $errors = [];

        // Validate required fields
        if (empty($firstName))
            $errors[] = 'First name is required';
        if (empty($lastName))
            $errors[] = 'Last name is required';

        // Stop if any errors
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error) . '</p></div>';
            }
        } else {

            $updated = $wpdb->update($table_name, [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => !empty($email) ? $email : null,
                'primary_phone' => $primary_phone,
                'secondary_phone' => $secondary_phone,
                'salesperson_id' => $salesperson ? $salesperson : null,
                'street_address' => $address,
                'city' => $city,
                'province' => $province,
                'postal_code' => $postalCode,
                'country' => $country,
                'notes' => $notes,
            ], ['id' => $customer_id]);

            if ($updated !== false) {
                echo '<div class="updated"><p>Customer updated successfully!</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . $wpdb->last_error . '</p></div>';
            }
        }
    }

    // Grab the values for edits
    $customer = $wpdb->get_row(
        $wpdb->prepare("
            SELECT *
            FROM $table_name c
            LEFT JOIN $phones_table p ON c.id = p.customer_id
            WHERE c.id = %d
            GROUP BY c.id
        ", $customer_id)
    );
    if (!$customer) {
        echo '<div class="notice notice-error is-dismissible"><p>Customer not found!</p></div>';
        return;
    }

    ?>
    <h2>Edit Customer</h2>
    <form name="customer" method="post" class="customer-grid-form">
        <?php wp_nonce_field('edit_customer_action', 'edit_customer_nonce'); ?>
        <input type="hidden" name="edit_customer" value="1">

        <div class="customer-form-grid">
            <!-- First Name -->
            <div class="form-group">
                <label for="firstName" class="form-label">First Name</label>
                <input
                    id="firstName"
                    type="text"
                    name="firstName"
                    class="regular-text"
                    value="<?php echo esc_attr($customer->first_name ?? ''); ?>"
                    required>
            </div>

            <!-- Last Name -->
            <div class="form-group">
                <label for="lastName" class="form-label">Last Name</label>
                <input
                    id="lastName"
                    type="text"
                    name="lastName"
                    class="regular-text"
                    value="<?php echo esc_attr($customer->last_name ?? ''); ?>"
                    required>
            </div>

            <!-- Primary Phone -->
            <div class="form-group">
                <label for="primaryPhone" class="form-label">Primary Phone</label>
                <input
                    id="primaryPhone"
                    type="tel"
                    name="primaryPhone"
                    class="regular-text"
                    value="<?php echo esc_attr($customer->primary_phone ?? ''); ?>">
            </div>

            <!-- Secondary Phone -->
            <div class="form-group">
                <label for="secondaryPhone" class="form-label">Secondary Phone</label>
                <input
                    id="secondaryPhone"
                    type="tel"
                    name="secondaryPhone"
                    class="regular-text"
                    value="<?php echo esc_attr($customer->secondary_phone ?? ''); ?>">
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    class="regular-text"
                    value="<?php echo esc_attr($customer->email ?? ''); ?>">
            </div>

            <!-- Salesperson -->
            <div class="form-group">
                <label for="salesperson" class="form-label">Salesperson</label>
                <?= mji_salesperson_dropdown(false, $customer->salesperson_id) ?>
            </div>

            <!-- Street Address -->
            <div class="form-group full-width">
                <label for="address" class="form-label">Street Address</label>
                <input
                    id="address"
                    type="text"
                    name="address"
                    class="regular-text"
                    value="<?php echo esc_attr($customer->street_address ?? ''); ?>">
            </div>

            <!-- City -->
            <div class="form-group">
                <label for="city" class="form-label">City</label>
                <input
                    id="city"
                    type="text"
                    name="city"
                    class="regular-text"
                    value="<?php echo esc_attr($customer->city ?? ''); ?>">
            </div>

            <!-- Province -->
            <div class="form-group">
                <label for="province" class="form-label">Province</label>
                <input
                    id="province"
                    type="text"
                    name="province"
                    class="regular-text"
                    value="<?php echo esc_attr($customer->province ?? ''); ?>">
            </div>

            <!-- Postal Code -->
            <div class="form-group">
                <label for="postalCode" class="form-label">Postal Code</label>
                <input
                    id="postalCode"
                    type="text"
                    name="postalCode"
                    class="regular-text"
                    value="<?php echo esc_attr($customer->postal_code ?? ''); ?>">
            </div>

            <!-- Country -->
            <div class="form-group full-width">
                <label for="country" class="form-label">Country</label>
                <?php countrySelector(esc_html($customer->country ?? 'Canada')); ?>
            </div>

            <!-- Notes -->
            <div class="form-group full-width">
                <label for="notes" class="form-label">Notes</label>
                <textarea
                    id="notes"
                    name="notes"
                    rows="3"
                    class="large-text"><?php echo esc_textarea($customer->notes ?? ''); ?></textarea>
            </div>
        </div>

        <?php submit_button('Update Customer', 'primary', 'customer_cta', false); ?>
    </form>
<?php
}

function view_customer_page()
{

    if (!isset($_GET['id'])) {
        return;
    }

    global $wpdb;
    $customer_id = intval($_GET['id']);

    $customers_table = $wpdb->prefix . 'mji_customers';
    $orders_table = $wpdb->prefix . 'mji_orders';
    $order_items = $wpdb->prefix . 'mji_order_items';
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';
    $product_inventory_units = $wpdb->prefix . 'mji_product_inventory_units';

    $customer = $wpdb->get_row(
        $wpdb->prepare("
            SELECT *
            FROM $customers_table c
            WHERE c.id = %d
        ", $customer_id)
    );

    if (!$customer) {
        echo '<div class="notice notice-error"><p>Customer not found.</p></div>';
        return;
    }

    $sql = "
    SELECT 
    oi.id AS order_item_id,
    oi.sale_price AS sale,
    oi.discount_amount AS discount,
    o.reference_num,
    o.created_at AS order_date,
    o.salesperson_id,
    o.customer_id,
    p.wc_product_id,
    p.wc_product_variant_id,
    p.retail_price,
    p.sku,
    c.first_name,
    c.last_name,
    s.first_name AS salesperson_first_name,
    s.last_name AS salesperson_last_name
    FROM $order_items oi
    JOIN  $orders_table o 
    ON oi.order_id = o.id
    JOIN $customers_table c
    ON o.customer_id = c.id
    JOIN $salespeople_table S
    ON s.id = o.salesperson_id
    JOIN $product_inventory_units p
    ON p.id = oi.product_inventory_unit_id
    WHERE c.id = $customer_id
    ";

    // Fetch orders
    $orders = $wpdb->get_results($sql);

    foreach ($orders as $row) {

        if ($row->wc_product_variant_id) {
            $variation = wc_get_product($row->wc_product_variant_id);
            $parent = wc_get_product($variation->get_parent_id());

            // Get raw attributes from the variation
            $attributes = $variation->get_attributes();

            // Convert array to string
            $flattened_attributes = implode(', ', $attributes);

            $name = $parent->get_name();
            if (!empty($flattened_attributes)) {
                $name .= ' - ' . $flattened_attributes;
            }

            // Image: prefer variation image, fall back to parent
            $image_id = $variation->get_image_id() ?: $parent->get_image_id();
            $image = $image_id ? wp_get_attachment_image_src($image_id, 'thumbnail')[0] : '';
        } else {
            // Simple product
            $product = wc_get_product($row->wc_product_id);
            $name = $product->get_name();
            $image_id = $product->get_image_id();
            $image = $image_id ? wp_get_attachment_image_src($image_id, 'thumbnail')[0] : '';
        }

        $row->name = $name;
        $row->image = $image;
    }
    $salespeople_array = mji_get_salespeople();
    $salesperson = current(array_filter($salespeople_array, function ($sp) use ($customer) {
        return $sp->id === $customer->salesperson_id;
    }));
    $salesperson_fullname = is_empty(!$salesperson) ? $salesperson->first_name . " " . $salesperson->last_name : '';
?>

    <div class="wrap">
        <h1>Customer Details</h1>

        <!-- Customer profile -->
        <div style="background:#fff; padding:15px; border:1px solid #ddd; margin-bottom:20px;">
            <h2><?php echo esc_html($customer->first_name . ' ' . $customer->last_name); ?></h2>
            <p><strong>Email:</strong> <?php echo esc_html($customer->email); ?></p>
            <p><strong>Priamry Phone:</strong> <?php echo esc_html($customer->primary_phone); ?></p>
            <p><strong>Secondary Phone:</strong> <?php echo esc_html($customer->secondary_phone); ?></p>
            <p><strong>Address:</strong>
                <?php echo esc_html($customer->street_address . ', ' . $customer->city . ' ' . $customer->province . ', ' . $customer->postal_code); ?>
            </p>
            <p><strong>Joined:</strong> <?php echo date('F j, Y', strtotime($customer->created_at)); ?></p>
            <p><strong>Served By:</strong> <?= $salesperson_fullname ?></p>
        </div>

        <!-- Tabs -->
        <h2 class="nav-tab-wrapper">
            <a href="#tab-history" class="nav-tab nav-tab-active">Purchase History</a>
            <a href="#tab-notes" class="nav-tab">Notes</a>
        </h2>

        <div id="tab-history" class="tab-content" style="display:block;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Date/Inv/Salesman</th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Retail Price</th>
                        <th>Sold Price</th>
                        <th>Discount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders): ?>
                        <?php foreach ($orders as $order):
                            $discount_pct = $order->retail_price > 0 ? number_format(($order->discount / $order->retail_price) * 100, 2)  : 0;
                        ?>
                            <tr>
                                <td><img src="<?= $order->image ?>" alt="<?= $order->name ?>" /></td>
                                <td>
                                    <?= date('M j, Y', strtotime($order->order_date)); ?> <br />
                                    Inv# <?= $order->reference_num ?> </br>
                                    <?= $order->salesperson_first_name ?> <?= $order->salesperson_last_name ?>
                                </td>
                                <td><?= $order->name ?></td>
                                <td><?= $order->sku ?></td>
                                <td>$<?php echo number_format($order->retail_price, 2); ?></td>
                                <td>$<?php echo number_format($order->sale, 2); ?></td>
                                <td>$
                                    <?php echo number_format($order->discount, 2); ?> <br />
                                    <?= $discount_pct; ?> %
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="tab-notes" class="tab-content" style="display:none;">
            <textarea style="width:100%; height:100px;" placeholder="Enter notes about this customer..."><?= $customer->notes ?></textarea>
            <p><button class="button button-primary">Save Notes</button></p>
        </div>
    </div>

<?php
}

function normalize_phone($raw_phone)
{
    // Remove everything except digits
    $digits = preg_replace('/\D+/', '', $raw_phone);

    return $digits;
}

function validate_phone($digits)
{
    if (empty($digits)) return '';

    if (strlen($digits) === 10) {
        return $digits;
    }

    if (strlen($digits) === 11 && $digits[0] === '1') {
        return substr($digits, 1);
    }

    return false;
}

function format_phone($phone)
{
    $area = substr($phone, 0, 3);
    $part1 = substr($phone, 3, 3);
    $part2 = substr($phone, 6, 4);
    $phone = "($area) $part1-$part2";
    return $phone;
}

function validate_postal_code($postalCode)
{
    $postalCode = trim(strtoupper($postalCode));

    $canadaRegex = '/^[ABCEGHJ-NPRSTVXY][0-9][ABCEGHJ-NPRSTV-Z] ?[0-9][ABCEGHJ-NPRSTV-Z][0-9]$/';
    $usRegex     = '/^\d{5}(-\d{4})?$/'; // Accepts 12345 or 12345-6789

    if (preg_match($canadaRegex, $postalCode)) {
        $postalCode = preg_replace('/^([A-Z]\d[A-Z]) ?(\d[A-Z]\d)$/', '$1 $2', $postalCode);
        return ['valid' => true, 'formatted' => $postalCode, 'country' => 'CA'];
    }

    if (preg_match($usRegex, $postalCode)) {
        return ['valid' => true, 'formatted' => $postalCode, 'country' => 'US'];
    }

    return ['valid' => false, 'error' => 'Invalid postal code format (must be valid US ZIP or Canadian postal code)'];
}
