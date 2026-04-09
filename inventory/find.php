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

        <?= render_search_section() ?>
    </div>

<?php
}

// Reports sales Section
function render_search_section()
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
            $results = reports_search_sales_results($reference_num);
            if (!$results) {
                echo "<div class='wrap'>";
                echo "<h2>Invoice " . esc_html($reference_num) . " not found!!";
                echo "</div>";
            } else {
                render_invoice($results);
                return;
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
            $results = search_refund_results($reference_num);
            if (!$results) {
                echo "<div class='wrap'>";
                echo "<h2>Invoice " . esc_html($reference_num) . " not found!!";
                echo "</div>";
            } else {
                render_refund_invoice($results);
                return;
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
            'order' => $order,
            'items' => $items,
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
    custom_log($results);
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
                                    $product_id = $item->wc_product_variant_id ?: $item->wc_product_id;
                                    $is_variant = empty($item->wc_product_variant_id) ? false : true;
                                    $product = wc_get_product($product_id);
                                    $description = "";

                                    if (!$product) {
                                        echo "<p>No product found";
                                        continue;
                                    }

                                    $image_url = esc_url(wp_get_attachment_image_url(get_post_thumbnail_id($item->wc_product_id), 'thumbnail'));
                                    if ($is_variant) {
                                        $description = $product->get_description();
                                    } else {
                                        $description = $product->get_short_description();
                                    }
                                    $item->description = $description;
                                    $item->image_url = $image_url;
                                    echo "<img src='" . esc_url($image_url) . "' alt='product image' />";
                                    echo "<p>";
                                    if (!empty($item->sku))
                                        echo "<b>SKU: " . esc_html($item->sku) . "</b><br/>";
                                    echo nl2br(wp_kses_post($description));
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
            <form method="post" style="margin-top:20px; position: relative; z-index:1;"
                onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                <?php wp_nonce_field('delete_invoice_action', 'delete_invoice_nonce'); ?>
                <input type="hidden" name="order_id" value="<?= intval($order->id) ?>">
                <input type="hidden" name="action" value="delete_invoice">
                <?php submit_button('Delete Invoice', 'primary', 'delete_invoice'); ?>
                <button type="button" class="button issue_credit" id="issue_credit">Issue credit</button>
                <button type="button" class="button issue_refund" id="issue_refund">Issue refund</button>
                <button type="button" class="button print" id="main-print-btn">Print</button>
            </form>
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
                                                id="return_items[<?= $item->id ?>]" value="<?= $item->id ?>"
                                                data-subtotal="<?= $item->sale_price ?>" data-gst="<?= $calculate_gst ?>"
                                                data-pst="<?= $calculate_pst ?>">
                                            <label for="return_items[<?= $item->id ?>]" class="item-content">
                                                <img class="item-image" src="<?= $item->image_url ?>"
                                                    alt="<?= esc_attr($product->get_name()) ?>">
                                                <div class="item-info">
                                                    <p class="item-details">
                                                        Order ID: <?= esc_html($item->id) ?><br>
                                                        <?= esc_html($product->get_name()) ?><br>
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
                                                        <input class="refund_price" name="refund_prices[<?= $item->id ?>]" step="0.01"
                                                            type="number" value="<?= $item->sale_price ?>"
                                                            max="<?= $item->sale_price ?>" />
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
                                                id="return_services[<?= $service->id ?>]" value="<?= $service->id ?>"
                                                data-subtotal="<?= $service->sold_price ?>" data-gst="<?= $calculate_gst ?>"
                                                data-pst="<?= $calculate_pst ?>">
                                            <label for="return_services[<?= $service->id ?>]" class="item-content">
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

                                                        <input class="refund_price" name="refund_prices[<?= $service->id ?>]"
                                                            step="0.01" type="number" value="<?= $service->sold_price ?>"
                                                            max="<?= $service->sold_price ?>" />
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
                                                id="refund_items[<?= $item->id ?>]" value="<?= $item->id ?>"
                                                data-subtotal="<?= $item->sale_price ?>" data-gst="<?= $calculate_gst ?>"
                                                data-pst="<?= $calculate_pst ?>">
                                            <label for="refund_items[<?= $item->id ?>]" class="item-content">
                                                <img class="item-image" src="<?= $item->image_url ?>"
                                                    alt="<?= esc_attr($product->get_name()) ?>">
                                                <div class="item-info">
                                                    <p class="item-details">
                                                        Order ID: <?= esc_html($item->id) ?><br>
                                                        <?= esc_html($product->get_name()) ?><br>
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
                                                        <input class="refund_price" name="refund_prices[<?= $item->id ?>]" step="0.01"
                                                            type="number" value="<?= $item->sale_price ?>"
                                                            max="<?= $item->sale_price ?>" />
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
                                                id="refund_services[<?= $service->id ?>]" value="<?= $service->id ?>"
                                                data-subtotal="<?= $service->sold_price ?>" data-gst="<?= $calculate_gst ?>"
                                                data-pst="<?= $calculate_pst ?>">
                                            <label for="refund_services[<?= $service->id ?>]" class="item-content">
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

                                                        <input class="refund_price" name="refund_prices[<?= $service->id ?>]"
                                                            step="0.01" type="number" value="<?= $service->sold_price ?>"
                                                            max="<?= $service->sold_price ?>" />
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
                                        <label for="discover">Discover:</label>
                                        <input type="number" min="0" step="0.01" id="discover" name="discover">
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

                    $product_id = $return_item->wc_product_variant_id ?: $return_item->wc_product_id;
                    $product = wc_get_product($product_id);
                    $description = "";

                    if (!$product) {
                        custom_log("{Product {$product_id} not found");
                        throw new Exception("Product {$product_id} not found.");
                    }

                    $image_url = esc_url(wp_get_attachment_image_url(get_post_thumbnail_id($return_item->wc_product_id), 'thumbnail'));
                    if ($return_item->wc_product_variant_id) {
                        $description = $product->get_description();
                    } else {
                        $description = $product->get_short_description();
                    }

                    $item = [
                        "sku" => $return_item->sku,
                        "serial" => $return_item->serial,
                        "description" => $description,
                        "image_url" => $image_url,
                        "sold_price" => $return_item->sale_price,
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
        <?php endif; ?>
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
                                <label for="discover">Discover:</label>
                                <input type="number" min="0" step="0.01" id="discover" name="discover">
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

                $product_id = $return_item->wc_product_variant_id ?: $return_item->wc_product_id;
                $product = wc_get_product($product_id);
                $description = "";

                if (!$product) {
                    custom_log("{Product {$product_id} not found");
                    throw new Exception("Product {$product_id} not found.");
                }

                $image_url = esc_url(wp_get_attachment_image_url(get_post_thumbnail_id($return_item->wc_product_id), 'thumbnail'));
                if ($return_item->wc_product_variant_id) {
                    $description = $product->get_description();
                } else {
                    $description = $product->get_short_description();
                }

                $item = [
                    "sku" => $return_item->sku,
                    "serial" => $return_item->serial,
                    "description" => $description,
                    "image_url" => $image_url,
                    "sold_price" => $return_item->sale_price,
                    "returned_price" => $return_item->unit_price
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
                        $product = wc_get_product($product_id);
                        if (!$product) {
                            throw new RuntimeException("Invalid WooCommerce product ID: {$product_id}");
                        }

                        $product->set_stock_quantity($product->get_stock_quantity() - 1);
                        $product->save();
                        $restored_stock[] = $product_id;
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
                    $product = wc_get_product($product_id);
                    if (!$product) {
                        throw new RuntimeException("Invalid WooCommerce product ID: {$product_id}");
                    }

                    $product->set_stock_quantity($product->get_stock_quantity() - 1);
                    $product->save();
                    $restored_stock[] = $product_id;
                }
            }
        }

        $payments = $wpdb->get_results($wpdb->prepare("
            SELECT layaway_id, credit_id, amount
            FROM $payments_table
            WHERE reference_num = %s
        ", $reference_num));

        if ($payments) {
            $credit_id = 0;
            $layaway_id = 0;
            $total_amount = 0;
            foreach ($payments as $payment) {
                $total_amount += $payment->amount;
                if (!empty($payment->credit_id)) {
                    $credit_id = $payment->credit_id;
                } else if (!empty($payment->layaway_id)) {
                    $layaway_id = $payment->layaway_id;
                }
            }
            if ($credit_id) {
                $result = $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE {$credits_table} 
                        SET total_amount = total_amount, 
                        remaining_amount = remaining_amount + %f, 
                        status = 'active'
                        WHERE id = %d",
                        $total_amount,
                        $credit_id
                    )
                );

                if ($result === 0) {
                    throw new Exception("Credits was not updated");
                }
                check_wpdb_error($wpdb);
            }
            if ($layaway_id) {
                $result = $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE {$layaways_table} 
                        SET total_amount = total_amount, 
                        remaining_amount = remaining_amount + %f, 
                        status = 'active'
                        WHERE id = %d",
                        $total_amount,
                        $layaway_id
                    )
                );

                if ($result === 0) {
                    throw new Exception("Layaways was not updated");
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
        'discover',
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

    $GST_RATE = 0.05;
    $PST_RATE = 0.07;

    $gst_total = 0;
    $pst_total = 0;
    $subtotal = 0;
    $total = 0;
    $payment_total = 0;
    $refund_prices = $data['refund_prices'];

    foreach ($refund_prices as $id => $prices) {
        $subtotal += $prices;
        if ($data['gst_total'] > 0) {
            $gst_total += round($prices * $GST_RATE, 2);
        }
        if ($data['pst_total'] > 0) {
            $pst_total += round($prices * $PST_RATE, 2);
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
                $items_data[$item->id] = $items_info;
                $data['item_returned'] && $product->set_stock_quantity($product->get_stock_quantity() + 1);
                $product->save();
                $restored_stock[] = $product_id;
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
        'discover',
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

    // === Determine Status ===
    $new_status = 'active';
    if ($refund_total == $layaway->total_amount) {
        $new_status = 'cancelled';
    } elseif (($layaway->remaining_amount - $refund_total) <= 0) {
        $new_status = 'active';
    }

    $wpdb->query('START TRANSACTION');
    try {

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
                'total_amount' => $layaway->total_amount,
                'remaining_amount' => $layaway->remaining_amount - $refund_total,
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
            'remaining_amount' => $layaway->remaining_amount - $refund_total,
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
        'discover',
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

    if (!$credits_table) {
        wp_send_json_error(['message' => "Layaway not found"], 404);
    }

    if ($refund_total > $credit->remaining_amount) {
        wp_send_json_error(['message' => "Refund amount greater than layaway value"], 422);
    }

    // === Determine Status ===
    $new_status = 'active';
    if ($refund_total == $credit->total_amount) {
        $new_status = 'cancelled';
    } elseif (($credit->remaining_amount - $refund_total) <= 0) {
        $new_status = 'active';
    }

    $wpdb->query('START TRANSACTION');
    try {

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
                'total_amount' => $credit->total_amount,
                'remaining_amount' => $credit->remaining_amount - $refund_total,
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
            'remaining_amount' => $credit->remaining_amount - $refund_total,
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
