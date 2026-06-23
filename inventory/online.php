<?php

add_filter('woocommerce_email_enabled_new_order', '__return_false');
add_action('woocommerce_refund_created', 'mji_sync_online_refund', 10, 2);
add_action('woocommerce_order_status_changed', 'mji_handle_wc_order_status_change', 5, 4);
add_filter('woocommerce_valid_order_statuses_for_cancel', 'mji_prevent_paid_order_cancel');
add_action('admin_notices', 'mji_cancel_blocked_notice');
add_action('mji_async_sale_email', 'mji_send_deferred_sale_email', 10, 2);

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

    if ($inserted) {
        return (int) $wpdb->insert_id;
    }

    // INSERT failed — if we have an email, a concurrent request may have just created this customer.
    // Re-select to recover from the race rather than aborting the order sync.
    if ($email) {
        $race_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}mji_customers WHERE email = %s",
            $email
        ));
        if ($race_id) {
            return (int) $race_id;
        }
    }

    return 0;
}

function mji_map_wc_payment_method($gateway_id, $order = null)
{
    $map = [
        'cod'            => 'cash',
        'cheque'         => 'cheque',
        'alipay'         => 'alipay',
        'wechat'         => 'alipay',
        'cup'            => 'cup',
        // PayPal — no dedicated enum bucket; visa is the closest fit
        'paypal'         => 'visa',
        'ppec_paypal'    => 'visa',
        'paypal_express' => 'visa',
    ];
    if (isset($map[$gateway_id])) {
        return $map[$gateway_id];
    }

    // For Stripe, check card funding first (debit/prepaid → 'debit'), then brand.
    if ($order && str_starts_with($gateway_id, 'stripe')) {
        $order_id = $order->get_id();
        $funding  = get_post_meta($order_id, '_stripe_card_funding', true);
        if (in_array($funding, ['debit', 'prepaid'], true)) {
            return 'debit';
        }
        $brand_map = [
            'visa'       => 'visa',
            'mastercard' => 'master_card',
            'amex'       => 'amex',
            'unionpay'   => 'cup',
            'discover'   => 'visa',  // no discover bucket in enum — closest fit
            'diners'     => 'visa',
            'jcb'        => 'cup',
        ];
        $brand = strtolower((string) get_post_meta($order_id, '_stripe_card_brand', true));
        if (isset($brand_map[$brand])) {
            return $brand_map[$brand];
        }
        // Stripe but brand unknown — log and default to visa
        custom_log("mji_map_wc_payment_method: unknown Stripe card brand '{$brand}' for order #{$order_id} — defaulting to visa.");
        return 'visa';
    }

    // Unknown gateway — log and default to visa
    custom_log("mji_map_wc_payment_method: unmapped gateway '{$gateway_id}' — defaulting to visa.");
    return 'visa';
}

add_action('woocommerce_payment_complete', 'adjust_stock_after_order');

function adjust_stock_after_order($order_id)
{
    $order = wc_get_order($order_id);
    if (!$order) {
        custom_log("adjust_stock_after_order: wc_get_order({$order_id}) returned false — skipping MJI sync.");
        return;
    }

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
            if (!($item instanceof WC_Order_Item_Product)) continue;
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
        // get_tax_total() covers product taxes; get_shipping_tax_total() covers shipping taxes (e.g. GST on shipping).
        // GST/HST → gst_total; PST/QST/RST → pst_total.
        // Handles inter-provincial orders: AB = GST only, ON = HST only, QC = GST+QST, MB = GST+RST.
        $gst_total = 0.0;
        $pst_total = 0.0;
        foreach ($order->get_taxes() as $tax_item) {
            $label      = strtolower($tax_item->get_name());
            $tax_amount = (float) $tax_item->get_tax_total() + (float) $tax_item->get_shipping_tax_total();
            if (str_contains($label, 'pst') || str_contains($label, 'qst') || str_contains($label, 'rst')) {
                $pst_total += $tax_amount;
            } else {
                $gst_total += $tax_amount;
            }
        }
        $gst_total = round($gst_total, 2);
        $pst_total = round($pst_total, 2);

        $shipping_total = round((float) $order->get_shipping_total(), 2);

        // Subtotal = items after discount + shipping (pre-tax). Shipping also stored as a mji_services row.
        // Total = subtotal + taxes, matching the WC order total.
        $item_subtotal = 0.0;
        foreach ($sold_units as $entry) {
            $item_subtotal += $entry['unit_price'];
        }
        $item_subtotal = round($item_subtotal + $shipping_total, 2);

        $notes = 'WooCommerce online order #' . $order->get_order_number();

        $inserted = $wpdb->insert($wpdb->prefix . 'mji_orders', [
            'customer_id'    => $customer_id,
            'salesperson_id' => $salesperson_id,
            'location_id'    => $location_id,
            'reference_num'  => $reference_num,
            'subtotal'       => $item_subtotal, // items + shipping (pre-tax)
            'gst_total'      => $gst_total,
            'pst_total'      => $pst_total,
            'total'          => round((float) $order->get_total(), 2),
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
                ['status' => 'sold', 'sold_date' => $now, 'location_id' => $location_id],
                ['id' => $unit->id]
            );
            if ($updated === false) {
                throw new RuntimeException("Failed to set unit {$unit->id} to sold: " . $wpdb->last_error);
            }
        }

        if ($shipping_total > 0) {
            $shipping_method = sanitize_text_field($order->get_shipping_method()) ?: 'Shipping';
            $inserted = $wpdb->insert($wpdb->prefix . 'mji_services', [
                'order_id'    => $mji_order_id,
                'location_id' => $location_id,
                'category'    => 'shipping',
                'description' => $shipping_method . ' — WC order #' . $order->get_order_number(),
                'cost_price'  => $shipping_total,
                'sold_price'  => $shipping_total,
            ]);
            if (!$inserted) {
                throw new RuntimeException('Failed to insert shipping service record: ' . $wpdb->last_error);
            }
        }

        if ((float) $order->get_total() > 0) {
            $payment_method = mji_map_wc_payment_method($order->get_payment_method(), $order);
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
        }

        $wpdb->query('COMMIT');

        wp_schedule_single_event(time(), 'mji_async_sale_email', [$order_id, $reference_num]);

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
        ? '<tr><td style="padding:4px 0;color:#666;">Shipping</td><td>$' . number_format($shipping_total, 2) . ' CAD</td></tr>'
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

function mji_notify_online_refund_error($order, $refund_id, $error_message)
{
    $to      = get_option('mji_notification_email') ?: get_option('admin_email');
    $subject = '[Montecristo] ACTION REQUIRED — Refund #' . $refund_id . ' for Order #' . $order->get_order_number() . ' not synced';

    $message = '<!DOCTYPE html><html><body style="font-family:sans-serif;color:#333;max-width:680px;">
                <h2 style="color:#c00;border-bottom:2px solid #c00;padding-bottom:8px;">Online Refund — Inventory Sync Failed</h2>
                <p>A WooCommerce refund was issued for Order <strong>#' . esc_html($order->get_order_number()) . '</strong> but could not be automatically recorded in the inventory system.</p>
                <table style="border-collapse:collapse;width:100%;margin-bottom:20px;">
                <tr><td style="padding:4px 0;width:150px;color:#666;">WC Refund ID</td><td>' . esc_html($refund_id) . '</td></tr>
                <tr><td style="padding:4px 0;color:#666;">Customer</td><td>' . esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) . '</td></tr>
                <tr><td style="padding:4px 0;color:#666;">Email</td><td>' . esc_html($order->get_billing_email()) . '</td></tr>
                </table>
                <p><strong>Error:</strong> ' . esc_html($error_message) . '</p>
                <p style="background:#fff3cd;padding:12px;border-left:4px solid #ffc107;">Please update the return records manually in the MJI inventory admin and restore the correct unit status if items were physically returned.</p>
                <p style="margin-top:20px;color:#888;font-size:12px;">This is an automated message from Montecristo Jewellers inventory system.</p>
                </body></html>';

    wp_mail($to, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
}

function mji_send_deferred_sale_email($order_id, $reference_num)
{
    global $wpdb;
    $order = wc_get_order($order_id);
    if (!$order) return;

    $mji_order = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}mji_orders WHERE reference_num = %s",
        $reference_num
    ));
    if (!$mji_order) return;

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT oi.sale_price, oi.discount_amount, u.sku, u.serial,
                COALESCE(p.post_title, CONCAT('SKU: ', u.sku)) AS product_name
         FROM {$wpdb->prefix}mji_order_items oi
         JOIN {$wpdb->prefix}mji_product_inventory_units u ON u.id = oi.product_inventory_unit_id
         LEFT JOIN {$wpdb->prefix}posts p ON p.ID = u.wc_product_id
         WHERE oi.order_id = %d",
        (int) $mji_order->id
    ));

    $sold_units = [];
    foreach ($rows as $row) {
        $sold_units[] = [
            'unit'          => (object) ['sku' => $row->sku, 'serial' => $row->serial],
            'product_name'  => $row->product_name,
            'unit_price'    => (float) $row->sale_price,
            'unit_discount' => (float) $row->discount_amount,
        ];
    }

    mji_notify_online_sale($order, $sold_units, $reference_num);
}

function mji_sync_online_refund($refund_id, $args)
{
    global $wpdb;

    $order_id = (int) ($args['order_id'] ?? 0);
    if (!$order_id) return;

    $order  = wc_get_order($order_id);
    $refund = wc_get_order($refund_id);
    if (!$order || !$refund) return;

    $reference_num = 'WEB-' . $order->get_order_number();
    $mji_order = $wpdb->get_row($wpdb->prepare(
        "SELECT id, customer_id, salesperson_id, location_id FROM {$wpdb->prefix}mji_orders WHERE reference_num = %s",
        $reference_num
    ));
    if (!$mji_order) {
        custom_log("WC refund #{$refund_id} for order #{$order_id}: no MJI order found for {$reference_num} — skipping.");
        return;
    }

    $mji_order_id  = (int) $mji_order->id;
    $return_ref    = 'WRET-' . $order->get_order_number() . '-' . $refund_id;

    // Idempotency guard — don't double-process if hook fires twice
    $already = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}mji_returns WHERE reference_num = %s",
        $return_ref
    ));
    if ($already) {
        return;
    }

    $refund_amount = round((float) $refund->get_amount(), 2);
    $reason        = sanitize_text_field($refund->get_reason()) ?: 'WooCommerce online refund';
    $now           = current_time('mysql');
    $today         = current_time('Y-m-d');

    // Split refund taxes into GST and PST (same logic as the sale; values are negative in refund object so use abs)
    $gst_total = 0.0;
    $pst_total = 0.0;
    foreach ($refund->get_taxes() as $tax_item) {
        $label      = strtolower($tax_item->get_name());
        $tax_amount = abs((float) $tax_item->get_tax_total() + (float) $tax_item->get_shipping_tax_total());
        if (str_contains($label, 'pst') || str_contains($label, 'qst') || str_contains($label, 'rst')) {
            $pst_total += $tax_amount;
        } else {
            $gst_total += $tax_amount;
        }
    }
    $gst_total = round($gst_total, 2);
    $pst_total = round($pst_total, 2);
    // Subtotal is computed below after $shipping_refund is known.

    // Build a map of wc_product/variant_id => qty being refunded from WC line items
    $product_qty_map = [];
    foreach ($refund->get_items() as $wc_item) {
        if (!($wc_item instanceof WC_Order_Item_Product)) continue;
        $qty = abs((int) $wc_item->get_quantity());
        if ($qty <= 0) continue;
        $var_id = (int) $wc_item->get_variation_id();
        $key    = $var_id > 0 ? "v_{$var_id}" : 'p_' . (int) $wc_item->get_product_id();
        $product_qty_map[$key] = ($product_qty_map[$key] ?? 0) + $qty;
    }

    // All MJI order items for this order, with WC product linkage
    $all_order_items = $wpdb->get_results($wpdb->prepare(
        "SELECT oi.id, oi.sale_price, oi.product_inventory_unit_id,
                u.wc_product_id, u.wc_product_variant_id
         FROM {$wpdb->prefix}mji_order_items oi
         JOIN {$wpdb->prefix}mji_product_inventory_units u ON u.id = oi.product_inventory_unit_id
         WHERE oi.order_id = %d",
        $mji_order_id
    ));

    // Units already covered by a previous return for this order — skip them
    $already_returned = array_column(
        $wpdb->get_results($wpdb->prepare(
            "SELECT ri.product_inventory_unit_id
             FROM {$wpdb->prefix}mji_return_items ri
             JOIN {$wpdb->prefix}mji_returns r ON r.id = ri.return_id
             WHERE r.order_id = %d",
            $mji_order_id
        )),
        'product_inventory_unit_id'
    );

    // Match WC refund line items to MJI order items by WC product/variant ID.
    // If the admin issued an amount-only refund (no line items specified), $mji_items_to_return
    // stays empty — inventory is left untouched and only the financial records are written.
    // Staff must manually update unit status in that case.
    $mji_items_to_return = [];
    if (!empty($product_qty_map)) {
        $remaining = $product_qty_map;
        foreach ($all_order_items as $oi) {
            if (in_array($oi->product_inventory_unit_id, $already_returned)) continue;
            $var_id = (int) $oi->wc_product_variant_id;
            $key    = $var_id > 0 ? "v_{$var_id}" : 'p_' . (int) $oi->wc_product_id;
            if (!empty($remaining[$key])) {
                $mji_items_to_return[] = $oi;
                $remaining[$key]--;
            }
        }
    } else {
        custom_log("WC refund #{$refund_id} for order #{$order_id}: no line items specified — financial records written but inventory not updated. Update unit status manually if items were physically returned.");
    }

    // Shipping service row for this order (needed for mji_return_services)
    $shipping_refund  = abs((float) $refund->get_shipping_total());
    $shipping_service = null;
    if ($shipping_refund > 0) {
        $shipping_service = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}mji_services WHERE order_id = %d AND category = 'shipping'",
            $mji_order_id
        ));
    }

    // Sum product line-item totals directly — avoids rounding errors from back-calculating via tax.
    $subtotal = 0.0;
    foreach ($refund->get_items() as $wc_item) {
        if (!($wc_item instanceof WC_Order_Item_Product)) continue;
        $subtotal += abs((float) $wc_item->get_total());
    }
    $subtotal = round($subtotal + $shipping_refund, 2);

    $wpdb->query('START TRANSACTION');
    try {
        $inserted = $wpdb->insert($wpdb->prefix . 'mji_returns', [
            'order_id'      => $mji_order_id,
            'reference_num' => $return_ref,
            'return_date'   => $today,
            'reason'        => $reason,
            'subtotal'      => $subtotal,
            'gst_total'     => $gst_total,
            'pst_total'     => $pst_total,
            'total'         => $refund_amount,
        ]);
        if (!$inserted) {
            throw new RuntimeException('Failed to insert mji_returns: ' . $wpdb->last_error);
        }
        $return_id = (int) $wpdb->insert_id;

        foreach ($mji_items_to_return as $oi) {
            $inserted = $wpdb->insert($wpdb->prefix . 'mji_inventory_status_history', [
                'inventory_unit_id' => $oi->product_inventory_unit_id,
                'from_status'       => 'sold',
                'to_status'         => 'in_stock',
                'reference_num'     => $return_ref,
                'created_at'        => $now,
                'notes'             => $reason,
            ]);
            if (!$inserted) {
                throw new RuntimeException("Failed to insert status history for unit {$oi->product_inventory_unit_id}: " . $wpdb->last_error);
            }

            // Use raw query — $wpdb->update() casts null to '' which breaks DATE columns
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}mji_product_inventory_units SET status = 'in_stock', sold_date = NULL WHERE id = %d",
                $oi->product_inventory_unit_id
            ));

            $inserted = $wpdb->insert($wpdb->prefix . 'mji_return_items', [
                'return_id'                 => $return_id,
                'order_item_id'             => $oi->id,
                'product_inventory_unit_id' => $oi->product_inventory_unit_id,
                'unit_price'                => $oi->sale_price,
            ]);
            if (!$inserted) {
                throw new RuntimeException("Failed to insert mji_return_items for unit {$oi->product_inventory_unit_id}: " . $wpdb->last_error);
            }
        }

        if ($shipping_service) {
            $inserted = $wpdb->insert($wpdb->prefix . 'mji_return_services', [
                'return_id'  => $return_id,
                'service_id' => (int) $shipping_service->id,
                'price'      => $shipping_refund,
            ]);
            if (!$inserted) {
                throw new RuntimeException('Failed to insert mji_return_services: ' . $wpdb->last_error);
            }
        }

        $inserted = $wpdb->insert($wpdb->prefix . 'mji_payments', [
            'order_id'         => $mji_order_id,
            'customer_id'      => (int) $mji_order->customer_id,
            'salesperson_id'   => (int) $mji_order->salesperson_id,
            'location_id'      => (int) $mji_order->location_id,
            'reference_num'    => $return_ref,
            'method'           => mji_map_wc_payment_method($order->get_payment_method(), $order),
            'amount'           => $refund_amount,
            'transaction_type' => 'refund',
            'payment_date'     => $now,
            'notes'            => $reason,
        ]);
        if (!$inserted) {
            throw new RuntimeException('Failed to insert refund payment: ' . $wpdb->last_error);
        }

        $wpdb->query('COMMIT');
        custom_log("WC refund #{$refund_id} synced to MJI as {$return_ref}: " . count($mji_items_to_return) . " item(s), \${$refund_amount} total.");

    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        custom_log("WC refund #{$refund_id} MJI sync failed: " . $e->getMessage());
        mji_notify_online_refund_error($order, $refund_id, $e->getMessage());
    }
}

function mji_prevent_paid_order_cancel($statuses)
{
    // Paid (processing) orders must be refunded, not cancelled — remove 'processing' from the allowed cancel list.
    return array_diff($statuses, ['processing']);
}

function mji_cancel_blocked_notice()
{
    $uid = get_current_user_id();
    if (!get_transient('mji_cancel_blocked_' . $uid)) return;
    delete_transient('mji_cancel_blocked_' . $uid);
    echo '<div class="notice notice-error is-dismissible"><p><strong>Order cannot be cancelled.</strong> This order has been paid. Please use the <strong>Refund</strong> button to issue a refund first.</p></div>';
}

function mji_handle_wc_order_status_change($order_id, $from, $to, $order)
{
    if ($to !== 'cancelled' && $to !== 'failed') {
        return;
    }

    // Defence-in-depth for programmatic update_status('cancelled') calls that bypass the filter.
    // Primary gate is mji_prevent_paid_order_cancel (woocommerce_valid_order_statuses_for_cancel).
    if ($to === 'cancelled' && $from === 'processing') {
        static $reverting = false;
        if ($reverting) return;
        $reverting = true;
        $order->update_status('processing', 'Cancellation blocked — this order has been paid. Please issue a refund using the Refund button first.');
        $reverting = false;
        if (is_admin() && ($uid = get_current_user_id())) {
            set_transient('mji_cancel_blocked_' . $uid, true, 60);
        }
        return;
    }

    // For pending/on-hold cancellations — payment never went through, clean up MJI if a record exists
    if (!in_array($from, ['pending', 'on-hold'])) {
        return;
    }

    global $wpdb;
    $reference_num = 'WEB-' . $order->get_order_number();

    $mji_order = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}mji_orders WHERE reference_num = %s",
        $reference_num
    ));
    if (!$mji_order) {
        return;
    }

    $mji_order_id = (int) $mji_order->id;
    $now          = current_time('mysql');

    // Guard: if a captured payment exists this was a real sale — do NOT auto-delete.
    // Catches processing → on-hold → cancelled/failed where $from passes the ['pending','on-hold']
    // check above but money was already taken.
    $has_purchase = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}mji_payments WHERE order_id = %d AND transaction_type = 'purchase'",
        $mji_order_id
    ));
    if ($has_purchase) {
        custom_log("WC order {$reference_num} moved to '{$to}' from '{$from}' but has a captured payment (mji_payments #{$has_purchase}) — NOT auto-reversing MJI records. Manual review required.");
        mji_notify_online_sale_error($order, "Order {$reference_num} transitioned to '{$to}' but a captured payment exists on record. MJI inventory records have NOT been automatically reversed — please review and update manually.");
        return;
    }

    $wpdb->query('START TRANSACTION');
    try {
        $order_items = $wpdb->get_results($wpdb->prepare(
            "SELECT product_inventory_unit_id FROM {$wpdb->prefix}mji_order_items WHERE order_id = %d",
            $mji_order_id
        ));

        foreach ($order_items as $oi) {
            $inserted = $wpdb->insert($wpdb->prefix . 'mji_inventory_status_history', [
                'inventory_unit_id' => $oi->product_inventory_unit_id,
                'from_status'       => 'sold',
                'to_status'         => 'in_stock',
                'reference_num'     => $reference_num,
                'created_at'        => $now,
                'notes'             => 'WC order cancelled (unpaid)',
            ]);
            if (!$inserted) {
                throw new RuntimeException("Failed to insert status history for unit {$oi->product_inventory_unit_id}: " . $wpdb->last_error);
            }

            // Use raw query — $wpdb->update() casts null to '' which breaks DATE columns
            $updated = $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}mji_product_inventory_units SET status = 'in_stock', sold_date = NULL WHERE id = %d",
                $oi->product_inventory_unit_id
            ));
            if ($updated === false) {
                throw new RuntimeException("Failed to restore unit {$oi->product_inventory_unit_id} to in_stock: " . $wpdb->last_error);
            }
        }

        $wpdb->delete($wpdb->prefix . 'mji_payments',    ['order_id' => $mji_order_id, 'transaction_type' => 'purchase'], ['%d', '%s']);
        $wpdb->delete($wpdb->prefix . 'mji_services',    ['order_id' => $mji_order_id], ['%d']);
        $wpdb->delete($wpdb->prefix . 'mji_order_items', ['order_id' => $mji_order_id], ['%d']);
        $wpdb->delete($wpdb->prefix . 'mji_orders',      ['id'       => $mji_order_id], ['%d']);

        $wpdb->query('COMMIT');

        custom_log("WC order {$reference_num} cancelled (unpaid, from '{$from}') — MJI order {$mji_order_id} deleted, " . count($order_items) . " unit(s) restored to in_stock.");

    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        custom_log("WC cancellation cleanup for {$reference_num} failed: " . $e->getMessage());
    }
}
