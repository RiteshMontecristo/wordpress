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
function customer_table($context = "customer", $search_query = "", $per_page = 20, $current_page = 1)
{
    global $wpdb;
    ob_start();
    $table_name = $wpdb->prefix . 'mji_customers';
    $phones_table = $wpdb->prefix . 'mji_customer_phones';

    // Pagination settings
    $offset = ($current_page - 1) * $per_page;
    $where = 'WHERE 1=1';
    $join = "LEFT JOIN $phones_table p ON c.id = p.customer_id";

    // Handle search
    if (!empty($search_query)) {
        $search = $search_query;
        $search_digits = normalize_phone($search);
        $search_escaped = $wpdb->esc_like($search_digits);

        $where .= " AND (
                        MATCH(c.first_name, c.last_name, c.street_address, c.city, c.province, c.postal_code, c.country)
                        AGAINST('" . esc_sql($search) . "' IN NATURAL LANGUAGE MODE)
                        OR p.phone LIKE '" . esc_sql($search_escaped) . "'
                    )";
    }

    // Main query with join and pagination
    $query = $wpdb->prepare("
        SELECT c.*, GROUP_CONCAT(p.phone ORDER BY p.phone ASC SEPARATOR ',') AS phones
        FROM $table_name c
        $join
        $where
        GROUP BY c.id
        ORDER BY c.created_at DESC
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
                                        $join
                                        $where
                                    ");

    // Pagination calculation
    $total_pages = ceil($total_customers / $per_page);

    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Primary Phone</th>
                <th>Secondary Phone</th>
                <th>Email</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
          </thead>';
    echo '<tbody>';

    foreach ($customers as $customer) {

        $primary_phone =  "";
        $secondary_phone =  "";
        $digits = $customer->phones;
        if (!empty($digits)) {
            $phone_numbers = explode(',', $customer->phones);
            $primary_digits   = $phone_numbers[0] ?? '';
            $secondary_digits = $phone_numbers[1] ?? '';
            $primary_phone   = !is_empty($primary_digits) ? format_phone($primary_digits) : '';
            $secondary_phone   = !is_empty($secondary_digits) ? format_phone($secondary_digits) : '';
        }
        $address = $customer->street_address . "<br />" . $customer->city . " " . $customer->province . " " . "<br />" . $customer->postal_code;

        if ($context === "customer") {
            $actionMethod = "
        <td>
            <a href='?page=customer-management&action=view&id={$customer->id}' class='button'>View</a>
            <a href='?page=customer-management&action=edit&id={$customer->id}' class='button'>Edit</a>
        </td>";
        } else {
            $actionMethod = '
                <td>
                    <button class="select-customer button" data-customerid="' . $customer->id . '">Select</button>
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

    echo '</tbody></table>';

    // Pagination controls
    // echo '<div class="tablenav">';
    // echo '<div class="tablenav-pages">';
    // echo paginate_links([
    //     'base' => add_query_arg('paged', '%#%'),
    //     'format' => '',
    //     'prev_text' => __('&laquo; Previous'),
    //     'next_text' => __('Next &raquo;'),
    //     'total' => $total_pages,
    //     'current' => $current_page,
    //     'type' => 'list',
    // ]);
    // echo '</div></div>';

    echo '<div class="tablenav">';
    echo '<div class="tablenav-pages">';
    if ($total_pages > 1) {
        for ($i = 1; $i <= $total_pages; $i++) {
            $active = ($i === $current_page) ? 'current' : '';
            echo "<a class='page-numbers $active' href='?page=customer-management&paged=$i&per_page=$per_page'>$i</a> ";
        }
    }
    echo '</div></div>';

    // Dropdown to change per page limit
    echo '<form method="GET">';
    echo '<input type="hidden" name="page" value="customer-management">';
    echo '<input type="hidden" name="search" value="' . esc_attr($search_query) . '">';
    echo '<select name="per_page" onchange="this.form.submit();">';
    $options = [10, 20, 50, 100];
    foreach ($options as $option) {
        $selected = ($per_page == $option) ? 'selected' : '';
        echo "<option value='$option' $selected>$option per page</option>";
    }
    echo '</select>';
    echo '</form>';

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
        $phones_table = $wpdb->prefix . 'mji_customer_phones';
        $firstName = sanitize_text_field(stripslashes($_POST['firstName']));
        $lastName = sanitize_text_field(stripslashes($_POST['lastName']));
        $email = sanitize_email($_POST['email']);
        $primary_phone = sanitize_text_field($_POST['primaryPhone']);
        $secondary_phone = sanitize_text_field($_POST['secondaryPhone']);
        $normalized_primary_phone = normalize_phone($primary_phone);
        $normalized_secondary_phone = normalize_phone($secondary_phone);
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

        // Validate email if provided
        if (!empty($email) && !is_email($email)) {
            $errors[] = 'Please enter a valid email address or leave the field blank';
        }

        if (!empty($postalCode)) {
            $validated_postal_code = validate_postal_code($postalCode);
            if (!$validated_postal_code['valid']) {
                $errors[] = 'Invalid Canadian postal code format';
            } else {
                // Format postal code with space only if valid
                if (strlen($postalCode) === 6) {
                    $postalCode = substr($postalCode, 0, 3) . ' ' . substr($postalCode, 3);
                }
            }
        }

        $validated_primary   = validate_phone($normalized_primary_phone);
        $validated_secondary = validate_phone($normalized_secondary_phone);

        if ($validated_primary === false) {
            $errors[] = "Primary phone number is invalid. Must be 10 digits or 11 digits starting with 1.";
        }
        if ($validated_secondary === false) {
            $errors[] = "Secondary phone number is invalid. Must be 10 digits or 11 digits starting with 1.";
        }

        // Stop if any errors
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error) . '</p></div>';
            }
            return;
        }

        $new_phones = array_filter([$validated_primary, $validated_secondary]);

        foreach ($new_phones as $phone) {
            $duplicate = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT customer_id FROM $phones_table 
                    WHERE phone = %s",
                    $phone
                )
            );

            if ($duplicate) {
                echo '<div class="notice notice-error"><p>The phone number ' . esc_html(format_phone($phone)) . ' is already used by another customer.</p></div>';
                return;
            }
        }

        $inserted = $wpdb->insert($table_name, [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => !empty($email) ? $email : null,
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

        $customer_id = $wpdb->insert_id;
        foreach ($new_phones as $phone) {
            $phone_inserted = $wpdb->insert($phones_table, [
                'phone' => $phone,
                'customer_id' => $customer_id
            ]);
            if (!$phone_inserted) {
                // If phone doesn't insert due to any issue, rollback customer and show error
                $wpdb->delete($table_name, ['id' => $customer_id], ['%d']);
                echo '<div class="notice notice-error is-dismissible"><p>Phone number ' . esc_html($phone) . ' already exists!</p></div>';
                return;
            }
        }

        $redirect_url = add_query_arg(
            ['page' => 'customer-management', 'added' => '1'],
            admin_url('admin.php')
        );
        wp_redirect($redirect_url);
        exit;
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
        $primary_phone = sanitize_text_field($_POST['primaryPhone']);
        $secondary_phone = sanitize_text_field($_POST['secondaryPhone']);
        $normalized_primary_phone = normalize_phone($primary_phone);
        $normalized_secondary_phone = normalize_phone($secondary_phone);
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

        // Validate email if provided
        if (!empty($email) && !is_email($email)) {
            $errors[] = 'Please enter a valid email address or leave the field blank';
        }

        if (!empty($postalCode)) {
            $validated_postal_code = validate_postal_code($postalCode);
            if (!$validated_postal_code['valid']) {
                $errors[] = 'Invalid Canadian postal code format';
            } else {
                // Format postal code with space only if valid
                if (strlen($postalCode) === 6) {
                    $postalCode = substr($postalCode, 0, 3) . ' ' . substr($postalCode, 3);
                }
            }
        }

        $validated_primary   = validate_phone($normalized_primary_phone);
        $validated_secondary = validate_phone($normalized_secondary_phone);

        if ($validated_primary === false) {
            $errors[] = "Primary phone number is invalid. Must be 10 digits or 11 digits starting with 1.";
        }
        if ($validated_secondary === false) {
            $errors[] = "Secondary phone number is invalid. Must be 10 digits or 11 digits starting with 1.";
        }

        // Stop if any errors
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error) . '</p></div>';
            }
        } else {

            // To ensure if the primary or secondary number is left blank, remove it
            $new_phones = array_filter([$validated_primary, $validated_secondary]);

            $existing_phones = $wpdb->get_col(
                $wpdb->prepare("SELECT phone FROM $phones_table WHERE customer_id = %d", $customer_id)
            );
            // Phones to insert
            $to_insert = array_diff($new_phones, $existing_phones);
            $to_delete = array_diff($existing_phones, $new_phones);
            $duplicate_number_exists = false;
            $phone_errors = [];
            if (!is_empty($to_insert)) {
                foreach ($new_phones as $phone) {
                    $duplicate = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT customer_id FROM $phones_table 
                        WHERE phone = %s AND customer_id != %d",
                            $phone,
                            $customer_id
                        )
                    );

                    if ($duplicate) {
                        echo '<div class="notice notice-error"><p>The phone number ' . esc_html(format_phone($phone)) . ' is already used by another customer.</p></div>';
                        $duplicate_number_exists = true;
                    }
                }

                if (!$duplicate_number_exists) {
                    foreach ($to_insert as $phone) {
                        $inserted = $wpdb->insert($phones_table, [
                            'customer_id' => $customer_id,
                            'phone' => $phone
                        ]);
                        if ($inserted === false) {
                            $phone_errors[] = 'Failed to add phone number ' . esc_html(format_phone($phone)) . ': ' . $wpdb->last_error;
                        }
                    }
                }
            }
            if (!$duplicate_number_exists) {
                if (!is_empty($to_delete)) {
                    foreach ($to_delete as $phone) {
                        $deleted = $wpdb->delete($phones_table, [
                            'customer_id' => $customer_id,
                            'phone' => $phone
                        ]);
                        if ($deleted === false) {
                            $phone_errors[] = 'Failed to delete phone number ' . esc_html(format_phone($phone)) . ': ' . $wpdb->last_error;
                        }
                    }
                }

                // If phone operations failed → do NOT continue
                if (!empty($phone_errors)) {
                    foreach ($phone_errors as $err) {
                        echo '<div class="notice notice-error is-dismissible"><p>' . $err . '</p></div>';
                    }
                } else {

                    $updated = $wpdb->update($table_name, [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => !empty($email) ? $email : null,
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
        }
    }

    // Grab the values for edits
    $customer = $wpdb->get_row(
        $wpdb->prepare("
            SELECT c.*, GROUP_CONCAT(p.phone ORDER BY p.phone ASC SEPARATOR ',') AS phones
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

    $primary_phone = '';
    $secondary_phone = '';
    if (!empty($customer->phones)) {
        $phone_numbers = explode(',', $customer->phones);
        $primary_phone   = $phone_numbers[0] ?? '';
        $secondary_phone = $phone_numbers[1] ?? '';
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
                    value="<?php echo esc_attr($primary_phone ?? ''); ?>">
            </div>

            <!-- Secondary Phone -->
            <div class="form-group">
                <label for="secondaryPhone" class="form-label">Secondary Phone</label>
                <input
                    id="secondaryPhone"
                    type="tel"
                    name="secondaryPhone"
                    class="regular-text"
                    value="<?php echo esc_attr($secondary_phone ?? ''); ?>">
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
    $phones_table = $wpdb->prefix . 'mji_customer_phones';
    $orders_table = $wpdb->prefix . 'mji_orders';
    $order_items = $wpdb->prefix . 'mji_order_items';
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';
    $product_inventory_units = $wpdb->prefix . 'mji_product_inventory_units';

    $customer = $wpdb->get_row(
        $wpdb->prepare("
            SELECT c.*, GROUP_CONCAT(p.phone ORDER BY p.phone ASC SEPARATOR ',') AS phones
            FROM $customers_table c
            LEFT JOIN $phones_table p ON c.id = p.customer_id
            WHERE c.id = %d
            GROUP BY c.id
        ", $customer_id)
    );

    if (!$customer) {
        echo '<div class="notice notice-error"><p>Customer not found.</p></div>';
        return;
    }

    $primary_phone = '';
    $secondary_phone = '';
    if (!empty($customer->phones)) {
        $phone_numbers = explode(',', $customer->phones);
        $primary_phone   = $phone_numbers[0] ?? '';
        $secondary_phone = $phone_numbers[1] ?? '';
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

?>

    <div class="wrap">
        <h1>Customer Details</h1>

        <!-- Customer profile -->
        <div style="background:#fff; padding:15px; border:1px solid #ddd; margin-bottom:20px;">
            <h2><?php echo esc_html($customer->first_name . ' ' . $customer->last_name); ?></h2>
            <p><strong>Email:</strong> <?php echo esc_html($customer->email); ?></p>
            <p><strong>Priamry Phone:</strong> <?php echo esc_html($primary_phone); ?></p>
            <p><strong>Secondary Phone:</strong> <?php echo esc_html($secondary_phone); ?></p>
            <p><strong>Address:</strong>
                <?php echo esc_html($customer->street_address . ', ' . $customer->city . ' ' . $customer->province . ', ' . $customer->postal_code); ?>
            </p>
            <p><strong>Joined:</strong> <?php echo date('F j, Y', strtotime($customer->created_at)); ?></p>
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
