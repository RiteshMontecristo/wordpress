<?php

function salespeople_page()
{
?>
    <div class="wrap">
        <h1>Salespeople Management</h1>

        <?php
        $action_map = [
            'add' => 'add_salesperson_form',
            'view' => 'view_salesperson_form',
            // 'edit' => 'edit_customer_form',
            'delete' => 'delete_salesperson'
        ];
        // Handle actions
        if (isset($_GET['action']) && isset($action_map[$_GET['action']])) {
            call_user_func($action_map[$_GET['action']]);
        } else {
        ?>
            <!-- Search Form -->
            <form method="get">
                <input type="hidden" name="page" value="salespeople-management">
                <input type="text" name="search" placeholder="Search by Name">
                <button type="submit" class="button">Search</button>
            </form>

            <!-- Add Customer Button -->
            <a href="?page=salespeople-management&action=add" class="button button-primary">Add New Salesperson</a>
        <?php
            $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
            echo fetch_salespeople($search_query);
        }
        ?>
    </div>
<?php
}

function fetch_salespeople($search_query = '')
{
    ob_start();
    global $wpdb;

    $table_name = $wpdb->prefix . 'mji_salespeople';

    $search_clause = '';
    if (!empty($_GET['search'])) {
        $search_term = '%' . $wpdb->esc_like($_GET['search']) . '%';
        $search_clause = $wpdb->prepare(
            " WHERE first_name LIKE %s OR last_name LIKE %s",
            $search_term,
            $search_term
        );
    }

    $query = "SELECT * FROM {$table_name} {$search_clause}";
    $salespeople = $wpdb->get_results($query);

    if (empty($salespeople)) {
        echo '<div class="notice notice-info is-dismissible"><p>No salesperson found!</p></div>';
        return ob_get_clean();
    }

    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Actions</th>
            </tr>
          </thead>';
    echo '<tbody>';

    foreach ($salespeople as $salespeople) {

        echo "<tr>
            <td id='firstName'>{$salespeople->first_name}</td>
            <td id='lastName'>{$salespeople->last_name}</td>
            <td>
                <a href='?page=salespeople-management&action=view&id={$salespeople->id}' class='button'>View</a>
                <a href='?page=salespeople-management&action=delete&id={$salespeople->id}'
                class='button'
                onclick=\"return confirm('Are you sure you want to delete this salesperson?');\">
                Delete
                </a>
            </td>
        </tr>";
    }

    echo '</tbody></table>';

    return ob_get_clean();
}

function add_salesperson_form()
{
?>
    <div class="wrap">
        <h2>Add Salespeople</h2>
        <form name="add_salespeople" method="post">

            <input type="hidden" name="add_salespeople" value="1">
            <?php wp_nonce_field('add_salespeople_action', 'add_salespeople_nonce'); ?>

            <label for="firstName">First Name:</label>
            <input id="firstName" type="text" name="firstName" required>

            <label for="lastName">Last Name:</label>
            <input id="lastName" type="text" name="lastName" required>

            <button id="add_salespeople" class="button button-primary">Save</button>
        </form>
        <?php
        if (isset($_POST['add_salespeople'])) {

            if (!isset($_POST['add_salespeople_nonce']) || !wp_verify_nonce($_POST['add_salespeople_nonce'], 'add_salespeople_action')) {
                die('Security check failed!');
            }

            global $wpdb;

            $table_name = $wpdb->prefix . 'mji_salespeople';
            $firstName = sanitize_text_field($_POST['firstName']);
            $lastName = sanitize_text_field($_POST['lastName']);

            if (empty($firstName)) $errors[] = 'First name is required';
            if (empty($lastName))  $errors[] = 'Last name is required';

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error) . '</p></div>';
                }
                return;
            }

            $inserted = $wpdb->insert($table_name, [
                'first_name' => $firstName,
                'last_name' => $lastName
            ]);

            if ($inserted) {
                echo '<div class="updated"><p>Salesperson added successfully!</p></div>';
                delete_transient('mji_salespeople');
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . $wpdb->last_error . '</p></div>';
            }
        }
        ?>
    </div>
<?php
}

function view_salesperson_form()
{
    global $wpdb;

    $customer_table =  $wpdb->prefix . 'mji_customers';
    $salesperson_id = isset($_GET['id']) ? $_GET['id'] : 0;
    $current_page   = isset($_GET['paged']) ? (int)$_GET['paged'] : 1;
    $per_page       = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;

    if (!$salesperson_id) {
        // Handle error: redirect, show 404, etc.
        echo "<p>Salesperson ID is required, Must select a salesperson!!!</p>";
        return;
    }

    $salespeople_array = mji_get_salespeople();
    $salesperson = current(array_filter($salespeople_array, function ($sp) use ($salesperson_id) {
        return $sp->id === $salesperson_id;
    }));

    // Pagination settings
    $offset = ($current_page - 1) * $per_page;
    $where = "WHERE salesperson_id = $salesperson_id";

    // Main query with join and pagination
    $query = $wpdb->prepare("
        SELECT *
        FROM $customer_table c
        $where
        ORDER BY created_at DESC
        LIMIT %d OFFSET %d
    ", $per_page, $offset);

    $customers = $wpdb->get_results($query);

    if (empty($customers)) {
        echo '<div class="notice notice-info is-dismissible"><p>No clients found for ' . $salesperson->first_name . ' ' . $salesperson->last_name . '!</p></div>';
        return;
    }
    // Get total number of customers
    $total_customers = $wpdb->get_var("
                                            SELECT COUNT(DISTINCT id)
                                            FROM $customer_table
                                            $where
                                        ");
    // Pagination calculation
    $total_pages = ceil($total_customers / $per_page);

?>

    <section>
        <h2>Clients list of <?= $salesperson->first_name ?> <?= $salesperson->last_name ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Primary Phone</th>
                    <th>Secondary Phone</th>
                    <th>Email</th>
                    <th>Address</th>
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

                    echo "
                <tr>
                    <td id='firstName'>{$customer->first_name}</td>
                    <td id='lastName'>{$customer->last_name}</td>
                    <td id='primaryPhone'>{$primary_phone}</td>
                    <td id='secondaryPhone'>{$secondary_phone}</td>
                    <td id='email'>{$customer->email}</td>
                    <td id='address'>$address</td>
                    <td>
                        <a href='?page=customer-management&action=view&id={$customer_id}' class='button'>View</a>
                    </td>
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
                        echo "<a class='page-numbers $active' href='?page=salespeople-management&id=$salesperson_id&action=view&paged=$i&per_page=$per_page'>$i</a> ";
                    }
                }
                ?>

                <form method="GET">
                    <input type="hidden" name="page" value="salespeople-management">
                    <input type="hidden" name="action" value="view">
                    <input type="hidden" name="id" value="<?= $salesperson_id ?>">
                    <select name="per_page" onchange="this.form.submit();">
                        <?php
                        $options = [1, 2, 50, 100];
                        foreach ($options as $option) {
                            $selected = ($per_page == $option) ? 'selected' : '';
                            echo "<option value='$option' $selected>$option per page</option>";
                        }
                        ?>
                    </select>
                </form>
            </div>
        </div>
    </section>
<?php
}

function delete_salesperson()
{
    global $wpdb;
    $salesperson_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    $table_name = $wpdb->prefix . 'mji_salespeople';
    $orders_table = $wpdb->prefix . 'mji_orders';

    if ($salesperson_id === 0) {
        echo "<div>Salesperson needs to be selected in order to be deleted. </div>";
    }

    $orders_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$orders_table} WHERE salesperson_id = %d",
        $salesperson_id
    ));

    if ($orders_count > 0) {
        echo '<div class="notice notice-error"><p>Salesperson can not be deleted as salesperson has sold items in order.</p></div>';
    } else {
        $deleted = $wpdb->delete($table_name, [
            'id' => $salesperson_id
        ]);

        if ($deleted) {
            echo '<div class="updated"><p>Salesperson deleted successfully!</p></div>';
            delete_transient('mji_salespeople');
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . $wpdb->last_error . '</p></div>';
        }
    }
}
