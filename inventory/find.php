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
        } elseif (
            $_POST['action'] === 'delete_credit'
            && isset($_POST['credit_id'], $_POST['delete_credit_nonce'])
            && wp_verify_nonce($_POST['delete_credit_nonce'], 'delete_credit_action')
        ) {
            $credit_id = (int) $_POST['credit_id'];
            $result = delete_credit($credit_id);

            if ($result['success']) {
                echo '<div class="notice notice-success"><p>Credit deleted successfully.</p></div>';
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
            WHERE reference_num = %s
        ", $reference_num));

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
    $notes = $results['order']->notes;

    $calculate_gst = $order->gst_total > 0 ? true : false;
    $calculate_pst = $order->pst_total > 0 ? true : false;
?>
    <div class="wrap">
        <div class="invoice">
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
                                    $item->description = $description;
                                    $item->image_url = $image_url;
                                    echo "<img src='{$image_url}' alt='product image' />";
                                    echo "<p>";
                                    if (!empty($item->sku)) echo "<b>SKU: " . esc_html($item->sku) . "</b><br/>";
                                    echo nl2br($description);
                                    if (!empty($item->serial)) echo "<br />Serial: " . esc_html($item->serial);
                                    if (!empty($item->notes)) echo "<br />Notes: " . esc_html($item->notes);
                                    echo "<br />Price: " . esc_html($item->sale_price);
                                    echo  "</p>";
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach;
                        ?>
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
                                    echo "<br /> Price: " . $service->sold_price;
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

                    <!-- NOTES SECTION -->
                    <?php if (!empty($notes)): ?>
                        <tr>
                            <td style="font-weight:bold; background:#f1f1f1;">Notes</td>
                        </tr>
                        <tr>
                            <td>
                                <?= esc_html($notes) ?>
                            </td>
                        </tr>
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
            <form method="post" style="margin-top:20px; position: relative; z-index:1;" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                <input type="hidden" name="order_id" value="<?= intval($order->id) ?>">
                <input type="hidden" name="action" value="delete_invoice">
                <?php submit_button('Delete Invoice', 'primary', 'delete_invoice'); ?>
                <button type="button" class="button issue_refund" id="issue_refund">Issue refund</button>
            </form>
        </div>

        <!-- Refund items -->
        <div class="hidden" id="refund">
            <div class="refund-container">
                <?php if (!empty($items) || !empty($services)): ?>
                    <div class="return-section">
                        <h3>Create Return / Refund</h3>
                        <form name="refund_invoice" method="post" class="return-form">
                            <input type="hidden" name="order_id" value="<?= intval($order->id) ?>">
                            <input type="hidden" name="action" value="create_return">
                            <input type="hidden" name="original_reference" value="<?= esc_html($order->reference_num) ?>">

                            <!-- Items -->
                            <?php if (!empty($items)): ?>
                                <div class="return-items">
                                    <?php foreach ($items as $item): ?>
                                        <div class="return-item">
                                            <input type="checkbox" class="return-item-checkbox"
                                                name="return_items[]" id="return_items[<?= $item->id ?>]" value="<?= $item->id ?>" data-subtotal="<?= $item->sale_price ?>" data-gst="<?= $calculate_gst ?>" data-pst="<?= $calculate_pst ?>">
                                            <label for="return_items[<?= $item->id ?>]" class="item-content">
                                                <img class="item-image" src="<?= $item->image_url ?>" alt="<?= esc_attr($product->get_name()) ?>">
                                                <div class="item-info">
                                                    <p class="item-details">
                                                        <!-- <?= esc_html($product->get_name()) ?><br> -->
                                                        <?php if (!empty($item->sku)): ?>
                                                            SKU: <?= esc_html($item->sku) ?><br>
                                                        <?php endif; ?>

                                                        <?php if (!empty($item->serial)): ?>
                                                            Serial: <?= esc_html($item->serial) ?><br>
                                                        <?php endif; ?>

                                                        <?php if (!empty($item->sale_price)): ?>
                                                            Sale Price: <?= esc_html($item->sale_price) ?><br>
                                                        <?php endif; ?>

                                                        <?php if (!empty($item->discount_amount) && $item->discount_amount != 0): ?>
                                                            Discount: <?= esc_html($item->discount_amount) ?>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                        </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="return-info-totals">
                                <!-- Left: Form Fields -->
                                <div class="return-form-fields">
                                    <div class="form-group">
                                        <label for="reference">Reference Number:</label>
                                        <input type="text" id="reference" name="reference" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="date">Date:</label>
                                        <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="reason">Reason for return:</label>
                                        <textarea name="reason" id="reason" rows="3"></textarea>
                                    </div>
                                </div>

                                <!-- Right: Totals -->
                                <div class="return-totals">
                                    <p>Subtotal: $<span id="display-subtotal">0.00</span></p>
                                    <p>GST: $<span id="display-gst">0.00</span></p>
                                    <p>PST: $<span id="display-pst">0.00</span></p>
                                    <p>Total Refund: $<span id="display-total">0.00</span></p>
                                </div>
                            </div>

                            <div class="form-submit">
                                <?php submit_button('Process Return', 'primary', 'submit_return'); ?>
                                <button class="button cancel" id="cancel">Cancel</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    <?php endif; ?>
    </div>

    </div>

<?php
}

function delete_invoice($order_id)
{

    $order_id = absint($order_id);
    if (!$order_id) {
        return array("message" => "Order id is required to delete.");
    }

    global $wpdb;

    $orders_table      = $wpdb->prefix . 'mji_orders';
    $order_items_table = $wpdb->prefix . 'mji_order_items';
    $inventory_table   = $wpdb->prefix . 'mji_product_inventory_units';
    $service_table     = $wpdb->prefix . 'mji_services';
    $payments_table    = $wpdb->prefix . 'mji_payments';
    $layaways_table    = $wpdb->prefix . 'mji_layaways';
    $credits_table     = $wpdb->prefix . 'mji_credits';
    $returns_table     = $wpdb->prefix . 'mji_returns';

    $wpdb->query('SET autocommit = 0');
    $wpdb->query('START TRANSACTION');

    try {
        $stock_adjustments = [];

        $return = $wpdb->get_row($wpdb->prepare("
            SELECT *
            FROM {$returns_table} 
            WHERE order_id = %d
        ", $order_id));

        check_wpdb_error($wpdb);

        if (!is_empty($return)) {
            throw new Exception("Items already returned in this order, Unable to proceed");
        }

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

        // grabbing the layaway and credit so that we refill those since they no longer used it
        $payment_items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT layaway_id, credit_id, amount 
                 FROM {$payments_table}
                 WHERE order_id = %d AND transaction_type IN ('layaway_redemption', 'credit_redemption')",
                $order_id
            )
        );

        check_wpdb_error($wpdb);

        if (!empty($payment_items)) {
            foreach ($payment_items as $payment_item) {

                if (!empty($payment_item->layaway_id)) {
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
                } elseif (!empty($payment_item->credit_id)) {
                    $remaining_amount = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT remaining_amount FROM {$credits_table} WHERE id = %d",
                            $payment_item->credit_id
                        )
                    );

                    check_wpdb_error($wpdb);
                    $amount_redeemed = $remaining_amount + $payment_item->amount;
                    $wpdb->update(
                        $credits_table,
                        [
                            'remaining_amount' => $amount_redeemed,
                            'status' => 'active',
                        ],
                        ['id' => $payment_item->credit_id],
                        ['%f', '%s'],
                        ['%d']
                    );
                    check_wpdb_error($wpdb);
                }
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
                p.layaway_id,
                p.credit_id,
                p.reference_num,
                p.payment_date,
                p.notes,
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
            FROM {$payments_table} p
            LEFT JOIN {$customers_table} c ON c.id = p.customer_id
            LEFT JOIN {$salespeople_table} s ON s.id = p.salesperson_id
            WHERE p.reference_num = %s
            AND p.transaction_type IN ('layaway_deposit', 'credit_deposit')
        ", $reference_num);

        $results = $wpdb->get_row($sql_layaway);
        check_wpdb_error($wpdb);

        if (!$results) return null;

        // Payments used to fill the layaway/credit
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

        $usage = null;
        // IF credit/layawyay used
        if ($results->layaway_id) {
            $sql_layaway_used = $wpdb->prepare("
                SELECT
                        p.reference_num,
                        p.amount,
                        p.payment_date
                        FROM {$payments_table} p
                    WHERE p.layaway_id = %d AND transaction_type = 'layaway_redemption'
                ", $results->layaway_id);

            $usage  = $wpdb->get_results($sql_layaway_used);
            check_wpdb_error($wpdb);
            $results->type = 'layaway';
        } elseif ($results->credit_id) {
            $sql_credit_used = $wpdb->prepare("
                SELECT
                        p.reference_num,
                        p.amount,
                        p.payment_date
                        FROM {$payments_table} p
                    WHERE p.credit_id = %d AND transaction_type = 'credit_redemption'
                ", $results->credit_id);

            $usage  = $wpdb->get_results($sql_credit_used);
            check_wpdb_error($wpdb);
            $results->type = 'credit';
        }

        $results->usage = $usage;
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
    $type = $invoice->type;
?>
    <div style="max-width:700px;">

        <h2><?php echo ucfirst(esc_html($type)); ?> Invoice #<?php echo esc_html($invoice->reference_num); ?></h2>

        <p><strong>Bill To</strong></p>
        <p>
            <?php echo implode('<br>', $bill_to_lines); ?><br />
            <strong>Served By:</strong>
            <?php echo esc_html(
                $invoice->salesperson_first_name . ' ' . $invoice->salesperson_last_name
            ); ?><br>

            <strong>Date Created:</strong>
            <?php echo esc_html($purchased_date); ?>
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
        if (empty($invoice->usage)) :
        ?>
            <form method="post" onsubmit="return confirm('Delete this <?php echo esc_html($type === 'credit' ? 'credit' : 'layaway'); ?>? This cannot be undone.');">
                <?php wp_nonce_field('delete_' . $type . '_action', 'delete_' . $type . '_nonce'); ?>
                <input type="hidden" name="action" value="delete_<?php echo esc_attr($type); ?>">

                <?php if ($type === 'layaway'): ?>
                    <input type="hidden" name="layaway_id" value="<?php echo esc_attr($invoice->layaway_id); ?>">
                <?php elseif ($type === 'credit'): ?>
                    <input type="hidden" name="credit_id" value="<?php echo esc_attr($invoice->credit_id); ?>">
                <?php endif; ?>

                <button type="submit" class="button button-danger">
                    Delete <?php echo ucfirst(esc_html($type)); ?>
                </button>
            </form>
        <?php else : ?>

            <p><i>We can not delete the <?= $type ?> as it has been used, Will have to delete the invoices using this layaway before we can delete this.</i></p>
            <h3><?php echo ucfirst(esc_html($type)); ?> Usage</h3>

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
                    $total = 0.00;

                    foreach ($invoice->usage as $used) :
                        $amount = (float) $used->amount;
                        $total += $amount;
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
                        <th colspan="2" align="right"><?php echo ucfirst(esc_html($type)); ?> Total</th>
                        <th align="right">$<?php echo number_format($total, 2); ?></th>
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
        return array("success" => false, "message" => "Layaway id is required to delete.");
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
            return array("success" => false, "message" => "No Layaway found with that ID.");
        }

        if ($layaway_amount->total_amount == $layaway_amount->remaining_amount) {
            $wpdb->delete($payments_table, ['layaway_id' => $layaway_id], ['%d']);
            check_wpdb_error($wpdb);
            $wpdb->delete($layaways_table, ['id' => $layaway_id], ['%d']);
            check_wpdb_error($wpdb);
            $wpdb->query('COMMIT');
            return [
                'success' => true,
                "message" => "Layaway successfully deleted."
            ];
        } else {
            return [
                'success' => false,
                "message" => "Layaway already used so unable to delete."
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

function delete_credit($id)
{

/*

Things TODO 

1. Change item status back to sold
2. Decrease item quantity
3. Delete from inventory status history
4. Delete from return and return items table
5. Delete from credit and payment table

*/
    $credit_id = absint($id);
    if (!$credit_id) {
        return array("success" => false, "message" => "Credit id is required to delete.");
    }

    global $wpdb;

    $credits_table = $wpdb->prefix . 'mji_credits';
    $payments_table = $wpdb->prefix . 'mji_payments';
    $wpdb->query('START TRANSACTION');

    try {
        $credit_amount = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT total_amount, remaining_amount 
                 FROM {$credits_table}
                 WHERE id = %d",
                $credit_id
            )
        );

        check_wpdb_error($wpdb);

        if (!$credit_amount) {
            return array("message" => "No Credits found with that ID.");
        }

        if ($credit_amount->total_amount == $credit_amount->remaining_amount) {
            $wpdb->delete($payments_table, ['credit_id' => $credit_id], ['%d']);
            check_wpdb_error($wpdb);
            $wpdb->delete($credits_table, ['id' => $credit_id], ['%d']);
            check_wpdb_error($wpdb);
            $wpdb->query('COMMIT');
            return [
                'success' => true,
                "message" => "Credit successfully deleted."
            ];
        } else {
            return [
                'success' => false,
                "message" => "Credit already used so unable to delete."
            ];
        }
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        custom_log('[Delete Credit Invoice Failed] ' . $e->getMessage());
        return [
            'success' => false,
            'message' => '[Delete Credit Invoice Failed] ' . $e->getMessage(),
        ];
    }
}

function create_return()
{
    $data = sanitize_and_validate_return($_POST);

    $order = order_exists($data['order_id']);
    if (!$order) {
        wp_send_json_error(['message' => 'Order does not exist'], 404);
    }

    // get the order item ids to see if they match the items order id
    $order_item_ids = get_order_items($data['order_id']);

    if (! order_items_valid($order_item_ids, $data['return_items'])) {
        wp_send_json_error(['message' => 'One or more return items are invalid'], 422);
    }

    $already_returned = check_already_returned($data['return_items']);
    if ($already_returned) {
        wp_send_json_error(['message' => 'Some selected items have already been returned'], 409);
    }

    insert_return_transactions($data, $order);
}

add_action('wp_ajax_create_return', 'create_return');

function sanitize_and_validate_return($post_data)
{
    $data = wp_unslash($post_data);

    $sanitized = [
        'order_id'     => isset($data['order_id']) ? absint($data['order_id']) : 0,
        'return_items' => isset($data['return_items']) && is_array($data['return_items'])
            ? array_map('absint', $data['return_items'])
            : [],
        'reference'             => isset($data['reference']) ? sanitize_text_field($data['reference']) : '',
        'gst_total'             => isset($data['gst']) ? round((float) $data['gst'], 2) : '',
        'pst_total'             => isset($data['pst']) ? round((float) $data['pst'], 2) : '',
        'subtotal'              => isset($data['subtotal']) ? round((float) $data['subtotal'], 2) : '',
        'total'                 => isset($data['total']) ? round((float) $data['total'], 2) : '',
        'date'                  => isset($data['date']) ? sanitize_text_field($data['date']) : '',
        'reason'                => isset($data['reason']) ? sanitize_textarea_field($data['reason']) : '',
        'original_reference'    => isset($data['original_reference']) ? sanitize_text_field($data['original_reference']) : '',
    ];

    $errors = [];

    if ($sanitized['order_id'] <= 0) {
        $errors['message'] = 'Invalid order ID';
    }

    if (empty($sanitized['return_items'])) {
        $errors['message'] = 'No return items provided';
    }

    $date = DateTime::createFromFormat('Y-m-d', $sanitized['date']);
    if (! $date || $date->format('Y-m-d') !== $sanitized['date']) {
        $errors['date'] = 'Invalid date format';
    }

    if (! empty($errors)) {
        wp_send_json_error([
            'message' => $errors['message'],
        ], 422);
    }

    return $sanitized;
}

function order_exists($order_id)
{
    global $wpdb;
    $order_table = $wpdb->prefix . "mji_orders";
    $payment_table = $wpdb->prefix . "mji_payments";

    return $wpdb->get_row($wpdb->prepare("
            SELECT 
                o.*,
                p.location_id
            FROM {$order_table} o
            LEFT JOIN {$payment_table} p 
            ON p.order_id = o.id
            WHERE o.id = %d
        ", $order_id));
}

function get_order_items($order_id)
{
    global $wpdb;
    $order_items_table = $wpdb->prefix . "mji_order_items";
    return $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM $order_items_table WHERE order_id = %d",
        $order_id
    ));
}

function order_items_valid($order_item_ids, $selected_item_ids)
{
    return empty(array_diff($selected_item_ids, $order_item_ids));
}

function check_already_returned($item_ids)
{
    global $wpdb;
    $return_items_table = $wpdb->prefix . "mji_return_items";
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $return_items_table WHERE order_item_id IN (" . implode(',', $item_ids) . ")",
    ));
    return $count > 0;
}

function insert_return_transactions($data, $order)
{
    global $wpdb;

    $GST_RATE = 0.05;
    $PST_RATE = 0.07;
    $return_table = $wpdb->prefix . "mji_returns";
    $customer_table = $wpdb->prefix . "mji_customers";
    $return_items_table = $wpdb->prefix . "mji_return_items";
    $payment_table = $wpdb->prefix . "mji_payments";
    $credit_table = $wpdb->prefix . "mji_credits";
    $inventory_status_history_table = $wpdb->prefix . "mji_inventory_status_history";
    $product_inventory_units_table = $wpdb->prefix . "mji_product_inventory_units";
    $mji_order_items_table = $wpdb->prefix . "mji_order_items";
    $mji_orders_table = $wpdb->prefix . "mji_orders";
    $order_item_ids = $data['return_items'];
    $items_data = [];

    $order_items = $wpdb->get_results(
        "SELECT 
            oi.id AS order_item_id,
            oi.sale_price,
            oi.product_inventory_unit_id,
            pi.wc_product_id,
            pi.wc_product_variant_id,
            pi.sku,
            pi.serial,
            o.gst_total,
            o.pst_total
        FROM $mji_order_items_table oi
        JOIN $product_inventory_units_table pi
            ON pi.id = oi.product_inventory_unit_id
        JOIN $mji_orders_table o
            ON o.id = oi.order_id
        WHERE oi.id IN (" . implode(',', $order_item_ids) . ")"
    );
    $gst_total = 0;
    $pst_total = 0;
    $subtotal = 0;
    $total = 0;

    foreach ($order_items as $item) {
        $subtotal += (float)$item->sale_price;
        if ($item->gst_total > 0) {
            $gst_total += round($item->sale_price * $GST_RATE, 2);
        }
        if ($item->pst_total > 0) {
            $pst_total += round($item->sale_price * $PST_RATE, 2);
        }
    }

    $total = $gst_total + $pst_total + $subtotal;

    if (abs($data['gst_total'] - $gst_total) > 0.01) {
        wp_send_json_error(['message' => 'GST total mismatch. Provided GST: ' . $data['gst_total'] . ' and calculated GST:' . $gst_total], 422);
    }

    if (abs($data['pst_total'] - $pst_total) > 0.01) {
        wp_send_json_error(['message' => 'PST total mismatch. Provided PST: ' . $data['pst_total'] . ' and calculated PST:' . $pst_total], 422);
    }

    if (abs($data['subtotal'] - $subtotal) > 0.01) {
        wp_send_json_error(['message' => 'Subtotal mismatch. Provided subtotal: ' . $data['subtotal'] . ' and calculated subtotal:' . $subtotal], 422);
    }

    if (abs($data['total'] - $total) > 0.01) {
        wp_send_json_error(['message' => 'Total mismatch. Provided total: ' . $data['total'] . ' and calculated total:' . $total], 422);
    }

    $restored_stock = [];
    $wpdb->query('START TRANSACTION');
    try {

        // Insert into returns
        $wpdb->insert(
            $return_table,
            [
                'order_id'    => $data['order_id'],
                'reference_num'   => $data['reference'],
                'return_date' => $data['date'],
                'reason'      => $data['reason'],
                'subtotal'    => $subtotal,
                'gst_total'   => $gst_total,
                'pst_total'   => $pst_total,
                'total'       => $total,
            ],
            ['%d', '%s', '%s', '%s', '%f', '%f', '%f', '%f']
        );
        $return_id = $wpdb->insert_id;

        if (!$return_id) {
            throw new RuntimeException("Failed to insert return: " . $wpdb->last_error);
        }
        // Inert into return item
        foreach ($order_items as $item) {
            $success = $wpdb->insert(
                $return_items_table,
                [
                    'return_id'     => $return_id,
                    'order_item_id' => $item->order_item_id,
                    'product_inventory_unit_id' => $item->product_inventory_unit_id,
                    'unit_price' => $item->sale_price,
                ],
                ['%d', '%d', '%d', '%f']
            );
            if (!$success) {
                throw new RuntimeException("Failed to insert return item: " . $wpdb->last_error);
            }
        }

        // Issue credit to customer in payements
        $success = $wpdb->insert(
            $credit_table,
            [
                'customer_id'       => $order->customer_id,
                'location_id'       => $order->location_id,
                'reference_num'     => $data['reference'],
                'total_amount'      => $total,
                'remaining_amount'  => $total,
                'status'            => "active",
                'created_at'        => $data['date'],
            ],
            ['%d', '%d', '%s', '%f', '%f', '%s', '%s']
        );
        $credit_id = $wpdb->insert_id;

        if (!$credit_id) {
            throw new RuntimeException("Failed to insert credit: " . $wpdb->last_error);
        }
        $success = $wpdb->insert(
            $payment_table,
            [
                'customer_id'       => $order->customer_id,
                'salesperson_id'    => $order->salesperson_id,
                'location_id'       => $order->location_id,
                'order_id'          => $data['order_id'],
                'credit_id'         => $credit_id,
                'reference_num'     => $data['reference'],
                'method'            => 'credit',
                'amount'            => $total,
                'transaction_type'  => 'credit_deposit',
                'payment_date'      => $data['date'],
                'notes'             => $data['reason'],
            ],
            ['%d', '%d', '%d', '%d', '%d', '%s', '%s', '%f', '%s', '%s', '%s']
        );

        if (!$success) {
            throw new RuntimeException("Failed to insert payment: " . $wpdb->last_error);
        }

        // Change the item status in inventory_status_history, product_inventory_units table and also woocommerce stock  
        foreach ($order_items as $item) {

            $items_info = [];

            $success = $wpdb->insert(
                $inventory_status_history_table,
                [
                    'inventory_unit_id' => $item->product_inventory_unit_id,
                    'from_status'       => "sold",
                    'to_status'         => "in_stock",
                    'reference_num'     => $data['reference'],
                    'created_at'        => $data['date'],
                ],
                ['%d', '%s', '%s', '%s', '%s']
            );

            if (!$success) {
                throw new RuntimeException("Failed to insert in status history table: " . $wpdb->last_error);
            }

            $success = $wpdb->update(
                $product_inventory_units_table,
                [
                    'status' => 'in_stock',
                ],
                ['id' => $item->product_inventory_unit_id],
                ['%s'],
                ['%d']
            );

            if ($success === false) {
                throw new RuntimeException("Failed to update product inventory units table: " . $wpdb->last_error);
            }

            $product_id = $item->wc_product_variant_id ?: $item->wc_product_id;
            $product = wc_get_product($product_id);
            if (!$product) {
                throw new RuntimeException("Invalid WooCommerce product ID: {$product_id}");
            }
            $image_url = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
            $items_info['image_url'] = $image_url;
            $items_info['sku'] = $item->sku;
            $items_info['serial'] = $item->serial;
            $items_info['price'] = $item->sale_price;
            if ($item->wc_product_variant_id) {
                $items_info['description'] = $product->get_description();
            } else {
                $items_info['description'] = $product->get_short_description();
            }
            $items_data[] = $items_info;
            $product->set_stock_quantity($product->get_stock_quantity() + 1);
            $product->save();
            $restored_stock[] = $product_id;
        }
        $wpdb->query('COMMIT');

        $totals = [
            'subtotal' => $data['subtotal'],
            'gst' => $data['gst_total'],
            'pst' => $data['pst_total'],
            'total' => $data['total'],
        ];

        // Grab customer and salesperson info for the print receipt
        $customer_id = $order->customer_id;
        $salesperson_id = $order->salesperson_id;
        $all_salepeople = mji_get_salespeople();
        $salesperson = array_find($all_salepeople, fn($p) => $p->id == $salesperson_id);


        $query = $wpdb->prepare("SELECT * FROM $customer_table WHERE id = %d", $customer_id);
        $customer_info = $wpdb->get_row($query);

        wp_send_json_success([
            'items' => $items_data,
            'totals' => $totals,
            'reference_num' => $data['reference'],
            'salesperson' => $salesperson,
            'customer_info' => $customer_info,
            'date' => $data['date'],
            'original_reference' => $data['original_reference']
        ]);
    } catch (Exception $e) {

        // restore WooCommerce stock
        foreach ($restored_stock as $product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                $product->set_stock_quantity($product->get_stock_quantity() - 1);
                $product->save();
            }
        }

        custom_log($e->getMessage());
        $wpdb->query('ROLLBACK');
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
