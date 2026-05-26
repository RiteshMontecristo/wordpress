<?php

function customer_page()
{
    if (isset($_GET['added']) && $_GET['added'] === '1') {
        echo '<div class="updated notice is-dismissible"><p>Customer added successfully!</p></div>';
    }
    $locations = mji_get_locations();
?>
    <div class="wrap customer-management">

        <!-- Store selection modal — shown by JS until a location is saved in localStorage -->
        <div id="store-modal" class="modal" style="display:none;">
            <div class="modal-content customer-store-modal">
                <h2>Select Your Store</h2>
                <p>Choose a location to view and filter customers.</p>
                <div class="store-btn-group">
                    <?php foreach ($locations as $location) : ?>
                        <button class="store-btn button button-hero"
                            data-id="<?= esc_attr($location->id) ?>"
                            data-name="<?= esc_attr($location->name) ?>">
                            <span class="dashicons dashicons-store"></span>
                            <?= esc_html($location->name) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Store header bar — shown once a location is active -->
        <div id="top-store" class="customer-store-bar" style="display:none;">
            <div class="store-bar-info">
                <span class="dashicons dashicons-store"></span>
                <span class="store-bar-label">Store:</span>
                <strong id="store-name"></strong>
            </div>
            <button id="change-store-btn" class="button">Change Store</button>
        </div>

        <!-- Page header + Add button -->
        <div class="customer-page-header">
            <h1 class="wp-heading-inline">Customer Management</h1>
            <a href="?page=customer-management&action=add&location=<?= esc_attr($_GET['location'] ?? '') ?>"
                class="page-title-action">Add New Customer</a>
        </div>

        <?php
        $action_map = [
            'view'   => 'view_customer_page',
            'add'    => 'add_customer_form',
            'edit'   => 'edit_customer_form',
            'delete' => 'delete_customer_form',
        ];

        if (isset($_GET['action']) && isset($action_map[$_GET['action']])) {
            call_user_func($action_map[$_GET['action']]);
        } else {
            $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
            $location_id  = absint($_GET['location'] ?? 0);
            $per_page     = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;
            $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        ?>

            <!-- Search toolbar -->
            <div class="customer-toolbar">
                <form method="get" class="customer-search-form">
                    <input type="hidden" name="page" value="customer-management">
                    <input type="hidden" name="location" value="<?= esc_attr($location_id ?: '') ?>">
                    <div class="search-input-wrap">
                        <span class="dashicons dashicons-search"></span>
                        <input type="text" name="search"
                            placeholder="Search by name or phone…"
                            value="<?= esc_attr($search_query) ?>"
                            class="customer-search-input">
                    </div>
                    <button type="submit" class="button">Search</button>
                    <?php if (!empty($search_query)) : ?>
                        <a href="?page=customer-management&location=<?= esc_attr($location_id ?: '') ?>"
                            class="button">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

        <?php
            $output = customer_table("customer", $search_query, $per_page, $current_page, $location_id);
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

    $count_query = "SELECT COUNT(DISTINCT id) FROM $table_name $where";
    // Get total number of customers
    $total_customers = $wpdb->get_var($count_query);

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

                $customer_id = absint($customer->id);
                $salesperson_id = $customer->salesperson_id;
                $primary_phone =  $customer->primary_phone;
                $secondary_phone =  $customer->secondary_phone;

                $address =  esc_html($customer->street_address) . '<br />'
                    . esc_html($customer->city) . ' '
                    . esc_html($customer->province) . '<br />'
                    . esc_html($customer->postal_code);
                $salesperson = current(array_filter($salespeople_array, function ($sp) use ($salesperson_id) {
                    return $sp->id === $salesperson_id;
                }));
                if ($context === "customer") {
                    $saleperson_html = empty($salesperson)
                        ? '<td></td>'
                        : "<td class='salesperson'>" . esc_html($salesperson->first_name) . " " . esc_html($salesperson->last_name) . "</td>";

                    $actionMethod = "
                        {$saleperson_html}
                        <td>
                            <a href='?page=customer-management&action=view&id={$customer_id}' class='button'>View</a>
                            <a href='?page=customer-management&action=edit&id={$customer_id}' class='button'>Edit</a>
                            <a href='" . esc_url(wp_nonce_url("?page=customer-management&action=delete&id={$customer_id}", "delete_customer_{$customer_id}")) . "' class='button' onclick=\"return confirm('Are you sure you want to delete this customer?');\">Delete</a>
                        </td>";
                } else {
                    $layaway = get_active_layaway_list($customer_id, $location_id);
                    $credit = get_active_credit_list($customer_id, $location_id);

                    $layaway_arr = array_map(function ($n) {
                        return "Layaway #" . esc_html($n->reference_num) . ": $" . esc_html($n->remaining_amount);
                    }, $layaway);

                    $layaway_el = implode("<br />", $layaway_arr);

                    $credit_arr = array_map(function ($n) {
                        return "Credit #" . esc_html($n->reference_num) . ": $" . esc_html($n->remaining_amount);
                    }, $credit);
                    $credit_el = implode("<br />", $credit_arr);

                    $actionMethod = '
                            <td>' . $layaway_el . '<br />' . $credit_el . '</td>
                            <td>
                                <button class="select-customer button" data-customerid="' . $customer_id . '">Select</button>
                            </td>
                    ';
                }

                echo "<tr>";
                echo "<td class='firstName'>" . esc_html($customer->prefix) . " " . esc_html($customer->first_name) . "</td>";
                echo "<td class='lastName'>" . esc_html($customer->last_name) . "</td>";
                echo "<td class='primaryPhone'>" . esc_html($primary_phone) . "</td>";
                echo "<td class='secondaryPhone'>" . esc_html($secondary_phone) . "</td>";
                echo "<td class='email'>" . esc_html($customer->email) . "</td>";
                echo "<td class='address'>" . $address . "</td>";
                echo $actionMethod;
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="tablenav">
        <div class="tablenav-pages">
            <?php
            if ($total_pages > 1) {
                $base_args = [
                    'page'     => 'customer-management',
                    'search'   => $search_query,
                    'location' => $location_id ?: '',
                    'per_page' => $per_page,
                ];
                echo mji_customer_pagination($total_pages, $current_page, $base_args);
            }
            ?>

            <form method="GET">
                <input type="hidden" name="page" value="customer-management">
                <input type="hidden" name="search" value="<?= esc_attr($search_query) ?>">
                <input type="hidden" name="location" value="<?= esc_attr($location_id) ?>">
                <select name="per_page" onchange="this.form.submit();">
                    <?php
                    $options = [10, 20, 50, 100];
                    foreach ($options as $option) {
                        $selected = ($per_page == $option) ? 'selected' : '';
                        echo "<option value='" . esc_attr($option) . "' " . esc_attr($selected) . ">" . esc_attr($option) . " per page</option>";
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
            <!-- Prefix (Honorific) -->
            <div class="form-group">
                <label for="prefix" class="form-label">Prefix</label>
                <select id="prefix" name="prefix" class="regular-text">
                    <option value="">—</option>
                    <option value="Mr.">Mr.</option>
                    <option value="Mrs.">Mrs.</option>
                    <option value="Miss">Miss</option>
                    <option value="Ms.">Ms.</option>
                    <option value="Dr.">Dr.</option>
                    <option value="Mr. & Mrs.">Mr. and Mrs.</option>
                </select>
            </div>
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
        $prefix = sanitize_text_field(stripslashes($_POST['prefix']));
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
                'prefix' => $prefix,
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
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($wpdb->last_error) . '</p></div>';
            } else {
                $redirect_url = add_query_arg(
                    ['page' => 'customer-management', 'added' => '1'],
                    admin_url('admin.php')
                );
                wp_redirect($redirect_url);
            }
            exit;
        } catch (Exception $e) {
            custom_log($e->getMessage());
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
        }
    }
}

function delete_customer_form()
{
    if (!isset($_GET['id'])) {
        return;
    }

    $customer_id = intval($_GET['id']);

    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], "delete_customer_{$customer_id}")) {
        wp_die(__('Security check failed.'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mji_customers';
    $orders_table = $wpdb->prefix . 'mji_orders';

    $customer = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $customer_id));

    if (!$customer) {
        echo '<div class="notice notice-error is-dismissible"><p>Customer not found!</p></div>';
        return;
    }

    $orders_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$orders_table} WHERE customer_id = %d",
        $customer_id
    ));

    if ($orders_count > 0) {
        echo '<div class="notice notice-error"><p>Customer has purchased items/services, need to delete those invoices before deleting this customer.</p></div>';
    } else {

        $deleted = $wpdb->delete($table_name, ['id' => $customer_id]);

        if ($deleted) {
            echo '<div class="updated"><p>Customer deleted successfully!</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($wpdb->last_error) . '</p></div>';
        }
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
    $customer_id = intval($_GET['id']);

    // Handle form submission
    if (isset($_POST['edit_customer'])) {
        // Verify the nonce
        if (!isset($_POST['edit_customer_nonce']) || !wp_verify_nonce($_POST['edit_customer_nonce'], 'edit_customer_action')) {
            die('Security check failed!');
        }
        global $wpdb;

        $prefix = sanitize_text_field(stripslashes($_POST['prefix']));
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
                'prefix' => $prefix,
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
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($wpdb->last_error) . '</p></div>';
            }
        }
    }

    // Grab the values for edits
    $customer = $wpdb->get_row(
        $wpdb->prepare("
            SELECT *
            FROM $table_name c
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
            <!-- Prefix -->
            <div class="form-group">
                <label for="prefix" class="form-label">Prefix</label>
                <select id="prefix" name="prefix" class="regular-text">
                    <option value="">—</option>
                    <option value="Mr." <?= selected('Mr.',  trim($customer->prefix), false); ?>>Mr.</option>
                    <option value="Mrs." <?= selected('Mrs.', trim($customer->prefix), false); ?>>Mrs.</option>
                    <option value="Miss" <?= selected('Miss', trim($customer->prefix), false); ?>>Miss</option>
                    <option value="Ms." <?= selected('Ms.',  trim($customer->prefix), false); ?>>Ms.</option>
                    <option value="Dr." <?= selected('Dr.',  trim($customer->prefix), false); ?>>Dr.</option>
                    <option value="Mr. & Mrs." <?= selected('Mr. & Mrs.',  trim($customer->prefix), false); ?>>Mr. and Mrs.</option>
                </select>
            </div>
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
    $view_location_id = absint($_GET['location'] ?? 0);
    $edit_url = admin_url("admin.php?page=customer-management&action=edit&id={$customer_id}");

    $customers_table = $wpdb->prefix . 'mji_customers';
    $orders_table = $wpdb->prefix . 'mji_orders';
    $order_items = $wpdb->prefix . 'mji_order_items';
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';
    $product_inventory_units = $wpdb->prefix . 'mji_product_inventory_units';
    $models_table = $wpdb->prefix . 'mji_models';
    $services_table = $wpdb->prefix . 'mji_services';
    $return_items_table = $wpdb->prefix . 'mji_return_items';

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
    p.cost_price,
    p.sku,
    m.name AS model_name,
    p.serial,
    c.first_name,
    c.last_name,
    s.first_name AS salesperson_first_name,
    s.last_name AS salesperson_last_name,
    ri.return_id
    FROM $orders_table o
    JOIN  $order_items oi 
    ON oi.order_id = o.id
    JOIN $customers_table c
    ON o.customer_id = c.id
    JOIN $salespeople_table s
    ON s.id = o.salesperson_id
    JOIN $product_inventory_units p
    ON p.id = oi.product_inventory_unit_id
    LEFT JOIN $models_table m
    ON m.id = p.model_id
    LEFT JOIN $return_items_table ri
    on ri.order_item_id = oi.id
    WHERE o.customer_id = $customer_id
    " . ($view_location_id ? $wpdb->prepare("AND o.location_id = %d", $view_location_id) : "") . "
    ";

    $item_orders = $wpdb->get_results($sql);

    $sql = "
    SELECT 
    sv.sold_price AS sale,
    o.reference_num,
    o.created_at AS order_date,
    o.salesperson_id,
    o.customer_id,
    sv.cost_price,
    sv.category,
    sv.description,
    c.first_name,
    c.last_name,
    s.first_name AS salesperson_first_name,
    s.last_name AS salesperson_last_name
    FROM $orders_table o
    JOIN $services_table sv
    ON sv.order_id = o.id
    JOIN $customers_table c
    ON o.customer_id = c.id
    JOIN $salespeople_table s
    ON s.id = o.salesperson_id
    WHERE o.customer_id = $customer_id
    " . ($view_location_id ? $wpdb->prepare("AND o.location_id = %d", $view_location_id) : "") . "
    ";

    $service_orders = $wpdb->get_results($sql);

    // Layaway history
    $layaways_table    = $wpdb->prefix . 'mji_layaways';
    $payments_table    = $wpdb->prefix . 'mji_payments';
    $layaway_where     = $view_location_id
        ? $wpdb->prepare("AND l.location_id = %d", $view_location_id)
        : "";
    $layaway_history   = $wpdb->get_results($wpdb->prepare("
        SELECT
            l.id,
            l.reference_num,
            l.created_at,
            l.status,
            l.total_amount,
            l.remaining_amount,
            s.first_name AS salesperson_first_name,
            s.last_name  AS salesperson_last_name
        FROM {$layaways_table} l
        LEFT JOIN {$payments_table} p
            ON p.layaway_id = l.id AND p.transaction_type = 'layaway_deposit'
        LEFT JOIN {$salespeople_table} s ON s.id = p.salesperson_id
        WHERE l.customer_id = %d
        {$layaway_where}
        GROUP BY l.id
        ORDER BY l.created_at DESC
    ", $customer_id));

    // Credit history
    $credits_table   = $wpdb->prefix . 'mji_credits';
    $credit_where    = $view_location_id
        ? $wpdb->prepare("AND c2.location_id = %d", $view_location_id)
        : "";
    $credit_history  = $wpdb->get_results($wpdb->prepare("
        SELECT
            c2.id,
            c2.reference_num,
            c2.created_at,
            c2.status,
            c2.total_amount,
            c2.remaining_amount,
            s.first_name AS salesperson_first_name,
            s.last_name  AS salesperson_last_name
        FROM {$credits_table} c2
        LEFT JOIN {$payments_table} p
            ON p.credit_id = c2.id AND p.transaction_type = 'credit_deposit'
        LEFT JOIN {$salespeople_table} s ON s.id = p.salesperson_id
        WHERE c2.customer_id = %d
        {$credit_where}
        GROUP BY c2.id
        ORDER BY c2.created_at DESC
    ", $customer_id));

    foreach ($item_orders as $row) {

        if (empty($row->order_item_id)) {
            continue;
        }
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
    $salesperson_fullname = empty(!$salesperson) ? $salesperson->first_name . " " . $salesperson->last_name : '';
?>

    <div class="wrap customer-management">

        <!-- Back link + edit action -->
        <div class="customer-view-topbar">
            <a href="<?= esc_url(admin_url('admin.php?page=customer-management&location=' . $view_location_id)) ?>"
                class="button">
                &larr; All Customers
            </a>
            <a href="<?= esc_url($edit_url) ?>" class="button">
                <span class="dashicons dashicons-edit"></span> Edit Customer
            </a>
        </div>

        <!-- Customer profile card -->
        <div class="customer-profile-card">
            <div class="customer-profile-avatar">
                <?= esc_html(mb_strtoupper(mb_substr($customer->first_name, 0, 1) . mb_substr($customer->last_name, 0, 1))) ?>
            </div>
            <div class="customer-profile-body">
                <h2 class="customer-profile-name">
                    <?php if ($customer->prefix) echo esc_html($customer->prefix) . ' '; ?>
                    <?= esc_html($customer->first_name . ' ' . $customer->last_name) ?>
                </h2>
                <div class="customer-profile-grid">
                    <?php if ($customer->email) : ?>
                        <div class="profile-field">
                            <span class="profile-field-label"><span class="dashicons dashicons-email-alt"></span> Email</span>
                            <span><?= esc_html($customer->email) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($customer->primary_phone) : ?>
                        <div class="profile-field">
                            <span class="profile-field-label"><span class="dashicons dashicons-phone"></span> Primary Phone</span>
                            <span><?= esc_html($customer->primary_phone) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($customer->secondary_phone) : ?>
                        <div class="profile-field">
                            <span class="profile-field-label"><span class="dashicons dashicons-phone"></span> Secondary Phone</span>
                            <span><?= esc_html($customer->secondary_phone) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($customer->street_address) : ?>
                        <div class="profile-field">
                            <span class="profile-field-label"><span class="dashicons dashicons-location"></span> Address</span>
                            <span><?= esc_html($customer->street_address . ', ' . $customer->city . ' ' . $customer->province . ', ' . $customer->postal_code) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="profile-field">
                        <span class="profile-field-label"><span class="dashicons dashicons-calendar-alt"></span> Customer Since</span>
                        <span><?= date('F j, Y', strtotime($customer->created_at)) ?></span>
                    </div>
                    <?php if ($salesperson_fullname) : ?>
                        <div class="profile-field">
                            <span class="profile-field-label"><span class="dashicons dashicons-businessman"></span> Served By</span>
                            <span><?= esc_html($salesperson_fullname) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <h2 class="nav-tab-wrapper" style="margin-top:24px;">
            <a href="#tab-history" class="nav-tab nav-tab-active">Purchase History</a>
            <a href="#tab-layaway" class="nav-tab">Layaways</a>
            <a href="#tab-credit" class="nav-tab">Credits</a>
            <a href="#tab-notes" class="nav-tab">Notes</a>
        </h2>

        <div id="tab-history" class="tab-content" style="display:block;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Date / Invoice / Salesperson</th>
                        <th>Item</th>
                        <th>SKU / Model / Serial</th>
                        <th>Cost</th>
                        <th>Retail</th>
                        <th>Sold</th>
                        <th>Discount</th>
                        <th>Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($item_orders): ?>
                        <?php foreach ($item_orders as $order):
                            $is_returned  = !empty($order->return_id);
                            $discount_pct = $order->retail_price > 0
                                ? number_format(($order->discount / $order->retail_price) * 100, 2)
                                : 0;
                            $img_url      = !empty($order->image) ? $order->image : wc_placeholder_img_src('thumbnail');
                            $invoice_url  = admin_url("admin.php?page=invoice-management&reference_num=" . urlencode($order->reference_num));
                        ?>
                            <tr class="<?= $is_returned ? 'purchase-row-returned' : '' ?>">
                                <td><img src="<?= esc_url($img_url) ?>" alt="<?= esc_attr($order->name ?? '') ?>" style="width:50px;height:50px;object-fit:cover;" /></td>
                                <td>
                                    <?= date('M j, Y', strtotime($order->order_date)) ?><br>
                                    Inv# <?= esc_html($order->reference_num) ?><br>
                                    <?= esc_html($order->salesperson_first_name . ' ' . $order->salesperson_last_name) ?>
                                </td>
                                <td>
                                    <?= esc_html($order->name ?? '') ?>
                                    <?php if ($is_returned) : ?>
                                        <span class="customer-status-badge customer-status-returned">Returned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= esc_html($order->sku) ?><br>
                                    <?= esc_html($order->model_name) ?><br>
                                    <?= esc_html($order->serial) ?>
                                </td>
                                <td><?= $order->cost_price !== null ? '$' . number_format($order->cost_price, 2) : '—' ?></td>
                                <td>$<?= number_format($order->retail_price, 2) ?></td>
                                <td>$<?= number_format($order->sale, 2) ?></td>
                                <td>
                                    $<?= number_format($order->discount, 2) ?><br>
                                    <?= $discount_pct ?>%
                                </td>
                                <td><a href="<?= esc_url($invoice_url) ?>" class="button button-small">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if ($service_orders): ?>
                        <?php foreach ($service_orders as $order):
                            $invoice_url = admin_url("admin.php?page=invoice-management&reference_num=" . urlencode($order->reference_num));
                        ?>
                            <tr>
                                <td>—</td>
                                <td>
                                    <?= date('M j, Y', strtotime($order->order_date)) ?><br>
                                    Inv# <?= esc_html($order->reference_num) ?><br>
                                    <?= esc_html($order->salesperson_first_name . ' ' . $order->salesperson_last_name) ?>
                                </td>
                                <td><?= esc_html($order->category) ?></td>
                                <td>Service</td>
                                <td>$<?= number_format($order->cost_price, 2) ?></td>
                                <td>—</td>
                                <td>$<?= number_format($order->sale, 2) ?></td>
                                <td>—</td>
                                <td><a href="<?= esc_url($invoice_url) ?>" class="button button-small">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!$item_orders && !$service_orders): ?>
                        <tr>
                            <td colspan="9">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Layaway History Tab -->
        <div id="tab-layaway" class="tab-content" style="display:none;">
            <?php if (empty($layaway_history)) : ?>
                <p class="customer-empty-state">No layaway accounts found<?= $view_location_id ? ' for this store' : '' ?>.</p>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Reference #</th>
                            <th>Date</th>
                            <th>Salesperson</th>
                            <th>Status</th>
                            <th>Total Deposited</th>
                            <th>Applied</th>
                            <th>Balance</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($layaway_history as $la) :
                            $applied  = $la->total_amount - $la->remaining_amount;
                            $find_url = admin_url("admin.php?page=invoice-management&tab=layaway&reference_num=" . urlencode($la->reference_num));
                        ?>
                            <tr>
                                <td><strong><?= esc_html($la->reference_num) ?></strong></td>
                                <td><?= date('M j, Y', strtotime($la->created_at)) ?></td>
                                <td><?= esc_html(trim($la->salesperson_first_name . ' ' . $la->salesperson_last_name)) ?></td>
                                <td>
                                    <span class="customer-status-badge customer-status-<?= esc_attr($la->status) ?>">
                                        <?= esc_html(ucfirst($la->status)) ?>
                                    </span>
                                </td>
                                <td>$<?= number_format($la->total_amount, 2) ?></td>
                                <td>$<?= number_format($applied, 2) ?></td>
                                <td><strong>$<?= number_format($la->remaining_amount, 2) ?></strong></td>
                                <td><a href="<?= esc_url($find_url) ?>" class="button button-small">View Invoice</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Credit History Tab -->
        <div id="tab-credit" class="tab-content" style="display:none;">
            <?php if (empty($credit_history)) : ?>
                <p class="customer-empty-state">No store credit accounts found<?= $view_location_id ? ' for this store' : '' ?>.</p>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Reference #</th>
                            <th>Date</th>
                            <th>Salesperson</th>
                            <th>Status</th>
                            <th>Total Issued</th>
                            <th>Used</th>
                            <th>Balance</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($credit_history as $cr) :
                            $used     = $cr->total_amount - $cr->remaining_amount;
                            $find_url = admin_url("admin.php?page=invoice-management&tab=layaway&reference_num=" . urlencode($cr->reference_num));
                        ?>
                            <tr>
                                <td><strong><?= esc_html($cr->reference_num) ?></strong></td>
                                <td><?= date('M j, Y', strtotime($cr->created_at)) ?></td>
                                <td><?= esc_html(trim($cr->salesperson_first_name . ' ' . $cr->salesperson_last_name)) ?></td>
                                <td>
                                    <span class="customer-status-badge customer-status-<?= esc_attr($cr->status) ?>">
                                        <?= esc_html(ucfirst($cr->status)) ?>
                                    </span>
                                </td>
                                <td>$<?= number_format($cr->total_amount, 2) ?></td>
                                <td>$<?= number_format($used, 2) ?></td>
                                <td><strong>$<?= number_format($cr->remaining_amount, 2) ?></strong></td>
                                <td><a href="<?= esc_url($find_url) ?>" class="button button-small">View Invoice</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div id="tab-notes" class="tab-content" style="display:none;">
            <textarea id="customer-notes" placeholder="Enter notes about this customer…"><?= esc_textarea($customer->notes) ?></textarea>
            <p>
                <button
                    id="save-customer-notes"
                    class="button button-primary"
                    data-customer-id="<?= esc_attr($customer_id) ?>"
                    data-nonce="<?= esc_attr(wp_create_nonce('save_customer_notes_nonce')) ?>">Save Notes</button>
                <span id="notes-save-status" style="margin-left:8px;"></span>
            </p>
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

function save_customer_notes_handler()
{
    check_ajax_referer('save_customer_notes_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }
    global $wpdb;
    $customer_id = absint($_POST['customer_id']);
    $notes = sanitize_textarea_field($_POST['notes'] ?? '');

    if (!$customer_id) {
        wp_send_json_error(['message' => 'Invalid customer ID']);
    }

    $updated = $wpdb->update(
        $wpdb->prefix . 'mji_customers',
        ['notes' => $notes],
        ['id' => $customer_id],
        ['%s'],
        ['%d']
    );

    if ($updated !== false) {
        wp_send_json_success(['message' => 'Notes saved']);
    } else {
        wp_send_json_error(['message' => esc_html($wpdb->last_error) ?: 'Failed to save notes']);
    }
}
add_action('wp_ajax_save_customer_notes', 'save_customer_notes_handler');

function mji_customer_pagination($total_pages, $current_page, $base_args)
{
    $window = 2;
    $pages = [1, $total_pages];
    for ($i = max(1, $current_page - $window); $i <= min($total_pages, $current_page + $window); $i++) {
        $pages[] = $i;
    }
    $pages = array_unique($pages);
    sort($pages);

    $html = '';
    $prev = null;
    foreach ($pages as $page) {
        if ($prev !== null && $page - $prev > 1) {
            $html .= '<span class="page-numbers dots">&hellip;</span> ';
        }
        $url  = esc_url(add_query_arg(array_merge($base_args, ['paged' => $page]), admin_url('admin.php')));
        $cls  = 'page-numbers' . ((int) $page === (int) $current_page ? ' current' : '');
        $html .= "<a class='{$cls}' href='{$url}'>{$page}</a> ";
        $prev = $page;
    }
    return $html;
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
