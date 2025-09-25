<?php

function inventory_page()
{ ?>
    <div class="wrap inventory-sales">
        <h1>Inventory Management</h1>

        <div id="customerDetails" class="customer-details hidden">
            <div id="customerInfo" class="customer-info">
                <span id="customer-name">Customer Name</span> <br />
                <span id="customer-address">Customer Address</span> <br />
                <h3 id="layawaySum"></h3>
            </div>
            <div>
                <button id="viewProducts">Search Products</button>
                <button id="viewCart">View Cart</button>
                <button id="viewLayaway">View Layaway</button>
            </div>
        </div>

        <div class="search-customer" id="search-customer">
            <h2>Select Customer</h2>
            <form name="search-customer" method="get">
                <input id="search" size="30" type="text" name="search" placeholder="Search by Name or Phone">
                <button type="submit" id="search-btn">Search</button>
            </form>
            <div id="search-customer-results">

            </div>
        </div>

        <div class="layaway-details hidden" id="layawayDetails">

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference Number</th>
                        <th>Transaction Type</th>
                        <th>Method</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody id="layawayItems">
                    <!-- Layaway items will be populated here -->
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4">Total:</td>
                        <td id="layaway-total">0.00 CAD</td>
                    </tr>
                </tfoot>
            </table>

            <button id="addLayaway">Add Layaway</button>
        </div>

        <div class="add-layaway hidden" id="addLayawayForm">

            <h2>Add Layaway</h2>
            <form name="add-layaway" method="post">

                <div class="payment-methods">

                    <div>
                        <label for="cash">Cash:</label>
                        <input type="number" step="0.01" id="cash" name="cash">
                    </div>

                    <div>
                        <label for="cheque">Cheque:</label>
                        <input type="number" step="0.01" id="cheque" name="cheque">
                    </div>

                    <div>
                        <label for="debit">Debit:</label>
                        <input type="number" step="0.01" id="debit" name="debit">
                    </div>

                    <div>
                        <label for="visa">Visa:</label>
                        <input type="number" step="0.01" id="visa" name="visa">
                    </div>

                    <div>
                        <label for="master_card">Mastercard:</label>
                        <input type="number" step="0.01" id="master_card" name="master_card">
                    </div>

                    <div>
                        <label for="amex">Amex:</label>
                        <input type="number" step="0.01" id="amex" name="amex">
                    </div>

                    <div>
                        <label for="discover">Discover:</label>
                        <input type="number" step="0.01" id="discover" name="discover">
                    </div>

                    <div>
                        <label for="travel_cheque">Travel Cheque:</label>
                        <input type="number" step="0.01" id="travel_cheque" name="travel_cheque">
                    </div>

                    <div>
                        <label for="cup">Cup:</label>
                        <input type="number" step="0.01" id="cup" name="cup">
                    </div>

                    <div>
                        <label for="alipay">Alipay:</label>
                        <input type="number" step="0.01" id="alipay" name="alipay">
                    </div>

                </div>

                <div>
                    <div>
                        <label for="layaway-reference">Reference Number:</label>
                        <input type="text" id="layaway-reference" name="layaway_reference" required>
                    </div>
                    <div>
                        <label for="salesperson">Salesperson</label>
                        <?= mji_salesperson_dropdown() ?>
                    </div>
                    <div>
                        <label for="layaway-notes">Notes:</label>
                        <textarea id="layaway-notes" name="layaway_notes" rows="4" cols="50"></textarea>
                    </div>
                    <div>
                        <label for="layaway-date">Date:</label>
                        <input type="date" id="layaway-date" name="layaway_date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <button type="submit" id="submit-layaway">Submit Payment</button>
            </form>

        </div>

        <div class="layaway-receipt hidden" id="layawayReceipt">

            <header>
                <h2>Montecristo Jewellers</h2>
                <h3>Layaway Receipt</h3>
            </header>

            <main>
                <div id="receiptCustomerInfo">
                    <p>Customer: <span id="receiptCustomerName"></span></p>
                    <p>Address: <span id="receiptCustomerAddress"></span></p>
                </div>

                <div>
                    <p>Your Layaway as of today:</p>
                    <p>Layaway Total: <span id="layawayTotal"></span></p>
                    <p>Amount of Last payment made:<span id="paymentAmount"></span></p>
                    <p>Last payment mode:<span id="paymentMode"></span></p>
                    <p>Payment Date: <span id="receiptDate"></span></p>
                    <p>Served by: <span id="salesmanName"></span></p>
                </div>
            </main>

            <footer>
                <p>Thank you for shopping at Montecristo Jewellers!!</p>
            </footer>

            <button id="layawayPrintReceipt">Print Receipt</button>

        </div>

        <div class="search-products hidden" id="search-products">

            <h2>Search Products</h2>
            <form name="search-products" method="post">
                <input id="search-products" size="30" type="text" name="search-products" placeholder="Search by SKU">
                <button type="submit" id="search-product-btn">Search</button>
            </form>

            <div id="search-product-results">

            </div>
        </div>

        <div class="cart hidden" id="cart">

            <h3>Items in the Cart:</h3>
            <div class="cart-items">
                <p>No items in the cart!!. Please add by searching the prooducts.</p>
            </div>

            <h3>Finalize Sale</h3>

            <form name="finalize-sale" method="post">

                <div class="payment-methods">
                    <div>
                        <label for="cash">Cash:</label>
                        <input type="number" min="0" step="0.01" id="cash" name="cash">
                    </div>

                    <div>
                        <label for="cheque">Cheque:</label>
                        <input type="number" min="0" step="0.01" id="cheque" name="cheque">
                    </div>

                    <div>
                        <label for="debit">Debit:</label>
                        <input type="number" min="0" step="0.01" id="debit" name="debit">
                    </div>

                    <div>
                        <label for="visa">Visa:</label>
                        <input type="number" min="0" step="0.01" id="visa" name="visa">
                    </div>

                    <div>
                        <label for="master_card">Mastercard:</label>
                        <input type="number" min="0" step="0.01" id="master_card" name="master_card">
                    </div>

                    <div>
                        <label for="amex">Amex:</label>
                        <input type="number" min="0" step="0.01" id="amex" name="amex">
                    </div>

                    <div>
                        <label for="discover">Discover:</label>
                        <input type="number" min="0" step="0.01" id="discover" name="discover">
                    </div>

                    <div>
                        <label for="travel_cheque">Travel Cheque:</label>
                        <input type="number" min="0" step="0.01" id="travel_cheque" name="travel_cheque">
                    </div>

                    <div>
                        <label for="cup">Cup:</label>
                        <input type="number" min="0" step="0.01" id="cup" name="cup">
                    </div>

                    <div>
                        <label for="alipay">Alipay:</label>
                        <input type="number" min="0" step="0.01" id="alipay" name="alipay">
                    </div>

                    <div>
                        <label for="layaway">Layaway:</label>
                        <input type="number" min="0" step="0.01" id="layaway" name="layaway">
                    </div>

                </div>

                <div>
                    <div>
                        <label for="reference">Reference Number:</label>
                        <input type="text" id="reference" name="reference" required>
                    </div>
                    <div>
                        <label for="salesperson">Salesperson</label>
                        <?= mji_salesperson_dropdown() ?>
                    </div>
                    <div>
                        <label for="date">Date:</label>
                        <input type="date" id="sales-date" name="date" value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div>
                        <label for="subtotal">Subtotal:</label>
                        <input type="number" readonly name="subtotal" id="subtotal"></input>
                    </div>

                    <div>
                        <label for="gst">GST:</label>
                        <input type="number" readonly name="gst" id="gst"></input>
                    </div>
                    <div>
                        <label for="pst">PST:</label>
                        <input type="number" readonly name="pst" id="pst"></input>
                    </div>

                    <div>
                        <label for="exclude-gst">Exclude GST:</label>
                        <input type="checkbox" name="exclude_gst" id="exclude-gst"></input>
                    </div>
                    <div>
                        <label for="exclude-pst">Exclude PST:</label>
                        <input type="checkbox" name="exclude_pst" id="exclude-pst"></input>
                    </div>
                    <div>
                        <label for="total">Total:</label>
                        <input type="number" readonly name="total" id="total"></input>
                    </div>
                </div>

                <button type="submit" id="submit-sale">Finalize Sale</button>
            </form>
        </div>

        <!-- Edit Item Modal -->
        <div id="edit-item-modal" class="modal hidden">
            <div class="modal-content">
                <h3>Edit Item</h3>
                <p><strong id="edit-item-title"></strong></p>
                <p>SKU: <span id="edit-item-sku"></span></p>
                <p>Price: <span id="edit-item-price"></span></p>
                <label>
                    Discount Amt($):
                    <input type="number" id="edit-discount-amt" min="0" step="0.01" />
                </label>
                <label>
                    Discount Pct(%):
                    <input type="number" id="edit-discount-pct" min="0" step="0.01" />
                </label>
                <label>
                    Price After Discount:
                    <input type="number" id="edit-price-after-discount" min="0" step="0.01" />
                </label>
                <div class="modal-actions">
                    <button id="save-edit">Save</button>
                    <button id="cancel-edit">Cancel</button>
                </div>
            </div>
        </div>

        <div class="sales-reuslt hidden" id="saleResult">
            <h2>Sale Receipt</h2>
            <div id="receiptContent" class="receipt-content">
                <!-- Receipt content will be populated here -->
            </div>
            <button id="salesPrintReceipt">Print Receipt</button>
            <a href="admin.php?page=inventory-management" class="">Enter new sales</a>
        </div>
    <?php
}

function search_customer()
{
    if (!isset($_GET['search_value'])) {
        return wp_send_json_error('Search value is required');
    }
    $search = sanitize_text_field($_GET['search_value']);

    $result = customer_table("inventory", $search, 50);
    wp_send_json_success($result);
    wp_die(); // this is required to terminate immediately and return a proper response
}

add_action('wp_ajax_search_customer', 'search_customer');

function get_layaway_sum($id = null)
{
    $customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) :  $id;

    if (!$customer_id) {
        return wp_send_json_error('Customer ID is required');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mji_payments';

    $query = $wpdb->prepare("
    SELECT
        (
            SUM(CASE
                WHEN transaction_type = 'layaway_deposit' THEN amount
                ELSE 0
            END)
            -
            SUM(CASE
                WHEN transaction_type = 'layaway_redemption' THEN amount
                ELSE 0
            END)
        ) AS net_layaway_balance
    FROM {$table_name}
    WHERE transaction_type IN ('layaway_deposit', 'layaway_redemption')
    AND customer_id = %d
    ", $customer_id);

    $result = $wpdb->get_row($query);
    $balance = !is_null($result->net_layaway_balance) ? (float) $result->net_layaway_balance : 0.0;
    if ($id) return $balance;
    return wp_send_json_success($balance);

    wp_die();
}

add_action('wp_ajax_getLayawaySum', 'get_layaway_sum');

function get_layaway()
{

    if (!isset($_GET['customer_id'])) {
        return wp_send_json_error('Customer ID is required');
    }

    $customer_id = intval($_GET['customer_id']);

    global $wpdb;

    $table_name = $wpdb->prefix . 'mji_payments';

    $query = $wpdb->prepare("
        SELECT *
        FROM {$table_name}
        WHERE (transaction_type = 'layaway_deposit' OR transaction_type = 'layaway_redemption')
        AND customer_id = %d
    ", $customer_id);

    $layaway_items = $wpdb->get_results($query);

    if (empty($layaway_items)) {
        return wp_send_json_error('No layaway items found for this customer.');
    }

    return wp_send_json_success($layaway_items);
}

add_action('wp_ajax_getLayaway', 'get_layaway');

function add_layaway()
{
    global $wpdb;
    $payment_methods = ['cash', 'cheque', 'debit', 'visa', 'master_card', 'amex', 'discover', 'travel_cheque', 'cup', 'alipay'];
    $payments = [];
    $table_name = $wpdb->prefix . 'mji_payments';

    foreach ($payment_methods as $method) {
        $amount = isset($_POST[$method]) ? floatval($_POST[$method]) : 0;
        if ($amount > 0) {
            $payments[] = [
                'method' => $method,
                'amount' => $amount
            ];
        }
    }

    if (empty($payments)) {
        wp_send_json_error(['message' => 'No valid payments entered.']);
        wp_die();
    }

    $reference_num = sanitize_text_field($_POST['layaway_reference']);
    $salesperson_id = sanitize_text_field($_POST['salesperson']);
    $payment_date = sanitize_text_field($_POST['layaway_date']);
    $notes = sanitize_textarea_field($_POST['layaway_notes']);
    $customer_id = intval($_POST['customer_id']);

    if (empty($reference_num) || empty($salesperson_id) || empty($payment_date) || empty($customer_id)) {
        wp_send_json_error(['message' => 'Reference number, salesperson, payment date, and customer ID are required.']);
        wp_die();
    }

    // Start transaction
    $wpdb->query('START TRANSACTION');

    try {
        foreach ($payments as $payment) {

            $layaway_data = [
                'reference_num' => $reference_num,
                'salesperson_id' => $salesperson_id,
                'method' => $payment['method'],
                'amount' => $payment['amount'],
                'transaction_type' => 'layaway_deposit',
                'payment_date' => $payment_date,
                'notes' => $notes,
                'customer_id' => $customer_id,
            ];

            $format = array('%s', '%d', '%s', '%f', '%s', '%s', '%s', '%d');

            $success = $wpdb->insert($table_name, $layaway_data, $format);
            if (!$success) {
                throw new Exception("Failed to insert payment: " . $wpdb->last_error);
            }
        }

        $salesperson = $wpdb->get_row($wpdb->prepare("SELECT first_name, last_name FROM {$wpdb->prefix}mji_salespeople WHERE id = %d", $salesperson_id));

        $layaway_sum = get_layaway_sum($customer_id);
        $response = [
            'salesperson' => $salesperson->first_name . ' ' . $salesperson->last_name,
            'reference_num' => $reference_num,
            'payment_date' => $payment_date,
            'payments' => $payments,
            'layaway_sum' => $layaway_sum
        ];

        $wpdb->query('COMMIT');
        wp_send_json_success($response);
    } catch (Exception $e) {
        // Rollback on error
        $wpdb->query('ROLLBACK');
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

add_action('wp_ajax_addLayaway', 'add_layaway');

function searchProducts()
{
    if (!isset($_GET['search_product']) || empty(trim($_GET['search_product']))) {
        wp_send_json_error(array("message" => "search field not set"));
    }

    $search_query = sanitize_text_field($_GET['search_product']);

    global $wpdb;
    $table_name = $wpdb->prefix . 'mji_product_inventory_units';

    $query = $wpdb->prepare("
            SELECT id, wc_product_id, wc_product_variant_id, sku, retail_price
            FROM {$table_name}
            WHERE sku LIKE %s
            AND status = 'in_stock'
            ", $search_query);
    $result = $wpdb->get_row($query);

    if (!empty($result)) {
        $unit_id = $result->id;
        $product_id = $result->wc_product_id;
        $product_variant_id = $result->wc_product_variant_id;
        $sku = $result->sku;
        $variation_detail = "";

        $product = wc_get_product($product_id);
        $price = $result->retail_price;

        if (!$product) {
            wp_send_json_error(array("message" => "Product not found"));
            wp_die();
        }

        if ($product->is_type('variable')) {
            $variation = wc_get_product($product_variant_id);

            // Get the variation attributes
            $variationAttributes = $variation->get_attributes();
            $variation_detail = implode(', ', $variationAttributes);
        }

        $response_data = array(
            'unit_id' => $unit_id,
            'product_id' => $product_id,
            'product_variant_id' => $product_variant_id,
            'title' => get_the_title($product_id),
            'image_url' => esc_url(wp_get_attachment_image_url(get_post_thumbnail_id($product_id), 'thumbnail')),
            'sku' => $sku,
            'variation_detail' => $variation_detail,
            'price' => $price
        );

        wp_send_json_success($response_data);
    } else {
        wp_send_json_error(array("message" => "No products found"));
    }
}

add_action('wp_ajax_searchProducts', 'searchProducts');

add_action('wp_ajax_finalizeSale', 'finalizeSale');

function validate_sale_input($post_data)
{
    $required_fields = ['customer_id', 'reference', 'salesperson', 'subtotal', 'total', 'date'];
    $missing_fields = [];

    foreach ($required_fields as $field) {
        if (empty($post_data[$field])) $missing_fields[] = $field;
    }

    if (!empty($missing_fields)) {
        wp_send_json_error(['message' => 'Missing required fields: ' . implode(', ', $missing_fields)]);
    }

    if (!DateTime::createFromFormat('Y-m-d', $post_data['date'])) {
        wp_send_json_error(['message' => 'Invalid date format. Expected YYYY-MM-DD.']);
    }

    $items_data = json_decode(stripslashes($post_data['items']));
    if (json_last_error() !== JSON_ERROR_NONE || empty($items_data)) {
        wp_send_json_error(['message' => 'Invalid or empty items JSON']);
    }

    return $items_data;
}

function calculate_sale_totals($items_data, $exclude_gst = false, $exclude_pst = false)
{
    define('GST_RATE', 0.05);
    define('PST_RATE', 0.08);
    define('FLOAT_COMPARE_EPSILON', 0.01);

    $subtotal = 0;
    foreach ($items_data as $item) {
        $subtotal += floatval($item->price_after_discount);
    }
    $subtotal = round($subtotal, 2);
    $gst = $exclude_gst ? 0 : round($subtotal * GST_RATE, 2);
    $pst = $exclude_pst ? 0 : round($subtotal * PST_RATE, 2);
    $total = round($subtotal + $gst + $pst, 2);

    return compact('subtotal', 'gst', 'pst', 'total');
}

function get_payments($post_data, $expected_total)
{
    $payment_methods = ['cash', 'cheque', 'debit', 'visa', 'master_card', 'amex', 'discover', 'travel_cheque', 'cup', 'alipay', 'layaway'];
    $payments = [];
    $payment_total = 0;

    foreach ($payment_methods as $method) {
        $amount = floatval($post_data[$method] ?? 0);
        if ($amount > 0) {
            $payments[] = ['method' => $method, 'amount' => $amount];
            $payment_total += $amount;
        }
    }

    if (empty($payments)) wp_send_json_error(['message' => 'No valid payments entered']);
    if (abs($payment_total - $expected_total) > FLOAT_COMPARE_EPSILON) {
        wp_send_json_error(['message' => 'Payment total mismatch']);
    }

    return $payments;
}

function insert_order_and_items($order_data, $items_data, $payments, $date, $customer_id, $salesperson_id)
{
    global $wpdb;
    $wpdb->query('START TRANSACTION');
    try {
        // Insert order
        $success = $wpdb->insert($wpdb->prefix . 'mji_orders', $order_data);
        if (!$success) {
            if (strpos($wpdb->last_error, 'Duplicate entry') !== false) {
                throw new RuntimeException("Order reference number already exists: " . $order_data['reference_num']);
            } else {
                throw new RuntimeException("Database error: " . $wpdb->last_error);
            }
        }

        $order_id = $wpdb->insert_id;

        // Insert payments
        foreach ($payments as $payment) {
            $success = $wpdb->insert($wpdb->prefix . 'mji_payments', [
                'order_id' => $order_id,
                'amount' => $payment['amount'],
                'method' => $payment['method'],
                'payment_date' => $date,
                'customer_id' => $customer_id,
                'salesperson_id' => $salesperson_id,
                'transaction_type' => $payment['method'] === 'layaway' ? 'layaway_redemption' : 'purchase',
                'reference_num' => $order_data['reference_num'],
            ]);
            if (!$success) {
                throw new RuntimeException("Failed to insert payment: " . $wpdb->last_error);
            }
        }

        // Insert order items and update inventory
        foreach ($items_data as $item) {

            // Check if this inventory unit is already sold
            $status = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT status FROM {$wpdb->prefix}mji_product_inventory_units WHERE id = %d",
                    $item->unit_id
                )
            );
            if ($status == 'in_stock') {

                $success = $wpdb->insert($wpdb->prefix . 'mji_order_items', [
                    'order_id' => $order_id,
                    'product_inventory_unit_id' => $item->unit_id,
                    'sale_price' => $item->price_after_discount,
                    'discount_amount' => $item->discount_amount,
                    'created_at' => $date
                ]);

                if (!$success) {
                    throw new RuntimeException("Failed to insert order item for {$item->title}: " . $wpdb->last_error);
                }

                $success = $wpdb->update(
                    $wpdb->prefix . 'mji_product_inventory_units',
                    ['status' => 'sold', 'sold_date' => $date],
                    ['id' => $item->unit_id]
                );

                if ($success === false) { // note: update returns 0 if nothing changed, false on error
                    throw new RuntimeException("Failed to update inventory for {$item->title}: " . $wpdb->last_error);
                }
            } else {
                throw new Exception("Item {$item->title} is already sold or is reserved.");
            }
        }

        $wpdb->query('COMMIT');
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

function finalizeSale()
{
    $items_data = validate_sale_input($_POST);
    $totals = calculate_sale_totals($items_data, !empty($_POST['exclude_gst']), !empty($_POST['exclude_pst']));
    $payments = get_payments($_POST, $totals['total']);

    // clean and prepare data
    $customer_id = intval($_POST['customer_id']);
    $salesperson_id = intval($_POST['salesperson']);
    $reference_num = sanitize_text_field($_POST['reference']);
    $created_at = sanitize_text_field($_POST['date']);

    $order_data = [
        'customer_id' => $customer_id,
        'salesperson_id' => $salesperson_id,
        'reference_num' => $reference_num,
        'subtotal' => $totals['subtotal'],
        'gst_total' => $totals['gst'],
        'pst_total' => $totals['pst'],
        'total' => $totals['total'],
        'created_at' => $created_at,
    ];

    // insert_order_and_items($order_data, $items_data, $payments, $created_at, $customer_id, $salesperson_id);

    foreach ($items_data as $item) {

        $wc_id = $item->product_id;
        $product = wc_get_product($wc_id);

        $attributes = $product->get_attributes();
        $regular_attributes = array();

        foreach ($attributes as $attribute) {
            if (!$attribute->get_variation()) {
                $regular_attributes[] = [
                    $attribute['name'] => implode(", ", $attribute['options'])
                ];
            }
        }

        if ($item->product_variant_id) {
            $variation = wc_get_product($item->product_variant_id);
            $variation_attributes = $variation->get_attributes();

            foreach ($variation_attributes as $attr_name => $attr_value) {
                $regular_attributes[] = [
                    $attr_name => $attr_value
                ];
            }
        }
        $item->attributes = $regular_attributes;
    }

    $salespeople = mji_get_salespeople();
    $salesperson = $salespeople[$salesperson_id];
    $salesperson_name = $salesperson->first_name . ' ' . $salesperson->last_name;

    wp_send_json_success([
        'items' => $items_data,
        'totals' => $totals,
        'payments' => $payments,
        'reference_num' => $reference_num,
        'salesperson_name' => $salesperson_name,
        'date' => $created_at
    ]);
}
