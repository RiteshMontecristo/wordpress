<?php

function reports_page()
{
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'sales';
    $allowed_tabs = ['sales', 'inventory', 'layaway', 'credit', 'refund', 'financial', 'out-of-status'];

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

    $credit_url = add_query_arg(
        'tab',
        'credit',
        menu_page_url('reports-management', false)
    );

    $refund_url = add_query_arg(
        'tab',
        'refund',
        menu_page_url('reports-management', false)
    );

    $financial_url = add_query_arg(
        'tab',
        'financial',
        menu_page_url('reports-management', false)
    );

    $out_of_status_url = add_query_arg(
        'tab',
        'out-of-status',
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
            <a href="<?php echo esc_url($credit_url); ?>"
                class="nav-tab <?php echo $active_tab === 'credit' ? 'nav-tab-active' : ''; ?>">
                Credit Report
            </a>
            <a href="<?php echo esc_url($refund_url); ?>"
                class="nav-tab <?php echo $active_tab === 'refund' ? 'nav-tab-active' : ''; ?>">
                Refund Report
            </a>
            <a href="<?php echo esc_url($financial_url); ?>"
                class="nav-tab <?php echo $active_tab === 'financial' ? 'nav-tab-active' : ''; ?>">
                Financial Report
            </a>
            <a href="<?php echo esc_url($out_of_status_url); ?>"
                class="nav-tab <?php echo $active_tab === 'out-of-status' ? 'nav-tab-active' : ''; ?>">
                Out of status Report
            </a>
        </h2>

        <?php
        if ($active_tab === 'sales') {
            reports_render_sales_section();
        } elseif ($active_tab === 'inventory') {
            reports_render_inventory_section();
        } elseif ($active_tab === 'layaway') {
            reports_render_layaway_section();
        } elseif ($active_tab === 'credit') {
            reports_render_credit_section();
        } elseif ($active_tab === 'refund') {
            reports_render_refund_section();
        } elseif ($active_tab === 'financial') {
            reports_render_financial_section();
        } elseif ($active_tab === 'out-of-status') {
            reports_render_out_of_status_section();
        }
        ?>
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
            <tr>
                <th scope="row"><label for="brands">Brand</label></th>
                <td>
                    <?= mji_brands_dropdown(false) ?>
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
    $brand = !empty($_GET['brands']) ? intval($_GET['brands']) : null;

    $orders_table = $wpdb->prefix . 'mji_orders';
    $order_items = $wpdb->prefix . 'mji_order_items';
    $inventory_table = $wpdb->prefix . 'mji_product_inventory_units';
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';
    $customers_table = $wpdb->prefix . 'mji_customers';
    $service_table = $wpdb->prefix . 'mji_services';
    $models_table = $wpdb->prefix . 'mji_models';
    $brands_table = $wpdb->prefix . 'mji_brands';
    $returns_table = $wpdb->prefix . 'mji_returns';
    $return_items_table = $wpdb->prefix . 'mji_return_items';
    $return_services_table = $wpdb->prefix . 'mji_return_services';

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

    if ($brand !== null) {
        $where1[] = "pi.brand_id = %d";
        $params1[] = $brand;
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
                    pi.brand_id,
                    COALESCE(oi.sale_price, 0) AS retail_paid,
                    COALESCE(oi.discount_amount, 0) AS discount_amount,
                    COALESCE(pi.cost_price, 0) AS cost_price,
                    COALESCE(pi.retail_price, 0) AS retail_price,
                    NULL AS description,
                    CASE 
                        WHEN COUNT(retn.id) > 0 
                        THEN JSON_ARRAYAGG(
                            JSON_OBJECT(
                                'reference_num', retn.reference_num,
                                'refund_amount', retn.unit_price
                            )
                        )
                        ELSE NULL
                    END AS returns
                FROM $orders_table o
                INNER JOIN $order_items oi ON o.id = oi.order_id
                INNER JOIN $inventory_table pi ON oi.product_inventory_unit_id = pi.id
                INNER JOIN $salespeople_table s ON o.salesperson_id = s.id
                INNER JOIN $customers_table c ON o.customer_id = c.id
                LEFT JOIN $models_table m on m.id = pi.model_id
                LEFT JOIN $brands_table b on b.id = pi.brand_id
                LEFT JOIN (
                    SELECT r.id, r.order_id, r.reference_num, ri.unit_price
                    FROM $returns_table r
                    JOIN $return_items_table ri ON ri.return_id = r.id
                ) retn ON retn.order_id = o.id
                WHERE " . implode(" AND ", $where1) . "
                GROUP BY
                    o.id,
                    oi.id,
                    pi.id,
                    s.id,
                    c.id,
                    m.id
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
                    NULL AS model_name,
                    NULL AS location_id,
                    NULL AS brand_id,
                    COALESCE(si.sold_price, 0) as retail_paid,
                    COALESCE(0, 0) as discount_amount,
                    COALESCE(si.cost_price, 0) as cost_price,
                    COALESCE(si.sold_price, 0) as retail_price,
                    si.description AS description,
                    CASE 
                        WHEN COUNT(retn.id) > 0
                        THEN JSON_ARRAYAGG(
                            JSON_OBJECT(
                                'reference_num', retn.reference_num,
                                'refund_amount', retn.price
                            )
                        )
                        ELSE NULL
                    END AS returns
                FROM $orders_table o
                INNER JOIN $service_table si ON si.order_id = o.id
                INNER JOIN $salespeople_table s ON o.salesperson_id = s.id
                INNER JOIN $customers_table c ON o.customer_id = c.id
                 LEFT JOIN (
                    SELECT r.id, r.order_id, r.reference_num, rs.price
                    FROM $returns_table r
                    JOIN $return_services_table rs ON rs.return_id = r.id
                ) retn ON retn.order_id = o.id
                WHERE " . implode(" AND ", $where2) . "
                GROUP BY
                    o.id,
                    s.id,
                    c.id
            ";


    if ($brand !== null) {
        $results = $wpdb->get_results($wpdb->prepare($query1, $params1));
    } else {
        $query = "
        ($query1)
        UNION ALL
        ($query2)
        ORDER BY sold_date ASC
    ";

        $params = array_merge($params1, $params2);
        $results = $wpdb->get_results($wpdb->prepare($query, ...$params));
    }
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
                        <th>Model</th>
                        <th>Serial</th>
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

            // Build the invoice display with returns
            $invoice_display = esc_html($row->invoice);
            $total_current_retail_paid = $row->retail_paid;
            $retail_paid_display = "$" . number_format($row->retail_paid, 2);

            if (!empty($row->returns) && $row->returns !== 'null') {
                $returns = json_decode($row->returns);

                if (json_last_error() === JSON_ERROR_NONE && is_array($returns) && count($returns) > 0) {

                    foreach ($returns as $return) {
                        if (!empty($return->reference_num)) {
                            $invoice_display .= '<br />-' . esc_html($return->reference_num);
                        }
                        if (!empty($return->refund_amount)) {
                            $retail_paid_display .= '<br />-$' . number_format($return->refund_amount, 2);
                            $total_current_retail_paid -= $return->refund_amount;
                        }
                    }

                    $invoice_display .= '</small>';
                }
            }

            $discount_percent = 0;
            $profit = 0;
            $margin_percent = 0;

            if ($total_current_retail_paid > 0) {
                $discount_percent = $row->retail_price ? ($row->discount_amount / $row->retail_price) * 100 : 0;
                $profit = $total_current_retail_paid - $row->cost_price;
                $margin_percent = $total_current_retail_paid ? ($profit / $total_current_retail_paid) * 100 : 0;

                // Calculate totals
                $total_cost += $row->cost_price;
                $total_retail += $row->retail_price;
                $total_retail_paid += $total_current_retail_paid;
                $total_profit += $profit;
            }
            if (!$product) {
                echo '<tr>';
                echo '<td>' . $placeholder_image . '</td>';
                echo '<td>' . $invoice_display . '</td>';
                echo '<td>' . $date . '</td>';
                echo '<td>' . $name . '</td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td>' . number_format($row->cost_price, 2) . '</td>';
                echo '<td>' . number_format($row->retail_price, 2) . '</td>';
                echo '<td>' . $retail_paid_display . '</td>';
                echo '<td>' . number_format($row->discount_amount, 2) . '</td>';
                echo '<td>' . number_format(0, 2) . '%</td>';
                echo '<td>' . number_format($profit, 2) . '</td>';
                echo '<td>' . number_format($margin_percent, 2) . '%</td>';
                echo '<td>' . esc_html($row->salesperson_first_name) . ' ' . esc_html($row->salesperson_last_name) . '</td>';
                echo '<td>' . esc_html($row->customer_first_name) . ' ' . esc_html($row->customer_last_name) . '</td>';
                echo '</tr>';
                continue; // Skip invalid products
            }

            $image = $product->get_image([50, 50]);
            $name = $product->get_name();

            if ($product->is_type('variation')) {
                $parent = wc_get_product($product->get_parent_id());
                if ($parent) {
                    $name = $parent->get_name() . ' - ' . wc_get_formatted_variation($product, true);
                }
            }

            echo '<tr>';
            echo '<td>' . $image . '</td>';
            echo '<td style="white-space: nowrap;">' . $invoice_display . '</td>';
            echo '<td style="white-space: nowrap;">' . $date . '</td>';
            echo '<td>' . $name . '</td>';
            echo '<td>' . $row->sku . '</td>';
            echo '<td>' . $row->model_name  . '</td>';
            echo '<td>' . $row->serial . '</td>';
            echo '<td>$' . number_format($row->cost_price, 2) . '</td>';
            echo '<td>$' . number_format($row->retail_price, 2) . '</td>';
            echo '<td>' . $retail_paid_display . '</td>';
            echo '<td>$' . number_format($row->discount_amount, 2) . '</td>';
            echo '<td>' . number_format($discount_percent, 2) . '%</td>';
            echo '<td>' . number_format($profit, 2) . '</td>';
            echo '<td>' . number_format($margin_percent, 2) . '%</td>';
            echo '<td>' . esc_html($row->salesperson_first_name) . ' ' . esc_html($row->salesperson_last_name) . '</td>';
            echo '<td>' . esc_html($row->customer_first_name) . ' ' . esc_html($row->customer_last_name) . '</td>';
            echo '</tr>';
        }

        $margin_percent = ($total_profit / $total_retail_paid) * 100;
        echo '
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total Cost: $' . number_format($total_cost, 2) . '</th>
                        <th>Total Retail: $' . number_format($total_retail, 2) . '</th>
                        <th>Total Paid: $' . number_format($total_retail_paid, 2) . '</th>
                        <th>Total Profit: $' . number_format($total_profit, 2) . '</th>
                        <th>Margin: ' . number_format($margin_percent, 2) . '%</th>
                        <th> </th>
                    </tr>
            </table>
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
    $products_collections_table = $wpdb->prefix . 'mji_products_collections';
    $collections_table = $wpdb->prefix . 'mji_collections';
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
        $where[] = "(i.sku LIKE %s OR i.serial LIKE %s OR m.name LIKE %s OR collections.collections LIKE %s)";
        $params[] = $like;
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
    -- Join collections
    LEFT JOIN (
        SELECT
            pc.product_id,
            GROUP_CONCAT(c.name) as collections
        FROM {$products_collections_table} pc
        JOIN {$collections_table} c
            ON c.id = pc.collection_id
        GROUP BY pc.product_id
    ) collections
    ON collections.product_id = i.wc_product_id

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

    LEFT JOIN (
        SELECT
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
        GROUP BY ish.inventory_unit_id
    ) status_events 
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
    $end_date = $results['end_date'];

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
                <th>SKU</th>
                <th>Model</th>
                <th>Serial</th>
                <th>Status</th>
                <th>Cost Price</th>
                <th>Retail Price</th>
                <th>Info</th>
            </tr>
          </thead>';
    echo '<tbody>';

    foreach ($results as $row) {

        if (!is_object($row))
            continue;

        $product_id = $row->wc_product_variant_id ?: $row->wc_product_id;
        $product = wc_get_product($product_id);

        if (!$product) {
            $missing_count++;
            continue;
        }

        $events = json_decode($row->events);
        $sku = $row->sku;
        $model = $row->model_name ?: '';
        $serial = $row->serial ?: '';
        $product_status = $row->latest_status ?: '';
        $cost_price = (float) $row->cost_price;
        $retail_price = (float) $row->retail_price;

        $total_count++;
        $total_cost_price += $cost_price;
        $total_retail_price += $retail_price;

        $desc = $row->wc_product_variant_id ? $product->get_description() : $product->get_short_description();

        $image_id = $product->get_image_id();
        $image_url = $image_id
            ? wp_get_attachment_image_url($image_id, 'woocommerce_gallery_thumbnail')
            : wc_placeholder_img_src('woocommerce_gallery_thumbnail');

        echo '<tr>';
        echo '<td><img style="height:150px; width:150px; object-fit:cover;" src="' . esc_url($image_url) . '" alt="' . esc_attr($product->get_name()) . '"></td>';
        echo '<td>' . nl2br(esc_html($desc)) . '</td>';
        echo '<td>' . esc_html($sku) . '</td>';
        echo '<td>' . esc_html($model) . '</td>';
        echo '<td>' . esc_html($serial) . '</td>';
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
                <td colspan="7">Total (' . $total_count . ' items)</td>
                <td>Total Cost ' . number_format($total_cost_price, 2) . '</td>
                <td>Total Retail ' . number_format($total_retail_price, 2) . '</td>
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
{ ?>
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
                <th scope="row"><label for="location">Store</label></th>
                <td>
                    <?= mji_store_dropdown(false) ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="query">Query</label></th>
                <td>
                    <select name="query" id="query">
                        <option value="deposit">Deposit</option>
                        <option value="redeem">Redeem</option>
                        <option value="outstanding">Outstanding</option>
                    </select>
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

    $location = !empty($_GET['location']) ? intval($_GET['location']) : null;
    $query = !empty($_GET['query']) ? $_GET['query'] : 'depost';

    $payments_table = "{$wpdb->prefix}mji_payments";
    $customers_table = "{$wpdb->prefix}mji_customers";
    $salespeople_table = "{$wpdb->prefix}mji_salespeople";
    $layaways_table = "{$wpdb->prefix}mji_layaways";

    $where = [];
    $params = [];

    if ($location !== null) {
        $where[] = "location_id = %d";
        $params[] = $location;
    }

    if ($query == 'deposit') {
        $where[] = "p.payment_date BETWEEN %s AND %s AND p.transaction_type IN ('layaway_deposit') AND p.layaway_id IS NOT NULL";
        $params[] = [$start_date, $end_date];
        $sql_query = "
            SELECT 
               p.reference_num, p.transaction_type, p.payment_date, p.notes, c.first_name, c.last_name, s.first_name as salesperson_first_name, s.last_name as salesperson_last_name, 
                JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'method', p.method,
                        'amount', p.amount
                    )
                ) AS payment_details
            FROM {$payments_table} p
            JOIN {$customers_table} c ON p.customer_id = c.id
            JOIN {$salespeople_table} s ON p.salesperson_id = s.id
            JOIN {$layaways_table} l on l.id = p.layaway_id
            WHERE " . implode(" AND ", $where) . "
            GROUP BY p.reference_num, p.layaway_id, p.transaction_type, p.payment_date, p.notes, c.first_name, c.last_name, s.first_name, s.last_name
            ORDER BY p.reference_num, p.layaway_id";
    } else if ($query == 'redeem') {
        $where[] = "p.payment_date BETWEEN %s AND %s AND p.transaction_type IN ('layaway_redemption') AND p.layaway_id IS NOT NULL";
        $params[] = [$start_date, $end_date];
        $sql_query = "
            -- Pre-aggregate deposit payments per layaway
            WITH deposit_payments AS (
                SELECT 
                    layaway_id,
                    JSON_ARRAYAGG(JSON_OBJECT(
                        'method', method,
                        'amount', amount
                    )) AS deposit_payment_details
                FROM {$payments_table}
                WHERE transaction_type = 'layaway_deposit'
                GROUP BY layaway_id
            )
            SELECT 
				p.reference_num, p.transaction_type, p.payment_date, p.notes, c.first_name, c.last_name, s.first_name as salesperson_first_name, s.last_name as salesperson_last_name, l.reference_num as layaway_reference_num, l.created_at as layaway_date,
                JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'method', p.method,
                        'amount', p.amount
                    )
                ) AS payment_details,
                -- Layaway deposit payment details
                dp.deposit_payment_details
            FROM {$payments_table} p
            JOIN {$customers_table} c ON p.customer_id = c.id
            JOIN {$salespeople_table} s ON p.salesperson_id = s.id
            JOIN {$layaways_table} l ON l.id = p.layaway_id
            LEFT JOIN deposit_payments dp ON dp.layaway_id = l.id 
            WHERE  " . implode(" AND ", $where) . "
            GROUP BY p.reference_num, p.layaway_id, p.transaction_type, p.payment_date, p.notes, c.first_name, c.last_name, s.first_name, s.last_name, dp.deposit_payment_details
            ORDER BY p.reference_num, p.layaway_id";
    } else {
        $where[] = "p.payment_date < %s AND p.layaway_id IS NOT NULL AND l.remaining_amount > 0 AND p.transaction_type = 'layaway_deposit'";
        $params[] = $end_date;
        $sql_query = "
            WITH redeem_payments AS (
                SELECT layaway_id,
                JSON_ARRAYAGG(JSON_OBJECT(
                    'reference_num', reference_num,
                    'method', method,
                    'amount', amount,
                    'date', payment_date
                )) AS redeem_payment_details
                FROM wp_mji_payments
                WHERE transaction_type = 'layaway_redemption'
                GROUP BY layaway_id
            )

            SELECT 
               p.reference_num, p.transaction_type, p.payment_date, p.notes, c.first_name, c.last_name, s.first_name as salesperson_first_name, s.last_name as salesperson_last_name, l.remaining_amount,
                JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'method', p.method,
                        'amount', p.amount
                    )
                ) AS payment_details, dp.redeem_payment_details
            FROM {$payments_table} p
            JOIN {$customers_table} c ON p.customer_id = c.id
            JOIN {$salespeople_table} s ON p.salesperson_id = s.id
            JOIN {$layaways_table} l on l.id = p.layaway_id
            LEFT JOIN redeem_payments dp ON dp.layaway_id = l.id
            WHERE " . implode(" AND ", $where) . "
            GROUP BY p.reference_num, p.layaway_id, p.transaction_type, p.payment_date, p.notes, c.first_name, c.last_name, s.first_name, s.last_name, dp.redeem_payment_details
            ORDER BY p.layaway_id";
    }

    $results = $wpdb->get_results($wpdb->prepare($sql_query, ...$params));

    $results['start_date'] = $start_date;
    $results['end_date'] = $end_date;
    $results['status'] = $query;
    $results['location'] = $location;
    return $results;
}

function reports_render_layaway_report($results)
{
    if ($results) {
        $store_locations = mji_get_locations();
        $location_obj = array_find($store_locations, fn($loc) => $loc->id == intval($_GET['location']));
        $location_name = $location_obj ? $location_obj->name : 'All Location';

        $start_date = explode(" ", $results['start_date'])[0];
        $end_date = explode(" ", $results['end_date'])[0];
        $status = $results['status'];

        echo '<div style="max-height:700px; overflow-y:auto; position:relative;">';
        echo '<button id="exportInventory" class="button button-primary" style="margin-bottom:10px;">Export to CSV</button>';
        echo '<button id="printInventory" class="button button-secondary" style="margin-bottom:10px;">Print Report</button>';

        echo '<div id="report">';
        echo '<header>';
        echo '<h2>' . ucfirst(esc_html($status)) . ' Layaway Report for ' . esc_html($location_name) . ' - Montecristo Jewellers</h2>';
        echo '<p>From ' . esc_html($start_date) . ' to ' . esc_html($end_date) . '</p>';
        echo '</header>';

        echo '<table id="inventoryTable" class="widefat ">';

        if ($status == "deposit") {
            echo '<thead>
            <tr>
                <th>Invoice</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Salesperson</th>
                <th>Method</th>
                <th>Amount</th>
                <th>Notes</th>
            </tr>
          </thead>';
            echo '<tbody>';
            foreach ($results as $index => $row) {

                if (!is_object($row))
                    continue;
                $payments = json_decode($row->payment_details, true);
                $rowspan = count($payments);
                $first = true;
                $payment_date = explode(" ", $row->payment_date)[0];
                foreach ($payments as $payment) {

                    $isLast = ($index % 2 == 0) ? "group-end" : "";
                    echo "<tr class='{$isLast}'>";

                    if ($first) {
                        echo "<td rowspan='{$rowspan}'>{$row->reference_num}</td>";
                        echo "<td rowspan='{$rowspan}'>{$payment_date}</td>";
                        echo "<td rowspan='{$rowspan}'>{$row->first_name} {$row->last_name}</td>";
                        echo "<td rowspan='{$rowspan}'>{$row->salesperson_first_name} {$row->salesperson_last_name}</td>";
                    }

                    // Payment-specific columns (always printed)
                    echo "<td>{$payment['method']}</td>";
                    echo "<td>{$payment['amount']}</td>";

                    if ($first) {
                        echo "<td rowspan='{$rowspan}'>{$row->notes}</td>";
                        $first = false;
                    }
                    echo "</tr>";
                }
            }
        } elseif ($status == "redeem") {

            echo '<thead>
                <tr>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Salesperson</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Deposit Invoice</th>
                    <th>Deposit Date</th>
                    <th>Deposit Method</th>
                    <th>Deposit Amount</th>
                    <th>Notes</th>
                </tr>
              </thead>';
            echo '<tbody>';
            foreach ($results as $index => $row) {

                if (!is_object($row))
                    continue;
                $payments = json_decode($row->payment_details, true);
                $deposit_payment_details = json_decode($row->deposit_payment_details, true);
                $rowspan = max(count($payments), count($deposit_payment_details));
                $isLast = ($index % 2 == 0) ? "group-end" : "";

                for ($i = 0; $i < $rowspan; $i++) {
                    echo "<tr class='{$isLast}'>";

                    // Ensuring it covers multiple rowspan if we have tons of payment deposits
                    if ($i === 0) {
                        echo "<td rowspan='{$rowspan}'>{$row->reference_num}</td>";
                        echo "<td rowspan='{$rowspan}'>" . explode(" ", $row->payment_date)[0] . "</td>";
                        echo "<td rowspan='{$rowspan}'>{$row->first_name} {$row->last_name}</td>";
                        echo "<td rowspan='{$rowspan}'>{$row->salesperson_first_name} {$row->salesperson_last_name}</td>";
                        echo "<td rowspan='{$rowspan}'>{$payments[$i]['method']}</td>";
                        echo "<td rowspan='{$rowspan}'>{$payments[$i]['amount']}</td>";
                    } else {
                        echo "<td>" . ($payments[$i]['method'] ?? '') . "</td>";
                        echo "<td>" . ($payments[$i]['amount'] ?? '') . "</td>";
                    }
                    if ($i === 0) {
                        echo "<td>{$row->layaway_reference_num}</td>";
                        echo "<td>" . explode(" ", $row->layaway_date)[0] . "</td>";
                        echo "<td>{$deposit_payment_details[$i]['method']}</td>";
                        echo "<td>{$deposit_payment_details[$i]['amount']}</td>";
                    } else {
                        echo "<td>" . ($deposit_payment_details[$i]['method'] ?? '') . "</td>";
                        echo "<td>" . ($deposit_payment_details[$i]['amount'] ?? '') . "</td>";
                    }

                    if ($i === 0) {
                        echo "<td rowspan='{$rowspan}'>{$row->notes}</td>";
                    }
                    echo '</tr>';
                }
            }
        } else {
            echo '<thead>
                <tr>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Salesperson</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Redeemed Invoice</th>
                    <th>Redeemed Date</th>
                    <th>Redeemed Amount</th>
                    <th>Notes</th>
                </tr>
              </thead>';
            echo '<tbody>';
            foreach ($results as $index => $row) {

                if (!is_object($row))
                    continue;
                $payments = json_decode($row->payment_details, true);
                $redeem_payment_details = json_decode($row->redeem_payment_details, true);
                $redeem_payment_details_len = is_empty($redeem_payment_details) ? 0 : count($redeem_payment_details);
                $rowspan = max(count($payments), $redeem_payment_details_len);
                custom_log($row);
                $isLast = ($index % 2 == 0) ? "group-end" : "";

                for ($i = 0; $i < $rowspan; $i++) {
                    echo "<tr class='{$isLast}'>";

                    // Ensuring it covers multiple rowspan if we have tons of payment deposits
                    if ($i === 0) {
                        echo "<td rowspan='{$rowspan}'>{$row->reference_num}</td>";
                        echo "<td rowspan='{$rowspan}'>" . explode(" ", $row->payment_date)[0] . "</td>";
                        echo "<td rowspan='{$rowspan}'>{$row->first_name} {$row->last_name}</td>";
                        echo "<td rowspan='{$rowspan}'>{$row->salesperson_first_name} {$row->salesperson_last_name}</td>";
                        echo "<td >{$payments[$i]['method']}</td>";
                        echo "<td >{$payments[$i]['amount']}</td>";
                    } else {
                        echo "<td>" . ($payments[$i]['method'] ?? '') . "</td>";
                        echo "<td>" . ($payments[$i]['amount'] ?? '') . "</td>";
                    }

                    if ($redeem_payment_details_len > $i) {
                        echo "<td>" . ($redeem_payment_details[$i]['reference_num'] ?? '') . "</td>";
                        echo "<td>" . explode(" ", $redeem_payment_details[$i]['date'])[0] . "</td>";
                        echo "<td>" . ($redeem_payment_details[$i]['amount'] ?? '') . "</td>";
                    } else {
                        echo "<td></td><td></td><td></td>";
                    }
                    if ($i === 0) {
                        echo "<td rowspan='{$rowspan}'>{$row->notes}</td>";
                    }
                    echo '</tr>';
                }
            }
        }

        echo '</table>';
        echo '</div>';
    }
}

// Reports Credit Section
function reports_render_credit_section()
{
    reports_render_credit_filters();

    echo '<hr>';
    if (isset($_GET['start_date'], $_GET['end_date'])) {
        $results = reports_get_credit_results();
        reports_render_credit_report($results);
    }
}

function reports_render_credit_filters()
{
?>
    <form method="get" action="">
        <input type="hidden" name="page" value="reports-management">
        <input type="hidden" name="tab" value="credit">

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
                    <input type="date" name="start_date" id="start_date" value="<?= $startDate; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="end_date">End Date</label></th>
                <td>
                    <?php
                    $endDate =
                        isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : date("Y-m-t", strtotime("last month"));
                    ?>
                    <input type="date" name="end_date" id="end_date" value="<?= $endDate; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="location">Store</label></th>
                <td>
                    <?= mji_store_dropdown(false) ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="query">Query</label></th>
                <td>
                    <select name="query" id="query">
                        <option value="deposit">Deposit</option>
                        <option value="redeem">Redeem</option>
                        <option value="outstanding">Outstanding</option>
                    </select>
                </td>
            </tr>
        </table>

        <?php submit_button('Generate Report'); ?>
    </form>
<?php
}

function reports_get_credit_results()
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

    $location = !empty($_GET['location']) ? intval($_GET['location']) : null;
    $query = !empty($_GET['query']) ? $_GET['query'] : 'deposit';

    $payments_table = "{$wpdb->prefix}mji_payments";
    $customers_table = "{$wpdb->prefix}mji_customers";
    $salespeople_table = "{$wpdb->prefix}mji_salespeople";
    $credits_table = "{$wpdb->prefix}mji_credits";

    $where = [];
    $params = [];

    if ($location !== null) {
        $where[] = "location_id = %d";
        $params[] = $location;
    }

    if ($query == 'deposit') {
        $where[] = "p.payment_date BETWEEN %s AND %s AND p.transaction_type IN ('credit_deposit') AND p.credit_id IS NOT NULL";
        $params[] = [$start_date, $end_date];
        $sql_query = "
            SELECT 
               p.reference_num, p.transaction_type, p.payment_date, p.notes, c.first_name, c.last_name, s.first_name as salesperson_first_name, s.last_name as salesperson_last_name, 
                JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'method', p.method,
                        'amount', p.amount
                    )
                ) AS payment_details
            FROM {$payments_table} p
            JOIN {$customers_table} c ON p.customer_id = c.id
            JOIN {$salespeople_table} s ON p.salesperson_id = s.id
            JOIN {$credits_table} cr on cr.id = p.credit_id
            WHERE " . implode(" AND ", $where) . "
            GROUP BY p.reference_num, p.credit_id, p.transaction_type, p.payment_date, p.notes, c.first_name, c.last_name, s.first_name, s.last_name
            ORDER BY p.reference_num, p.credit_id";
    } else if ($query == 'redeem') {
        $where[] = "p.payment_date BETWEEN %s AND %s AND p.transaction_type IN ('credit_redemption') AND p.credit_id IS NOT NULL";
        $params[] = [$start_date, $end_date];
        $sql_query = "
            -- Pre-aggregate deposit payments per layaway
            WITH deposit_payments AS (
                SELECT 
                    credit_id,
                    JSON_ARRAYAGG(JSON_OBJECT(
                        'method', method,
                        'amount', amount
                    )) AS deposit_payment_details
                FROM {$payments_table}
                WHERE transaction_type = 'credit_deposit'
                GROUP BY credit_id
            )
            SELECT 
				p.reference_num, p.transaction_type, p.payment_date, p.notes, c.first_name, c.last_name, s.first_name as salesperson_first_name, s.last_name as salesperson_last_name, cr.reference_num as credit_reference_num, cr.created_at as credit_date,
                JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'method', p.method,
                        'amount', p.amount
                    )
                ) AS payment_details,
                -- Credit deposit payment details
                dp.deposit_payment_details
            FROM {$payments_table} p
            JOIN {$customers_table} c ON p.customer_id = c.id
            JOIN {$salespeople_table} s ON p.salesperson_id = s.id
            JOIN {$credits_table} cr ON cr.id = p.credit_id
            LEFT JOIN deposit_payments dp ON dp.credit_id = cr.id 
            WHERE  " . implode(" AND ", $where) . "
            GROUP BY p.reference_num, p.credit_id, p.transaction_type, p.payment_date, p.notes, c.first_name, c.last_name, s.first_name, s.last_name, dp.deposit_payment_details
            ORDER BY p.reference_num, p.credit_id";
    } else {
        $where[] = "p.payment_date < %s AND p.credit_id IS NOT NULL AND cr.remaining_amount > 0 AND p.transaction_type = 'credit_deposit'";
        $params[] = $end_date;
        $sql_query = "
            WITH redeem_payments AS (
                SELECT credit_id,
                JSON_ARRAYAGG(JSON_OBJECT(
                    'reference_num', reference_num,
                    'method', method,
                    'amount', amount,
                    'date', payment_date
                )) AS redeem_payment_details
                FROM wp_mji_payments
                WHERE transaction_type = 'credit_redemption'
                GROUP BY credit_id
            )

            SELECT 
               p.reference_num, p.transaction_type, p.payment_date, p.notes, c.first_name, c.last_name, s.first_name as salesperson_first_name, s.last_name as salesperson_last_name, cr.remaining_amount,
                JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'method', p.method,
                        'amount', p.amount
                    )
                ) AS payment_details, dp.redeem_payment_details
            FROM {$payments_table} p
            JOIN {$customers_table} c ON p.customer_id = c.id
            JOIN {$salespeople_table} s ON p.salesperson_id = s.id
            JOIN {$credits_table} cr on cr.id = p.credit_id
            LEFT JOIN redeem_payments dp ON dp.credit_id = cr.id
            WHERE " . implode(" AND ", $where) . "
            GROUP BY p.reference_num, p.layaway_id, p.transaction_type, p.payment_date, p.notes, c.first_name, c.last_name, s.first_name, s.last_name, dp.redeem_payment_details
            ORDER BY p.credit_id";
    }

    $results = $wpdb->get_results($wpdb->prepare($sql_query, ...$params));

    $results['start_date'] = $start_date;
    $results['end_date'] = $end_date;
    $results['status'] = $query;
    $results['location'] = $location;
    return $results;
}

function reports_render_credit_report($results)
{
    if ($results) {
        $store_locations = mji_get_locations();
        $location_obj = array_find($store_locations, fn($loc) => $loc->id == intval($_GET['location']));
        $location_name = $location_obj ? $location_obj->name : 'All Location';

        $start_date = explode(" ", $results['start_date'])[0];
        $end_date = explode(" ", $results['end_date'])[0];
        $status = $results['status'];

        echo '<div style="max-height:700px; overflow-y:auto; position:relative;">';
        echo '<button id="exportInventory" class="button button-primary" style="margin-bottom:10px;">Export to CSV</button>';
        echo '<button id="printInventory" class="button button-secondary" style="margin-bottom:10px;">Print Report</button>';

        echo '<div id="report">';
        echo '<header>';
        echo '<h2>' . ucfirst(esc_html($status)) . ' Credit Report for ' . esc_html($location_name) . ' - Montecristo Jewellers</h2>';
        echo '<p>From ' . esc_html($start_date) . ' to ' . esc_html($end_date) . '</p>';
        echo '</header>';

        echo '<table id="inventoryTable" class="widefat ">';

        if ($status == "deposit") {
            echo '<thead>
            <tr>
                <th>Invoice</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Salesperson</th>
                <th>Method</th>
                <th>Amount</th>
                <th>Notes</th>
            </tr>
          </thead>';
            echo '<tbody>';
            foreach ($results as $index => $row) {

                if (!is_object($row))
                    continue;
                $payments = json_decode($row->payment_details, true);
                $rowspan = count($payments);
                $first = true;
                $payment_date = explode(" ", $row->payment_date)[0];
                foreach ($payments as $payment) {

                    $isLast = ($index % 2 == 0) ? "group-end" : "";
                    echo "<tr class='{$isLast}'>";

                    if ($first) {
                        echo "<td rowspan='{$rowspan}'>{$row->reference_num}</td>";
                        echo "<td rowspan='{$rowspan}'>{$payment_date}</td>";
                        echo "<td rowspan='{$rowspan}'>{$row->first_name} {$row->last_name}</td>";
                        echo "<td rowspan='{$rowspan}'>{$row->salesperson_first_name} {$row->salesperson_last_name}</td>";
                    }

                    // Payment-specific columns (always printed)
                    echo "<td>{$payment['method']}</td>";
                    echo "<td>{$payment['amount']}</td>";

                    if ($first) {
                        echo "<td rowspan='{$rowspan}'>{$row->notes}</td>";
                        $first = false;
                    }
                    echo "</tr>";
                }
            }
        } elseif ($status == "redeem") {

            echo '<thead>
                <tr>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Salesperson</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Deposit Invoice</th>
                    <th>Deposit Date</th>
                    <th>Deposit Method</th>
                    <th>Deposit Amount</th>
                    <th>Notes</th>
                </tr>
              </thead>';
            echo '<tbody>';
            foreach ($results as $index => $row) {

                if (!is_object($row))
                    continue;
                $payments = json_decode($row->payment_details, true);
                $deposit_payment_details = json_decode($row->deposit_payment_details, true);
                $rowspan = max(count($payments), count($deposit_payment_details));
                $isLast = ($index % 2 == 0) ? "group-end" : "";

                for ($i = 0; $i < $rowspan; $i++) {
                    echo "<tr class='{$isLast}'>";

                    // Ensuring it covers multiple rowspan if we have tons of payment deposits
                    if ($i === 0) {
                        echo "<td rowspan='{$rowspan}'>{$row->reference_num}</td>";
                        echo "<td rowspan='{$rowspan}'>" . explode(" ", $row->payment_date)[0] . "</td>";
                        echo "<td rowspan='{$rowspan}'>{$row->first_name} {$row->last_name}</td>";
                        echo "<td rowspan='{$rowspan}'>{$row->salesperson_first_name} {$row->salesperson_last_name}</td>";
                        echo "<td rowspan='{$rowspan}'>{$payments[$i]['method']}</td>";
                        echo "<td rowspan='{$rowspan}'>{$payments[$i]['amount']}</td>";
                    } else {
                        echo "<td>" . ($payments[$i]['method'] ?? '') . "</td>";
                        echo "<td>" . ($payments[$i]['amount'] ?? '') . "</td>";
                    }
                    if ($i === 0) {
                        echo "<td>{$row->layaway_reference_num}</td>";
                        echo "<td>" . explode(" ", $row->layaway_date)[0] . "</td>";
                        echo "<td>{$deposit_payment_details[$i]['method']}</td>";
                        echo "<td>{$deposit_payment_details[$i]['amount']}</td>";
                    } else {
                        echo "<td>" . ($deposit_payment_details[$i]['method'] ?? '') . "</td>";
                        echo "<td>" . ($deposit_payment_details[$i]['amount'] ?? '') . "</td>";
                    }

                    if ($i === 0) {
                        echo "<td rowspan='{$rowspan}'>{$row->notes}</td>";
                    }
                    echo '</tr>';
                }
            }
        } else {
            echo '<thead>
                <tr>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Salesperson</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Redeemed Invoice</th>
                    <th>Redeemed Date</th>
                    <th>Redeemed Amount</th>
                    <th>Notes</th>
                </tr>
              </thead>';
            echo '<tbody>';
            foreach ($results as $index => $row) {

                if (!is_object($row))
                    continue;
                $payments = json_decode($row->payment_details, true);
                $redeem_payment_details = json_decode($row->redeem_payment_details, true);
                $redeem_payment_details_len = is_empty($redeem_payment_details) ? 0 : count($redeem_payment_details);
                $rowspan = max(count($payments), $redeem_payment_details_len);
                $isLast = ($index % 2 == 0) ? "group-end" : "";

                for ($i = 0; $i < $rowspan; $i++) {
                    echo "<tr class='{$isLast}'>";

                    // Ensuring it covers multiple rowspan if we have tons of payment deposits
                    if ($i === 0) {
                        echo "<td rowspan='{$rowspan}'>{$row->reference_num}</td>";
                        echo "<td rowspan='{$rowspan}'>" . explode(" ", $row->payment_date)[0] . "</td>";
                        echo "<td rowspan='{$rowspan}'>{$row->first_name} {$row->last_name}</td>";
                        echo "<td rowspan='{$rowspan}'>{$row->salesperson_first_name} {$row->salesperson_last_name}</td>";
                        echo "<td >{$payments[$i]['method']}</td>";
                        echo "<td >{$payments[$i]['amount']}</td>";
                    } else {
                        echo "<td>" . ($payments[$i]['method'] ?? '') . "</td>";
                        echo "<td>" . ($payments[$i]['amount'] ?? '') . "</td>";
                    }

                    if ($redeem_payment_details_len > $i) {
                        echo "<td>" . ($redeem_payment_details[$i]['reference_num'] ?? '') . "</td>";
                        echo "<td>" . explode(" ", $redeem_payment_details[$i]['date'])[0] . "</td>";
                        echo "<td>" . ($redeem_payment_details[$i]['amount'] ?? '') . "</td>";
                    } else {
                        echo "<td></td><td></td><td></td>";
                    }
                    if ($i === 0) {
                        echo "<td rowspan='{$rowspan}'>{$row->notes}</td>";
                    }
                    echo '</tr>';
                }
            }
        }

        echo '</table>';
        echo '</div>';
    }
}

// Reports Refund Section
function reports_render_refund_section()
{
    reports_render_refund_filters();

    echo '<hr>';
    if (isset($_GET['start_date'], $_GET['end_date'])) {
        $results = reports_get_refund_results();
        reports_render_refund_report($results);
    }
}

function reports_render_refund_filters()
{
?>
    <form method="get" action="">
        <input type="hidden" name="page" value="reports-management">
        <input type="hidden" name="tab" value="refund">

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
                    <input type="date" name="start_date" id="start_date" value="<?= $startDate; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="end_date">End Date</label></th>
                <td>
                    <?php
                    $endDate =
                        isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : date("Y-m-t", strtotime("last month"));
                    ?>
                    <input type="date" name="end_date" id="end_date" value="<?= $endDate; ?>">
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

function reports_get_refund_results()
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

    $location = !empty($_GET['location']) ? intval($_GET['location']) : null;

    $payments_table = "{$wpdb->prefix}mji_payments";
    $customers_table = "{$wpdb->prefix}mji_customers";
    $salespeople_table = "{$wpdb->prefix}mji_salespeople";

    $where = [];
    $params = [];

    if ($location !== null) {
        $where[] = "location_id = %d";
        $params[] = $location;
    }

    $where[] = "p.payment_date BETWEEN %s AND %s AND p.transaction_type = 'refund'";
    $params[] = [$start_date, $end_date];
    $sql_query = "
            SELECT 
               p.reference_num, p.transaction_type, p.payment_date, p.notes, c.first_name, c.last_name, s.first_name as salesperson_first_name, s.last_name as salesperson_last_name, 
                JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'method', p.method,
                        'amount', p.amount
                    )
                ) AS payment_details
            FROM {$payments_table} p
            JOIN {$customers_table} c ON p.customer_id = c.id
            JOIN {$salespeople_table} s ON p.salesperson_id = s.id
            WHERE " . implode(" AND ", $where) . "
            GROUP BY p.reference_num, p.transaction_type, p.payment_date, p.notes, c.first_name, c.last_name, s.first_name, s.last_name
            ORDER BY p.reference_num";


    $results = $wpdb->get_results($wpdb->prepare($sql_query, ...$params));

    $results['start_date'] = $start_date;
    $results['end_date'] = $end_date;
    $results['location'] = $location;
    return $results;
}

function reports_render_refund_report($results)
{
    if ($results) {
        $store_locations = mji_get_locations();
        $location_obj = array_find($store_locations, fn($loc) => $loc->id == intval($_GET['location']));
        $location_name = $location_obj ? $location_obj->name : 'All Location';

        $start_date = explode(" ", $results['start_date'])[0];
        $end_date = explode(" ", $results['end_date'])[0];

        echo '<div style="max-height:700px; overflow-y:auto; position:relative;">';
        echo '<button id="exportInventory" class="button button-primary" style="margin-bottom:10px;">Export to CSV</button>';
        echo '<button id="printInventory" class="button button-secondary" style="margin-bottom:10px;">Print Report</button>';

        echo '<div id="report">';
        echo '<header>';
        echo '<h2>Refund Report for ' . esc_html($location_name) . ' - Montecristo Jewellers</h2>';
        echo '<p>From ' . esc_html($start_date) . ' to ' . esc_html($end_date) . '</p>';
        echo '</header>';

        echo '<table id="inventoryTable" class="widefat ">';

        echo '<thead>
            <tr>
                <th>Invoice</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Salesperson</th>
                <th>Method</th>
                <th>Amount</th>
                <th>Notes</th>
            </tr>
          </thead>';
        echo '<tbody>';
        foreach ($results as $index => $row) {

            if (!is_object($row))
                continue;
            $payments = json_decode($row->payment_details, true);
            $rowspan = count($payments);
            $first = true;
            $payment_date = explode(" ", $row->payment_date)[0];
            foreach ($payments as $payment) {

                $isLast = ($index % 2 == 0) ? "group-end" : "";
                echo "<tr class='{$isLast}'>";

                if ($first) {
                    echo "<td rowspan='{$rowspan}'>{$row->reference_num}</td>";
                    echo "<td rowspan='{$rowspan}'>{$payment_date}</td>";
                    echo "<td rowspan='{$rowspan}'>{$row->first_name} {$row->last_name}</td>";
                    echo "<td rowspan='{$rowspan}'>{$row->salesperson_first_name} {$row->salesperson_last_name}</td>";
                }

                // Payment-specific columns (always printed)
                echo "<td>{$payment['method']}</td>";
                echo "<td>{$payment['amount']}</td>";

                if ($first) {
                    echo "<td rowspan='{$rowspan}'>{$row->notes}</td>";
                    $first = false;
                }
                echo "</tr>";
            }
        }
        echo '</table>';
        echo '</div>';
    }
}

// Reports Financial Section
function reports_render_financial_section()
{
    reports_render_financial_filters();

    echo '<hr>';
    if (isset($_GET['start_date'], $_GET['end_date'])) {
        $results = reports_get_financial_results();
        reports_render_financial_report($results);
    }
}

function reports_render_financial_filters()
{
?>
    <form method="get" action="">
        <input type="hidden" name="page" value="reports-management">
        <input type="hidden" name="tab" value="financial">

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
                    <input type="date" name="start_date" id="start_date" value="<?= $startDate; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="end_date">End Date</label></th>
                <td>
                    <?php
                    $endDate =
                        isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : date("Y-m-t", strtotime("last month"));
                    ?>
                    <input type="date" name="end_date" id="end_date" value="<?= $endDate; ?>">
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

function reports_get_financial_results()
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

    $location = !empty($_GET['location']) ? intval($_GET['location']) : null;

    $payments_table = "{$wpdb->prefix}mji_payments";
    $order_items_table = "{$wpdb->prefix}mji_order_items";
    $services_table = "{$wpdb->prefix}mji_services";
    $orders_table = "{$wpdb->prefix}mji_orders";
    $layaways_table = "{$wpdb->prefix}mji_layaways";
    $credits_table = "{$wpdb->prefix}mji_credits";
    $customers_table = "{$wpdb->prefix}mji_customers";
    $salespeople_table = "{$wpdb->prefix}mji_salespeople";
    $order_items_table = "{$wpdb->prefix}mji_order_items";
    $product_inventory_units_table = "{$wpdb->prefix}mji_product_inventory_units";
    $returns_table = "{$wpdb->prefix}mji_returns";
    $return_items_table = "{$wpdb->prefix}mji_return_items";
    $return_services_table = "{$wpdb->prefix}mji_return_services";

    $where_orders = ["o.created_at BETWEEN %s AND %s"];
    $params_orders = [$start_date, $end_date];

    $where_returns = ["r.return_date BETWEEN %s AND %s"];
    $params_returns = [$start_date, $end_date];

    if ($location !== null) {
        $where_orders[] = "o.location_id = %d";
        $params_orders[] = $location;

        $where_returns[] = "o.location_id = %d";
        $params_returns[] = $location;
    }

    $sql = "
        SELECT 
            o.id,
            o.reference_num,
            o.created_at,
            o.subtotal,
            o.gst_total,
            o.pst_total,
            c.first_name,
            c.last_name,
            sa.first_name AS salesperson_first_name,
            sa.last_name AS salesperson_last_name,
            'order' AS type
        FROM $orders_table o
        JOIN $customers_table c ON c.id = o.customer_id
        JOIN $salespeople_table sa ON sa.id = o.salesperson_id
        WHERE " . implode(' AND ', $where_orders) . "

        UNION ALL

        SELECT 
            r.id,
            r.reference_num,
            r.return_date AS created_at,
            -r.subtotal,
            -r.gst_total,
            -r.pst_total,
            c.first_name,
            c.last_name,
            sa.first_name,
            sa.last_name,
            'return' AS type
        FROM $returns_table r
        JOIN $orders_table o ON o.id = r.order_id
        JOIN $customers_table c ON c.id = o.customer_id
        JOIN $salespeople_table sa ON sa.id = o.salesperson_id
        WHERE " . implode(' AND ', $where_returns);


    $params = array_merge($params_orders, $params_returns);
    $results = $wpdb->get_results($wpdb->prepare($sql, ...$params));
    $reference_nums = [];
    $order_ids = [];
    $return_ids = [];

    foreach ($results as $row) {
        $reference_nums[] = $row->reference_num;

        if ($row->type === 'order') {
            $order_ids[] = $row->id;
        } else {
            $return_ids[] = $row->id;
        }
    }

    // Fetch payments
    $placeholders = implode(',', array_fill(0, count($reference_nums), '%s'));
    $sql = "
        SELECT 
            p.reference_num,
            p.method,
            p.amount,
            COALESCE(l.reference_num, cr.reference_num, '') AS ref
        FROM $payments_table p
        LEFT JOIN $layaways_table l ON l.id = p.layaway_id
        LEFT JOIN $credits_table cr ON cr.id = p.credit_id
        WHERE p.reference_num IN ($placeholders)
        ";

    $payments = $wpdb->get_results($wpdb->prepare($sql, ...$reference_nums));

    // Grouping the fetch payments together
    $payments_map = [];
    foreach ($payments as $p) {
        $amount = $p->amount;

        $payments_map[$p->reference_num][] = [
            'method' => $p->method,
            'amount' => $amount,
            'reference_num' => $p->ref
        ];
    }

    // Fetching items and services for original order
    $placeholders = implode(',', array_fill(0, count($order_ids), '%d'));
    $sql = "
        SELECT 
            oi.order_id,
            oi.sale_price,
            i.sku,
            i.cost_price
        FROM $order_items_table oi
        JOIN $product_inventory_units_table i ON oi.product_inventory_unit_id = i.id
        WHERE oi.order_id IN ($placeholders)
        ";
    $order_items = $wpdb->get_results($wpdb->prepare($sql, ...$order_ids));

    $sql = "
        SELECT 
            order_id,
            sold_price AS sale_price,
            cost_price
        FROM $services_table
        WHERE order_id IN ($placeholders)
        ";
    $services = $wpdb->get_results($wpdb->prepare($sql, ...$order_ids));

    // Fetching return items and return services
    $placeholders = implode(',', array_fill(0, count($return_ids), '%d'));
    $sql = "
        SELECT 
            ri.return_id,
            -ri.unit_price AS sale_price,
            i.sku,
            i.cost_price
        FROM $return_items_table ri
        JOIN $product_inventory_units_table i ON ri.product_inventory_unit_id = i.id
        WHERE ri.return_id IN ($placeholders)
        ";
    $return_items = $wpdb->get_results($wpdb->prepare($sql, ...$return_ids));
    $sql = "
        SELECT 
            rs.return_id,
            -rs.price AS sale_price,
            s.cost_price
        FROM $return_services_table rs
        JOIN $services_table s ON s.id = rs.service_id
        WHERE rs.return_id IN ($placeholders)
        ";
    $return_services = $wpdb->get_results($wpdb->prepare($sql, ...$return_ids));

    // Mergin items map together
    $items_map = [];

    foreach ($order_items as $item) {
        $items_map[$item->order_id][] = [
            'sale_price' => $item->sale_price,
            'sku' => $item->sku,
            'cost_price' => $item->cost_price
        ];
    }

    foreach ($services as $s) {
        $items_map[$s->order_id][] = [
            'sale_price' => $s->sale_price,
            'sku' => 'Service',
            'cost_price' => $s->cost_price
        ];
    }

    // returns
    foreach ($return_items as $ri) {
        $items_map[$ri->return_id][] = [
            'sale_price' => $ri->sale_price,
            'sku' => $ri->sku,
            'cost_price' => $ri->cost_price
        ];
    }
    foreach ($return_services as $rs) {
        $items_map[$rs->return_id][] = [
            'sale_price' => $rs->sale_price,
            'sku' => 'Service',
            'cost_price' => $rs->cost_price
        ];
    }

    // Adding payments and items to the original sql query
    foreach ($results as &$row) {
        // payments
        $payments = $payments_map[$row->reference_num] ?? [];

        if ($row->type === 'return') {
            foreach ($payments as &$p) {
                $p['amount'] *= -1;
            }
        }
        $row->payment_details = $payments;
        // items
        $row->all_items = $items_map[$row->id] ?? [];
    }

    $results['start_date'] = $start_date;
    $results['end_date'] = $end_date;
    $results['location'] = $location;
    return $results;
}

function reports_render_financial_report($results)
{
    if ($results) {
        $store_locations = mji_get_locations();
        $location_obj = array_find($store_locations, fn($loc) => $loc->id == intval($_GET['location']));
        $location_name = $location_obj ? $location_obj->name : 'All Location';

        $start_date = explode(" ", $results['start_date'])[0];
        $end_date = explode(" ", $results['end_date'])[0];

        $gst_total = 0;
        $pst_total = 0;
        $sales_total = 0;
        $cost_total = 0;
        $refund_gst_total = 0;
        $refund_pst_total = 0;
        $refund_sales_total = 0;
        $refund_cost_total = 0;
        echo '<div style="max-height:700px; overflow-y:auto; position:relative;">';
        echo '<button id="exportInventory" class="button button-primary" style="margin-bottom:10px;">Export to CSV</button>';
        echo '<button id="printInventory" class="button button-secondary" style="margin-bottom:10px;">Print Report</button>';

        echo '<div id="report">';
        echo '<header>';
        echo '<h2>Financial Report for ' . esc_html($location_name) . ' - Montecristo Jewellers</h2>';
        echo '<p>From ' . esc_html($start_date) . ' to ' . esc_html($end_date) . '</p>';
        echo '</header>';

        echo '<table id="inventoryTable" class="widefat stripped">';

        echo '<thead>
            <tr>
                <th>Invoice</th>
                <th>Date</th>
                <th>Customer Name</th>
                <th>Salesperson Name</th>
                <th>Retail Paid</th>
                <th>GST</th>
                <th>PST</th>
                <th>Method</th>
                <th>Amount</th>
                <th>SKU</th>
                <th>Cost</th>
                <th>Sold</th>
            </tr>
          </thead>';
        echo '<tbody>';
        custom_log($results);
        foreach ($results as $index => $row) {
            if (!is_object($row))
                continue;

            if ($row->type === "order") {
                $gst_total += $row->gst_total;
                $pst_total += $row->pst_total;
                $sales_total += $row->subtotal;
            } else {
                $refund_gst_total -= $row->gst_total;
                $refund_pst_total -= $row->pst_total;
                $refund_sales_total -= $row->subtotal;
            }

            $payments = $row->payment_details;
            $all_items = $row->all_items;
            $rowspan = max(count($payments), count($all_items));
            $isLast = ($index % 2 == 0) ? "group-end" : "";
            $date = explode(" ", $row->created_at)[0];

            for ($i = 0; $i < $rowspan; $i++) {
                echo "<tr class='{$isLast}'>";
                // to add layaway/credit invoice number
                $ref = $payments[$i]['reference_num'] ?? null;
                $reference_num = $ref ? " (#{$ref})" : '';

                // Ensuring it covers multiple rowspan if we have tons of payment deposits
                if ($i === 0) {
                    echo "<td rowspan='{$rowspan}'>{$row->reference_num}</td>";
                    echo "<td rowspan='{$rowspan}'>{$date}</td>";
                    echo "<td rowspan='{$rowspan}'>{$row->first_name} {$row->last_name}</td>";
                    echo "<td rowspan='{$rowspan}'>{$row->salesperson_first_name} {$row->salesperson_last_name}</td>";
                    echo "<td rowspan='{$rowspan}'>{$row->subtotal}</td>";
                    echo "<td rowspan='{$rowspan}'>{$row->gst_total}</td>";
                    echo "<td rowspan='{$rowspan}'>{$row->pst_total}</td>";
                    echo "<td >{$payments[$i]['method']}{$reference_num}</td>";
                    echo "<td >{$payments[$i]['amount']}</td>";
                } else {
                    echo "<td>" . ($payments[$i]['method'] ?? '') . "{$reference_num}</td>";
                    echo "<td>" . ($payments[$i]['amount'] ?? '') . "</td>";
                }

                if ($all_items > $i) {
                    $cost_price = $all_items[$i]['cost_price'] ?? 0;
                    echo "<td>" . ($all_items[$i]['sku'] ?? '') . "</td>";
                    echo "<td>" . ($cost_price ?: '') . "</td>";
                    echo "<td>" . ($all_items[$i]['sale_price'] ?? '') . "</td>";
                    if ($row->type === "order") {
                        $cost_total += $cost_price;
                    } else {
                        $refund_cost_total += $cost_price;
                    }
                } else {
                    echo "<td></td><td></td><td></td>";
                }
                echo '</tr>';
            }
        }
        echo '<tfoot>';
        echo '<tr>';
        echo '<td>Total Sales</td>';
        echo '<td>Cost $' . number_format($cost_total) . '</td>';
        echo '<td>Retail $' . number_format($sales_total) . '</td>';
        echo '<td>GST $' . number_format($gst_total) . '</td>';
        echo '<td>PST $' . number_format($pst_total) . '</td>';
        echo '<td>Retail with GST $' . number_format($sales_total + $gst_total) . '</td>';
        echo '<td>Retail with PST $' . number_format($sales_total + $pst_total) . '</td>';
        echo '<td>Retail with GST AND PST $' . number_format($sales_total + $gst_total + $pst_total) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>Total Refund</td>';
        echo '<td>Cost $' . number_format($refund_cost_total) . '</td>';
        echo '<td>Retail $' . number_format($refund_sales_total) . '</td>';
        echo '<td>GST $' . number_format($refund_gst_total) . '</td>';
        echo '<td>PST $' . number_format($refund_pst_total) . '</td>';
        echo '<td>Retail with GST $' . number_format($refund_sales_total + $refund_gst_total) . '</td>';
        echo '<td>Retail with PST $' . number_format($refund_sales_total + $refund_pst_total) . '</td>';
        echo '<td>Retail with GST AND PST $' . number_format($refund_sales_total + $refund_gst_total + $refund_pst_total) . '</td>';
        echo '</tr>';
        echo '</tfoot>';
        echo '</table>';
        echo '</div>';
    }
}

// Reports Out of status Section
function reports_render_out_of_status_section()
{
    reports_render_out_of_status_filters();

    echo '<hr>';
    if (isset($_GET['start_date'], $_GET['end_date'])) {
        $results = reports_get_out_of_status_results();
        reports_render_out_of_status_report($results);
    }
}

function reports_render_out_of_status_filters()
{
?>
    <form method="get" action="">
        <input type="hidden" name="page" value="reports-management">
        <input type="hidden" name="tab" value="out-of-status">

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
                    <input type="date" name="start_date" id="start_date" value="<?= $startDate; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="end_date">End Date</label></th>
                <td>
                    <?php
                    $endDate =
                        isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : date("Y-m-t", strtotime("last month"));
                    ?>
                    <input type="date" name="end_date" id="end_date" value="<?= $endDate; ?>">
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

function reports_get_out_of_status_results()
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

    $location = !empty($_GET['location']) ? intval($_GET['location']) : null;

    $inventory_status_history_table = "{$wpdb->prefix}mji_inventory_status_history";
    $product_units_table = "{$wpdb->prefix}mji_product_inventory_units";

    $where[] = "created_at BETWEEN %s AND %s ";
    $params[] = $start_date;
    $params[] = $end_date;
    $location_filter = '';

    if ($location !== null) {
        $location_filter = " AND i.location_id = %d";
        $params[] = $location;
    }

    $sql_query = "
            SELECT *
            FROM (
                SELECT *, 
                    ROW_NUMBER() OVER (
                        PARTITION BY inventory_unit_id 
                        ORDER BY created_at DESC, id DESC
                    ) as row_num
                FROM {$inventory_status_history_table}
                WHERE  " . implode(" AND ", $where) . "
            ) t JOIN {$product_units_table} i ON t.inventory_unit_id = i.id
            WHERE t.to_status NOT IN ('in_stock', 'sold') AND t.row_num = 1 {$location_filter}";

    $results = $wpdb->get_results($wpdb->prepare($sql_query, ...$params));

    $results['start_date'] = $start_date;
    $results['end_date'] = $end_date;
    $results['location'] = $location;
    return $results;
}

function reports_render_out_of_status_report($results)
{
    if ($results) {
        $store_locations = mji_get_locations();
        $location_obj = array_find($store_locations, fn($loc) => $loc->id == intval($_GET['location']));
        $location_name = $location_obj ? $location_obj->name : 'All Location';

        $start_date = explode(" ", $results['start_date'])[0];
        $end_date = explode(" ", $results['end_date'])[0];

        echo '<div style="max-height:700px; overflow-y:auto; position:relative;">';
        echo '<button id="exportInventory" class="button button-primary" style="margin-bottom:10px;">Export to CSV</button>';
        echo '<button id="printInventory" class="button button-secondary" style="margin-bottom:10px;">Print Report</button>';

        echo '<div id="report">';
        echo '<header>';
        echo '<h2>Refund Report for ' . esc_html($location_name) . ' - Montecristo Jewellers</h2>';
        echo '<p>From ' . esc_html($start_date) . ' to ' . esc_html($end_date) . '</p>';
        echo '</header>';

        echo '<table id="inventoryTable" class="widefat stripped">';

        echo '<thead>
            <tr>
                <th>sku</th>
                <th>serial</th>
                <th>from_status</th>
                <th>to_status</th>
                <th>created_at</th>
                <th>Notes</th>
            </tr>
          </thead>';
        echo '<tbody>';
        foreach ($results as $index => $row) {
            if (!is_object($row))
                continue;

            $date = explode(" ", $row->created_at)[0];

            echo "<tr>";
            echo "<td>{$row->sku}</td>";
            echo "<td>{$row->serial}</td>";
            echo "<td>{$row->from_status}</td>";
            echo "<td>{$row->to_status}</td>";
            echo "<td>{$date}</td>";
            echo "<td>{$row->notes}</td>";
            echo "</tr>";
        }
        echo '</table>';
        echo '</div>';
    }
}
