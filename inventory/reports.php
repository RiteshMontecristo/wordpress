<?php
function reports_page()
{
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'sales';
    $allowed_tabs = ['sales', 'inventory'];

    if (!in_array($active_tab, $allowed_tabs)) {
        $active_tab = 'sales';
    }

    $sales_url = add_query_arg(
        'tab',
        'sales',
        menu_page_url('reports-management', false)
    );

    $inventory_url = add_query_arg(
        'tab',
        'inventory',
        menu_page_url('reports-management', false)
    );

?>
    <div class="wrap">
        <h1>Reports</h1>

        <h2 class="nav-tab-wrapper">
            <a href="<?php echo esc_url($sales_url); ?>"
                class="nav-tab <?php echo $active_tab === 'sales' ? 'nav-tab-active' : ''; ?>">
                Sales Report
            </a>
            <a href="<?php echo esc_url($inventory_url); ?>"
                class="nav-tab <?php echo $active_tab === 'inventory' ? 'nav-tab-active' : ''; ?>">
                Inventory Report
            </a>
        </h2>

        <?php if ($active_tab === 'sales') {
            reports_render_sales_section();
        } elseif ($active_tab === 'inventory') {
            reports_render_inventory_section();
        } ?>
    </div>
<?php
}


// Reports sales Section
function reports_render_sales_section()
{
    reports_render_sales_filters();

    echo '<hr>';
    if (isset($_GET['start_date'], $_GET['end_date'])) {
        $results = reports_get_sales_results();
        reports_render_sales_report($results);
    }
}

function reports_render_sales_filters()
{
?>
    <form method="get" action="">
        <input type="hidden" name="page" value="reports-management">

        <table class="form-table">
            <tr>
                <th scope="row"><label for="start_date">Start Date</label></th>
                <td>
                    <?php
                    if (isset($_GET['start_date'])) {
                        $startDate = sanitize_text_field($_GET['start_date']);
                    } else {
                        $startDate = date("Y-m-01", strtotime("first day of last month"));
                    }
                    ?>
                    <input type="date" name="start_date" id="start_date" value="<?php echo $startDate; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="end_date">End Date</label></th>
                <td>
                    <?php

                    if (isset($_GET['end_date'])) {
                        $endDate = sanitize_text_field($_GET['end_date']);
                    } else {
                        $endDate = date("Y-m-t", strtotime("last month"));
                    }
                    ?>
                    <input type="date" name="end_date" id="end_date" value="<?php echo $endDate; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="salesperson">Salesperson</label></th>
                <td>
                    <?= mji_salesperson_dropdown(false) ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="location">Store</label></th>
                <td>
                    <?= mji_store_dropdown(false) ?>
                </td>
            </tr>
        </table>

        <?php submit_button('Generate Report'); ?>
    </form>
<?php
}

function reports_get_sales_results()
{
    global $wpdb;

    $start_raw = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
    $end_raw   = isset($_GET['end_date'])   ? sanitize_text_field($_GET['end_date'])   : '';

    $start_ts = strtotime($start_raw);
    $end_ts   = strtotime($end_raw);

    if ($start_ts === false || $end_ts === false) {
        return [];
    }

    $start_date = date('Y-m-d H:i:s', $start_ts);
    $end_date   = date('Y-m-d H:i:s', $end_ts);

    $salesperson = !empty($_GET['salesperson']) ? intval($_GET['salesperson']) : null;
    $location = !empty($_GET['location']) ? intval($_GET['location']) : null;

    $orders_table = $wpdb->prefix . 'mji_orders';
    $order_items = $wpdb->prefix . 'mji_order_items';
    $inventory_table = $wpdb->prefix . 'mji_product_inventory_units';
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';
    $customers_table = $wpdb->prefix . 'mji_customers';
    $service_table = $wpdb->prefix . 'mji_services';
    $models_table = $wpdb->prefix . 'mji_models';

    $where1 = ["o.created_at BETWEEN %s AND %s"];
    $params1 = [$start_date, $end_date];

    if ($salesperson !== null) {
        $where1[] = "o.salesperson_id = %d";
        $params1[] = $salesperson;
    }

    if ($location !== null) {
        $where1[] = "pi.location_id = %d";
        $params1[] = $location;
    }

    $query1 = "
                SELECT 
                    o.reference_num AS invoice,
                    o.created_at AS sold_date,
                    s.first_name AS salesperson_first_name,
                    s.last_name AS salesperson_last_name, 
                    c.first_name AS customer_first_name,
                    c.last_name AS customer_last_name, 
                    pi.wc_product_id AS product_id,
                    pi.wc_product_variant_id AS product_variant_id,
                    pi.sku AS sku,
                    pi.serial AS serial,
                    m.name AS model_name,
                    pi.location_id,
                    COALESCE(oi.sale_price, 0) AS retail_paid,
                    COALESCE(oi.discount_amount, 0) AS discount_amount,
                    COALESCE(pi.cost_price, 0) AS cost_price,
                    COALESCE(pi.retail_price, 0) AS retail_price,
                    'TEST' AS description
                FROM $orders_table o
                INNER JOIN $order_items oi ON o.id = oi.order_id
                INNER JOIN $inventory_table pi ON oi.product_inventory_unit_id = pi.id
                INNER JOIN $salespeople_table s ON o.salesperson_id = s.id
                INNER JOIN $customers_table c ON o.customer_id = c.id
                INNER JOIN $models_table m on m.id = pi.model_id
                WHERE " . implode(" AND ", $where1) . "
            ";

    $where2 = ["o.created_at BETWEEN %s AND %s"];
    $params2 = [$start_date, $end_date];

    if ($salesperson !== null) {
        $where2[] = "o.salesperson_id = %d";
        $params2[] = $salesperson;
    }

    if ($location !== null) {
        $where2[] = "si.location_id = %d";
        $params2[] = $location;
    }

    $query2 = "
                SELECT 
                    o.reference_num AS invoice,
                    o.created_at AS sold_date,
                    s.first_name AS salesperson_first_name,
                    s.last_name AS salesperson_last_name, 
                    c.first_name AS customer_first_name,
                    c.last_name AS customer_last_name, 
                    NULL AS product_id,
                    NULL AS product_variant_id,
                    category AS sku,
                    NULL AS serial,
                    NULL AS sku,
                    NULL AS model_name,
                    COALESCE(si.sold_price, 0) as retail_paid,
                    COALESCE(0, 0) as discount_amount,
                    COALESCE(si.cost_price, 0) as cost_price,
                    COALESCE(si.sold_price, 0) as retail_price,
                    si.description AS description
                FROM $orders_table o
                INNER JOIN $service_table si ON si.order_id = o.id
                INNER JOIN $salespeople_table s ON o.salesperson_id = s.id
                INNER JOIN $customers_table c ON o.customer_id = c.id
                WHERE " . implode(" AND ", $where2) . "
            ";

    $query = "
        ($query1)
        UNION ALL
        ($query2)
        ORDER BY sold_date ASC
    ";

    $params = array_merge($params1, $params2);
    $results = $wpdb->get_results($wpdb->prepare($query, ...$params));

    return $results;
}

function reports_render_sales_report($results)
{
    if ($results) {
        $total_cost = 0;
        $total_retail = 0;
        $total_retail_paid = 0;
        $total_profit = 0;

        $location_id = isset($_GET['location']) ? intval($_GET['location']) : 0;
        $location_arr = mji_get_locations();
        $location = $location_id > 0 ? $location_arr[$location_id]->name : '';

        echo '<button id="exportInventory" class="button button-primary" style="margin-bottom:10px;">Export to CSV</button>';
        echo '<button id="printInventory" class="button button-secondary" style="margin-bottom:10px;">Print Report</button>';
        echo '<div id="report">
                <header>
                        <h2>Sales Report - Montecristo Jewellers ' .  $location . '</h2>
                        <p>Date: ' . esc_html($_GET['start_date']) . ' to ' . esc_html($_GET['end_date']) . '</p>
                </header>
                <table id="inventoryTable" class="widefat striped"><thead>
                    <tr>
                        <th>Image</th>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Item</th>
                        <th>SKU</th>
                        <th>Cost</th>
                        <th>Retail</th>
                        <th>Retail Paid</th>
                        <th>Discount</th>
                        <th>Discount(%)</th>
                        <th>Profit</th>
                        <th>Margin(%)</th>
                        <th>Salesperson</th>
                        <th>Customer</th>
                    </tr>
                </thead><tbody>';

        foreach ($results as $row) {
            // Prefer variant over base product
            $product_id = $row->product_variant_id ?: $row->product_id;
            $product = wc_get_product($product_id);

            $profit = $row->retail_paid - $row->cost_price;
            $margin_percent = $row->retail_paid ? ($profit / $row->retail_paid) * 100 : 0;
            $desc = $row->description ? ' - ' . $row->description : '';
            $name = format_label($row->sku) . $desc;
            $dt = new DateTime($row->sold_date);
            $date = $dt->format('Y-m-d');
            $placeholder_image = wc_placeholder_img([50, 50]);
            if (!$product) {
                echo '<tr>';
                echo '<td>' . $placeholder_image . '</td>';
                echo '<td>' .  $row->invoice . '</td>';
                echo '<td>' .  $date . '</td>';
                echo '<td>' .  $name . '</td>';
                echo '<td>Service</td>';
                echo '<td>' . number_format($row->cost_price, 2) . '</td>';
                echo '<td>' . number_format($row->retail_paid, 2) . '</td>';
                echo '<td>' . number_format($row->retail_paid, 2) . '</td>';
                echo '<td>' . number_format($row->discount_amount, 2) . '</td>';
                echo '<td>' . number_format(0, 2) . '%</td>';
                echo '<td>' . number_format($profit, 2) . '</td>';
                echo '<td>' . number_format($margin_percent, 2) . '%</td>';
                echo '<td>' . esc_html($row->salesperson_first_name) . ' ' . esc_html($row->salesperson_last_name) . '</td>';
                echo '<td>' . esc_html($row->customer_first_name) . ' ' . esc_html($row->customer_last_name) . '</td>';
                echo '</tr>';
                continue; // Skip invalid products
            }

            $name = $product->get_name();

            if ($product->is_type('variation')) {
                $parent = wc_get_product($product->get_parent_id());
                if ($parent) {
                    $name = $parent->get_name() . ' - ' . wc_get_formatted_variation($product, true);
                }
            }

            $discount_percent = $row->retail_price ? ($row->discount_amount / $row->retail_price) * 100 : 0;
            $profit = $row->retail_paid - $row->cost_price;
            $margin_percent = $row->retail_paid ? ($profit / $row->retail_paid) * 100 : 0;
            $image = $product->get_image([50, 50]);

            // Calculate totals
            $total_cost += $row->cost_price;
            $total_retail += $row->retail_price;
            $total_retail_paid += $row->retail_paid;
            $total_profit += $profit;

            echo '<tr>';
            echo '<td>' . $image . '</td>';
            echo '<td>' .  $row->invoice . '</td>';
            echo '<td>' .  $date . '</td>';
            echo '<td>' . $name . '</td>';
            echo '<td>' . $row->sku . '<br/>' . $row->model_name . '<br />' . $row->serial . '</td>';
            echo '<td>' . number_format($row->cost_price, 2) . '</td>';
            echo '<td>' . number_format($row->retail_price, 2) . '</td>';
            echo '<td>' . number_format($row->retail_paid, 2) . '</td>';
            echo '<td>' . number_format($row->discount_amount, 2) . '</td>';
            echo '<td>' . number_format($discount_percent, 2) . '%</td>';
            echo '<td>' . number_format($profit, 2) . '</td>';
            echo '<td>' . number_format($margin_percent, 2) . '%</td>';
            echo '<td>' . esc_html($row->salesperson_first_name) . ' ' . esc_html($row->salesperson_last_name) . '</td>';
            echo '<td>' . esc_html($row->customer_first_name) . ' ' . esc_html($row->customer_last_name) . '</td>';
            echo '</tr>';
        }

        echo '
                </tbody>
            </table>
            <div> 
                <strong>Total Cost: ' . number_format($total_cost, 2) . '</strong>
                <strong>Total Retail: ' . number_format($total_retail, 2) . '</strong> 
                <strong>Total Retail Paid: ' . number_format($total_retail_paid, 2) . '</strong>
                <strong>Total Profit: ' . number_format($total_profit, 2) . '</strong>
            </div>
        </div>
        ';
    } else {
        echo '<p>No orders found for this period.</p>';
    }
}

// Reports Inventory Section
function reports_render_inventory_section()
{
    reports_render_inventory_filters();

    echo '<hr>';
    if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
        $results = reports_get_inventory_result();
        reports_render_inventory_report($results);
    }
}

function reports_render_inventory_filters()
{
    $location = isset($_GET['location']) ? intval($_GET['location']) : '';
    $brands = isset($_GET['brands']) ? intval($_GET['brands']) : '';
?>
    <form method="get" action="">
        <input type="hidden" name="page" value="reports-management">
        <input type="hidden" name="tab" value="inventory">

        <table class="form-table">
            <tr>
                <th scope="row"><label for="start_date">Start Date</label></th>
                <td>
                    <?php
                    if (isset($_GET['start_date'])) {
                        $startDate = sanitize_text_field($_GET['start_date']);
                    } else {
                        $startDate = date("Y-m-01", strtotime("first day of last month"));
                    }
                    ?>
                    <input type="date" name="start_date" id="start_date" value="<?php echo $startDate; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="end_date">End Date</label></th>
                <td>
                    <?php

                    if (isset($_GET['end_date'])) {
                        $endDate = sanitize_text_field($_GET['end_date']);
                    } else {
                        $endDate = date("Y-m-t", strtotime("last month"));
                    }
                    ?>
                    <input type="date" name="end_date" id="end_date" value="<?php echo $endDate; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="location">Store</label></th>
                <td>
                    <?= mji_store_dropdown(false, $location) ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="brands">Brands</label></th>
                <td>
                    <?= mji_brands_dropdown(false, $brands) ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="status">Status</label></th>
                <td>
                    <select name="status" id="status">
                        <option value="in_stock">In stock</option>
                        <option value="out_of_stock">Out of stock</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="search">Search</label></th>
                <td>
                    <input type="text" id="search" name="search" placeholder="Search..." />
                </td>
            </tr>
        </table>

        <?php submit_button('Generate Report'); ?>
    </form>

<?php
}

function reports_get_inventory_result()
{
    global $wpdb;

    $start_raw = !empty($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
    $end_raw = !empty($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';
    $location = !empty($_GET['location']) ? intval($_GET['location']) : null;
    $brands =  !empty($_GET['brands']) ? intval($_GET['brands']) : null;
    $status =  !empty($_GET['status']) ? $_GET['status'] : "in_stock";
    $search_text = !empty($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

    $start_ts = strtotime($start_raw);
    $end_ts   = strtotime($end_raw);

    if ($start_ts === false || $end_ts === false) {
        echo '<p style="color:red">No start and end date provided.</p>';
        return;
    }

    $start_date = date('Y-m-d', $start_ts);
    $end_date   = date('Y-m-d', $end_ts);

    if (!$start_date || !$end_date) {
        echo '<p style="color:red">Please provide start and end date!!</p>';
        return;
    }

    $inventory_table = $wpdb->prefix . 'mji_product_inventory_units';
    $models = $wpdb->prefix . 'mji_models';

    $where = [];
    $params = [];

    // Base relevance filter: existed by end, not sold before start
    $where[] = "i.created_date <= %s";
    $params[] = $end_date;

    $where[] = "(i.sold_date IS NULL OR i.sold_date >= %s)";
    $params[] = $start_date;

    if ($location) {
        $where[] = "i.location_id = %d";
        $params[] = $location;
    }

    if ($brands) {
        $where[] = "i.brand_id = %d";
        $params[] = $brands;
    }

    if ($status === 'in_stock') {
        $where[] = "i.status = 'in_stock'";
    } else {
        $where[] = "i.status NOT IN ('in_stock')";
    }

    // Search: SKU, Serial, Model Name
    if (!empty($search_text)) {
        $like = '%' . $wpdb->esc_like($search_text) . '%';
        $where[] = "(i.sku LIKE %s OR i.serial LIKE %s OR m.name LIKE %s)";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $where_clause = implode(' AND ', $where);

    // We can join with wpdb->posts to speed up 
    $query = "
            SELECT 
                i.wc_product_id AS product_id,
                i.wc_product_variant_id AS product_variant_id,
                i.sku AS sku,
                i.serial,
                m.name, 
                i.status,
                i.sold_date,
                COALESCE(i.cost_price, 0) as cost_price,
                COALESCE(i.retail_price, 0) as retail_price
            FROM $inventory_table i
            LEFT JOIN $models m 
            ON m.id = i.model_id
            WHERE {$where_clause}
        ";

    $results = $wpdb->get_results($wpdb->prepare($query, ...$params));
    $results['start_date'] = $start_date;
    $results['end_date'] = $end_date;
    $results['status'] = $status;
    return $results;
}

function reports_render_inventory_report($results)
{
    if ($results) {

        $missing_count = 0;
        $total_count = 0;
        $total_cost_price = 0;
        $total_retail_price = 0;
        $get_store_locations = mji_get_locations();
        $location_obj = array_find($get_store_locations, fn($loc) => $loc->id == intval($_GET['location']));
        $location_name = $location_obj ? $location_obj->name : 'All Location';
        $start_date = $results['start_date'];
        $end_date = $results['end_date'];
        $status = $results['status'];
        $header = $status == "in_stock" ? "" : "<th>Sold Date</th>";

        echo '<div style="max-height:700px; overflow-y:auto; position:relative;">';
        echo '<button id="exportInventory" class="button button-primary" style="margin-bottom:10px;">Export to CSV</button>';
        echo '<button id="printInventory" class="button button-secondary" style="margin-bottom:10px;">Print Report</button>';
        echo '<div id="report">
                            <header>
                                    <h2>Inventory Report for ' . $location_name .  '- Montecristo Jewellers</h2>
                                    <p>From ' . $start_date . ' to ' . $end_date . '</p>
                            </header>
                            <table id="inventoryTable" class="widefat striped">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product</th>
                                    <th>SKU / Model / Serial</th>
                                    <th>Status</th>
                                    <th>Cost Price</th>
                                    <th>Retail Price</th>
                                    ' . $header . '
                                </tr>
                            </thead>
                            <tbody>';

        foreach ($results as $row) {

            if (!is_object($row)) {
                continue;
            }
            // If variant get variant else base product
            $product_id = $row->product_variant_id ?: $row->product_id;
            $product = wc_get_product($product_id);

            if (!$product) {
                $missing_count++;
                continue; // Skip invalid products
            }

            $name = $product->get_name();

            if ($product->is_type('variation')) {
                $parent = wc_get_product($product->get_parent_id());
                if ($parent) {
                    $name = $parent->get_name() . ' - ' . wc_get_formatted_variation($product, true);
                }
            }

            $image = $product->get_image([50, 50]);
            $sku = $row->sku;
            $model = $row->name ?: '';
            $serial = $row->serial ?: '';
            $status = $row->status ?: '';
            $cost_price = (float) $row->cost_price;
            $retail_price = (float) $row->retail_price;

            $total_count++;
            $total_cost_price += $cost_price;
            $total_retail_price += $retail_price;
            $sold_date = strtotime($row->sold_date);

            $sold_date = $status == "in_stock" ? "" : "<td>" . date('Y-m-d', $sold_date) . "</td>";

            echo '<tr>';
            echo '<td>' . $image . '</td>';
            echo '<td>' . esc_html($name) . '</td>';
            echo '<td>' . esc_html($sku) . ' <br />' . esc_html($model) .  '<br />' . esc_html($serial) . '</td>';
            echo '<td>' . $status . '</td>';
            echo '<td>' . number_format($cost_price, 2) . '</td>';
            echo '<td>' . number_format($retail_price, 2) . '</td>';
            echo $sold_date;
            echo '</tr>';
        }

        $colspan = $status == "in_stock" ? "3" : "4";
        echo '</tbody>
                                <tfoot>
                                    <tr style="font-weight:bold; position:sticky; bottom:0; background:#fff; box-shadow:0 -2px 5px rgba(0,0,0,0.1);">
                                        <td colspan="' . $colspan . '">Total (' . $total_count . ' items)</td>
                                        <td></td>
                                        <td>' . number_format($total_cost_price, 2) . '</td>
                                        <td>' . number_format($total_retail_price, 2) . '</td>
                                    </tr>
                                </tfoot>';
        echo '</table></div>';

        if ($missing_count > 0) {
            echo '
                <div class="notice notice-error">
                    <p>Missing ' . $missing_count . ' products. Need to investigate!</p>
                </div>';
        }
    } else {
        echo '<p>No inventory reports found for this store.</p>';
    }
}
