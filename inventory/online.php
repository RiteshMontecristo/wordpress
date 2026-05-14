<?php

function mji_online_settings_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('Access denied.'));
    }

    if (isset($_POST['mji_online_settings_nonce']) && wp_verify_nonce($_POST['mji_online_settings_nonce'], 'mji_save_online_settings')) {
        update_option('mji_notification_email', sanitize_email($_POST['mji_notification_email'] ?? ''));
        echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
    }

    $saved_email = esc_attr(get_option('mji_notification_email', ''));
    $online_sp   = mji_get_online_salesperson_id();
    ?>
    <div class="wrap">
        <h1>Online Order Settings</h1>
        <p>These settings control how WooCommerce online orders are recorded in the MJI inventory system.</p>
        <p>All online orders are automatically assigned to the <strong>Online Store</strong> salesperson (ID: <?php echo esc_html($online_sp); ?>).</p>
        <form method="post">
            <?php wp_nonce_field('mji_save_online_settings', 'mji_online_settings_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="mji_notification_email">Notification Email</label></th>
                    <td>
                        <input type="email" name="mji_notification_email" id="mji_notification_email"
                               class="regular-text" value="<?php echo $saved_email; ?>">
                        <p class="description">Who receives inventory-updated / error emails when an online order is placed. Defaults to the WordPress admin email if left blank.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
    <?php
}

function mji_get_online_salesperson_id()
{
    global $wpdb;
    $table = $wpdb->prefix . 'mji_salespeople';

    $id = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$table} WHERE first_name = %s AND last_name = %s",
        'Online', 'Store'
    ));

    if ($id) {
        return (int) $id;
    }

    $wpdb->insert($table, ['first_name' => 'Online', 'last_name' => 'Store']);
    delete_transient('mji_salespeople');

    return (int) $wpdb->insert_id;
}

function mji_get_online_location_id()
{
    global $wpdb;
    $table = $wpdb->prefix . 'mji_locations';

    $id = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$table} WHERE name = %s",
        'Online'
    ));

    if ($id) {
        return (int) $id;
    }

    $wpdb->insert($table, ['name' => 'Online']);
    delete_transient('mji_locations');

    return (int) $wpdb->insert_id;
}

function mji_get_or_create_customer_from_order($order)
{
    global $wpdb;
    $email = sanitize_email($order->get_billing_email());

    if ($email) {
        $existing_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}mji_customers WHERE email = %s",
            $email
        ));
        if ($existing_id) {
            return (int) $existing_id;
        }
    }

    $inserted = $wpdb->insert($wpdb->prefix . 'mji_customers', [
        'first_name'     => sanitize_text_field($order->get_billing_first_name()) ?: 'Online',
        'last_name'      => sanitize_text_field($order->get_billing_last_name()) ?: 'Customer',
        'email'          => $email ?: null,
        'street_address' => sanitize_text_field($order->get_billing_address_1()),
        'city'           => sanitize_text_field($order->get_billing_city()),
        'province'       => sanitize_text_field($order->get_billing_state()),
        'postal_code'    => sanitize_text_field($order->get_billing_postcode()),
        'country'        => sanitize_text_field($order->get_billing_country()),
        'primary_phone'  => sanitize_text_field($order->get_billing_phone()),
    ]);

    return $inserted ? (int) $wpdb->insert_id : 0;
}

function mji_map_wc_payment_method($gateway_id)
{
    $map = [
        'cod'    => 'cash',
        'cheque' => 'cheque',
        'alipay' => 'alipay',
        'wechat' => 'alipay',
        'cup'    => 'cup',
    ];
    return $map[$gateway_id] ?? 'visa';
}

add_action('woocommerce_checkout_order_processed', 'adjust_stock_after_order', 10, 3);

function adjust_stock_after_order($order_id, $posted_data, $order)
{
    global $wpdb;

    $salesperson_id = mji_get_online_salesperson_id();
    $location_id    = mji_get_online_location_id();
    if (!$salesperson_id || !$location_id) {
        custom_log("Online order #{$order_id}: could not get/create Online Store salesperson or location — skipping MJI inventory sync.");
        mji_notify_online_sale_error($order, 'Could not get or create the Online Store salesperson/location record. Please check the database.');
        return;
    }

    $reference_num = 'WEB-' . $order->get_order_number();

    // Idempotency guard — don't double-process if hook fires twice
    $already = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}mji_orders WHERE reference_num = %s",
        $reference_num
    ));
    if ($already) {
        return;
    }

    $customer_id = mji_get_or_create_customer_from_order($order);
    if (!$customer_id) {
        custom_log("Online order #{$order_id}: could not get/create MJI customer — aborting sync.");
        mji_notify_online_sale_error($order, 'Could not find or create an MJI customer record for this order. Please create the sale manually.');
        return;
    }

    $now        = current_time('mysql');
    $sold_units = [];

    $wpdb->query('START TRANSACTION');
    try {
        foreach ($order->get_items() as $item) {
            $variation_id    = (int) $item->get_variation_id();
            $product_id      = (int) $item->get_product_id();
            $qty             = (int) $item->get_quantity();
            $unit_price      = $qty > 0 ? round((float) $item->get_total() / $qty, 2) : 0.0;
            $unit_list_price = $qty > 0 ? round((float) $item->get_subtotal() / $qty, 2) : 0.0;
            $unit_discount   = round($unit_list_price - $unit_price, 2);

            if ($variation_id > 0) {
                $units = $wpdb->get_results($wpdb->prepare(
                    "SELECT id, sku, serial, retail_price
                     FROM {$wpdb->prefix}mji_product_inventory_units
                     WHERE wc_product_variant_id = %d AND status = 'in_stock'
                     ORDER BY created_date ASC
                     LIMIT %d
                     FOR UPDATE",
                    $variation_id,
                    $qty
                ));
            } else {
                $units = $wpdb->get_results($wpdb->prepare(
                    "SELECT id, sku, serial, retail_price
                     FROM {$wpdb->prefix}mji_product_inventory_units
                     WHERE wc_product_id = %d AND wc_product_variant_id IS NULL AND status = 'in_stock'
                     ORDER BY created_date ASC
                     LIMIT %d
                     FOR UPDATE",
                    $product_id,
                    $qty
                ));
            }

            if (count($units) < $qty) {
                throw new RuntimeException(sprintf(
                    "Not enough in_stock units for '%s' (need %d, found %d). Ensure this product's inventory units are linked via wc_product_id.",
                    $item->get_name(), $qty, count($units)
                ));
            }

            foreach ($units as $unit) {
                $sold_units[] = [
                    'unit'          => $unit,
                    'product_name'  => $item->get_name(),
                    'unit_price'    => $unit_price,
                    'unit_discount' => $unit_discount,
                ];
            }
        }

        // Split WC taxes into GST and PST by tax label name.
        // GST/HST → gst_total; PST/QST/RST → pst_total.
        // Handles inter-provincial orders: AB = GST only, ON = HST only, QC = GST+QST, MB = GST+RST.
        $gst_total = 0.0;
        $pst_total = 0.0;
        foreach ($order->get_taxes() as $tax_item) {
            $label = strtolower($tax_item->get_name());
            if (str_contains($label, 'pst') || str_contains($label, 'qst') || str_contains($label, 'rst')) {
                $pst_total += (float) $tax_item->get_tax_total();
            } else {
                $gst_total += (float) $tax_item->get_tax_total();
            }
        }
        $gst_total = round($gst_total, 2);
        $pst_total = round($pst_total, 2);

        // Subtotal = items after discount, excluding shipping.
        // Total = subtotal + taxes, excluding shipping (MJI records product sales, not fulfillment costs).
        $item_subtotal = 0.0;
        foreach ($sold_units as $entry) {
            $item_subtotal += $entry['unit_price'];
        }
        $item_subtotal = round($item_subtotal, 2);
        $mji_total     = round($item_subtotal + $gst_total + $pst_total, 2);

        $shipping_total = round((float) $order->get_shipping_total(), 2);
        $notes = 'WooCommerce online order #' . $order->get_order_number();
        if ($shipping_total > 0) {
            $notes .= sprintf(' | Shipping: $%s (not included in MJI total)', number_format($shipping_total, 2));
        }

        $inserted = $wpdb->insert($wpdb->prefix . 'mji_orders', [
            'customer_id'    => $customer_id,
            'salesperson_id' => $salesperson_id,
            'location_id'    => $location_id,
            'reference_num'  => $reference_num,
            'subtotal'       => $item_subtotal,
            'gst_total'      => $gst_total,
            'pst_total'      => $pst_total,
            'total'          => $mji_total,
            'created_at'     => $now,
            'notes'          => $notes,
        ]);
        if (!$inserted) {
            throw new RuntimeException('Failed to create MJI order record: ' . $wpdb->last_error);
        }
        $mji_order_id = $wpdb->insert_id;

        foreach ($sold_units as $entry) {
            $unit       = $entry['unit'];
            $sale_price = $entry['unit_price'];
            $discount   = $entry['unit_discount'];

            $inserted = $wpdb->insert($wpdb->prefix . 'mji_order_items', [
                'order_id'                  => $mji_order_id,
                'product_inventory_unit_id' => $unit->id,
                'sale_price'                => $sale_price,
                'discount_amount'           => $discount,
                'total'                     => $sale_price,
                'created_at'                => $now,
            ]);
            if (!$inserted) {
                throw new RuntimeException("Failed to insert order item for unit {$unit->id}: " . $wpdb->last_error);
            }

            $inserted = $wpdb->insert($wpdb->prefix . 'mji_inventory_status_history', [
                'inventory_unit_id' => $unit->id,
                'from_status'       => 'in_stock',
                'to_status'         => 'sold',
                'reference_num'     => $reference_num,
                'created_at'        => $now,
                'notes'             => 'Online order #' . $order->get_order_number(),
            ]);
            if (!$inserted) {
                throw new RuntimeException("Failed to insert status history for unit {$unit->id}: " . $wpdb->last_error);
            }

            $updated = $wpdb->update(
                $wpdb->prefix . 'mji_product_inventory_units',
                ['status' => 'sold', 'sold_date' => $now],
                ['id' => $unit->id]
            );
            if ($updated === false) {
                throw new RuntimeException("Failed to set unit {$unit->id} to sold: " . $wpdb->last_error);
            }
        }

        $payment_method = mji_map_wc_payment_method($order->get_payment_method());
        $inserted = $wpdb->insert($wpdb->prefix . 'mji_payments', [
            'order_id'         => $mji_order_id,
            'customer_id'      => $customer_id,
            'salesperson_id'   => $salesperson_id,
            'location_id'      => $location_id,
            'reference_num'    => $reference_num,
            'method'           => $payment_method,
            'amount'           => (float) $order->get_total(),
            'transaction_type' => 'purchase',
            'payment_date'     => $now,
            'notes'            => 'WC gateway: ' . $order->get_payment_method_title(),
        ]);
        if (!$inserted) {
            throw new RuntimeException('Failed to insert payment record: ' . $wpdb->last_error);
        }

        $wpdb->query('COMMIT');

        mji_notify_online_sale($order, $sold_units, $reference_num);

    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        custom_log("Online order #{$order_id} MJI sync error: " . $e->getMessage());
        mji_notify_online_sale_error($order, $e->getMessage());
    }
}

function mji_notify_online_sale($order, $sold_units, $reference_num)
{
    $to      = get_option('mji_notification_email') ?: get_option('admin_email');
    $subject = '[Montecristo] Online Order ' . $reference_num . ' — Inventory Updated';

    $rows = '';
    foreach ($sold_units as $entry) {
        $unit         = $entry['unit'];
        $serial       = $unit->serial ? esc_html($unit->serial) : '—';
        $discount_col = $entry['unit_discount'] > 0
            ? '<span style="color:#c00;">-$' . number_format($entry['unit_discount'], 2) . '</span>'
            : '—';
        $rows .= '<tr>
            <td style="padding:6px 12px;border-bottom:1px solid #eee;">' . esc_html($entry['product_name']) . '</td>
            <td style="padding:6px 12px;border-bottom:1px solid #eee;font-family:monospace;">' . esc_html($unit->sku) . '</td>
            <td style="padding:6px 12px;border-bottom:1px solid #eee;font-family:monospace;">' . $serial . '</td>
            <td style="padding:6px 12px;border-bottom:1px solid #eee;">' . $discount_col . '</td>
            <td style="padding:6px 12px;border-bottom:1px solid #eee;font-weight:bold;">$' . number_format($entry['unit_price'], 2) . '</td>
        </tr>';
    }

    $shipping_total = (float) $order->get_shipping_total();
    $shipping_row   = $shipping_total > 0
        ? '<tr><td style="padding:4px 0;color:#666;">Shipping (WC)</td><td>$' . number_format($shipping_total, 2) . ' CAD (not in MJI total)</td></tr>'
        : '';

    $message = '<!DOCTYPE html><html><body style="font-family:sans-serif;color:#333;max-width:680px;">
<h2 style="border-bottom:2px solid #c9a96e;padding-bottom:8px;color:#222;">Online Order — Inventory Synced</h2>
<table style="border-collapse:collapse;width:100%;margin-bottom:20px;">
<tr><td style="padding:4px 0;width:150px;color:#666;">WC Order #</td><td><strong>' . esc_html($order->get_order_number()) . '</strong></td></tr>
<tr><td style="padding:4px 0;color:#666;">MJI Reference</td><td><strong>' . esc_html($reference_num) . '</strong></td></tr>
<tr><td style="padding:4px 0;color:#666;">Customer</td><td>' . esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) . '</td></tr>
<tr><td style="padding:4px 0;color:#666;">Province</td><td>' . esc_html($order->get_billing_state()) . '</td></tr>
<tr><td style="padding:4px 0;color:#666;">Email</td><td>' . esc_html($order->get_billing_email()) . '</td></tr>
<tr><td style="padding:4px 0;color:#666;">Payment</td><td>' . esc_html($order->get_payment_method_title()) . '</td></tr>
<tr><td style="padding:4px 0;color:#666;">WC Order Total</td><td>$' . number_format((float) $order->get_total(), 2) . ' CAD</td></tr>
' . $shipping_row . '
</table>
<h3 style="margin-bottom:8px;">Units Marked as SOLD</h3>
<table style="border-collapse:collapse;width:100%;">
<thead><tr style="background:#f5f0e8;">
<th style="padding:8px 12px;text-align:left;border-bottom:2px solid #c9a96e;">Product</th>
<th style="padding:8px 12px;text-align:left;border-bottom:2px solid #c9a96e;">SKU</th>
<th style="padding:8px 12px;text-align:left;border-bottom:2px solid #c9a96e;">Serial</th>
<th style="padding:8px 12px;text-align:left;border-bottom:2px solid #c9a96e;">Discount</th>
<th style="padding:8px 12px;text-align:left;border-bottom:2px solid #c9a96e;">Sale Price</th>
</tr></thead>
<tbody>' . $rows . '</tbody>
</table>
<p style="margin-top:20px;color:#888;font-size:12px;">This is an automated message from Montecristo Jewellers inventory system.</p>
</body></html>';

    wp_mail($to, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
}

function mji_notify_online_sale_error($order, $error_message)
{
    $to      = get_option('mji_notification_email') ?: get_option('admin_email');
    $subject = '[Montecristo] ACTION REQUIRED — Online Order #' . $order->get_order_number() . ' not synced';

    $message = '<!DOCTYPE html><html><body style="font-family:sans-serif;color:#333;max-width:680px;">
<h2 style="color:#c00;border-bottom:2px solid #c00;padding-bottom:8px;">Online Order — Inventory Sync Failed</h2>
<p>WooCommerce Order <strong>#' . esc_html($order->get_order_number()) . '</strong> was placed but could not be automatically recorded in the inventory system.</p>
<table style="border-collapse:collapse;width:100%;margin-bottom:20px;">
<tr><td style="padding:4px 0;width:150px;color:#666;">Customer</td><td>' . esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) . '</td></tr>
<tr><td style="padding:4px 0;color:#666;">Email</td><td>' . esc_html($order->get_billing_email()) . '</td></tr>
<tr><td style="padding:4px 0;color:#666;">Total</td><td>$' . number_format((float) $order->get_total(), 2) . ' CAD</td></tr>
</table>
<p><strong>Error:</strong> ' . esc_html($error_message) . '</p>
<p style="background:#fff3cd;padding:12px;border-left:4px solid #ffc107;">Please create this sale manually in the MJI inventory admin and mark the correct SKU(s) as sold.</p>
<p style="margin-top:20px;color:#888;font-size:12px;">This is an automated message from Montecristo Jewellers inventory system.</p>
</body></html>';

    wp_mail($to, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
}
