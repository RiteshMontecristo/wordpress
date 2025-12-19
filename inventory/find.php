<?php

function find_page()
{
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'sales';
    $allowed_tabs = ['sales', 'layaway'];

    if (!in_array($active_tab, $allowed_tabs)) {
        $active_tab = 'sales';
    }

    $sales_url = add_query_arg(
        'tab',
        'sales',
        menu_page_url('invoice-management', false)
    );

    $layaway_url = add_query_arg(
        'tab',
        'layaway',
        menu_page_url('invoice-management', false)
    );
?>

    <div class="wrap">
        <h1>Find invoices</h1>

        <h2 class="nav-tab-wrapper">
            <a href="<?php echo esc_url($sales_url); ?>"
                class="nav-tab <?php echo $active_tab === 'sales' ? 'nav-tab-active' : ''; ?>">
                Sales
            </a>
            <a href="<?php echo esc_url($layaway_url); ?>"
                class="nav-tab <?php echo $active_tab === 'layaway' ? 'nav-tab-active' : ''; ?>">
                Layaway/Credit
            </a>
        </h2>

        <?= render_search_section() ?>
    </div>

<?php
}

// Reports sales Section
function render_search_section()
{
    echo '<hr>';
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'sales';

    if (isset($_POST['order_id'])) {
        $reference_num = $_POST['order_id'];
        $result = delete_invoice($reference_num);

        if ($result['success']) {
            echo '<div class="notice notice-success"><p>Invoice deleted successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
        }
    } else if (isset($_GET['reference_num'])) {
        $reference_num = $_GET['reference_num'];
        $results = reports_search_sales_results($reference_num, $active_tab);
        render_invoice($results);
        return;
    }

?>
    <form method="get" action="">
        <input type="hidden" name="page" value="invoice-management">
        <input type="hidden" name="tab" value="<?= $active_tab ?>">
        <table class="form-table">
            <tr>
                <th scope="row"><label for="reference_num">Invoice Number</label></th>
                <td>
                    <input type="text" name="reference_num" id="reference_num"></input>
                </td>
            </tr>
        </table>

        <?php submit_button('Generate Report'); ?>
    </form>
<?php
}

function reports_search_sales_results($reference_num, $search = "sales")
{
    global $wpdb;

    $orders_table = $wpdb->prefix . 'mji_orders';
    $order_items_table = $wpdb->prefix . 'mji_order_items';
    $inventory_table = $wpdb->prefix . 'mji_product_inventory_units';
    $service_table = $wpdb->prefix . 'mji_services';
    $payments_table = $wpdb->prefix . 'mji_payments';
    $customers_table = $wpdb->prefix . 'mji_customers';
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';
    $models_table = $wpdb->prefix . 'mji_models';

    try {
        // Main order query (with customers & salespeople)
        $sql_order = $wpdb->prepare("
            SELECT 
                o.*,
                c.first_name AS customer_first_name,
                c.last_name  AS customer_last_name,
                c.email,
                c.street_address,
                c.city,
                c.province,
                c.postal_code,
                c.country,
                s.first_name AS salesperson_first_name,
                s.last_name  AS salesperson_last_name
            FROM {$orders_table} o
            LEFT JOIN {$customers_table} c ON c.id = o.customer_id
            LEFT JOIN {$salespeople_table} s ON s.id = o.salesperson_id
            WHERE o.reference_num = %s
        ", $reference_num);

        $order = $wpdb->get_row($sql_order);

        if ($wpdb->last_error) {
            throw new Exception($wpdb->last_error);
        }
        if (!$order) return null;

        $order_id = $order->id;
        $items = $wpdb->get_results($wpdb->prepare("
            SELECT 
                oi.*,
                inv.sku,
                inv.wc_product_id,
                inv.wc_product_variant_id,
                inv.serial,
                inv.notes,
                m.name as model_name
            FROM {$order_items_table} oi
            LEFT JOIN {$inventory_table} inv ON inv.id = oi.product_inventory_unit_id
            LEFT JOIN {$models_table} m ON m.id = inv.model_id
            WHERE oi.order_id = %d
        ", $order_id));

        if ($wpdb->last_error) {
            throw new Exception($wpdb->last_error);
        }

        $services = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$service_table}
            WHERE order_id = %d
        ", $order_id));

        if ($wpdb->last_error) {
            throw new Exception($wpdb->last_error);
        }

        // Fetch payments (per method, filtered by sales/layaway)
        $payment_condition = ($search === 'sales')
            ? "transaction_type NOT IN ('layaway_deposit','credit_deposit')"
            : "transaction_type IN ('layaway_deposit','credit_deposit', 'layaway_redemption', 'credit_redemption')";

        $payments = $wpdb->get_results($wpdb->prepare("
            SELECT method, amount
            FROM {$payments_table}
            WHERE order_id = %d AND {$payment_condition}
        ", $order_id));

        if ($wpdb->last_error) {
            throw new Exception($wpdb->last_error);
        }

        return [
            'order'    => $order,
            'items'    => $items,
            'services' => $services,
            'payments' => $payments
        ];
    } catch (Exception $e) {
        custom_log($e->getMessage());
    }
}

function render_invoice($results)
{
    $order = $results['order'];
    $items = $results['items'];
    $services = $results['services'];
    $payments = $results['payments'];
?>
    <div class="wrap">

        <h2>Invoice #<?= esc_html($order->reference_num) ?></h2>

        <!-- Customer & Invoice Info -->
        <div style="margin-bottom:20px;">
            <p><strong>Customer:</strong> <?= esc_html($order->customer_first_name . ' ' . $order->customer_last_name) ?></p>
            <p><strong>Address:</strong> <?= esc_html($order->street_address . ', ' . $order->city . ', ' . $order->province . ' ' . $order->postal_code . ', ' . $order->country) ?></p>
            <p><strong>Served by:</strong> <?= esc_html($order->salesperson_first_name . ' ' . $order->salesperson_last_name) ?></p>
            <p><strong>Date:</strong> <?= esc_html(date('F j, Y', strtotime($order->created_at))) ?></p>
        </div>

        <!-- Invoice Table -->
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>

                <!-- ITEMS SECTION -->
                <?php if (!empty($items)): ?>
                    <tr>
                        <td style="font-weight:bold; background:#f1f1f1;">Items</td>
                    </tr>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <?php
                                $parts = [];
                                if (!empty($item->sku)) $parts[] = "SKU: " . esc_html($item->sku);
                                if (!empty($item->model_name)) $parts[] = "Model: " . esc_html($item->model_name);
                                if (!empty($item->serial)) $parts[] = "Serial: " . esc_html($item->serial);
                                if (!empty($item->notes)) $parts[] = "Notes: " . esc_html($item->notes);
                                echo implode(' <br /> ', $parts);
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- SERVICES SECTION -->
                <?php if (!empty($services)): ?>
                    <tr>
                        <td style="font-weight:bold; background:#f1f1f1;">Services</td>
                    </tr>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td>
                                <?php
                                $parts = [];
                                if (!empty($service->category)) $parts[] = esc_html($service->category);
                                if (!empty($service->description)) $parts[] = esc_html($service->description);
                                echo implode(' <br />  ', $parts);
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- PAYMENTS SECTION -->
                <?php if (!empty($payments)): ?>
                    <tr>
                        <td style="font-weight:bold; background:#f1f1f1;">Payments Method</td>
                    </tr>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td>
                                <?= esc_html($payment->method) ?>: $<?= number_format($payment->amount, 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

            </tbody>
        </table>

        <!-- Totals / Summary -->
        <div style="margin-top:20px; font-weight:bold;">
            <p>Subtotal: $<?= number_format($order->subtotal, 2) ?></p>
            <p>GST: $<?= number_format($order->gst_total, 2) ?></p>
            <p>PST: $<?= number_format($order->pst_total, 2) ?></p>
            <p>Total: $<?= number_format($order->total, 2) ?></p>
        </div>

        <!-- Delete Invoice Button -->
        <form method="post" style="margin-top:20px;" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
            <input type="hidden" name="order_id" value="<?= intval($order->id) ?>">
            <?php submit_button('Delete Invoice', 'delete', 'delete_invoice'); ?>
        </form>

    </div>

<?php
}

function delete_invoice($order_id)
{

    $order_id = absint($order_id);
    if (!$order_id) {
        return;
    }

    global $wpdb;

    $orders_table      = $wpdb->prefix . 'mji_orders';
    $order_items_table = $wpdb->prefix . 'mji_order_items';
    $inventory_table   = $wpdb->prefix . 'mji_product_inventory_units';
    $service_table     = $wpdb->prefix . 'mji_services';
    $payments_table    = $wpdb->prefix . 'mji_payments';

    $wpdb->query('SET autocommit = 0');
    $wpdb->query('START TRANSACTION');

    try {
        $stock_adjustments = [];

        $order_items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT product_inventory_unit_id 
                 FROM {$order_items_table}
                 WHERE order_id = %d",
                $order_id
            )
        );

        check_wpdb_error($wpdb);

        $inventory_ids = array_filter(
            wp_list_pluck($order_items, 'product_inventory_unit_id')
        );

        if (!empty($inventory_ids)) {

            $placeholders = implode(',', array_fill(0, count($inventory_ids), '%d'));

            $products = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, wc_product_id, wc_product_variant_id
                     FROM {$inventory_table}
                     WHERE id IN ($placeholders) AND status = 'sold'
                    FOR UPDATE",
                    ...$inventory_ids
                )
            );
            check_wpdb_error($wpdb);

            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$inventory_table}
                    SET status = 'in_stock', sold_date = NULL
                    WHERE id IN ({$placeholders}) AND status = 'sold'",
                    ...$inventory_ids
                )
            );
            check_wpdb_error($wpdb);

            foreach ($products as $product) {
                $product_id = $product->wc_product_variant_id ?: $product->wc_product_id;
                $stock_adjustments[] = $product_id;
            }
        }

        $wpdb->delete($order_items_table, ['order_id' => $order_id], ['%d']);
        check_wpdb_error($wpdb);
        $wpdb->delete($service_table, ['order_id' => $order_id], ['%d']);
        check_wpdb_error($wpdb);
        $wpdb->delete($payments_table, ['order_id' => $order_id], ['%d']);
        check_wpdb_error($wpdb);
        $wpdb->delete($orders_table, ['id' => $order_id], ['%d']);
        check_wpdb_error($wpdb);

        $wpdb->query('COMMIT');
        $wpdb->query('SET autocommit = 1');

        foreach ($stock_adjustments as $product_id) {
            $wc_product = wc_get_product($product_id);
            if ($wc_product && $wc_product->managing_stock()) {
                $wc_product->set_stock_quantity(
                    (int) $wc_product->get_stock_quantity() + 1
                );
                $wc_product->save();
            }
        }

        return [
            'success' => true,
            'message' => 'Invoice deleted successfully.'
        ];
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        $wpdb->query('SET autocommit = 1');
        custom_log('[Delete Invoice Failed] ' . $e->getMessage());
        return [
            'success' => false,
            'message' => '[Delete Invoice Failed] ' . $e->getMessage(),
        ];
    }
}

function check_wpdb_error($wpdb)
{
    if ($wpdb->last_error) {
        throw new Exception($wpdb->last_error);
    }
}

// ALTER TABLE wp_mji_payments
// ADD COLUMN used_amount DECIMAL(10,2) DEFAULT 0 NOT NULL,
// ADD COLUMN redeemed_at DATETIME NULL;

// UPDATE wp_mji_payments
// SET reference_num = NULL
// where order_id IS NOT NULL;