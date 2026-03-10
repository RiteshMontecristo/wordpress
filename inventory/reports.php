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

    $layaway_url = add_query_arg(
        'tab',
        'layaway',
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
            <a href="<?php echo esc_url($layaway_url); ?>"
                class="nav-tab <?php echo $active_tab === 'layaway' ? 'nav-tab-active' : ''; ?>">
                Layaway Report
            </a>
        </h2>

        <?php if ($active_tab === 'sales') {
            reports_render_sales_section();
        } elseif ($active_tab === 'inventory') {
            reports_render_inventory_section();
        } elseif ($active_tab === 'layaway') {
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
    $end_raw = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';

    $start_ts = strtotime($start_raw);
    $end_ts = strtotime($end_raw);

    if ($start_ts === false || $end_ts === false) {
        return [];
    }

    $start_date = date('Y-m-d H:i:s', $start_ts);
    $end_date = date('Y-m-d H:i:s', $end_ts);

    $salesperson = !empty($_GET['salesperson']) ? intval($_GET['salesperson']) : null;
    $location = !empty($_GET['location']) ? intval($_GET['location']) : null;

    $orders_table = $wpdb->prefix . 'mji_orders';
    $order_items = $wpdb->prefix . 'mji_order_items';
    $inventory_table = $wpdb->prefix . 'mji_product_inventory_units';
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';
    $customers_table = $wpdb->prefix . 'mji_customers';
    $service_table = $wpdb->prefix . 'mji_services';
    $models_table = $wpdb->prefix . 'mji_models';
    $returns_table = $wpdb->prefix . 'mji_returns';

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
                    r.reference_num AS return_reference_num,
                    'TEST' AS description
                FROM $orders_table o
                INNER JOIN $order_items oi ON o.id = oi.order_id
                INNER JOIN $inventory_table pi ON oi.product_inventory_unit_id = pi.id
                INNER JOIN $salespeople_table s ON o.salesperson_id = s.id
                INNER JOIN $customers_table c ON o.customer_id = c.id
                LEFT JOIN $models_table m on m.id = pi.model_id
                LEFT JOIN $returns_table r on r.order_id = o.id
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
                    NULL AS return_reference_num,
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
                        <h2>Sales Report - Montecristo Jewellers ' . $location . '</h2>
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

            $retail_paid = (float) $row->retail_paid;
            $profit = $retail_paid - $row->cost_price;
            $margin_percent = $retail_paid > 0 ? ($profit / $retail_paid) * 100 : 0;
            $desc = $row->description ? ' - ' . $row->description : '';
            $name = format_label($row->sku) . $desc;
            $dt = new DateTime($row->sold_date);
            $date = $dt->format('Y-m-d');
            $placeholder_image = wc_placeholder_img([50, 50]);
            if (!$product) {
                echo '<tr>';
                echo '<td>' . $placeholder_image . '</td>';
                echo '<td>' . $row->invoice . '</td>';
                echo '<td>' . $date . '</td>';
                echo '<td>' . $name . '</td>';
                echo '<td>Service</td>';
                echo '<td>' . number_format($row->cost_price, 2) . '</td>';
                echo '<td>' . number_format($row->retail_price, 2) . '</td>';
                echo '<td>' . number_format($retail_paid, 2) . '</td>';
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
            $profit = $retail_paid - $row->cost_price;
            $margin_percent = $retail_paid ? ($profit / $retail_paid) * 100 : 0;
            $image = $product->get_image([50, 50]);

            if (is_empty($row->return_reference_num)) {
                // Calculate totals
                $total_cost += $row->cost_price;
                $total_retail += $row->retail_price;
                $total_retail_paid += $retail_paid;
                $total_profit += $profit;

                echo '<tr>';
                echo '<td>' . $image . '</td>';
                echo '<td>' . $row->invoice . '</td>';
                echo '<td style="white-space: nowrap;">' . $date . '</td>';
                echo '<td>' . $name . '</td>';
                echo '<td>' . $row->sku . '<br/>' . $row->model_name . '<br />' . $row->serial . '</td>';
                echo '<td>$' . number_format($row->cost_price, 2) . '</td>';
                echo '<td>$' . number_format($row->retail_price, 2) . '</td>';
                echo '<td>$' . number_format($retail_paid, 2) . '</td>';
                echo '<td>$' . number_format($row->discount_amount, 2) . '</td>';
                echo '<td>' . number_format($discount_percent, 2) . '%</td>';
                echo '<td>' . number_format($profit, 2) . '</td>';
                echo '<td>' . number_format($margin_percent, 2) . '%</td>';
                echo '<td>' . esc_html($row->salesperson_first_name) . ' ' . esc_html($row->salesperson_last_name) . '</td>';
                echo '<td>' . esc_html($row->customer_first_name) . ' ' . esc_html($row->customer_last_name) . '</td>';
                echo '</tr>';
            } else {
                echo '<tr>';
                echo '<td>' . $image . '</td>';
                echo '<td>' . $row->invoice . '<br>(' . $row->return_reference_num . ')</td>';
                echo '<td style="white-space: nowrap;">' . $date . '</td>';
                echo '<td>' . $name . ' <strong>(Returned for credit)</strong></td>';
                echo '<td>-' . $row->sku . ' <strong>(RETURNED)</strong><br/>' . $row->model_name . '<br />' . $row->serial . '</td>';
                echo '<td>-$' . number_format($row->cost_price, 2) . '</td>';
                echo '<td>-$' . number_format($row->retail_price, 2) . '</td>';
                echo '<td>-$' . number_format($retail_paid, 2) . '</td>';
                echo '<td>-$' . number_format($row->discount_amount, 2) . '</td>';
                echo '<td>-' . number_format($discount_percent, 2) . '%</td>';
                echo '<td>-' . number_format($profit, 2) . '</td>';
                echo '<td>-' . number_format($margin_percent, 2) . '%</td>';
                echo '<td>' . esc_html($row->salesperson_first_name) . ' ' . esc_html($row->salesperson_last_name) . '</td>';
                echo '<td>' . esc_html($row->customer_first_name) . ' ' . esc_html($row->customer_last_name) . '</td>';
                echo '</tr>';
            }
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
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    if (isset($_GET['start_date'])) {
        $startDate = sanitize_text_field($_GET['start_date']);
    } else {
        $startDate = date("Y-m-01", strtotime("first day of last month"));
    }
    if (isset($_GET['end_date'])) {
        $endDate = sanitize_text_field($_GET['end_date']);
    } else {
        $endDate = date("Y-m-t", strtotime("last month"));
    }
?>
    <form method="get" action="">
        <input type="hidden" name="page" value="reports-management">
        <input type="hidden" name="tab" value="inventory">

        <table class="form-table">
            <tr>
                <th scope="row"><label for="start_date">Start Date</label></th>
                <td>
                    <input type="date" name="start_date" id="start_date" value="<?php echo $startDate; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="end_date">End Date</label></th>
                <td>
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
                        <option value="in_stock" <?php selected($status, 'in_stock'); ?>>In stock</option>
                        <option value="sold" <?php selected($status, 'sold'); ?>>Sold</option>
                        <option value="out_of_stock" <?php selected($status, 'out_of_stock'); ?>>Returned</option>
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
    $brands = !empty($_GET['brands']) ? intval($_GET['brands']) : null;
    $status = !empty($_GET['status']) ? $_GET['status'] : "in_stock";
    $search_text = !empty($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

    $start_ts = strtotime($start_raw);
    $end_ts = strtotime($end_raw);

    if ($start_ts === false || $end_ts === false) {
        echo '<p style="color:red">No start and end date provided.</p>';
        return;
    }

    $start_date = date('Y-m-d', $start_ts);
    $end_date = date('Y-m-d', $end_ts);

    if (!$start_date || !$end_date) {
        echo '<p style="color:red">Please provide start and end date!!</p>';
        return;
    }

    $inventory_table = $wpdb->prefix . 'mji_product_inventory_units';
    $models_table = $wpdb->prefix . 'mji_models';
    $customers_table = $wpdb->prefix . 'mji_customers';
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';
    $payments_table = $wpdb->prefix . 'mji_payments';
    $inventory_status_history = $wpdb->prefix . 'mji_inventory_status_history';

    $where = [];
    $params = [];

    if ($location) {
        $where[] = "i.location_id = %d";
        $params[] = $location;
    }

    if ($brands) {
        $where[] = "i.brand_id = %d";
        $params[] = $brands;
    }

    if (!empty($search_text)) {
        $like = '%' . $wpdb->esc_like($search_text) . '%';
        $where[] = "(i.sku LIKE %s OR i.serial LIKE %s OR m.name LIKE %s)";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $where_clause = $where ? ' AND ' . implode(' AND ', $where) : '';

    // Build dynamic status filter FOR THE SUBQUERY
    $status_subquery_where = "";
    $status_params = [];

    if ($status == "in_stock") {
        $status_subquery_where = "AND ish.to_status = 'in_stock'";
    } elseif ($status == "sold") {
        $status_subquery_where = "AND ish.to_status = 'sold' AND ish.created_at BETWEEN %s AND %s";
        $status_params = array_merge($status_params, [$start_date, $end_date]);
    } else {
        $status_subquery_where = "AND ish.to_status IN ('damaged', 'missing', 'rtv', 'dismantled') AND ish.created_at BETWEEN %s AND %s";
        $status_params = array_merge($status_params, [$start_date, $end_date]);
    }

    $query = "
    SELECT
        i.id AS inventory_unit_id,
        i.wc_product_id,
        i.wc_product_variant_id,
        i.sku,
        i.serial,
        i.cost_price,
        i.retail_price,
        m.name AS model_name,
        latest_status.to_status AS latest_status,
        latest_status.created_at AS latest_status_date,
        status_events.events
    FROM {$inventory_table} i

    -- Join model
    LEFT JOIN {$models_table} m
        ON m.id = i.model_id

    -- if left join is needed then update the 1=1 to latest_status.id IS NOT NULL 
    -- Latest status as of report end date
    JOIN (
        SELECT ish.*
        FROM {$inventory_status_history} ish
        JOIN (
            SELECT inventory_unit_id, MAX(id) AS latest_id
            FROM {$inventory_status_history}
            WHERE created_at <= %s
            GROUP BY inventory_unit_id
        ) latest
            ON latest.latest_id = ish.id
            WHERE 1=1 {$status_subquery_where}
    ) latest_status
        ON latest_status.inventory_unit_id = i.id

    LEFT JOIN (SELECT
            ish.inventory_unit_id,
            JSON_ARRAYAGG(
                JSON_OBJECT(
                    'from_status', ish.from_status,
                    'to_status', ish.to_status,
                    'reference_num', ish.reference_num,
                    'date', ish.created_at,
                    'customer_id', p.customer_id,
                    'salesperson_id', p.salesperson_id,
                    'customer_name', CONCAT(c.first_name, ' ', c.last_name),
                    'salesperson_name', CONCAT(s.first_name, ' ', s.last_name)
                )
            ) AS events
        FROM (
            SELECT
                inventory_unit_id,
                from_status,
                to_status,
                reference_num,
                created_at
            FROM {$inventory_status_history}
            ORDER BY inventory_unit_id, created_at
        ) AS ish
        
    -- Get FIRST payment per reference_num
    LEFT JOIN (
        SELECT 
            reference_num,
            customer_id,
            salesperson_id,
            ROW_NUMBER() OVER (PARTITION BY reference_num ) AS rn
        FROM {$payments_table}
    ) p ON p.reference_num = ish.reference_num AND p.rn = 1
    -- Get customer name
    LEFT JOIN {$customers_table} c ON c.id = p.customer_id
    -- Get salesperson name
    LEFT JOIN {$salespeople_table} s ON s.id = p.salesperson_id
    GROUP BY ish.inventory_unit_id) status_events 
    ON status_events.inventory_unit_id = i.id
    WHERE 1=1 {$where_clause}
    LIMIT 200
    ";

    $all_params = array_merge(
        [$end_date],                    // latest_status subquery: created_at <= %s
        $status_params,                 // dynamic status filter params
        $params                         // search/location/brand filters
    );

    $results = $wpdb->get_results($wpdb->prepare($query, $all_params));
    $results['start_date'] = $start_date;
    $results['end_date'] = $end_date;
    $results['status'] = $status;
    return $results;
}

function reports_render_inventory_report($results)
{
    if (!$results) {
        echo '<p>No inventory reports found for this store.</p>';
        return;
    }

    $missing_count = 0;
    $total_count = 0;
    $total_cost_price = 0;
    $total_retail_price = 0;

    $store_locations = mji_get_locations();
    $location_obj = array_find($store_locations, fn($loc) => $loc->id == intval($_GET['location']));
    $location_name = $location_obj ? $location_obj->name : 'All Location';

    $start_date = $results['start_date'];
    $end_date   = $results['end_date'];

    echo '<div style="max-height:700px; overflow-y:auto; position:relative;">';
    echo '<button id="exportInventory" class="button button-primary" style="margin-bottom:10px;">Export to CSV</button>';
    echo '<button id="printInventory" class="button button-secondary" style="margin-bottom:10px;">Print Report</button>';

    echo '<div id="report">';
    echo '<header>';
    echo '<h2>Inventory Report for ' . esc_html($location_name) . ' - Montecristo Jewellers</h2>';
    echo '<p>From ' . esc_html($start_date) . ' to ' . esc_html($end_date) . '</p>';
    echo '</header>';

    echo '<table id="inventoryTable" class="widefat striped">';
    echo '<thead>
            <tr>
                <th>Image</th>
                <th>Product</th>
                <th>SKU / Model / Serial</th>
                <th>Status</th>
                <th>Cost Price</th>
                <th>Retail Price</th>
                <th>Info</th>
            </tr>
          </thead>';
    echo '<tbody>';

    foreach ($results as $row) {

        if (!is_object($row)) continue;

        $product_id = $row->wc_product_variant_id ?: $row->wc_product_id;
        $product    = wc_get_product($product_id);

        if (!$product) {
            $missing_count++;
            continue;
        }

        $events       = json_decode($row->events);
        $sku          = $row->sku;
        $model        = $row->model_name ?: '';
        $serial       = $row->serial ?: '';
        $product_status = $row->latest_status ?: '';
        $cost_price   = (float) $row->cost_price;
        $retail_price = (float) $row->retail_price;

        $total_count++;
        $total_cost_price   += $cost_price;
        $total_retail_price += $retail_price;

        $desc = $row->wc_product_variant_id ? $product->get_description() : $product->get_short_description();

        $image_id = $product->get_image_id();
        $image_url = $image_id
            ? wp_get_attachment_image_url($image_id, 'woocommerce_gallery_thumbnail')
            : wc_placeholder_img_src('woocommerce_gallery_thumbnail');

        echo '<tr>';
        echo '<td><img style="height:150px; width:150px; object-fit:cover;" src="' . esc_url($image_url) . '" alt="' . esc_attr($product->get_name()) . '"></td>';
        echo '<td>' . nl2br(esc_html($desc)) . '</td>';
        echo '<td>' . esc_html($sku) . '<br>' . esc_html($model) . '<br>' . esc_html($serial) . '</td>';
        echo '<td>' . esc_html($product_status) . '</td>';
        echo '<td>' . number_format($cost_price, 2) . '</td>';
        echo '<td>' . number_format($retail_price, 2) . '</td>';
        echo '<td style="white-space:nowrap;">';

        if ($events) {
            echo '<ul style="margin:0; padding:0; list-style:none;">';

            foreach ($events as $event) {
                $updated_date = date('Y-m-d', strtotime($event->date));

                $status_label = '';
                switch ($event->to_status) {

                    case 'in_stock':
                        $status_label = !empty($event->reference_num) ? 'Returned' : 'In Stock';
                        break;
                    case 'sold':
                        $status_label = 'Sold';
                        break;
                    case 'damage':
                        $status_label = 'Damaged';
                        break;
                    case 'defective':
                        $status_label = 'Defective';
                        break;
                    case 'rtv':
                        $status_label = 'RTV';
                        break;
                    case 'dismantled':
                        $status_label = 'Dismantled';
                        break;
                    default:
                        $status_label = ucfirst($event->to_status);
                }

                echo '<li style="margin-bottom:8px;">';
                echo '<span style="font-weight:bold;">' . esc_html($status_label) . '</span> on ' . esc_html($updated_date);

                if (!empty($event->reference_num)) {
                    echo '<br>' . esc_html($event->customer_name);
                    echo '<br>by ' . esc_html($event->salesperson_name);
                    echo '<br>Reference # ' . esc_html($event->reference_num);
                }

                echo '</li>';
            }

            echo '</ul>';
        }

        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody>';

    // Table footer with totals
    echo '<tfoot>
            <tr style="font-weight:bold; position:sticky; bottom:0; background:#fff; box-shadow:0 -2px 5px rgba(0,0,0,0.1);">
                <td colspan="5">Total (' . $total_count . ' items)</td>
                <td>' . number_format($total_cost_price, 2) . '</td>
                <td>' . number_format($total_retail_price, 2) . '</td>
            </tr>
          </tfoot>';

    echo '</table>';
    echo '</div>'; // end report div

    // Missing products notice
    if ($missing_count > 0) {
        echo '<div class="notice notice-error" style="margin-top:10px;">
                <p>Missing ' . $missing_count . ' products. Need to investigate!</p>
              </div>';
    }

    echo '</div>'; // end container
}

// Reports layaway Section
function reports_render_layaway_section()
{
    reports_render_layaway_filters();

    echo '<hr>';
    if (isset($_GET['start_date'], $_GET['end_date'])) {
        $results = reports_get_layaway_results();
        reports_render_layaway_report($results);
    }
}

function reports_render_layaway_filters()
{
?>
    <form method="get" action="">
        <input type="hidden" name="page" value="reports-management">
        <input type="hidden" name="tab" value="layaway">

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
                    $endDate =
                        isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : date("Y-m-t", strtotime("last month"));
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

function reports_get_layaway_results()
{
    global $wpdb;

    $start_raw = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
    $end_raw = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';

    $start_ts = strtotime($start_raw);
    $end_ts = strtotime($end_raw);

    if ($start_ts === false || $end_ts === false) {
        return [];
    }

    $start_date = date('Y-m-d H:i:s', $start_ts);
    $end_date = date('Y-m-d H:i:s', $end_ts);

    $salesperson = !empty($_GET['salesperson']) ? intval($_GET['salesperson']) : null;
    $location = !empty($_GET['location']) ? intval($_GET['location']) : null;

    $orders_table = "{$wpdb->prefix}mji_orders";
    $order_items = "{$wpdb->prefix}mji_order_items";
    $inventory_table = "{$wpdb->prefix}mji_product_inventory_units";
    $salespeople_table = "{$wpdb->prefix}mji_salespeople";
    $customers_table = "{$wpdb->prefix}mji_customers";
    $service_table = "{$wpdb->prefix}mji_services";
    $models_table = "{$wpdb->prefix}mji_models";

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

    $query = "
                SELECT 
                     AS invoice,
                     AS sold_date,
                     AS salesperson_first_name,
                    s.last_name AS salesperson_last_name, 
                    c.first_name AS customer_first_name,
                    c.last_name AS customer_last_name, 
                     AS product_id,
                     AS product_variant_id,
                     AS sku,
                     AS serial,
                     AS model_name,
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

    $where = ["o.created_at BETWEEN %s AND %s"];
    $params = [$start_date, $end_date];

    if ($salesperson !== null) {
        $where[] = "o.salesperson_id = %d";
        $params[] = $salesperson;
    }

    if ($location !== null) {
        $where[] = "si.location_id = %d";
        $params[] = $location;
    }

    $results = $wpdb->get_results($wpdb->prepare($query, ...$params));

    return $results;
}
function reports_render_layaway_report($results)
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
                        <h2>Sales Report - Montecristo Jewellers ' . $location . '</h2>
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
                echo '<td>' . $row->invoice . '</td>';
                echo '<td>' . $date . '</td>';
                echo '<td>' . $name . '</td>';
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
            echo '<td>' . $row->invoice . '</td>';
            echo '<td>' . $date . '</td>';
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
