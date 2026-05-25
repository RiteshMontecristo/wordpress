<?php

function find_page()
{
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'sales';
    $allowed_tabs = ['sales', 'layaway', 'refund'];

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

    $refund_url = add_query_arg(
        'tab',
        'refund',
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
            <a href="<?php echo esc_url($refund_url); ?>"
                class="nav-tab <?php echo $active_tab === 'refund' ? 'nav-tab-active' : ''; ?>">
                Refund
            </a>
        </h2>

        <?php render_search_section(); ?>
    </div>

<?php
}

// Reports sales Section
function render_search_section(): void
{
    echo '<hr>';
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'sales';
    if (isset($_POST['action'])) {

        if (
            $_POST['action'] === 'delete_invoice'
            && isset($_POST['order_id'], $_POST['delete_invoice_nonce'])
            && wp_verify_nonce($_POST['delete_invoice_nonce'], 'delete_invoice_action')
        ) {
            $reference_num = sanitize_text_field($_POST['order_id']);
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
            && isset($_POST['reference_num'], $_POST['delete_credit_nonce'])
            && wp_verify_nonce($_POST['delete_credit_nonce'], 'delete_credit_action')
        ) {
            $reference_num = sanitize_text_field($_POST['reference_num']);
            $result = delete_credit($reference_num);

            if ($result['success']) {
                echo '<div class="notice notice-success"><p>Credit deleted successfully.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
            }
        } elseif (
            $_POST['action'] === 'delete_refund'
            && isset($_POST['reference_num'], $_POST['delete_refund_nonce'])
            && wp_verify_nonce($_POST['delete_refund_nonce'], 'delete_refund_action')
        ) {
            $reference_num = sanitize_text_field($_POST['reference_num']);
            $result = delete_refund($reference_num);

            if ($result['success']) {
                echo '<div class="notice notice-success"><p>Refund deleted successfully.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }
    } else if (isset($_GET['reference_num'])) {

        $reference_num = sanitize_text_field($_GET['reference_num']);

        if ($active_tab == "sales") {
            try {
                $results = reports_search_sales_results($reference_num);
                if (!$results) {
                    echo "<div class='wrap'>";
                    echo "<h2>Invoice " . esc_html($reference_num) . " not found!!";
                    echo "</div>";
                } else {
                    render_invoice($results);
                    return;
                }
            } catch (Exception $e) {
                echo "<div class='wrap'>";
                echo "<h2>" . esc_html($e->getMessage()) . "</h2>";
                echo "</div>";
            }
        } else if ($active_tab == "layaway") {
            try {
                $results = search_layaway_results($reference_num);
                if (!$results) {
                    echo "<div class='wrap'>";
                    echo "<h2>Invoice " . esc_html($reference_num) . " not found!!";
                    echo "</div>";
                } else {
                    render_layaway_invoice($results);
                    return;
                }
            } catch (Exception $e) {
                echo "<div class='wrap'>";
                echo "<h2>" . esc_html($e->getMessage()) . "</h2>";
                echo "</div>";
            }
        } else {
            try {
                $results = search_refund_results($reference_num);
                if (!$results) {
                    echo "<div class='wrap'>";
                    echo "<h2>Invoice " . esc_html($reference_num) . " not found!!";
                    echo "</div>";
                } else {
                    render_refund_invoice($results);
                    return;
                }
            } catch (Exception $e) {
                echo "<div class='wrap'>";
                echo "<h2>" . esc_html($e->getMessage()) . "</h2>";
                echo "</div>";
            }
        }
    }

?>
    <form method="get" action="">
        <input type="hidden" name="page" value="invoice-management">
        <input type="hidden" name="tab" value="<?= esc_attr($active_tab) ?>">
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
        if (!$order)
            return null;

        $order_id = $order->id;
        $items = $wpdb->get_results($wpdb->prepare("
            SELECT
                oi.*,
                inv.sku,
                inv.wc_product_id,
                inv.wc_product_variant_id,
                inv.serial,
                inv.notes,
                inv.name,
                inv.description,
                inv.image_id,
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
            SELECT p.id, p.method, p.amount, p.transaction_type, p.layaway_id, p.credit_id,
                   l.reference_num AS layaway_ref,
                   c.reference_num AS credit_ref
            FROM {$payments_table} p
            LEFT JOIN {$wpdb->prefix}mji_layaways l ON l.id = p.layaway_id
            LEFT JOIN {$wpdb->prefix}mji_credits  c ON c.id = p.credit_id
            WHERE p.reference_num = %s
        ", $reference_num));

        if ($wpdb->last_error) {
            throw new Exception($wpdb->last_error);
        }

        return [
            'order' => $order,
            'items' => $items,
            'services' => $services,
            'payments' => $payments
        ];
    } catch (Exception $e) {
        custom_log($e->getMessage());
        throw $e;
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
            <h2>Sale Invoice #<?= esc_html($order->reference_num) ?></h2>

            <!-- Customer & Invoice Info -->
            <div style="margin-bottom:20px;">
                <p><strong>Customer:</strong>
                    <?= esc_html($order->customer_first_name . ' ' . $order->customer_last_name) ?></p>
                <p><strong>Address:</strong>
                    <?= esc_html($order->street_address . ', ' . $order->city . ', ' . $order->province . ' ' . $order->postal_code . ', ' . $order->country) ?>
                </p>
                <p><strong>Served by:</strong>
                    <?= esc_html($order->salesperson_first_name . ' ' . $order->salesperson_last_name) ?></p>
                <p><strong>Date:</strong> <?= esc_html(date('F j, Y', strtotime($order->created_at))) ?></p>
                <p><strong>Notes:</strong> <?= esc_html($order->notes) ?></p>
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
                                    $item->display_name = $item->name ?? '';
                                    $item->image_url    = esc_url(mji_get_unit_image_url($item, 'thumbnail'));

                                    echo "<img src='" . esc_url($item->image_url) . "' alt='product image' />";
                                    echo "<p>";
                                    if (!empty($item->sku))
                                        echo "<b>SKU: " . esc_html($item->sku) . "</b><br/>";
                                    echo nl2br(wp_kses_post($item->description));
                                    if (!empty($item->serial))
                                        echo "<br />Serial: " . esc_html($item->serial);
                                    if (!empty($item->notes))
                                        echo "<br />Notes: " . esc_html($item->notes);
                                    echo "<br />Price: " . esc_html($item->sale_price);
                                    echo "</p>";
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
                                    if (!empty($service->category))
                                        $parts[] = esc_html($service->category);
                                    if (!empty($service->description))
                                        $parts[] = esc_html($service->description);
                                    echo implode(' <br />  ', $parts);
                                    echo "<br /> Price: " . esc_html($service->sold_price);
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
                                    <?php
                                    if ($payment->method === 'layaway' && $payment->layaway_ref) {
                                        echo 'Layaway# ' . esc_html($payment->layaway_ref);
                                    } elseif ($payment->method === 'credit' && $payment->credit_ref) {
                                        echo 'Credit# ' . esc_html($payment->credit_ref);
                                    } else {
                                        echo esc_html(ucwords(str_replace('_', ' ', $payment->method)));
                                    }
                                    ?>: $<?= number_format($payment->amount, 2) ?>
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
            <form method="post" style="margin-top:20px; position: relative; z-index:1;"
                onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                <?php wp_nonce_field('delete_invoice_action', 'delete_invoice_nonce'); ?>
                <input type="hidden" name="order_id" value="<?= intval($order->id) ?>">
                <input type="hidden" name="action" value="delete_invoice">
                <?php submit_button('Delete Invoice', 'primary', 'delete_invoice'); ?>
                <button type="button" class="button edit-invoice-btn" id="edit-invoice-btn">Edit Invoice</button>
                <button type="button" class="button issue_credit" id="issue_credit">Issue credit</button>
                <button type="button" class="button issue_refund" id="issue_refund">Issue refund</button>
                <button type="button" class="button print" id="main-print-btn">Print</button>
            </form>
        </div>

        <div class="hidden" id="edit-invoice">
            <div class="edit-invoice-container">
                <?php render_edit_sale_form($results); ?>
            </div>
        </div>

        <!-- Credit items -->
        <div class="hidden" id="credit">
            <div class="credit-container">
                <?php if (!empty($items) || !empty($services)): ?>
                    <div class="return-section">
                        <h3>Create Return / Credit</h3>
                        <form name="credit_invoice" method="post" class="return-form">
                            <input type="hidden" name="order_id" value="<?= intval($order->id) ?>">
                            <input type="hidden" name="action" value="create_credit_return">
                            <input type="hidden" name="original_reference" value="<?= esc_html($order->reference_num) ?>">

                            <!-- Items -->
                            <?php if (!empty($items)): ?>
                                <div class="return-items">
                                    <?php foreach ($items as $item): ?>
                                        <div class="return-item">
                                            <input type="checkbox" class="return-item-checkbox" name="return_items[]"
                                                id="return_items[<?= esc_attr($item->id) ?>]" value="<?= esc_attr($item->id) ?>"
                                                data-subtotal="<?= esc_attr($item->sale_price) ?>" data-gst="<?= esc_attr($calculate_gst) ?>"
                                                data-pst="<?= esc_attr($calculate_pst) ?>">
                                            <label for="return_items[<?= esc_attr($item->id) ?>]" class="item-content">
                                                <img class="item-image" src="<?= esc_url($item->image_url) ?>"
                                                    alt="<?= esc_attr($item->display_name) ?>">
                                                <div class="item-info">
                                                    <p class="item-details">
                                                        Order ID: <?= esc_html($item->id) ?><br>
                                                        <?= esc_html($item->display_name) ?><br>
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
                                                        <input class="refund_price" name="refund_prices[<?= esc_attr($item->id) ?>]" step="0.01"
                                                            type="number" value="<?= esc_attr($item->sale_price) ?>"
                                                            max="<?= esc_attr($item->sale_price) ?>" />
                                                    </p>
                                                </div>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- services -->
                            <?php if (!empty($services)): ?>
                                <div class="return-items">
                                    <?php foreach ($services as $service):
                                    ?>
                                        <div class="return-item">
                                            <input type="checkbox" class="return-item-checkbox" name="return_services[]"
                                                id="return_services[<?= esc_attr($service->id) ?>]" value="<?= esc_attr($service->id) ?>"
                                                data-subtotal="<?= esc_attr($service->sold_price) ?>" data-gst="<?= esc_attr($calculate_gst) ?>"
                                                data-pst="<?= esc_attr($calculate_pst) ?>">
                                            <label for="return_services[<?= esc_attr($service->id) ?>]" class="item-content">
                                                <?= wc_placeholder_img([150, 150]); ?>
                                                <div class="item-info">
                                                    <p class="item-details">
                                                        Order ID: <?= esc_html($service->id) ?><br>
                                                        <?= esc_html($service->category) ?><br>
                                                        <?php if (!empty($service->description)): ?>
                                                            Description: <?= esc_html($service->description) ?><br>
                                                        <?php endif; ?>

                                                        <?php if (!empty($service->sold_price)): ?>
                                                            Sale Price: <?= esc_html($service->sold_price) ?><br>
                                                        <?php endif; ?>

                                                        <input class="refund_price" name="refund_prices[<?= esc_attr($service->id) ?>]"
                                                            step="0.01" type="number" value="<?= esc_attr($service->sold_price) ?>"
                                                            max="<?= esc_attr($service->sold_price) ?>" />
                                                </div>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="return-info-totals">
                                <!-- Left: Form Fields -->
                                <div class="return-form-fields">
                                    <div class="form-group">
                                        <label for="reference">Reference Number:</label>
                                        <input type="text" id="reference" name="reference" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="item_returned">Item Returned:</label>
                                        <select id="item_returned" name="item_returned">
                                            <option value="yes">Yes</option>
                                            <option value="no">No</option>
                                        </select>
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
                                    <p>Total Credit: $<span id="display-total">0.00</span></p>
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

        <!-- Refund items -->
        <div class="hidden" id="refund">
            <div class="refund-container">
                <?php if (!empty($items) || !empty($services)): ?>
                    <div class="return-section">
                        <h3>Create Return / Refund</h3>
                        <form name="refund_invoice" method="post" class="return-form">
                            <input type="hidden" name="order_id" value="<?= intval($order->id) ?>">
                            <input type="hidden" name="action" value="create_refund_return">
                            <input type="hidden" name="original_reference" value="<?= esc_html($order->reference_num) ?>">

                            <!-- Items -->
                            <?php if (!empty($items)): ?>
                                <div class="return-items">
                                    <?php foreach ($items as $item):
                                    ?>
                                        <div class="return-item">
                                            <input type="checkbox" class="return-item-checkbox" name="refund_items[]"
                                                id="refund_items[<?= esc_attr($item->id) ?>]" value="<?= esc_attr($item->id) ?>"
                                                data-subtotal="<?= esc_attr($item->sale_price) ?>" data-gst="<?= esc_attr($calculate_gst) ?>"
                                                data-pst="<?= esc_attr($calculate_pst) ?>">
                                            <label for="refund_items[<?= esc_attr($item->id) ?>]" class="item-content">
                                                <img class="item-image" src="<?= esc_url($item->image_url) ?>"
                                                    alt="<?= esc_attr($item->display_name) ?>">
                                                <div class="item-info">
                                                    <p class="item-details">
                                                        Order ID: <?= esc_html($item->id) ?><br>
                                                        <?= esc_html($item->display_name) ?><br>
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
                                                        <input class="refund_price" name="refund_prices[<?= esc_attr($item->id) ?>]" step="0.01"
                                                            type="number" value="<?= esc_attr($item->sale_price) ?>"
                                                            max="<?= esc_attr($item->sale_price) ?>" />
                                                    </p>
                                                </div>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- services -->
                            <?php if (!empty($services)): ?>
                                <div class="return-items">
                                    <?php foreach ($services as $service):
                                    ?>
                                        <div class="return-item">
                                            <input type="checkbox" class="return-item-checkbox" name="refund_services[]"
                                                id="refund_services[<?= esc_attr($service->id) ?>]" value="<?= esc_attr($service->id) ?>"
                                                data-subtotal="<?= esc_attr($service->sold_price) ?>" data-gst="<?= esc_attr($calculate_gst) ?>"
                                                data-pst="<?= esc_attr($calculate_pst) ?>">
                                            <label for="refund_services[<?= esc_attr($service->id) ?>]" class="item-content">
                                                <?= wc_placeholder_img([150, 150]); ?>
                                                <div class="item-info">
                                                    <p class="item-details">
                                                        Order ID: <?= esc_html($service->id) ?><br>
                                                        <?= esc_html($service->category) ?><br>
                                                        <?php if (!empty($service->description)): ?>
                                                            Description: <?= esc_html($service->description) ?><br>
                                                        <?php endif; ?>

                                                        <?php if (!empty($service->sold_price)): ?>
                                                            Sale Price: <?= esc_html($service->sold_price) ?><br>
                                                        <?php endif; ?>

                                                        <input class="refund_price" name="refund_prices[<?= esc_attr($service->id) ?>]"
                                                            step="0.01" type="number" value="<?= esc_attr($service->sold_price) ?>"
                                                            max="<?= esc_attr($service->sold_price) ?>" />
                                                </div>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="return-info-totals">
                                <!-- Left: Form Fields -->
                                <div class="return-form-fields">
                                    <div class="form-group">
                                        <label for="refund-reference">Reference Number:</label>
                                        <input type="text" id="refund-reference" name="refund-reference" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="item_returned">Item Returned:</label>
                                        <select id="item_returned" name="item_returned">
                                            <option value="yes">Yes</option>
                                            <option value="no">No</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="refund-date">Date:</label>
                                        <input type="date" id="refund-date" name="refund-date"
                                            value="<?php echo date('Y-m-d'); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="refund-reason">Reason for return:</label>
                                        <textarea name="refund-reason" id="refund-reason" rows="3"></textarea>
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

                            <div class="payment-methods">
                                <h3>Refund Payment Methods</h3>
                                <div class="payment-grid">
                                    <div class="payment-item">
                                        <label for="cash">Cash:</label>
                                        <input type="number" min="0" step="0.01" id="cash" name="cash">
                                    </div>

                                    <div class="payment-item">
                                        <label for="cheque">Cheque:</label>
                                        <input type="number" min="0" step="0.01" id="cheque" name="cheque">
                                    </div>

                                    <div class="payment-item">
                                        <label for="debit">Debit/Interac:</label>
                                        <input type="number" min="0" step="0.01" id="debit" name="debit">
                                    </div>

                                    <div class="payment-item">
                                        <label for="visa">Visa:</label>
                                        <input type="number" min="0" step="0.01" id="visa" name="visa">
                                    </div>

                                    <div class="payment-item">
                                        <label for="master_card">Mastercard:</label>
                                        <input type="number" min="0" step="0.01" id="master_card" name="master_card">
                                    </div>

                                    <div class="payment-item">
                                        <label for="amex">Amex:</label>
                                        <input type="number" min="0" step="0.01" id="amex" name="amex">
                                    </div>

                                    <div class="payment-item">
                                        <label for="bank_draft">Bank Draft:</label>
                                        <input type="number" min="0" step="0.01" id="bank_draft" name="bank_draft">
                                    </div>

                                    <div class="payment-item">
                                        <label for="cup">Cup:</label>
                                        <input type="number" min="0" step="0.01" id="cup" name="cup">
                                    </div>

                                    <div class="payment-item">
                                        <label for="alipay">Alipay:</label>
                                        <input type="number" min="0" step="0.01" id="alipay" name="alipay">
                                    </div>

                                    <div class="payment-item">
                                        <label for="wire">Wire:</label>
                                        <input type="number" min="0" step="0.01" id="wire" name="wire">
                                    </div>
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

    $orders_table = $wpdb->prefix . 'mji_orders';
    $order_items_table = $wpdb->prefix . 'mji_order_items';
    $inventory_table = $wpdb->prefix . 'mji_product_inventory_units';
    $inventory_history_table = $wpdb->prefix . 'mji_inventory_status_history';
    $service_table = $wpdb->prefix . 'mji_services';
    $payments_table = $wpdb->prefix . 'mji_payments';
    $layaways_table = $wpdb->prefix . 'mji_layaways';
    $credits_table = $wpdb->prefix . 'mji_credits';
    $returns_table = $wpdb->prefix . 'mji_returns';

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

        if (!empty($return)) {
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

            $reference_num = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT reference_num 
                 FROM {$orders_table}
                 WHERE id = %d",
                    $order_id
                )
            )->reference_num;

            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$inventory_history_table} WHERE reference_num = %s",
                $reference_num
            ));
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

    $payments_table = $wpdb->prefix . 'mji_payments';
    $customers_table = $wpdb->prefix . 'mji_customers';
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';
    $returns_table = $wpdb->prefix . 'mji_returns';
    $order_items_table = $wpdb->prefix . 'mji_order_items';
    $return_items_table = $wpdb->prefix . 'mji_return_items';
    $services_table = $wpdb->prefix . 'mji_services';
    $return_services_table = $wpdb->prefix . 'mji_return_services';
    $product_inventory_units_table = $wpdb->prefix . 'mji_product_inventory_units';

    try {
        $sql_layaway = $wpdb->prepare("
            SELECT
                p.layaway_id,
                p.credit_id,
                p.order_id,
                p.reference_num,
                p.payment_date,
                p.notes,
                p.salesperson_id,
                p.customer_id,
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

        if (!$results)
            return null;

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

        $payments = $wpdb->get_results($sql_payments);
        check_wpdb_error($wpdb);

        $results->payment = $payments;

        $items = null;
        $usage = null;
        // IF credit/layawyay used
        if ($results->layaway_id) {
            $sql_layaway_used = $wpdb->prepare("
                SELECT
                        p.reference_num,
                        p.amount,
                        p.payment_date
                        FROM {$payments_table} p
                    WHERE p.layaway_id = %d AND transaction_type IN ('layaway_redemption', 'refund')
                ", $results->layaway_id);

            $usage = $wpdb->get_results($sql_layaway_used);
            check_wpdb_error($wpdb);
            $results->type = 'layaway';
        } elseif ($results->credit_id) {
            $sql_credit_used = $wpdb->prepare("
                SELECT
                        p.reference_num,
                        p.amount,
                        p.payment_date
                        FROM {$payments_table} p
                    WHERE p.credit_id = %d AND transaction_type IN ('credit_redemption', 'refund')
                ", $results->credit_id);

            $usage = $wpdb->get_results($sql_credit_used);
            check_wpdb_error($wpdb);

            $sql_get_return_items = $wpdb->prepare("
            SELECT
                r.reference_num,
                p.wc_product_id,
                p.wc_product_variant_id,
                p.sku,
                p.serial,
                p.name,
                p.description,
                p.image_id,
                ri.unit_price,
                oi.sale_price
            FROM {$returns_table} r
            JOIN {$return_items_table} ri ON r.id = ri.return_id
            JOIN {$order_items_table} oi ON oi.id = ri.order_item_id
            JOIN {$product_inventory_units_table} p ON p.id = ri.product_inventory_unit_id
            WHERE r.reference_num = %s
        ", "$reference_num");

            $return_items = $wpdb->get_results($sql_get_return_items);

            check_wpdb_error($wpdb);

            if ($return_items) {
                $items = [];
                foreach ($return_items as $return_item) {

                    $item = [
                        "sku"            => $return_item->sku,
                        "serial"         => $return_item->serial,
                        "description"    => $return_item->description ?? '',
                        "image_url"      => esc_url(mji_get_unit_image_url($return_item, 'thumbnail')),
                        "sold_price"     => $return_item->sale_price,
                        "returned_price" => $return_item->unit_price
                    ];

                    array_push($items, $item);
                }
            }

            $sql_get_return_services = $wpdb->prepare("
            SELECT
                r.reference_num,
                s.category,
                s.description,
                s.sold_price,
                rs.price
            FROM {$returns_table} r
            JOIN {$return_services_table} rs ON r.id = rs.return_id
            JOIN {$services_table} s ON s.id = rs.service_id
            WHERE r.reference_num = %s", "$reference_num");
            $return_services = $wpdb->get_results($sql_get_return_services);
            check_wpdb_error($wpdb);

            $results->return_services = $return_services;
            $results->items = $items;
            $results->type = 'credit';

            if (!empty($results->order_id)) {
                $original_order = $wpdb->get_row($wpdb->prepare(
                    "SELECT reference_num FROM {$wpdb->prefix}mji_orders WHERE id = %d",
                    $results->order_id
                ));
                $results->original_order_reference = $original_order ? $original_order->reference_num : null;
            }
        }

        $results->usage = $usage;
        return $results;
    } catch (Exception $e) {
        custom_log($e->getMessage());
        throw $e;
    }
}

function render_edit_sale_form($results)
{
    global $wpdb;

    $order       = $results['order'];
    $payments    = $results['payments'];
    $salespeople = mji_get_salespeople();

    $allowed_methods = [
        'cash',
        'cheque',
        'debit',
        'visa',
        'master_card',
        'amex',
        'bank_draft',
        'cup',
        'alipay',
        'gift_card',
        'wire'
    ];

    // Collect linked account IDs so we can include them even if status = redeemed
    $current_layaway_ids = array_values(array_filter(array_map(fn($p) => (int) $p->layaway_id, $payments)));
    $current_credit_ids  = array_values(array_filter(array_map(fn($p) => (int) $p->credit_id,  $payments)));

    $customer_layaways = [];
    $customer_credits  = [];

    if ($order->customer_id) {
        $lay_placeholders = implode(',', array_fill(0, max(1, count($current_layaway_ids)), '%d'));
        $customer_layaways = $wpdb->get_results($wpdb->prepare(
            "SELECT id, reference_num, remaining_amount
             FROM {$wpdb->prefix}mji_layaways
             WHERE customer_id = %d
               AND (status = 'active' OR id IN ($lay_placeholders))
             ORDER BY status DESC, id DESC",
            array_merge([$order->customer_id], $current_layaway_ids ?: [0])
        ));

        $cr_placeholders = implode(',', array_fill(0, max(1, count($current_credit_ids)), '%d'));
        $customer_credits = $wpdb->get_results($wpdb->prepare(
            "SELECT id, reference_num, remaining_amount
             FROM {$wpdb->prefix}mji_credits
             WHERE customer_id = %d
               AND (status = 'active' OR id IN ($cr_placeholders))
             ORDER BY status DESC, id DESC",
            array_merge([$order->customer_id], $current_credit_ids ?: [0])
        ));
    }

?>
    <h3>Edit Invoice</h3>
    <table class="form-table">
        <tr>
            <th><label for="edit_reference_num">Reference Number</label></th>
            <td><input type="text" id="edit_reference_num"
                    value="<?= esc_attr($order->reference_num) ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="edit_date">Date</label></th>
            <td><input type="date" id="edit_date"
                    value="<?= esc_attr(date('Y-m-d', strtotime($order->created_at))) ?>"></td>
        </tr>
        <tr>
            <th><label for="edit_salesperson">Salesperson</label></th>
            <td>
                <select id="edit_salesperson">
                    <?php foreach ($salespeople as $s): ?>
                        <option value="<?= intval($s->id) ?>"
                            <?= selected($order->salesperson_id, $s->id, false) ?>>
                            <?= esc_html($s->first_name . ' ' . $s->last_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="edit_notes">Notes</label></th>
            <td><textarea id="edit_notes" rows="3" class="large-text"><?= esc_textarea($order->notes) ?></textarea></td>
        </tr>
    </table>

    <?php
    // Build map: key → amount for pre-filling
    $payment_map = [];
    foreach ($payments as $p) {
        if ($p->method === 'layaway' && $p->layaway_id) {
            $payment_map['layaway:' . (int) $p->layaway_id] = (float) $p->amount;
        } elseif ($p->method === 'credit' && $p->credit_id) {
            $payment_map['credit:' . (int) $p->credit_id] = (float) $p->amount;
        } else {
            $payment_map[$p->method] = (float) $p->amount;
        }
    }

    $regular_methods = [
        'cash'        => 'Cash',
        'cheque'      => 'Cheque',
        'debit'       => 'Debit / Interac',
        'visa'        => 'Visa',
        'master_card' => 'Mastercard',
        'amex'        => 'Amex',
        'bank_draft'  => 'Bank Draft',
        'cup'         => 'Cup',
        'alipay'      => 'Alipay',
        'gift_card'   => 'Gift Card',
        'wire'        => 'Wire',
    ];
    ?>

    <h4>Payments</h4>
    <p>
        <strong>Order total: $<?= number_format((float) $order->total, 2) ?></strong>
        &nbsp;&mdash;&nbsp;
        <strong id="edit-payment-sum">$0.00</strong>
        <span id="edit-payment-sum-status"></span>
    </p>
    <input type="hidden" id="edit_order_total" value="<?= esc_attr($order->total) ?>">

    <div class="payment-methods-grid" id="edit-payment-grid">
        <?php foreach ($regular_methods as $key => $label):
            $val = isset($payment_map[$key]) ? number_format($payment_map[$key], 2, '.', '') : '';
        ?>
            <div class="payment-method-field">
                <label><?= esc_html($label) ?></label>
                <input type="number" name="pay[<?= esc_attr($key) ?>]"
                    value="<?= esc_attr($val) ?>"
                    min="0" step="0.01" placeholder="0.00">
            </div>
        <?php endforeach; ?>

        <?php foreach ($customer_layaways as $lay):
            $lkey = 'layaway:' . (int) $lay->id;
            $val  = isset($payment_map[$lkey]) ? number_format($payment_map[$lkey], 2, '.', '') : '';
        ?>
            <div class="payment-method-field">
                <label>Layaway #<?= esc_html($lay->reference_num) ?></label>
                <input type="number" name="pay[<?= esc_attr($lkey) ?>]"
                    value="<?= esc_attr($val) ?>"
                    min="0" step="0.01" placeholder="0.00">
                <small style="color:#666;">Balance: $<?= number_format((float) $lay->remaining_amount, 2) ?></small>
            </div>
        <?php endforeach; ?>

        <?php foreach ($customer_credits as $cr):
            $ckey = 'credit:' . (int) $cr->id;
            $val  = isset($payment_map[$ckey]) ? number_format($payment_map[$ckey], 2, '.', '') : '';
        ?>
            <div class="payment-method-field">
                <label>Credit #<?= esc_html($cr->reference_num) ?></label>
                <input type="number" name="pay[<?= esc_attr($ckey) ?>]"
                    value="<?= esc_attr($val) ?>"
                    min="0" step="0.01" placeholder="0.00">
                <small style="color:#666;">Balance: $<?= number_format((float) $cr->remaining_amount, 2) ?></small>
            </div>
        <?php endforeach; ?>
    </div>

    <p style="margin-top:12px;">
        <input type="hidden" id="edit_order_id" value="<?= intval($order->id) ?>">
        <input type="hidden" id="edit_original_reference" value="<?= esc_attr($order->reference_num) ?>">
        <button type="button" class="button button-primary" id="save-invoice-btn">Save Changes</button>
        <button type="button" class="button" id="cancel-edit-btn" style="margin-left:8px;">Cancel</button>
    </p>
<?php
}

function render_edit_layaway_form($invoice)
{
    $salespeople = mji_get_salespeople();

    $regular_methods = [
        'cash'        => 'Cash',
        'cheque'      => 'Cheque',
        'debit'       => 'Debit / Interac',
        'visa'        => 'Visa',
        'master_card' => 'Mastercard',
        'amex'        => 'Amex',
        'bank_draft'  => 'Bank Draft',
        'cup'         => 'Cup',
        'alipay'      => 'Alipay',
        'gift_card'   => 'Gift Card',
        'wire'        => 'Wire',
    ];

    $payment_map   = [];
    $deposit_total = 0.0;
    foreach ($invoice->payment as $p) {
        if (array_key_exists($p->method, $regular_methods)) {
            $payment_map[$p->method] = (float) $p->amount;
            $deposit_total += (float) $p->amount;
        }
    }

    $type       = $invoice->type;
    $account_id = $type === 'layaway' ? (int) $invoice->layaway_id : (int) $invoice->credit_id;
?>
    <h3>Edit <?= esc_html(ucfirst($type)) ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="edit_lay_reference_num">Reference Number</label></th>
            <td><input type="text" id="edit_lay_reference_num"
                    value="<?= esc_attr($invoice->reference_num) ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="edit_lay_date">Date</label></th>
            <td><input type="date" id="edit_lay_date"
                    value="<?= esc_attr(date('Y-m-d', strtotime($invoice->payment_date))) ?>"></td>
        </tr>
        <tr>
            <th><label for="edit_lay_salesperson">Salesperson</label></th>
            <td>
                <select id="edit_lay_salesperson">
                    <?php foreach ($salespeople as $s): ?>
                        <option value="<?= intval($s->id) ?>"
                            <?= selected($invoice->salesperson_id, $s->id, false) ?>>
                            <?= esc_html($s->first_name . ' ' . $s->last_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="edit_lay_notes">Notes</label></th>
            <td><textarea id="edit_lay_notes" rows="3" class="large-text"><?= esc_textarea($invoice->notes) ?></textarea></td>
        </tr>
    </table>

    <h4>Payments</h4>
    <p>
        <strong>Deposit total: $<?= number_format($deposit_total, 2) ?></strong>
        &nbsp;&mdash;&nbsp;
        <strong id="edit-lay-payment-sum">$0.00</strong>
        <span id="edit-lay-payment-sum-status"></span>
    </p>
    <input type="hidden" id="edit_lay_deposit_total" value="<?= esc_attr($deposit_total) ?>">

    <div class="payment-methods-grid" id="edit-lay-payment-grid">
        <?php foreach ($regular_methods as $key => $label):
            $val = isset($payment_map[$key]) ? number_format($payment_map[$key], 2, '.', '') : '';
        ?>
            <div class="payment-method-field">
                <label><?= esc_html($label) ?></label>
                <input type="number" name="lay_pay[<?= esc_attr($key) ?>]"
                    value="<?= esc_attr($val) ?>"
                    min="0" step="0.01" placeholder="0.00">
            </div>
        <?php endforeach; ?>
    </div>

    <p style="margin-top:12px;">
        <input type="hidden" id="edit_lay_type" value="<?= esc_attr($type) ?>">
        <input type="hidden" id="edit_lay_account_id" value="<?= intval($account_id) ?>">
        <input type="hidden" id="edit_lay_original_reference" value="<?= esc_attr($invoice->reference_num) ?>">
        <button type="button" class="button button-primary" id="save-layaway-btn">Save Changes</button>
        <button type="button" class="button" id="cancel-edit-lay-btn" style="margin-left:8px;">Cancel</button>
    </p>
<?php
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
    $total_paid = 0.00;
?>
    <div class="invoice" style="max-width:700px;">

        <h2><?php echo ucfirst(esc_html($type)); ?> Invoice #<?php echo esc_html($invoice->reference_num); ?></h2>

        <p><strong>Bill To</strong></p>
        <p>
            <?php echo implode('<br>', $bill_to_lines); ?><br />
            <strong>Served By:</strong>
            <?php echo esc_html(
                $invoice->salesperson_first_name . ' ' . $invoice->salesperson_last_name
            ); ?><br>

            <strong>Date Created:</strong>
            <?php echo esc_html($purchased_date); ?> <br />
            Notes: <?= esc_html($invoice->notes) ?>
        </p>

        <?php if ($type === 'credit' && !empty($invoice->original_order_reference)): ?>
        <p><strong>Purchase Invoice:</strong> #<?= esc_html($invoice->original_order_reference) ?></p>
        <?php endif; ?>

        <?php
        if (!empty($invoice->items)):
            echo "<h3>Items:</h3>";
            foreach ($invoice->items as $item):
                echo "<div>";
                echo "<img src='" . esc_url($item["image_url"]) . "' alt='product image' />";
                echo "<p>";
                if (!empty($item["sku"]))
                    echo "<b>SKU: " . esc_html($item["sku"]) . "</b>";
                echo nl2br(wp_kses_post($item["description"]));
                if (!empty($item["serial"]))
                    echo "<br />Serial: " . esc_html($item["serial"]);
                echo "<br />Sold Price: " . esc_html($item["sold_price"]);
                echo "<br />Returned Price: " . esc_html($item["returned_price"]);
                echo "</p>";
                echo "</div>";
            endforeach;
        endif;

        if (!empty($invoice->return_services)):
            echo "<h3>Services:</h3>";
            foreach ($invoice->return_services as $service):
                echo "<div>";
                echo "<p>";
                echo "<b>Category: " . esc_html($service->category) . "</b>";
                if (!empty($service->description))
                    echo "<br />Description: " . esc_html($service->description);
                echo "<br />Sold Price: " . esc_html($service->sold_price);
                echo "<br />Returned Price: " . esc_html($service->price);
                echo "</p>";
                echo "</div>";
            endforeach;
        endif;
        ?>

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

                if (!empty($invoice->payment)):
                    foreach ($invoice->payment as $payment):
                        $amount = (float) $payment->amount;
                        $total_paid += $amount;
                ?>
                        <tr>
                            <td><?php echo esc_html(ucfirst($payment->method)); ?></td>
                            <td align="right">$<?php echo number_format($amount, 2); ?></td>
                        </tr>
                    <?php
                    endforeach;
                else:
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
        if (empty($invoice->usage)):
        ?>
            <form method="post"
                onsubmit="return confirm('Delete this <?php echo esc_html($type === 'credit' ? 'credit' : 'layaway'); ?>? This cannot be undone.');">
                <?php wp_nonce_field('delete_' . $type . '_action', 'delete_' . $type . '_nonce'); ?>
                <input type="hidden" name="action" value="delete_<?php echo esc_attr($type); ?>">

                <?php if ($type === 'layaway'): ?>
                    <input type="hidden" name="layaway_id" value="<?php echo esc_attr($invoice->layaway_id); ?>">
                <?php elseif ($type === 'credit'): ?>
                    <input type="hidden" name="reference_num" value="<?php echo esc_attr($invoice->reference_num); ?>">
                <?php endif; ?>

                <button type="submit" class="button button-danger">
                    Delete <?php echo ucfirst(esc_html($type)); ?>
                </button>
                <button type="button" class="button print" id="main-print-btn">Print</button>
                <button type="button" class="button issue-refund" id="issue-layaway-refund-btn">Issue refund</button>
                <button type="button" class="button" id="edit-layaway-btn">Edit <?php echo ucfirst(esc_html($type)); ?></button>
            </form>
        <?php else: ?>

            <p><i>We can not delete/refund the <?= $type ?> as it has been used, Will have to delete the invoices using this layaway
                    before we can delete/refund this.</i></p>
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

                    foreach ($invoice->usage as $used):
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

            <button type="button" class="button print" id="main-print-btn">Print</button>
            <button type="button" class="button" id="edit-layaway-btn">Edit <?php echo ucfirst(esc_html($type)); ?></button>
        <?php endif; ?>
    </div>

    <div class="hidden" id="edit-layaway">
        <div class="edit-layaway-container">
            <?php render_edit_layaway_form($invoice); ?>
        </div>
    </div>

    <!-- Refund items -->
    <div class="hidden" id="layaway-refund">
        <div class="refund-container">
            <div class="return-section">
                <h3>Create <?php echo ucfirst(esc_html($type)); ?> Refund</h3>
                <form name="refund_invoice" method="post" class="return-form">
                    <input type="hidden" name="action" value="create_refund_<?= esc_html($type) ?>">
                    <input type="hidden" name="reference" value="<?= esc_html($invoice->reference_num) ?>">
                    <input type="hidden" name="total_value" value="<?= $total_paid ?>">

                    <div class="return-info-totals">
                        <!-- Left: Form Fields -->
                        <div class="return-form-fields">
                            <div class="form-group">
                                <label for="refund-reference">Reference Number:</label>
                                <input type="text" id="refund-reference" name="refund-reference" required>
                            </div>

                            <div class="form-group">
                                <label for="refund-date">Date:</label>
                                <input type="date" id="refund-date" name="refund-date"
                                    value="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="form-group">
                                <?= mji_salesperson_dropdown(); ?>
                            </div>

                            <div class="form-group">
                                <label for="refund-reason">Reason for return:</label>
                                <textarea name="refund-reason" id="refund-reason" rows="3"></textarea>
                            </div>
                        </div>

                        <!-- Right: Totals -->
                        <div class="return-totals">
                            <p>Total Refund: $<span id="display-total"><?= number_format($total_paid, 2); ?></span>
                            </p>
                        </div>
                    </div>

                    <div class="payment-methods">
                        <h3>Refund Payment Methods</h3>
                        <div class="payment-grid">
                            <div class="payment-item">
                                <label for="cash">Cash:</label>
                                <input type="number" min="0" step="0.01" id="cash" name="cash">
                            </div>

                            <div class="payment-item">
                                <label for="cheque">Cheque:</label>
                                <input type="number" min="0" step="0.01" id="cheque" name="cheque">
                            </div>

                            <div class="payment-item">
                                <label for="debit">Debit/Interac:</label>
                                <input type="number" min="0" step="0.01" id="debit" name="debit">
                            </div>

                            <div class="payment-item">
                                <label for="visa">Visa:</label>
                                <input type="number" min="0" step="0.01" id="visa" name="visa">
                            </div>

                            <div class="payment-item">
                                <label for="master_card">Mastercard:</label>
                                <input type="number" min="0" step="0.01" id="master_card" name="master_card">
                            </div>

                            <div class="payment-item">
                                <label for="amex">Amex:</label>
                                <input type="number" min="0" step="0.01" id="amex" name="amex">
                            </div>

                            <div class="payment-item">
                                <label for="bank_draft">Bank Draft:</label>
                                <input type="number" min="0" step="0.01" id="bank_draft" name="bank_draft">
                            </div>

                            <div class="payment-item">
                                <label for="cup">Cup:</label>
                                <input type="number" min="0" step="0.01" id="cup" name="cup">
                            </div>

                            <div class="payment-item">
                                <label for="alipay">Alipay:</label>
                                <input type="number" min="0" step="0.01" id="alipay" name="alipay">
                            </div>

                            <div class="payment-item">
                                <label for="wire">Wire:</label>
                                <input type="number" min="0" step="0.01" id="wire" name="wire">
                            </div>
                        </div>
                    </div>

                    <div class="form-submit">
                        <?php submit_button('Process Return', 'primary', 'submit_return'); ?>
                        <button class="button cancel" id="cancel">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php
}

function search_refund_results($reference_num)
{
    global $wpdb;

    $payments_table = $wpdb->prefix . 'mji_payments';
    $customers_table = $wpdb->prefix . 'mji_customers';
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';
    $returns_table = $wpdb->prefix . 'mji_returns';
    $return_items_table = $wpdb->prefix . 'mji_return_items';
    $order_items_table = $wpdb->prefix . 'mji_order_items';
    $services_table = $wpdb->prefix . 'mji_services';
    $return_services_table = $wpdb->prefix . 'mji_return_services';
    $product_inventory_units_table = $wpdb->prefix . 'mji_product_inventory_units';

    try {
        $sql_payments = $wpdb->prepare("
            SELECT
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
            JOIN {$customers_table} c ON c.id = p.customer_id
            JOIN {$salespeople_table} s ON s.id = p.salesperson_id
            WHERE p.reference_num = %s
            AND p.transaction_type = 'refund'
        ", $reference_num);

        $results = $wpdb->get_row($sql_payments);
        check_wpdb_error($wpdb);

        if (!$results)
            return null;

        $sql_payment = $wpdb->prepare("
            SELECT
                method,
                amount
            FROM {$payments_table}
            WHERE reference_num = %s
            AND transaction_type = 'refund'
        ", $reference_num);

        $payment = $wpdb->get_results($sql_payment);
        check_wpdb_error($wpdb);

        $results->payment = $payment;

        $sql_get_return_items = $wpdb->prepare("
            SELECT
                r.reference_num,
                p.wc_product_id,
                p.wc_product_variant_id,
                p.sku,
                p.serial,
                p.name,
                p.description,
                p.image_id,
                ri.unit_price,
                oi.sale_price
            FROM {$returns_table} r
            JOIN {$return_items_table} ri ON r.id = ri.return_id
            JOIN {$order_items_table} oi ON oi.id = ri.order_item_id
            JOIN {$product_inventory_units_table} p ON p.id = ri.product_inventory_unit_id
            WHERE r.reference_num = %s
        ", "$reference_num");

        $return_items = $wpdb->get_results($sql_get_return_items);

        if ($return_items) {
            $items = [];
            foreach ($return_items as $return_item) {
                $item = [
                    "sku"            => $return_item->sku,
                    "serial"         => $return_item->serial,
                    "description"    => $return_item->description ?? '',
                    "image_url"      => esc_url(mji_get_unit_image_url($return_item, 'thumbnail')),
                    "sold_price"     => $return_item->sale_price,
                    "returned_price" => $return_item->unit_price,
                ];
                array_push($items, $item);
            }
            $results->items = $items;
        }

        $sql_get_return_services = $wpdb->prepare("
            SELECT
                r.reference_num,
                s.category,
                s.description,
                s.sold_price,
                rs.price
            FROM {$returns_table} r
            JOIN {$return_services_table} rs ON r.id = rs.return_id
            JOIN {$services_table} s ON s.id = rs.service_id
            WHERE r.reference_num = %s
        ", "$reference_num");
        $return_services = $wpdb->get_results($sql_get_return_services);
        check_wpdb_error($wpdb);

        $results->return_services = $return_services;
        return $results;
    } catch (Exception $e) {
        custom_log($e->getMessage());
        throw $e;
    }
}

function render_refund_invoice($invoice)
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
    <div class="invoice" style="max-width:700px;">

        <h2>Refund Invoice #<?php echo esc_html($invoice->reference_num); ?></h2>

        <p><strong>Bill To</strong></p>
        <p>
            <?php echo implode('<br>', $bill_to_lines); ?><br />
            <strong>Served By:</strong>
            <?php echo esc_html(
                $invoice->salesperson_first_name . ' ' . $invoice->salesperson_last_name
            ); ?><br>

            <strong>Date Created:</strong><?php echo esc_html($purchased_date); ?> <br />
            <strong>Notes:</strong> <?= esc_html($invoice->notes) ?>
        </p>

        <?php
        if (!empty($invoice->items)):
            echo "<h3>Items:</h3>";
            foreach ($invoice->items as $item):
                echo "<div>";
                echo "<img src='" . esc_url($item["image_url"]) . "' alt='product image' />";
                echo "<p>";
                if (!empty($item["sku"]))
                    echo "<strong>SKU:</strong> " . esc_html($item["sku"]) . "<br />";
                echo nl2br(wp_kses_post($item["description"]));
                if (!empty($item["serial"]))
                    echo "<br />Serial: " . esc_html($item["serial"]);
                echo "<br />Sold Price: " . esc_html($item["sold_price"]);
                echo "<br />Returned Price: " . esc_html($item["returned_price"]);
                echo "</p>";
                echo "</div>";
            endforeach;
        endif;

        if (!empty($invoice->return_services)):
            echo "<h3>Services:</h3>";
            foreach ($invoice->return_services as $service):
                echo "<div>";
                echo "<p>";
                echo "<b>Category: " . esc_html($service->category) . "</b>";
                if (!empty($service->description))
                    echo "<br />Description: " . esc_html($service->description);
                echo "<br />Sold Price: " . esc_html($service->sold_price);
                echo "<br />Returned Price: " . esc_html($service->price);
                echo "</p>";
                echo "</div>";
            endforeach;
        endif;
        ?>

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

                if (!empty($invoice->payment)):
                    foreach ($invoice->payment as $payment):
                        $amount = (float) $payment->amount;
                        $total_paid += $amount;
                ?>
                        <tr>
                            <td><?php echo esc_html(ucfirst($payment->method)); ?></td>
                            <td align="right">$<?php echo number_format($amount, 2); ?></td>
                        </tr>
                    <?php
                    endforeach;
                else:
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


        <form method="post" onsubmit="return confirm('Delete this refund? This cannot be undone.');">
            <?php wp_nonce_field('delete_refund_action', 'delete_refund_nonce'); ?>
            <input type="hidden" name="action" value="delete_refund">
            <input type="hidden" name="reference_num" value="<?php echo esc_attr($invoice->reference_num); ?>">
            <button type="submit" class="button button-danger">
                Delete Refund
            </button>
            <button type="button" class="button print" id="main-print-btn">Print</button>
        </form>


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

    $layaways_table = $wpdb->prefix . 'mji_layaways';
    $payments_table = $wpdb->prefix . 'mji_payments';
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

function delete_credit($reference_num)
{
    if (!$reference_num) {
        return array("success" => false, "message" => "Credit id is required to delete.");
    }

    global $wpdb;
    $credits_table = $wpdb->prefix . 'mji_credits';
    $payments_table = $wpdb->prefix . 'mji_payments';
    $return_table = $wpdb->prefix . "mji_returns";
    $return_items_table = $wpdb->prefix . "mji_return_items";
    $return_services_table = $wpdb->prefix . "mji_return_services";
    $inventory_status_history_table = $wpdb->prefix . "mji_inventory_status_history";
    $product_inventory_units_table = $wpdb->prefix . "mji_product_inventory_units";
    $restored_stock = [];
    $wpdb->query('START TRANSACTION');
    try {
        $credit_amount = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, total_amount, remaining_amount 
                 FROM {$credits_table}
                 WHERE reference_num = %s",
                $reference_num
            )
        );

        check_wpdb_error($wpdb);

        if (!$credit_amount) {
            return array("message" => "No Credits found with that reference number.");
        }

        $credit_id = $credit_amount->id;
        if ($credit_amount->total_amount == $credit_amount->remaining_amount) {

            $wpdb->delete($payments_table, ['credit_id' => $credit_id], ['%d']);
            check_wpdb_error($wpdb);
            $wpdb->delete($credits_table, ['id' => $credit_id], ['%d']);
            check_wpdb_error($wpdb);

            $returns = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT r.id, ri.product_inventory_unit_id, p.wc_product_id, p.wc_product_variant_id,  ish.from_status, ish.to_status
                    FROM {$return_table} r
                    JOIN {$return_items_table} ri
                        ON r.id = ri.return_id
                    JOIN {$product_inventory_units_table} p
                        ON ri.product_inventory_unit_id = p.id
                    JOIN {$inventory_status_history_table} ish 
                        ON ish.inventory_unit_id = ri.product_inventory_unit_id
                        AND ish.reference_num = r.reference_num
                    WHERE r.reference_num = %s",
                    $reference_num
                )
            );

            check_wpdb_error($wpdb);

            if ($returns) {
                $wpdb->delete($inventory_status_history_table, ['reference_num' => $reference_num], ['%s']);
                check_wpdb_error($wpdb);
                $wpdb->delete($return_items_table, ['return_id' => $returns[0]->id], ['%d']);
                check_wpdb_error($wpdb);
                $wpdb->delete($return_services_table, ['return_id' => $returns[0]->id], ['%d']);
                check_wpdb_error($wpdb);
                $wpdb->delete($return_table, ['reference_num' => $reference_num], ['%s']);
                check_wpdb_error($wpdb);

                foreach ($returns as $return) {

                    if ($return->from_status == "sold" && $return->to_status == "in_stock") {
                        $wpdb->update($product_inventory_units_table, ["status" => "sold"], ['id' => $return->product_inventory_unit_id], ['%s'], ['%d']);
                        check_wpdb_error($wpdb);

                        $product_id = $return->wc_product_variant_id ?: $return->wc_product_id;
                        if ($product_id) {
                            $product = wc_get_product($product_id);
                            if ($product && $product->managing_stock()) {
                                $product->set_stock_quantity($product->get_stock_quantity() - 1);
                                $product->save();
                                $restored_stock[] = $product_id;
                            }
                        }
                    }
                }
            }

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

        // restore WooCommerce stock
        foreach ($restored_stock as $product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                $product->set_stock_quantity($product->get_stock_quantity() + 1);
                $product->save();
            }
        }
        custom_log('[Delete Credit Invoice Failed] ' . $e->getMessage());
        return [
            'success' => false,
            'message' => '[Delete Credit Invoice Failed] ' . $e->getMessage(),
        ];
    }
}

function delete_refund($reference_num)
{
    if (!$reference_num) {
        return array("success" => false, "message" => "Reference number is required to delete.");
    }

    global $wpdb;
    $payments_table = $wpdb->prefix . 'mji_payments';
    $layaways_table = $wpdb->prefix . 'mji_layaways';
    $credits_table = $wpdb->prefix . 'mji_credits';
    $return_table = $wpdb->prefix . "mji_returns";
    $return_items_table = $wpdb->prefix . "mji_return_items";
    $return_services_table = $wpdb->prefix . "mji_return_services";
    $inventory_status_history_table = $wpdb->prefix . "mji_inventory_status_history";
    $product_inventory_units_table = $wpdb->prefix . "mji_product_inventory_units";
    $restored_stock = [];
    $wpdb->query('START TRANSACTION');
    try {

        $returns = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT r.id, ri.product_inventory_unit_id, p.wc_product_id, p.wc_product_variant_id, ish.from_status, ish.to_status
                    FROM {$return_table} r
                    LEFT JOIN {$return_items_table} ri
                        ON r.id = ri.return_id
                    LEFT JOIN {$product_inventory_units_table} p
                        ON ri.product_inventory_unit_id = p.id
                    LEFT JOIN {$inventory_status_history_table} ish 
                        ON ish.inventory_unit_id = ri.product_inventory_unit_id
                        AND ish.reference_num = r.reference_num
                    WHERE r.reference_num = %s",
                $reference_num
            )
        );
        check_wpdb_error($wpdb);

        if ($returns) {
            $wpdb->delete($inventory_status_history_table, ['reference_num' => $reference_num], ['%s']);
            check_wpdb_error($wpdb);
            $wpdb->delete($return_items_table, ['return_id' => $returns[0]->id], ['%d']);
            check_wpdb_error($wpdb);
            $wpdb->delete($return_services_table, ['return_id' => $returns[0]->id], ['%d']);
            check_wpdb_error($wpdb);
            $wpdb->delete($return_table, ['reference_num' => $reference_num], ['%s']);
            check_wpdb_error($wpdb);

            foreach ($returns as $return) {

                if ($return->from_status == "sold" && $return->to_status == "in_stock") {
                    $wpdb->update($product_inventory_units_table, ["status" => "sold"], ['id' => $return->product_inventory_unit_id], ['%s'], ['%d']);
                    check_wpdb_error($wpdb);

                    $product_id = $return->wc_product_variant_id ?: $return->wc_product_id;
                    if ($product_id) {
                        $product = wc_get_product($product_id);
                        if ($product && $product->managing_stock()) {
                            $product->set_stock_quantity($product->get_stock_quantity() - 1);
                            $product->save();
                            $restored_stock[] = $product_id;
                        }
                    }
                }
            }
        }

        $payments = $wpdb->get_results($wpdb->prepare("
            SELECT layaway_id, credit_id, amount
            FROM $payments_table
            WHERE reference_num = %s
        ", $reference_num));

        if ($payments) {
            $credit_amounts  = [];
            $layaway_amounts = [];
            foreach ($payments as $payment) {
                if (!empty($payment->credit_id)) {
                    $credit_amounts[(int) $payment->credit_id] =
                        ($credit_amounts[(int) $payment->credit_id] ?? 0.0) + (float) $payment->amount;
                } elseif (!empty($payment->layaway_id)) {
                    $layaway_amounts[(int) $payment->layaway_id] =
                        ($layaway_amounts[(int) $payment->layaway_id] ?? 0.0) + (float) $payment->amount;
                }
            }

            foreach ($credit_amounts as $credit_id => $amount) {
                $result = $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE {$credits_table}
                        SET remaining_amount = remaining_amount + %f,
                            status = 'active'
                        WHERE id = %d",
                        $amount,
                        $credit_id
                    )
                );
                if ($result === false) {
                    throw new Exception("Failed to restore credit #{$credit_id}: " . $wpdb->last_error);
                }
                check_wpdb_error($wpdb);
            }

            foreach ($layaway_amounts as $layaway_id => $amount) {
                $result = $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE {$layaways_table}
                        SET remaining_amount = remaining_amount + %f,
                            status = 'active'
                        WHERE id = %d",
                        $amount,
                        $layaway_id
                    )
                );
                if ($result === false) {
                    throw new Exception("Failed to restore layaway #{$layaway_id}: " . $wpdb->last_error);
                }
                check_wpdb_error($wpdb);
            }
        }

        $wpdb->delete($payments_table, ['reference_num' => $reference_num], ['%s']);
        check_wpdb_error($wpdb);

        $wpdb->query('COMMIT');
        return [
            'success' => true,
            "message" => "Refund successfully deleted."
        ];
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');

        // restore WooCommerce stock
        foreach ($restored_stock as $product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                $product->set_stock_quantity($product->get_stock_quantity() + 1);
                $product->save();
            }
        }
        custom_log('[Delete Credit Invoice Failed] ' . $e->getMessage());
        return [
            'success' => false,
            'message' => '[Delete Credit Invoice Failed] ' . $e->getMessage(),
        ];
    }
}

function sanitize_payment_amount($value)
{
    if (!isset($value) || $value === '' || $value === null) {
        return 0.00;
    }

    $num = (float) $value;
    return is_naN($num) ? 0.00 : round($num, 2);
}

function sanitize_and_validate_return($post_data)
{
    $data = wp_unslash($post_data);
    $allowed_payment_methods = [
        'cash',
        'cheque',
        'debit',
        'visa',
        'master_card',
        'amex',
        'bank_draft',
        'cup',
        'alipay',
        'wire'
    ];
    $refund_items_data = [];
    $refund_services_data = [];

    $payment_data = [];
    foreach ($allowed_payment_methods as $method) {
        if (isset($data[$method]) && (sanitize_payment_amount($data[$method]) > 0)) {
            $payment_data[$method] = sanitize_payment_amount($data[$method]);
        }
    }

    $sanitized = [
        'order_id' => absint($data['order_id'] ?? 0),
        'return_items_ids' => is_array($data['return_items'] ?? null) ? array_map('absint', $data['return_items']) : [],
        'return_services_ids' => is_array($data['return_services'] ?? null) ? array_map('absint', $data['return_services']) : [],
        'refund_items_ids' => is_array($data['refund_items'] ?? null) ? array_map('absint', $data['refund_items']) : [],
        'refund_services_ids' => is_array($data['refund_services'] ?? null) ? array_map('absint', $data['refund_services']) : [],
        'refund_prices' => is_array($data['refund_prices'] ?? null) ? array_map('floatval', $data['refund_prices']) : [],
        'payment' => $payment_data,
        'gst_total' => round((float) ($data['gst'] ?? 0), 2),
        'pst_total' => round((float) ($data['pst'] ?? 0), 2),
        'subtotal' => round((float) ($data['subtotal'] ?? 0), 2),
        'total' => round((float) ($data['total'] ?? 0), 2),
        'reference' => sanitize_text_field($data['reference'] ?? $data['refund-reference'] ?? ''),
        'original_reference' => sanitize_text_field($data['original_reference'] ?? ''),
        'item_returned' => sanitize_text_field($data['item_returned']) == 'yes' ? true : false,
        'date' => sanitize_text_field($data['date'] ?? $data['refund-date'] ?? ''),
        'reason' => sanitize_textarea_field($data['reason'] ?? $data['refund-reason'] ?? ''),
    ];

    $refund_items_data = [];
    $refund_services_data = [];
    $filtered_refund_prices = [];

    if (!empty($sanitized['refund_prices'])) {
        foreach ($sanitized['refund_prices'] as $id => $amount) {
            // normalize
            $id = (int) $id;

            // skip invalid amounts
            if ($amount <= 0) {
                continue;
            }

            if (in_array($id, $sanitized['return_items_ids'], true) || in_array($id, $sanitized['refund_items_ids'], true)) {
                $refund_items_data[$id] = $amount;
                $filtered_refund_prices[$id] = $amount;
            } elseif (in_array($id, $sanitized['return_services_ids'], true) || in_array($id, $sanitized['refund_services_ids'], true)) {
                $refund_services_data[$id] = $amount;
                $filtered_refund_prices[$id] = $amount;
            }
        }
    }

    $sanitized['refund_items_data'] = $refund_items_data;
    $sanitized['refund_services_data'] = $refund_services_data;
    $sanitized['refund_prices'] = $filtered_refund_prices;
    $errors = [];

    if ($sanitized['order_id'] <= 0) {
        $errors['message'] = 'Invalid order ID';
    }

    if (empty($sanitized['return_items_ids']) && empty($sanitized['return_services_ids']) && empty($sanitized['refund_items_ids']) && empty($sanitized['refund_services_ids'])) {
        $errors['message'] = 'No return items/services provided';
    }

    $date = DateTime::createFromFormat('Y-m-d', $sanitized['date']);
    if (!$date || $date->format('Y-m-d') !== $sanitized['date']) {
        $errors['message'] = 'Invalid date format';
    }

    if (!empty($errors)) {
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

function get_order_services($order_id)
{
    global $wpdb;
    $services_table = $wpdb->prefix . "mji_services";
    return $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM $services_table WHERE order_id = %d",
        $order_id
    ));
}

function order_items_valid($order_item_ids, $selected_item_ids)
{
    return empty(array_diff($selected_item_ids, $order_item_ids));
}

// Check if item was already returned partially, if partial return then check total return doesnt exceed original amount
function check_already_returned($order_item_ids, $current_return_items_data)
{
    global $wpdb;
    $return_items_table = $wpdb->prefix . "mji_return_items";
    $order_items_table = $wpdb->prefix . "mji_order_items";
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT order_item_id, SUM(unit_price) AS total_returned_price, MAX(oi.sale_price) AS sale_price 
        FROM $return_items_table ri
        JOIN $order_items_table oi ON ri.order_item_id = oi.id 
        WHERE order_item_id IN (" . implode(',', $order_item_ids) . ") 
        GROUP BY order_item_id",
    ));

    $existing_returns = [];
    foreach ($results as $row) {
        $existing_returns[$row->order_item_id] = $row;
    }

    foreach ($current_return_items_data as $id => $incoming_amount) {
        if (isset($existing_returns[$id])) {
            $db_data = $existing_returns[$id];

            // Use round() to avoid floating-point precision issues with currency
            $already_returned = round((float) $db_data->total_returned_price, 2);
            $max_allowed = round((float) $db_data->sale_price, 2);

            if (($already_returned + $incoming_amount) > $max_allowed) {
                wp_send_json_error(['message' => "Order ID {$id} returned amount is greater than the ordered amount as there is already a return for this item"], 409);
            }
        }
    }
}

function check_service_already_returned($order_services_ids, $current_return_services_data)
{
    global $wpdb;
    $return_services_table = $wpdb->prefix . "mji_return_services";
    $services_table = $wpdb->prefix . "mji_services";

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT service_id, SUM(price) AS total_returned_price, MAX(s.sold_price) AS sale_price 
        FROM $return_services_table rs
        JOIN $services_table s ON rs.service_id = s.id 
        WHERE service_id IN (" . implode(',', $order_services_ids) . ") 
        GROUP BY service_id",
    ));

    $existing_returns = [];
    foreach ($results as $row) {
        $existing_returns[$row->service_id] = $row;
    }

    foreach ($current_return_services_data as $id => $incoming_amount) {

        if (isset($existing_returns[$id])) {
            $db_data = $existing_returns[$id];

            // Use round() to avoid floating-point precision issues with currency
            $already_returned = round((float) $db_data->total_returned_price, 2);
            $max_allowed = round((float) $db_data->sale_price, 2);

            if (($already_returned + $incoming_amount) > $max_allowed) {
                wp_send_json_error(['message' => "Order ID {$id} returned amount is greater than the ordered amount"], 409);
            }
        }
    }
}

// Check if item total return doesnt exceed original amount
function check_items_price($order_item_ids, $current_return_items_data, $item_returned)
{
    global $wpdb;
    $order_items_table = $wpdb->prefix . "mji_order_items";
    $product_inventory_units_table = $wpdb->prefix . "mji_product_inventory_units";
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT oi.id, oi.sale_price, p.status
        FROM $order_items_table oi
        JOIN $product_inventory_units_table p ON p.id = oi.product_inventory_unit_id
        WHERE id IN (" . implode(',', $order_item_ids) . ")",
    ));

    foreach ($results as $row) {
        // fetch the price that was sent
        $return_item_amount = $current_return_items_data[$row->id];
        $order_item_amount = $row->sale_price;

        if ($row->status == "in_stock" && $item_returned) {
            wp_send_json_error(['message' => "Order ID {$row->id} already returned, Please check previous returns"], 409);
        }

        if ($return_item_amount > $order_item_amount) {
            wp_send_json_error(['message' => "Order ID {$row->id} return amount is greater than the original ordered item amount"], 409);
        }
    }
}

function check_services_price($service_item_ids, $current_service_items_data)
{
    global $wpdb;
    $order_services_table = $wpdb->prefix . "mji_services";
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT *
        FROM $order_services_table  
        WHERE id IN (" . implode(',', $service_item_ids) . ")",
    ));

    foreach ($results as $row) {
        // fetch the price that was sent
        $return_service_amount = $current_service_items_data[$row->id];
        $order_service_amount = $row->sold_price;

        if ($return_service_amount > $order_service_amount) {
            wp_send_json_error(['message' => "Order ID {$row->id} return amount is greater than the original ordered services amount"], 409);
        }
    }
}

function check_tax_is_correct($data)
{

    $gst_total = 0;
    $pst_total = 0;
    $subtotal = 0;
    $total = 0;
    $payment_total = 0;
    $refund_prices = $data['refund_prices'];

    foreach ($refund_prices as $id => $prices) {
        $subtotal += $prices;
        if ($data['gst_total'] > 0) {
            $gst_total += round($prices * GST_RATE, 2);
        }
        if ($data['pst_total'] > 0) {
            $pst_total += round($prices * PST_RATE, 2);
        }
    }

    $total = $subtotal + $gst_total + $pst_total;

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

    // Will be empty when its credit
    if (!empty($data['payment'])) {
        foreach ($data['payment'] as $type => $amount) {
            $payment_total += $amount;
        }

        if (abs($payment_total - $total) > 0.01) {
            wp_send_json_error(['message' => 'Total mismatch. Provided total: ' . $data['total'] . ' and calculated total:' . $total], 422);
        }
    }
}

function insert_return_transactions($data, $order, $type)
{
    global $wpdb;

    $return_table = $wpdb->prefix . "mji_returns";
    $customer_table = $wpdb->prefix . "mji_customers";
    $return_items_table = $wpdb->prefix . "mji_return_items";
    $return_services_table = $wpdb->prefix . "mji_return_services";
    $payment_table = $wpdb->prefix . "mji_payments";
    $credit_table = $wpdb->prefix . "mji_credits";
    $inventory_status_history_table = $wpdb->prefix . "mji_inventory_status_history";
    $product_inventory_units_table = $wpdb->prefix . "mji_product_inventory_units";
    $mji_order_items_table = $wpdb->prefix . "mji_order_items";
    $mji_orders_table = $wpdb->prefix . "mji_orders";
    $services_table = $wpdb->prefix . "mji_services";

    // change this based on the the refund or credit
    $returned_items_ids = $type == 'refund' ? $data['refund_items_ids'] : $data['return_items_ids'];
    $returned_services_ids = $type == 'refund' ? $data['refund_services_ids'] : $data['return_services_ids'];
    $items_data = [];
    $services_data = [];
    $mapped_product_inventory_unit_id = [];

    $restored_stock = [];
    $wpdb->query('START TRANSACTION');
    try {

        // Insert into returns
        $wpdb->insert(
            $return_table,
            [
                'order_id' => $data['order_id'],
                'reference_num' => $data['reference'],
                'return_date' => $data['date'],
                'reason' => $data['reason'],
                'subtotal' => $data['subtotal'],
                'gst_total' => $data['gst_total'],
                'pst_total' => $data['pst_total'],
                'total' => $data['total'],
            ],
            ['%d', '%s', '%s', '%s', '%f', '%f', '%f', '%f']
        );
        $return_id = $wpdb->insert_id;

        if (!$return_id) {
            throw new RuntimeException("Failed to insert return: " . $wpdb->last_error);
        }

        if ($type == "refund") {
            foreach ($data["payment"] as $method => $amount) {
                $success = $wpdb->insert(
                    $payment_table,
                    [
                        'customer_id' => $order->customer_id,
                        'salesperson_id' => $order->salesperson_id,
                        'location_id' => $order->location_id,
                        'order_id' => $data['order_id'],
                        'reference_num' => $data['reference'],
                        'method' => $method,
                        'amount' => $amount,
                        'transaction_type' => 'refund',
                        'payment_date' => $data['date'],
                        'notes' => $data['reason'],
                    ],
                    ['%d', '%d', '%d', '%d', '%s', '%s', '%f', '%s', '%s', '%s']
                );

                if (!$success) {
                    throw new RuntimeException("Failed to insert payment: " . $wpdb->last_error);
                }
            }
        } else {
            // Issue credit to customer in payements
            $success = $wpdb->insert(
                $credit_table,
                [
                    'customer_id' => $order->customer_id,
                    'location_id' => $order->location_id,
                    'reference_num' => $data['reference'],
                    'total_amount' => $data['total'],
                    'remaining_amount' => $data['total'],
                    'status' => "active",
                    'created_at' => $data['date'],
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
                    'customer_id' => $order->customer_id,
                    'salesperson_id' => $order->salesperson_id,
                    'location_id' => $order->location_id,
                    'order_id' => $data['order_id'],
                    'credit_id' => $credit_id,
                    'reference_num' => $data['reference'],
                    'method' => 'credit',
                    'amount' => $data['total'],
                    'transaction_type' => 'credit_deposit',
                    'payment_date' => $data['date'],
                    'notes' => $data['reason'],
                ],
                ['%d', '%d', '%d', '%d', '%d', '%s', '%s', '%f', '%s', '%s', '%s']
            );

            if (!$success) {
                throw new RuntimeException("Failed to insert payment: " . $wpdb->last_error);
            }
        }

        if (!empty($returned_items_ids)) {
            $order_items = $wpdb->get_results(
                "SELECT
                    oi.id AS id,
                    oi.sale_price,
                    oi.product_inventory_unit_id,
                    pi.wc_product_id,
                    pi.wc_product_variant_id,
                    pi.sku,
                    pi.serial,
                    pi.name,
                    pi.description,
                    pi.image_id,
                    o.gst_total,
                    o.pst_total
                FROM $mji_order_items_table oi
                JOIN $product_inventory_units_table pi
                    ON pi.id = oi.product_inventory_unit_id
                JOIN $mji_orders_table o
                    ON o.id = oi.order_id
                WHERE oi.id IN (" . implode(',', $returned_items_ids) . ")"
            );

            // Change the item status in inventory_status_history, product_inventory_units table and also woocommerce stock  
            foreach ($order_items as $item) {

                $items_info = [];
                $mapped_product_inventory_unit_id[$item->id] = $item->product_inventory_unit_id;

                if ($data['item_returned']) {
                    $success = $wpdb->insert(
                        $inventory_status_history_table,
                        [
                            'inventory_unit_id' => $item->product_inventory_unit_id,
                            'from_status' => "sold",
                            'to_status' => "in_stock",
                            'reference_num' => $data['reference'],
                            'created_at' => $data['date'],
                            'notes' => $data['reason']
                        ],
                        ['%d', '%s', '%s', '%s', '%s', '%s']
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
                } else {
                    $success = $wpdb->insert(
                        $inventory_status_history_table,
                        [
                            'inventory_unit_id' => $item->product_inventory_unit_id,
                            'from_status' => "sold",
                            'to_status' => "sold",
                            'reference_num' => $data['reference'],
                            'created_at' => $data['date'],
                            'notes' => $data['reason'] ?: "Partial refund/credit"
                        ],
                        ['%d', '%s', '%s', '%s', '%s', '%s']
                    );

                    if (!$success) {
                        throw new RuntimeException("Failed to insert in status history table: " . $wpdb->last_error);
                    }
                }

                $items_info['image_url']   = mji_get_unit_image_url($item, 'thumbnail');
                $items_info['description'] = $item->description ?? '';
                $items_info['sku']         = $item->sku;
                $items_info['serial']      = $item->serial;
                $items_info['price']       = $item->sale_price;
                $items_data[$item->id]     = $items_info;

                if ($data['item_returned']) {
                    $product_id = $item->wc_product_variant_id ?: $item->wc_product_id;
                    if ($product_id) {
                        $product = wc_get_product($product_id);
                        if ($product && $product->managing_stock()) {
                            $product->set_stock_quantity($product->get_stock_quantity() + 1);
                            $product->save();
                            $restored_stock[] = $product_id;
                        }
                    }
                }
            }

            // Insert into return item 
            foreach ($data['refund_items_data'] as $id => $price) {
                $success = $wpdb->insert(
                    $return_items_table,
                    [
                        'return_id' => $return_id,
                        'order_item_id' => $id,
                        'product_inventory_unit_id' => $mapped_product_inventory_unit_id[$id],
                        'unit_price' => $price,
                    ],
                    ['%d', '%d', '%d', '%f']
                );
                if (!$success) {
                    throw new RuntimeException("Failed to insert return item: " . $wpdb->last_error);
                }
                $items_data[$id]["returned_price"] = $price;
            }
        }

        if (!empty($returned_services_ids)) {
            $service_results = $wpdb->get_results(
                "SELECT 
                    id,
                    category,
                    description,
                    sold_price
                FROM $services_table
                WHERE id IN (" . implode(',', $returned_services_ids) . ")"
            );

            foreach ($service_results as $service) {
                $service_arr = [];
                $service_arr['category'] = $service->category;
                $service_arr['description'] = $service->description;
                $service_arr['sold_price'] = $service->sold_price;

                $services_data[$service->id] = $service_arr;
            }

            foreach ($data['refund_services_data'] as $id => $price) {
                $success = $wpdb->insert(
                    $return_services_table,
                    [
                        'return_id' => $return_id,
                        'service_id' => $id,
                        'price' => $price,
                    ],
                    ['%d', '%d', '%f']
                );
                if (!$success) {
                    throw new RuntimeException("Failed to insert return item: " . $wpdb->last_error);
                }
                $services_data[$id]["returned_price"] = $price;
            }
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
            'items' => array_values($items_data),
            'services' => array_values($services_data),
            'totals' => $totals,
            'reference_num' => $data['reference'],
            'salesperson' => $salesperson,
            'customer_info' => $customer_info,
            'date' => $data['date'],
            'original_reference' => $data['original_reference'],
            'reason' => $data['reason']
        ]);
    } catch (Exception $e) {

        if ($data['item_returned']) {
            // restore WooCommerce stock
            foreach ($restored_stock as $product_id) {
                $product = wc_get_product($product_id);
                if ($product) {
                    $product->set_stock_quantity($product->get_stock_quantity() - 1);
                    $product->save();
                }
            }
        }
        custom_log($e->getMessage());
        $wpdb->query('ROLLBACK');
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

function create_credit_return()
{
    check_ajax_referer('mji_inventory_nonce', 'nonce');
    $data = sanitize_and_validate_return($_POST);

    $order = order_exists($data['order_id']);
    if (!$order) {
        wp_send_json_error(['message' => 'Order does not exist'], 404);
    }

    // get the order item ids to see if they match the items order id
    $order_item_ids = get_order_items($data['order_id']);
    if (!order_items_valid($order_item_ids, $data['return_items_ids'])) {
        wp_send_json_error(['message' => 'One or more return items are invalid'], 422);
    }

    $order_service_ids = get_order_services($data['order_id']);
    if (!order_items_valid($order_service_ids, $data['refund_services_ids'])) {
        wp_send_json_error(['message' => 'One or more return items are invalid'], 422);
    }

    if (!empty($data['refund_items_data'])) {
        check_already_returned($data['return_items_ids'], $data['refund_items_data']);
        check_items_price($data['return_items_ids'], $data['refund_items_data'], $data['item_returned']);
    }

    if (!empty($data['refund_services_data'])) {
        check_service_already_returned($data['return_services_ids'], $data['refund_services_data']);
        check_services_price($data['return_services_ids'], $data['refund_services_data']);
    }
    check_tax_is_correct($data);
    insert_return_transactions($data, $order, "credit");
}
add_action('wp_ajax_create_credit_return', 'create_credit_return');

function create_refund_return()
{
    check_ajax_referer('mji_inventory_nonce', 'nonce');
    $data = sanitize_and_validate_return($_POST);

    $order = order_exists($data['order_id']);
    if (!$order) {
        wp_send_json_error(['message' => 'Order does not exist'], 404);
    }

    // get the order item ids to see if they match the items order id
    $order_item_ids = get_order_items($data['order_id']);
    if (!order_items_valid($order_item_ids, $data['refund_items_ids'])) {
        wp_send_json_error(['message' => 'One or more return items are invalid'], 422);
    }

    $order_service_ids = get_order_services($data['order_id']);
    if (!order_items_valid($order_service_ids, $data['refund_services_ids'])) {
        wp_send_json_error(['message' => 'One or more return items are invalid'], 422);
    }

    if (!empty($data['refund_items_data'])) {
        check_already_returned($data['refund_items_ids'], $data['refund_items_data']);
        check_items_price($data['refund_items_ids'], $data['refund_items_data'], $data['item_returned']);
    }

    if (!empty($data['refund_services_data'])) {
        check_service_already_returned($data['refund_services_ids'], $data['refund_services_data']);
        check_services_price($data['refund_services_ids'], $data['refund_services_data']);
    }

    check_tax_is_correct($data);
    insert_return_transactions($data, $order, "refund");
}
add_action('wp_ajax_create_refund_return', 'create_refund_return');

function create_refund_layaway()
{
    check_ajax_referer('mji_inventory_nonce', 'nonce');
    global $wpdb;

    $layaways_table = "{$wpdb->prefix}mji_layaways";
    $payments_table = "{$wpdb->prefix}mji_payments";
    $customer_table = "{$wpdb->prefix}mji_customers";

    // Validate data
    $reference = sanitize_text_field($_POST['reference'] ?? '');
    $refund_reference = sanitize_text_field($_POST['refund-reference'] ?? '');
    $refund_date = sanitize_text_field($_POST['refund-date'] ?? '');
    $refund_reason = sanitize_textarea_field($_POST['refund-reason'] ?? '');
    $salesperson_id = absint($_POST['salesperson'] ?? 0);

    if (empty($reference) || empty($refund_reference)) {
        wp_send_json_error([
            'message' => "Original layaway reference number and new refund number is required",
        ], 422);
    }
    $date = DateTime::createFromFormat('Y-m-d', $refund_date);
    if (!$date || $date->format('Y-m-d') !== $refund_date) {
        wp_send_json_error([
            'message' => "Invalid date format",
        ], 422);
    }

    if ($salesperson_id == 0) {
        wp_send_json_error([
            'message' => "Salesperson needs to be selected",
        ], 422);
    }

    $allowed_payment_methods = [
        'cash',
        'cheque',
        'debit',
        'visa',
        'master_card',
        'amex',
        'bank_draft',
        'cup',
        'alipay',
        'wire'
    ];

    $payment_data = [];
    $refund_total = 0;

    foreach ($allowed_payment_methods as $method) {
        $amount = isset($_POST[$method]) ? sanitize_payment_amount($_POST[$method]) : 0;
        if ($amount > 0) {
            // Store as an indexed list of objects
            $payment_data[] = [
                'method' => $method,
                'amount' => $amount
            ];
            $refund_total += $amount;
        }
    }

    if ($refund_total <= 0) {
        wp_send_json_error(['message' => "Refund amount must be greater than zero"], 422);
    }

    $layaway = $wpdb->get_row($wpdb->prepare(
        "SELECT l.*, c.id as customer_id, c.prefix, c.first_name, c.last_name, c.street_address, c.city, c.province, c.postal_code, c.country, c.primary_phone, c.secondary_phone, c.email 
        FROM $layaways_table l
        JOIN $customer_table c ON l.customer_id = c.id
        WHERE l.reference_num = %s",
        $reference
    ));

    if (!$layaway) {
        wp_send_json_error(['message' => "Layaway not found"], 404);
    }

    if ($refund_total > $layaway->remaining_amount) {
        wp_send_json_error(['message' => "Refund amount greater than layaway value"], 422);
    }

    $wpdb->query('START TRANSACTION');
    try {
        // Re-read balance with row lock — prevents concurrent refunds double-deducting
        $fresh = $wpdb->get_row($wpdb->prepare(
            "SELECT remaining_amount, total_amount FROM $layaways_table WHERE id = %d FOR UPDATE",
            $layaway->id
        ));
        if (!$fresh || $refund_total > $fresh->remaining_amount) {
            throw new RuntimeException("Refund amount exceeds available layaway balance.");
        }

        $refundCents    = (int) round($refund_total * 100);
        $totalCents     = (int) round($fresh->total_amount * 100);
        $remainingCents = (int) round($fresh->remaining_amount * 100);

        $new_status = 'active';
        if ($refundCents === $totalCents) {
            $new_status = 'cancelled';
        } elseif ($remainingCents - $refundCents <= 0) {
            $new_status = 'cancelled';
        }

        foreach ($payment_data as $payment) {
            $wpdb->insert($payments_table, [
                'customer_id' => $layaway->customer_id,
                'salesperson_id' => $salesperson_id,
                'location_id' => $layaway->location_id,
                'layaway_id' => $layaway->id,
                'reference_num' => $refund_reference,
                'method' => $payment['method'],
                'amount' => $payment['amount'],
                'transaction_type' => 'refund',
                'payment_date' => $refund_date,
                'notes' => $refund_reason,
            ]);
            check_wpdb_error($wpdb);
        }

        $wpdb->update(
            $layaways_table,
            [
                'total_amount' => $fresh->total_amount,
                'remaining_amount' => $fresh->remaining_amount - $refund_total,
                'status' => $new_status,
            ],
            ['id' => $layaway->id]
        );
        check_wpdb_error($wpdb);

        $all_salepeople = mji_get_salespeople();
        $salesperson = array_find($all_salepeople, fn($p) => $p->id == $salesperson_id);
        $customer_info = [
            "prefix" => $layaway->prefix,
            "first_name" => $layaway->first_name,
            "last_name" => $layaway->last_name,
            "street_address" => $layaway->street_address,
            "city" => $layaway->city,
            "province" => $layaway->province,
            "postal_code" => $layaway->postal_code,
            "country" => $layaway->country,
            "phone" => $layaway->primary_phone ?: $layaway->secondary_phone,
            "email" => $layaway->email,
        ];
        $wpdb->query('COMMIT');

        wp_send_json_success([
            'message' => "Refund processed",
            'original_reference' => $reference,
            'reference_num' => $refund_reference,
            'remaining_amount' => $fresh->remaining_amount - $refund_total,
            'payment_data' => $payment_data,
            'reason' => $refund_reason,
            'date' => $date,
            'salesperson' => $salesperson,
            'customer_info' => $customer_info,
            'refund_total' => $refund_total,
            'type' => 'layaway'
        ]);
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
add_action('wp_ajax_create_refund_layaway', 'create_refund_layaway');

function create_refund_credit()
{
    check_ajax_referer('mji_inventory_nonce', 'nonce');
    global $wpdb;

    $credits_table = "{$wpdb->prefix}mji_credits";
    $payments_table = "{$wpdb->prefix}mji_payments";
    $customer_table = "{$wpdb->prefix}mji_customers";

    // Validate data
    $reference = sanitize_text_field($_POST['reference'] ?? '');
    $refund_reference = sanitize_text_field($_POST['refund-reference'] ?? '');
    $refund_date = sanitize_text_field($_POST['refund-date'] ?? '');
    $refund_reason = sanitize_textarea_field($_POST['refund-reason'] ?? '');
    $salesperson_id = absint($_POST['salesperson'] ?? 0);

    if (empty($reference) || empty($refund_reference)) {
        wp_send_json_error([
            'message' => "Original layaway reference number and new refund number is required",
        ], 422);
    }
    $date = DateTime::createFromFormat('Y-m-d', $refund_date);
    if (!$date || $date->format('Y-m-d') !== $refund_date) {
        wp_send_json_error([
            'message' => "Invalid date format",
        ], 422);
    }

    if ($salesperson_id == 0) {
        wp_send_json_error([
            'message' => "Salesperson needs to be selected",
        ], 422);
    }

    $allowed_payment_methods = [
        'cash',
        'cheque',
        'debit',
        'visa',
        'master_card',
        'amex',
        'bank_draft',
        'cup',
        'alipay',
        'wire'
    ];

    $payment_data = [];
    $refund_total = 0;

    foreach ($allowed_payment_methods as $method) {
        $amount = isset($_POST[$method]) ? sanitize_payment_amount($_POST[$method]) : 0;
        if ($amount > 0) {
            // Store as an indexed list of objects
            $payment_data[] = [
                'method' => $method,
                'amount' => $amount
            ];
            $refund_total += $amount;
        }
    }

    if ($refund_total <= 0) {
        wp_send_json_error(['message' => "Refund amount must be greater than zero"], 422);
    }

    $credit = $wpdb->get_row($wpdb->prepare(
        "SELECT l.*, c.id as customer_id, c.prefix, c.first_name, c.last_name, c.street_address, c.city, c.province, c.postal_code, c.country, c.primary_phone, c.secondary_phone, c.email 
        FROM $credits_table l
        JOIN $customer_table c ON l.customer_id = c.id
        WHERE l.reference_num = %s",
        $reference
    ));

    if (!$credit) {
        wp_send_json_error(['message' => "Credit not found"], 404);
    }

    $wpdb->query('START TRANSACTION');
    try {

        $fresh = $wpdb->get_row($wpdb->prepare(
            "SELECT remaining_amount, total_amount FROM $credits_table WHERE id = %d FOR UPDATE",
            $credit->id
        ));
        if (!$fresh) {
            throw new RuntimeException("Credit record not found.");
        }
        if ($refund_total > $fresh->remaining_amount) {
            throw new RuntimeException("Refund amount greater than credit balance.");
        }

        // === Determine Status ===
        $refundCents    = (int) round($refund_total * 100);
        $totalCents     = (int) round($fresh->total_amount * 100);
        $remainingCents = (int) round($fresh->remaining_amount * 100);

        $new_status = 'active';
        if ($refundCents === $totalCents) {
            $new_status = 'cancelled';
        } elseif ($remainingCents - $refundCents <= 0) {
            $new_status = 'cancelled';
        }

        foreach ($payment_data as $payment) {
            $wpdb->insert($payments_table, [
                'customer_id' => $credit->customer_id,
                'salesperson_id' => $salesperson_id,
                'location_id' => $credit->location_id,
                'credit_id' => $credit->id,
                'reference_num' => $refund_reference,
                'method' => $payment['method'],
                'amount' => $payment['amount'],
                'transaction_type' => 'refund',
                'payment_date' => $refund_date,
                'notes' => $refund_reason,
            ]);
            check_wpdb_error($wpdb);
        }

        $wpdb->update(
            $credits_table,
            [
                'remaining_amount' => $fresh->remaining_amount - $refund_total,
                'status' => $new_status,
            ],
            ['id' => $credit->id]
        );
        check_wpdb_error($wpdb);

        $all_salepeople = mji_get_salespeople();
        $salesperson = array_find($all_salepeople, fn($p) => $p->id == $salesperson_id);
        $customer_info = [
            "prefix" => $credit->prefix,
            "first_name" => $credit->first_name,
            "last_name" => $credit->last_name,
            "street_address" => $credit->street_address,
            "city" => $credit->city,
            "province" => $credit->province,
            "postal_code" => $credit->postal_code,
            "country" => $credit->country,
            "phone" => $credit->primary_phone ?: $credit->secondary_phone,
            "email" => $credit->email,
        ];
        $wpdb->query('COMMIT');

        wp_send_json_success([
            'message' => "Refund processed",
            'original_reference' => $reference,
            'reference_num' => $refund_reference,
            'remaining_amount' => $fresh->remaining_amount - $refund_total,
            'payment_data' => $payment_data,
            'reason' => $refund_reason,
            'date' => $date,
            'salesperson' => $salesperson,
            'customer_info' => $customer_info,
            'refund_total' => $refund_total,
            'type' => 'credit'
        ]);
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
add_action('wp_ajax_create_refund_credit', 'create_refund_credit');

function edit_sale()
{
    check_ajax_referer('mji_inventory_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorised.'], 403);
    }

    global $wpdb;
    $orders_table   = $wpdb->prefix . 'mji_orders';
    $payments_table = $wpdb->prefix . 'mji_payments';
    $layaways_table = $wpdb->prefix . 'mji_layaways';
    $credits_table  = $wpdb->prefix . 'mji_credits';

    $order_id          = absint($_POST['order_id'] ?? 0);
    $new_reference_num = sanitize_text_field($_POST['reference_num'] ?? '');
    $original_ref      = sanitize_text_field($_POST['original_reference'] ?? '');
    $date              = sanitize_text_field($_POST['date'] ?? '');
    $salesperson_id    = absint($_POST['salesperson_id'] ?? 0);
    $notes             = sanitize_textarea_field($_POST['notes'] ?? '');
    $pay_raw           = $_POST['pay'] ?? [];

    if (!$order_id || !$new_reference_num || !$date || !$salesperson_id) {
        wp_send_json_error(['message' => 'Required fields missing.']);
    }
    if (!strtotime($date)) {
        wp_send_json_error(['message' => 'Invalid date.']);
    }

    // Sanitize and validate pay[] keys and amounts
    $allowed_regular = [
        'cash',
        'cheque',
        'debit',
        'visa',
        'master_card',
        'amex',
        'bank_draft',
        'cup',
        'alipay',
        'gift_card',
        'wire'
    ];
    $pay = [];
    foreach ($pay_raw as $key => $raw_amount) {
        $amount = max(0.0, (float) $raw_amount);
        if (in_array(sanitize_key($key), $allowed_regular, true)) {
            $pay[sanitize_key($key)] = $amount;
        } elseif (preg_match('/^(layaway|credit):(\d+)$/', $key, $m)) {
            $pay[$m[1] . ':' . (int) $m[2]] = $amount; // rebuild clean key from captured groups
        } else {
            wp_send_json_error(['message' => 'Invalid payment field: ' . sanitize_text_field($key)]);
        }
    }

    if ($new_reference_num !== $original_ref) {
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$orders_table} WHERE reference_num = %s LIMIT 1",
            $new_reference_num
        ));
        if ($exists) {
            wp_send_json_error(['message' => 'Reference number already in use.']);
        }
    }

    // Fetch order for customer_id, location_id, total (needed for inserts + validation)
    $order_row = $wpdb->get_row($wpdb->prepare(
        "SELECT customer_id, location_id, total, created_at FROM {$orders_table} WHERE id = %d",
        $order_id
    ));
    if (!$order_row) {
        wp_send_json_error(['message' => 'Order not found.']);
    }

    $payment_sum = round(array_sum($pay), 2);
    if ($payment_sum !== round((float) $order_row->total, 2)) {
        wp_send_json_error(['message' => sprintf(
            'Payment total ($%.2f) does not match order total ($%.2f).',
            $payment_sum,
            (float) $order_row->total
        )]);
    }

    // Fetch existing payment rows, build map key → row
    $existing_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT id, method, amount, layaway_id, credit_id FROM {$payments_table} WHERE reference_num = %s",
        $original_ref
    ));
    $existing_map = [];
    foreach ($existing_rows as $er) {
        if ($er->method === 'layaway' && $er->layaway_id) {
            $existing_map['layaway:' . (int) $er->layaway_id] = $er;
        } elseif ($er->method === 'credit' && $er->credit_id) {
            $existing_map['credit:' . (int) $er->credit_id] = $er;
        } else {
            $existing_map[$er->method] = $er;
        }
    }

    $wpdb->query('START TRANSACTION');
    try {
        // Update order
        $updated = $wpdb->update(
            $orders_table,
            [
                'reference_num'  => $new_reference_num,
                'created_at'     => date('Y-m-d H:i:s', strtotime($date)),
                'salesperson_id' => $salesperson_id,
                'notes'          => $notes,
            ],
            ['id' => $order_id],
            ['%s', '%s', '%d', '%s'],
            ['%d']
        );
        if ($updated === false) {
            throw new Exception($wpdb->last_error ?: 'Failed to update order.');
        }

        // Cascade reference_num change to all payment rows
        if ($new_reference_num !== $original_ref) {
            $wpdb->update(
                $payments_table,
                ['reference_num' => $new_reference_num],
                ['reference_num' => $original_ref],
                ['%s'],
                ['%s']
            );
        }

        // Process each submitted pay field
        foreach ($pay as $key => $new_amount) {
            $existing = $existing_map[$key] ?? null;
            $is_account = str_contains($key, ':');

            if ($new_amount > 0 && $existing) {
                // Update existing row
                $old_amount  = (float) $existing->amount;
                $amount_diff = $new_amount - $old_amount;

                if ($is_account && $amount_diff != 0) {
                    [$acc_type, $acc_id] = explode(':', $key, 2);
                    $acc_id    = (int) $acc_id;
                    $acc_table = $acc_type === 'layaway' ? $layaways_table : $credits_table;

                    $acc = $wpdb->get_row($wpdb->prepare(
                        "SELECT remaining_amount, total_amount FROM {$acc_table} WHERE id = %d FOR UPDATE",
                        $acc_id
                    ));
                    if (!$acc) throw new Exception("Account {$key} not found.");
                    $new_remaining = (float) $acc->remaining_amount - $amount_diff;
                    if ($new_remaining < 0) throw new Exception("Amount exceeds balance for {$key}.");
                    if ($new_remaining > (float) $acc->total_amount) throw new Exception("Remaining exceeds total for {$key}.");
                    $new_status = $new_remaining == 0 ? 'redeemed' : 'active';
                    $wpdb->update(
                        $acc_table,
                        ['remaining_amount' => $new_remaining, 'status' => $new_status],
                        ['id' => $acc_id],
                        ['%f', '%s'],
                        ['%d']
                    );
                }

                $wpdb->update($payments_table, ['amount' => $new_amount], ['id' => $existing->id], ['%f'], ['%d']);
            } elseif ($new_amount > 0 && !$existing) {
                // Insert new payment row
                $transaction_type = 'purchase';
                $layaway_id       = null;
                $credit_id        = null;
                $method           = $key;

                if ($is_account) {
                    [$acc_type, $acc_id] = explode(':', $key, 2);
                    $acc_id    = (int) $acc_id;
                    $acc_table = $acc_type === 'layaway' ? $layaways_table : $credits_table;
                    $method    = $acc_type;

                    $acc = $wpdb->get_row($wpdb->prepare(
                        "SELECT remaining_amount, total_amount FROM {$acc_table} WHERE id = %d FOR UPDATE",
                        $acc_id
                    ));
                    if (!$acc) throw new Exception("Account {$key} not found.");
                    $new_remaining = (float) $acc->remaining_amount - $new_amount;
                    if ($new_remaining < 0) throw new Exception("Amount exceeds balance for {$key}.");
                    $new_status = $new_remaining == 0 ? 'redeemed' : 'active';
                    $wpdb->update(
                        $acc_table,
                        ['remaining_amount' => $new_remaining, 'status' => $new_status],
                        ['id' => $acc_id],
                        ['%f', '%s'],
                        ['%d']
                    );

                    $transaction_type = $acc_type === 'layaway' ? 'layaway_redemption' : 'credit_redemption';
                    if ($acc_type === 'layaway') $layaway_id = $acc_id;
                    else $credit_id = $acc_id;
                }

                $wpdb->insert($payments_table, [
                    'order_id'         => $order_id,
                    'reference_num'    => $new_reference_num,
                    'method'           => $method,
                    'amount'           => $new_amount,
                    'transaction_type' => $transaction_type,
                    'customer_id'      => $order_row->customer_id,
                    'salesperson_id'   => $salesperson_id,
                    'location_id'      => $order_row->location_id,
                    'layaway_id'       => $layaway_id,
                    'credit_id'        => $credit_id,
                    'payment_date'     => date('Y-m-d H:i:s', strtotime($date)),
                    'notes'            => '',
                ], ['%d', '%s', '%s', '%f', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s']);
                if ($wpdb->last_error) throw new Exception('Failed to insert payment: ' . $wpdb->last_error);
            } elseif ($new_amount == 0 && $existing) {
                // Delete zeroed-out row, restore account balance if applicable
                if ($is_account) {
                    [$acc_type, $acc_id] = explode(':', $key, 2);
                    $acc_id    = (int) $acc_id;
                    $acc_table = $acc_type === 'layaway' ? $layaways_table : $credits_table;
                    $old_amount = (float) $existing->amount;

                    $acc = $wpdb->get_row($wpdb->prepare(
                        "SELECT remaining_amount, total_amount FROM {$acc_table} WHERE id = %d FOR UPDATE",
                        $acc_id
                    ));
                    if ($acc) {
                        $restored = (float) $acc->remaining_amount + $old_amount;
                        $restored = min($restored, (float) $acc->total_amount);
                        $wpdb->update(
                            $acc_table,
                            ['remaining_amount' => $restored, 'status' => 'active'],
                            ['id' => $acc_id],
                            ['%f', '%s'],
                            ['%d']
                        );
                    }
                }
                $wpdb->delete($payments_table, ['id' => $existing->id], ['%d']);
            }
            // $new_amount == 0 && !$existing → nothing to do
        }

        $wpdb->query('COMMIT');
        wp_send_json_success(['message' => 'Invoice updated successfully.']);
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        custom_log('[Edit Sale Failed] ' . $e->getMessage());
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
add_action('wp_ajax_edit_sale', 'edit_sale');

function edit_layaway()
{
    check_ajax_referer('mji_inventory_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorised.'], 403);
    }

    global $wpdb;
    $layaways_table = $wpdb->prefix . 'mji_layaways';
    $credits_table  = $wpdb->prefix . 'mji_credits';
    $payments_table = $wpdb->prefix . 'mji_payments';

    $type           = sanitize_key($_POST['type'] ?? '');
    $account_id     = absint($_POST['account_id'] ?? 0);
    $new_reference  = sanitize_text_field($_POST['reference_num'] ?? '');
    $original_ref   = sanitize_text_field($_POST['original_reference'] ?? '');
    $date           = sanitize_text_field($_POST['date'] ?? '');
    $salesperson_id = absint($_POST['salesperson_id'] ?? 0);
    $notes          = sanitize_textarea_field($_POST['notes'] ?? '');
    $pay_raw        = $_POST['lay_pay'] ?? [];

    if (!in_array($type, ['layaway', 'credit'], true)) {
        wp_send_json_error(['message' => 'Invalid type.']);
    }
    if (!$account_id || !$new_reference || !$date || !$salesperson_id) {
        wp_send_json_error(['message' => 'Required fields missing.']);
    }
    if (!strtotime($date)) {
        wp_send_json_error(['message' => 'Invalid date.']);
    }

    $acc_table = $type === 'layaway' ? $layaways_table : $credits_table;

    $allowed_regular = [
        'cash', 'cheque', 'debit', 'visa', 'master_card', 'amex',
        'bank_draft', 'cup', 'alipay', 'gift_card', 'wire',
    ];
    $pay = [];
    foreach ($pay_raw as $key => $raw_amount) {
        $amount = max(0.0, (float) $raw_amount);
        if (in_array(sanitize_key($key), $allowed_regular, true)) {
            $pay[sanitize_key($key)] = $amount;
        } else {
            wp_send_json_error(['message' => 'Invalid payment field: ' . sanitize_text_field($key)]);
        }
    }

    // Fetch existing deposit rows and calculate total
    $existing_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT id, method, amount FROM {$payments_table} WHERE reference_num = %s",
        $original_ref
    ));
    if (!$existing_rows) {
        wp_send_json_error(['message' => 'No payment records found for this reference.']);
    }
    $deposit_total = round(array_sum(array_map(fn($r) => (float) $r->amount, $existing_rows)), 2);

    $payment_sum = round(array_sum($pay), 2);
    if ($payment_sum !== $deposit_total) {
        wp_send_json_error(['message' => sprintf(
            'Payment total ($%.2f) does not match deposit total ($%.2f).',
            $payment_sum,
            $deposit_total
        )]);
    }

    // Check reference uniqueness
    if ($new_reference !== $original_ref) {
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$payments_table} WHERE reference_num = %s LIMIT 1",
            $new_reference
        ));
        if ($exists) {
            wp_send_json_error(['message' => 'Reference number already in use.']);
        }
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$acc_table} WHERE reference_num = %s LIMIT 1",
            $new_reference
        ));
        if ($exists) {
            wp_send_json_error(['message' => 'Reference number already in use.']);
        }
    }

    // Fetch account row for customer_id and location_id
    $acc_row = $wpdb->get_row($wpdb->prepare(
        "SELECT customer_id, location_id FROM {$acc_table} WHERE id = %d",
        $account_id
    ));
    if (!$acc_row) {
        wp_send_json_error(['message' => ucfirst($type) . ' not found.']);
    }

    $wpdb->query('START TRANSACTION');
    try {
        // Update account table: reference_num and created_at
        $updated = $wpdb->update(
            $acc_table,
            [
                'reference_num' => $new_reference,
                'created_at'    => date('Y-m-d H:i:s', strtotime($date)),
            ],
            ['id' => $account_id],
            ['%s', '%s'],
            ['%d']
        );
        if ($updated === false) {
            throw new Exception($wpdb->last_error ?: 'Failed to update ' . $type . '.');
        }

        // Build existing map by method
        $existing_map = [];
        foreach ($existing_rows as $er) {
            $existing_map[$er->method] = $er;
        }

        // Process each payment field
        foreach ($pay as $key => $new_amount) {
            $existing = $existing_map[$key] ?? null;

            if ($new_amount > 0 && $existing) {
                $wpdb->update(
                    $payments_table,
                    [
                        'amount'         => $new_amount,
                        'reference_num'  => $new_reference,
                        'payment_date'   => date('Y-m-d H:i:s', strtotime($date)),
                        'salesperson_id' => $salesperson_id,
                        'notes'          => $notes,
                    ],
                    ['id' => $existing->id],
                    ['%f', '%s', '%s', '%d', '%s'],
                    ['%d']
                );
                if ($wpdb->last_error) throw new Exception('Failed to update payment: ' . $wpdb->last_error);
            } elseif ($new_amount > 0 && !$existing) {
                $transaction_type = $type === 'layaway' ? 'layaway_deposit' : 'credit_deposit';
                $layaway_id = $type === 'layaway' ? $account_id : null;
                $credit_id  = $type === 'credit'  ? $account_id : null;
                $wpdb->insert($payments_table, [
                    'reference_num'    => $new_reference,
                    'method'           => $key,
                    'amount'           => $new_amount,
                    'transaction_type' => $transaction_type,
                    'customer_id'      => $acc_row->customer_id,
                    'salesperson_id'   => $salesperson_id,
                    'location_id'      => $acc_row->location_id,
                    'layaway_id'       => $layaway_id,
                    'credit_id'        => $credit_id,
                    'payment_date'     => date('Y-m-d H:i:s', strtotime($date)),
                    'notes'            => $notes,
                ], ['%s', '%s', '%f', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s']);
                if ($wpdb->last_error) throw new Exception('Failed to insert payment: ' . $wpdb->last_error);
            } elseif ($new_amount == 0 && $existing) {
                $wpdb->delete($payments_table, ['id' => $existing->id], ['%d']);
            }
            // $new_amount == 0 && !$existing → nothing to do
        }

        // Cascade reference_num change to any remaining payment rows
        if ($new_reference !== $original_ref) {
            $wpdb->update(
                $payments_table,
                ['reference_num' => $new_reference],
                ['reference_num' => $original_ref],
                ['%s'],
                ['%s']
            );
        }

        $wpdb->query('COMMIT');
        wp_send_json_success(['message' => ucfirst($type) . ' updated successfully.']);
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        custom_log('[Edit Layaway/Credit Failed] ' . $e->getMessage());
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
add_action('wp_ajax_edit_layaway', 'edit_layaway');
