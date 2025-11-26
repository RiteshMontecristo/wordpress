<?php

function salespeople_page()
{
?>
    <div class="wrap">
        <h1>Salespeople Management</h1>

        <!-- Search Form -->
        <form method="get">
            <input type="hidden" name="page" value="salespeople-management">
            <input type="text" name="search" placeholder="Search by Name">
            <button type="submit" class="button">Search</button>
        </form>


        <!-- Add Customer Button -->
        <a href="?page=salespeople-management&action=add" class="button button-primary">Add New Salesperson</a>

        <?php

        // $action_map = [
        //     'add' => 'add_customer_form',
        //     'edit' => 'edit_customer_form',
        //     'delete' => 'delete_customer_form'
        // ];
        // // Handle actions
        // if (isset($_GET['action']) && isset($action_map[$_GET['action']])) {
        //     call_user_func($action_map[$_GET['action']]);
        // } else {
        //     $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        //     $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;
        //     $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        //     $output = customer_table("customer", $search_query, $per_page, $current_page);
        //     echo $output;
        // }


        if (isset($_GET['action']) && $_GET['action'] === 'add') {
            add_salesperson_form();
        } else {
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
            </tr>
          </thead>';
    echo '<tbody>';

    foreach ($salespeople as $salespeople) {

        echo "<tr>
                <td id='firstName'>{$salespeople->first_name}</td>
                <td id='lastName'>{$salespeople->last_name}</td>
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
            echo '<div class="updated"><p>Customer added successfully!</p></div>';
            delete_transient('mji_salespeople');
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . $wpdb->last_error . '</p></div>';
        }
    }
}
