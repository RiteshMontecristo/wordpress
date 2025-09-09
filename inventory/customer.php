<?php


function customer_page()
{
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

    // Pagination settings
    $offset = ($current_page - 1) * $per_page;

    // Handle search
    if (!empty($search_query)) {
        $search = $search_query;
        $search_query = $wpdb->prepare("
        AND MATCH(first_name, last_name, phone, street_address, city, province, postal_code, country) 
        AGAINST(%s IN NATURAL LANGUAGE MODE)
    ", $search);
    }

    // Build main query
    $query = "SELECT * FROM $table_name WHERE 1=1 $search_query LIMIT %d OFFSET %d";
    $sql = $wpdb->prepare($query, $per_page, $offset);
    $customers = $wpdb->get_results($sql);

    if (empty($customers)) {
        echo '<div class="notice notice-info is-dismissible"><p>No customers found!</p></div>';
        return ob_get_clean();
    }

    // Get total number of customers
    $total_customers = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE 1=1 $search_query");

    // Pagination calculation
    $total_pages = ceil($total_customers / $per_page);

    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
          </thead>';
    echo '<tbody>';

    foreach ($customers as $customer) {

        if ($context === "customer") {
            $actionMethod = "
        <td>
            <a href='?page=customer-management&action=view&id={$customer->id}' class='button'>View</a>
            <a href='?page=customer-management&action=edit&id={$customer->id}' class='button'>Edit</a>
            <a href='?page=customer-management&action=delete&id={$customer->id}' class='button' onclick='return confirm(\"Are you sure?\");'>Delete</a>
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
                <td id='phone'>{$customer->phone}</td>
                <td id='email'>{$customer->email}</td>
                <td id='address'>{$customer->street_address},<br /> {$customer->city} {$customer->province}, <br /> {$customer->postal_code}</td>
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
    <form name="customer" method="post">

        <!-- Hidden field to trigger the form submission as we are submitting through AJAX -->
        <input type="hidden" name="add_customer" value="1">
        <?php wp_nonce_field('add_customer_action', 'add_customer_nonce'); ?>
        <label for="firstName">First Name:</label>
        <input id="firstName" type="text" name="firstName" required>

        <label for="lastName">Last Name:</label>
        <input id="lastName" type="text" name="lastName" required>

        <label for="phone">Phone:</label>
        <input id="phone" type="text" name="phone" required>

        <label for="email">Email:</label>
        <input id="email" type="email" name="email">

        <label for="address">Street Address:</label>
        <input id="address" type="text" name="address"></input>

        <label for="city">City:</label>
        <input id="city" type="text" name="city"></input>

        <label for="province">Province:</label>
        <input id="province" type="text" name="province"></input>

        <label for="postalCode">Postal Code:</label>
        <input id="postalCode" type="text" name="postalCode"></input>

        <label for="country">Country:</label>
        <?php countrySelector(); ?>

        <button id="customer_cta" class="button button-primary">Save Customer</button>
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
        $firstName = sanitize_text_field($_POST['firstName']);
        $lastName = sanitize_text_field($_POST['lastName']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $address = sanitize_textarea_field($_POST['address']);
        $city = sanitize_textarea_field($_POST['city']);
        $province = sanitize_textarea_field($_POST['province']);
        $postalCode = sanitize_textarea_field($_POST['postalCode']);
        $country = sanitize_textarea_field($_POST['country']);

        $CANADA_POSTAL_REGEX = "/^[ABCEGHJ-NPRSTVXY][0-9][ABCEGHJ-NPRSTV-Z][ ]?[0-9][ABCEGHJ-NPRSTV-Z][0-9]$/";
        $errors = [];

        // Validate required fields
        if (empty($firstName))
            $errors[] = 'First name is required';
        if (empty($lastName))
            $errors[] = 'Last name is required';
        if (empty($phone))
            $errors[] = 'Phone number is required';
        if (empty($city))
            $errors[] = 'City is required';
        if (empty($province))
            $errors[] = 'Province is required';
        if (empty($postalCode))
            $errors[] = 'Postal code is required';
        if (empty($country))
            $errors[] = 'Country is required';

        // Validate email if provided
        if (!empty($email) && !is_email($email)) {
            $errors[] = 'Please enter a valid email address or leave the field blank';
        }

        if (!preg_match($CANADA_POSTAL_REGEX, $postalCode)) {
            $errors[] = 'Invalid Canadian postal code format';
        } else {
            // Format postal code with space only if valid
            if (strlen($postalCode) === 6) {
                $postalCode = substr($postalCode, 0, 3) . ' ' . substr($postalCode, 3);
            }
        }

        // Stop if any errors
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error) . '</p></div>';
            }
            return;
        }

        $inserted = $wpdb->insert($table_name, [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'email' => !empty($email) ? $email : null,
            'street_address' => $address,
            'city' => $city,
            'province' => $province,
            'postal_code' => $postalCode,
            'country' => $country
        ]);

        if ($inserted) {
            echo '<div class="updated"><p>Customer added successfully!</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . $wpdb->last_error . '</p></div>';
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
    $customer_id = intval($_GET['id']);

    // Handle form submission
    if (isset($_POST['edit_customer'])) {
        // Verify the nonce
        if (!isset($_POST['edit_customer_nonce']) || !wp_verify_nonce($_POST['edit_customer_nonce'], 'edit_customer_action')) {
            die('Security check failed!');
        }
        global $wpdb;

        $firstName = sanitize_text_field($_POST['firstName']);
        $lastName = sanitize_text_field($_POST['lastName']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $address = sanitize_textarea_field($_POST['address']);
        $city = sanitize_textarea_field($_POST['city']);
        $province = sanitize_textarea_field($_POST['province']);
        $postalCode = sanitize_textarea_field($_POST['postalCode']);
        $country = sanitize_textarea_field($_POST['country']);

        $CANADA_POSTAL_REGEX = "/^[ABCEGHJ-NPRSTVXY][0-9][ABCEGHJ-NPRSTV-Z][ ]?[0-9][ABCEGHJ-NPRSTV-Z][0-9]$/";
        $errors = [];

        // Validate required fields
        if (empty($firstName))
            $errors[] = 'First name is required';
        if (empty($lastName))
            $errors[] = 'Last name is required';
        if (empty($phone))
            $errors[] = 'Phone number is required';
        if (empty($city))
            $errors[] = 'City is required';
        if (empty($province))
            $errors[] = 'Province is required';
        if (empty($postalCode))
            $errors[] = 'Postal code is required';
        if (empty($country))
            $errors[] = 'Country is required';

        // Validate email if provided
        if (!empty($email) && !is_email($email)) {
            $errors[] = 'Please enter a valid email address or leave the field blank';
        }

        if (!preg_match($CANADA_POSTAL_REGEX, $postalCode)) {
            $errors[] = 'Invalid Canadian postal code format';
        } else {
            // Format postal code with space only if valid
            if (strlen($postalCode) === 6) {
                $postalCode = substr($postalCode, 0, 3) . ' ' . substr($postalCode, 3);
            }
        }

        // Stop if any errors
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error) . '</p></div>';
            }
            return;
        }

        $updated = $wpdb->update($table_name, [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'email' => !empty($email) ? $email : null,
            'street_address' => $address,
            'city' => $city,
            'province' => $province,
            'postal_code' => $postalCode,
            'country' => $country
        ], ['id' => $customer_id]);


        if ($updated || $updated === 0) {
            echo '<div class="updated"><p>Customer updated successfully!</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . $wpdb->last_error . '</p></div>';
        }
    }

    $customer = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $customer_id");
    if (!$customer) {
        echo '<div class="notice notice-error is-dismissible"><p>Customer not found!</p></div>';
        return;
    }
    ?>
    <h2>Edit Customer</h2>
    <form name="customer" method="post">

        <!-- Hidden field to trigger the form submission as we are submitting through AJAX -->
        <input type="hidden" name="edit_customer" value="1">
        <?php wp_nonce_field('edit_customer_action', 'edit_customer_nonce'); ?>
        <label>First Name:</label>
        <input id="firstName" type="text" name="firstName" value="<?php echo $customer->first_name; ?>" required>

        <label>Last Name:</label>
        <input id="lastName" type="text" name="lastName" value="<?php echo $customer->last_name; ?>" required>

        <label>Phone:</label>
        <input id="phone" type="text" name="phone" value="<?php echo $customer->phone; ?>" required>

        <label>Email:</label>
        <input id="email" type="email" name="email" value="<?php echo $customer->email; ?>">

        <label for="address">Street Address:</label>
        <input id="address" id="address" type="text" name="address"
            value="<?php echo $customer->street_address; ?>"></input>

        <label for="city">City:</label>
        <input id="city" type="text" name="city" value="<?php echo $customer->city; ?>"></input>

        <label for="province">Province:</label>
        <input id="province" type="text" name="province" value="<?php echo $customer->province; ?>"></input>

        <label for="postalCode">Postal Code:</label>
        <input id="postalCode" type="text" name="postalCode" value="<?php echo $customer->postal_code; ?>"></input>

        <label for="country">Country:</label>
        <?php countrySelector($customer->country); ?>

        <button id="customer_cta" class="button button-primary">Update Customer</button>
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

    // Fetch customer info
    $customer = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $customers_table WHERE id = %d",
        $customer_id
    ));

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

?>

    <div class="wrap">
        <h1>Customer Details</h1>

        <!-- Customer profile -->
        <div style="background:#fff; padding:15px; border:1px solid #ddd; margin-bottom:20px;">
            <h2><?php echo esc_html($customer->first_name . ' ' . $customer->last_name); ?></h2>
            <p><strong>Email:</strong> <?php echo esc_html($customer->email); ?></p>
            <p><strong>Phone:</strong> <?php echo esc_html($customer->phone); ?></p>
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
            <textarea style="width:100%; height:100px;" placeholder="Enter notes about this customer..."></textarea>
            <p><button class="button button-primary">Save Notes</button></p>
        </div>
    </div>

<?php
}
