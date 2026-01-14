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
    if (isset($_POST['action'])) {

        if ($_POST['action'] === 'delete_invoice' && isset($_POST['order_id'])) {
            $reference_num = $_POST['order_id'];
            $result = delete_invoice($reference_num);

            if ($result['success']) {
                echo '<div class="notice notice-success"><p>Invoice deleted successfully.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
            }
        } elseif (
            $_POST['action'] === 'delete_layaway'
            && isset($_POST['layaway_id'], $_POST['delete_layaway_nonce'])
            && wp_verify_nonce($_POST['delete_layaway_nonce'], 'delete_layaway_action')
        ) {
            $layaway_id = (int) $_POST['layaway_id'];
            $result = delete_layaway($layaway_id);

            if ($result['success']) {
                echo '<div class="notice notice-success"><p>Layaway deleted successfully.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }
    } else if (isset($_GET['reference_num'])) {

        $reference_num = $_GET['reference_num'];

        if ($active_tab == "sales") {
            $results = reports_search_sales_results($reference_num);
            if (!$results) {
                echo "<div class='wrap'>";
                echo "<h2>Invoice " . $reference_num . " not found!!";
                echo "</div>";
            } else {
                render_invoice($results);
                return;
            }
        } else {
            $results = search_layaway_results($reference_num);
            if (!$results) {
                echo "<div class='wrap'>";
                echo "<h2>Invoice " . $reference_num . " not found!!";
                echo "</div>";
            } else {
                render_layaway_invoice($results);
                return;
            }
        }
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

        <?php submit_button('Find Invoice'); ?>
    </form>
<?php
}

function reports_search_sales_results($reference_num)
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

        $payments = $wpdb->get_results($wpdb->prepare("
            SELECT method, amount
            FROM {$payments_table}
            WHERE order_id = %d
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
                                $product_id = $item->wc_product_variant_id ?: $item->wc_product_id;
                                $is_variant = empty($item->wc_product_variant_id) ? false : true;
                                $product = wc_get_product($product_id);
                                $description = "";

                                if (!$product) {
                                    echo "<p>No product found";
                                    continue;
                                }

                                $image_url  = esc_url(wp_get_attachment_image_url(get_post_thumbnail_id($item->wc_product_id), 'thumbnail'));
                                if ($is_variant) {
                                    $description = $product->get_description();
                                } else {
                                    $description = $product->get_short_description();
                                }
                                echo "<img src='{$image_url}' alt='product image' />";
                                echo "<p>";
                                if (!empty($item->sku)) echo "<b>SKU: " . esc_html($item->sku) . "</b><br/>";
                                echo nl2br($description);
                                if (!empty($item->serial)) echo "<br />Serial: " . esc_html($item->serial);
                                if (!empty($item->notes)) echo "<br />Notes: " . esc_html($item->notes);
                                echo  "</p>";
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
            <input type="hidden" name="action" value="delete_invoice">
            <?php submit_button('Delete Invoice', 'delete', 'delete_invoice'); ?>
        </form>

    </div>

<?php
}

function delete_invoice($order_id)
{

    $order_id = absint($order_id);
    if (!$order_id) {
        return array("messagge" => "Order id is required to delete.");
    }

    global $wpdb;

    $orders_table      = $wpdb->prefix . 'mji_orders';
    $order_items_table = $wpdb->prefix . 'mji_order_items';
    $inventory_table   = $wpdb->prefix . 'mji_product_inventory_units';
    $service_table     = $wpdb->prefix . 'mji_services';
    $payments_table    = $wpdb->prefix . 'mji_payments';
    $layaways_table    = $wpdb->prefix . 'mji_layaways';

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

        $payment_items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT layaway_id, amount 
                 FROM {$payments_table}
                 WHERE order_id = %d AND transaction_type = 'layaway_redemption'",
                $order_id
            )
        );

        check_wpdb_error($wpdb);

        if (!empty($payment_items)) {
            foreach ($payment_items as $payment_item) {
                $remaining_amount = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT remaining_amount FROM {$layaways_table} WHERE id = %d",
                        $payment_item->layaway_id
                    )
                );

                check_wpdb_error($wpdb);
                $amount_redeemed = $remaining_amount + $payment_item->amount;
                $wpdb->update(
                    $layaways_table,
                    [
                        'remaining_amount' => $amount_redeemed,
                        'status' => 'active',
                    ],
                    ['id' => $payment_item->layaway_id],
                    ['%f', '%s'],
                    ['%d']
                );
                check_wpdb_error($wpdb);
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

function search_layaway_results($reference_num)
{
    global $wpdb;

    $layaway_table = $wpdb->prefix . 'mji_layaways';
    $payments_table = $wpdb->prefix . 'mji_payments';
    $customers_table = $wpdb->prefix . 'mji_customers';
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';

    try {
        $sql_layaway = $wpdb->prepare("
            SELECT
                l.id AS layaway_id,
                l.reference_num,
                l.total_amount,
                p.payment_date,
                c.prefix,
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
            FROM {$layaway_table} l
            LEFT JOIN {$payments_table} p ON p.layaway_id = l.id
            LEFT JOIN {$customers_table} c ON c.id = p.customer_id
            LEFT JOIN {$salespeople_table} s ON s.id = p.salesperson_id
            WHERE p.reference_num = %s
            AND p.transaction_type = 'layaway_deposit'
        ", $reference_num);

        $results = $wpdb->get_row($sql_layaway);
        check_wpdb_error($wpdb);

        if (!$results) return null;

        $sql_payments = $wpdb->prepare("
            SELECT
                reference_num,
                layaway_id,
                method,
                amount
            FROM {$payments_table}
            WHERE reference_num = %s
        ", $reference_num);

        $payments  = $wpdb->get_results($sql_payments);
        check_wpdb_error($wpdb);

        $results->payment = $payments;

        $sql_layaway_used = $wpdb->prepare("
          SELECT
                p.reference_num,
                p.amount,
                p.payment_date
                FROM {$payments_table} p
            WHERE p.layaway_id = %d AND transaction_type = 'layaway_redemption'
        ", $results->layaway_id);

        $layaway_used  = $wpdb->get_results($sql_layaway_used);
        check_wpdb_error($wpdb);

        $results->layaway_used = $layaway_used;
        return $results;
    } catch (Exception $e) {
        custom_log($e->getMessage());
    }
}

function render_layaway_invoice($invoice)
{
    if (empty($invoice)) {
        return;
    }

    $bill_to_lines = [];
    $name = trim($invoice->prefix . ' ' . $invoice->customer_first_name . ' ' . $invoice->customer_last_name);
    if ($name !== '') {
        $bill_to_lines[] = esc_html($name);
    }
    if (!empty($invoice->street_address)) {
        $bill_to_lines[] = esc_html($invoice->street_address);
    }

    $city_line = [];
    if (!empty($invoice->city)) {
        $city_line[] = $invoice->city;
    }
    if (!empty($invoice->province)) {
        $city_line[] = $invoice->province;
    }
    if (!empty($invoice->postal_code)) {
        $city_line[] = $invoice->postal_code;
    }
    if (!empty($city_line)) {
        $bill_to_lines[] = esc_html(implode(', ', $city_line));
    }
    if (!empty($invoice->country)) {
        $bill_to_lines[] = esc_html($invoice->country);
    }
    if (!empty($invoice->email)) {
        $bill_to_lines[] = esc_html($invoice->email);
    }

    $purchased_date = $invoice->payment_date;
    $purchased_date = strtotime($purchased_date);
    $purchased_date = date('Y-m-d', $purchased_date);
?>
    <div style="max-width:700px;">

        <h2>Invoice #<?php echo esc_html($invoice->reference_num); ?></h2>

        <p><strong>Bill To</strong></p>
        <p>
            <?php echo implode('<br>', $bill_to_lines); ?><br />
            <strong>Served By:</strong>
            <?php echo esc_html(
                $invoice->salesperson_first_name . ' ' . $invoice->salesperson_last_name
            ); ?><br>

            <strong>Date Created:</strong>
            <?php echo esc_html(date('Y-m-d')); ?>
        </p>

        <h3>Payment Summary</h3>

        <table border="1" cellpadding="6" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th align="left">Method</th>
                    <th align="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_paid = 0.00;

                if (!empty($invoice->payment)) :
                    foreach ($invoice->payment as $payment) :
                        $amount = (float) $payment->amount;
                        $total_paid += $amount;
                ?>
                        <tr>
                            <td><?php echo esc_html(ucfirst($payment->method)); ?></td>
                            <td align="right">$<?php echo number_format($amount, 2); ?></td>
                        </tr>
                    <?php
                    endforeach;
                else :
                    ?>
                    <tr>
                        <td colspan="2" align="center">No payments found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th align="right">Total Paid</th>
                    <th align="right">$<?php echo number_format($total_paid, 2); ?></th>
                </tr>
            </tfoot>
        </table>

        <?php
        if (empty($invoice->layaway_used)) :
        ?>
            <form method="post" onsubmit="return confirm('Delete this layaway? This cannot be undone.');">
                <?php wp_nonce_field('delete_layaway_action', 'delete_layaway_nonce'); ?>
                <input type="hidden" name="action" value="delete_layaway">
                <input type="hidden" name="layaway_id" value="<?php echo esc_attr($invoice->layaway_id); ?>">
                <button type="submit" class="button button-danger">
                    Delete Layaway
                </button>
            </form>
        <?php else : ?>

            <p><i>We can not delete the Layaway as it has been used, Will have to delete the invoices using this layaway before we can delete this.</i></p>
            <h3>Layaway Usage</h3>

            <table border="1" cellpadding="6" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Reference #</th>
                        <th>Date</th>
                        <th align="right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $layaway_total = 0.00;

                    foreach ($invoice->layaway_used as $used) :
                        $amount = (float) $used->amount;
                        $layaway_total += $amount;
                    ?>
                        <tr>
                            <td><?php echo esc_html($used->reference_num); ?></td>
                            <td><?php echo esc_html(date('Y-m-d', strtotime($used->payment_date))); ?></td>
                            <td align="right">$<?php echo number_format($amount, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" align="right">Layaway Total</th>
                        <th align="right">$<?php echo number_format($layaway_total, 2); ?></th>
                    </tr>
                </tfoot>
            </table>

        <?php endif; ?>

    </div>
<?php
}

function delete_layaway($layaway_id)
{
    $layaway_id = absint($layaway_id);
    if (!$layaway_id) {
        return array("messagge" => "Layaway id is required to delete.");
    }

    global $wpdb;

    $layaways_table      = $wpdb->prefix . 'mji_layaways';
    $payments_table    = $wpdb->prefix . 'mji_payments';
    $wpdb->query('START TRANSACTION');

    try {
        $layaway_amount = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT total_amount, remaining_amount 
                 FROM {$layaways_table}
                 WHERE id = %d",
                $layaway_id
            )
        );

        check_wpdb_error($wpdb);

        if (!$layaway_amount) {
            return array("messagge" => "No Layaway found with that ID.");
        }

        if ($layaway_amount->total_amount == $layaway_amount->remaining_amount) {
            $wpdb->delete($payments_table, ['layaway_id' => $layaway_id], ['%d']);
            check_wpdb_error($wpdb);
            $wpdb->delete($layaways_table, ['id' => $layaway_id], ['%d']);
            check_wpdb_error($wpdb);
            $wpdb->query('COMMIT');
            return [
                'success' => true,
                "messagge" => "Layaway successfully deleted."
            ];
        } else {
            return [
                'success' => false,
                "messagge" => "Layaway already used so unable to delete."
            ];
        }
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        custom_log('[Delete Layaway Invoice Failed] ' . $e->getMessage());
        return [
            'success' => false,
            'message' => '[Delete Layaway Invoice Failed] ' . $e->getMessage(),
        ];
    }
}
