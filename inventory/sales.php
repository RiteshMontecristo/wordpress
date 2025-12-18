<?php

function inventory_page()
{ ?>
    <div class="wrap inventory-sales">
        <h1>Inventory Management</h1>


        <div id="top-store" class="store-location">
            <h2>Store: <span id="store-name"></span></h2>
            <button id="change-store-btn">Change</button>
        </div>

        <div id="store-modal" class="modal" style="display: none;">
            <div class="modal-content">

                <h2>Select Store:</h2>
                <?php
                $locations = mji_get_locations();

                foreach ($locations as  $location) {
                    echo "<button class='store-btn' data-id='" . $location->id . "'>" . $location->name . "</button>";
                }
                ?>
            </div>

        </div>

        <div id="customerDetails" class="customer-details hidden">
            <div id="customerInfo" class="customer-info">
                <span id="customer-name">Customer Name</span> <br />
                <span id="customer-address">Customer Address</span> <br />
                <h3 id="layawaySum"></h3>
            </div>
            <div>
                <button type="button" id="viewProducts">Search Products</button>
                <button type="button" id="viewCart">View Cart</button>
                <button type="button" id="viewLayaway">View Layaway</button>
                <button type="button" id="addService">Add Service</button>
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

            <h2>Add Layaway/Credit</h2>
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
                        <label for="debit">Debit/Interac:</label>
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
                        <label for="bank_draft">Bank Draft:</label>
                        <input type="number" step="0.01" id="bank_draft" name="bank_draft">
                    </div>

                    <div>
                        <label for="cup">Cup:</label>
                        <input type="number" step="0.01" id="cup" name="cup">
                    </div>

                    <div>
                        <label for="alipay">Alipay:</label>
                        <input type="number" step="0.01" id="alipay" name="alipay">
                    </div>

                    <div>
                        <label for="wire">Wire:</label>
                        <input type="number" step="0.01" id="wire" name="wire">
                    </div>

                    <div>
                        <label for="trade_in">Trade-In:</label>
                        <input type="number" step="0.01" id="trade_in" name="trade_in">
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
                    <div>
                        <label for="transaction_type">Deposit:</label>
                        <select name="transaction_type" id="transaction_type">
                            <option value="layaway_deposit">Layaway</option>
                            <option value="credit_deposit">Credit</option>
                        </select>
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
                    <p>Layaway and Credit Total: <span id="layawayTotal"></span></p>
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

        <div id="service-modal" class="modal hidden">
            <form name="add-service" class="modal-content">
                <h3>Services & Repairs</h3>
                <div>
                    <label for="category">
                        Category:
                    </label>
                    <select name="category" id="category">
                        <option value="watch_service">Watch Service</option>
                        <option value="jewellery_service">Jewellery Service</option>
                        <option value="shipping">Shipping</option>
                    </select>
                </div>

                <div>
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="2"></textarea>
                </div>

                <div>
                    <label for="costPrice">Cost Price:</label>
                    <input type="number" id="costPrice" min="0" step="0.01" required />
                </div>

                <div>
                    <label for="retailPrice">Retail Price:</label>
                    <input type="number" id="retailPrice" min="0" step="0.01" required />
                </div>

                <div>
                    <label for="reference">Reference:</label>
                    <input type="text" id="reference" min="0" step="0.01" />
                </div>

                <div class="modal-actions">
                    <button type="submit" id="add">Add</button>
                    <button type="button" id="cancel">Cancel</button>
                </div>
            </form>
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
                        <label for="debit">Debit/Interac:</label>
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
                        <label for="bank_draft">Bank Draft:</label>
                        <input type="number" min="0" step="0.01" id="bank_draft" name="bank_draft">
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
                        <label for="wire">Wire:</label>
                        <input type="number" min="0" step="0.01" id="wire" name="wire">
                    </div>

                    <div>
                        <label for="layaway">Layaway:</label>
                        <input type="number" min="0" step="0.01" id="layaway" name="layaway">
                    </div>

                    <div>
                        <label for="credit">Credit:</label>
                        <input type="number" min="0" step="0.01" id="credit" name="credit">
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
                        <label for="sales-date">Date:</label>
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
    if (!isset($_GET['search_value']) && !isset($_GET['location_id'])) {
        return wp_send_json_error('Search and Location value is required');
    }
    $search = sanitize_text_field($_GET['search_value']);
    $location_id = $_GET['search_value'];
    $result = customer_table("inventory", $search, 50, 1, $location_id);
    wp_send_json_success($result);
    wp_die(); // this is required to terminate immediately and return a proper response
}

add_action('wp_ajax_search_customer', 'search_customer');

function get_layaway_sum($customer_id = null, $location_id = null)
{
    $customer = isset($_GET['customer_id']) ? intval($_GET['customer_id']) :  $customer_id;
    $location = isset($_GET['location_id']) ? intval($_GET['location_id']) :  $location_id;

    if (!$customer) {
        return wp_send_json_error('Customer ID is required');
    }

    if (!$location) {
        return wp_send_json_error('Location ID is required');
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
    AND location_id = %d
    ", $customer, $location);

    $result = $wpdb->get_row($query);
    $layaway_balance = !is_null($result->net_layaway_balance) ? (float) $result->net_layaway_balance : 0.0;

    $query = $wpdb->prepare("
    SELECT
        (
            SUM(CASE
                WHEN transaction_type = 'credit_deposit' THEN amount
                ELSE 0
            END)
            -
            SUM(CASE
                WHEN transaction_type = 'credit_redemption' THEN amount
                ELSE 0
            END)
        ) AS net_layaway_balance
    FROM {$table_name}
    WHERE transaction_type IN ('credit_deposit', 'credit_redemption')
    AND customer_id = %d
    AND location_id = %d
    ", $customer, $location);

    $result = $wpdb->get_row($query);
    $credit_balance = !is_null($result->net_layaway_balance) ? (float) $result->net_layaway_balance : 0.0;

    $balance = [
        "layaway" => $layaway_balance,
        "credit" => $credit_balance
    ];

    if ($customer_id && $location_id) return $balance;
    return wp_send_json_success($balance);

    wp_die();
}

add_action('wp_ajax_getLayawaySum', 'get_layaway_sum');

function get_layaway_list()
{

    if (!isset($_GET['customer_id'])) {
        return wp_send_json_error('Customer ID is required');
    }

    if (!isset($_GET['location_id'])) {
        return wp_send_json_error('Location ID is required');
    }

    $customer_id = intval($_GET['customer_id']);
    $location_id = intval($_GET['location_id']);

    global $wpdb;

    $table_name = $wpdb->prefix . 'mji_payments';

    $query = $wpdb->prepare("
        SELECT *
        FROM {$table_name}
        WHERE (transaction_type = 'layaway_deposit' OR transaction_type = 'layaway_redemption' OR transaction_type = 'credit_deposit' OR transaction_type = 'credit_redemption')
        AND customer_id = %d
        AND location_id = %d
    ", $customer_id, $location_id);

    $layaway_items = $wpdb->get_results($query);

    if (empty($layaway_items)) {
        return wp_send_json_error('No layaway items found for this customer.');
    }

    return wp_send_json_success($layaway_items);
}

add_action('wp_ajax_getLayaway', 'get_layaway_list');

function add_layaway()
{
    global $wpdb;
    $payment_methods = ['cash', 'cheque', 'debit', 'visa', 'master_card', 'amex', 'discover', 'bank_draft', 'cup', 'alipay', 'wire', 'trade_in'];
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
    $salesperson_id = sanitize_text_field($_POST['salesperson']);
    $notes = sanitize_textarea_field($_POST['layaway_notes']);
    $transaction_type = sanitize_text_field($_POST['transaction_type']);
    $customer_id = intval($_POST['customer_id']);
    $location_id = intval($_POST['location_id']);

    if (empty($reference_num) || empty($salesperson_id) || empty($payment_date) || empty($customer_id) || empty($location_id) || empty($transaction_type)) {
        wp_send_json_error(['message' => 'Reference number, salesperson, payment date, Location ID, customer ID and deposit type are required.']);
        wp_die();
    }

    $wpdb->query('START TRANSACTION');

    try {

        foreach ($payments as $payment) {

            $layaway_data = [
                'reference_num' => $reference_num,
                'salesperson_id' => $salesperson_id,
                'method' => $payment['method'],
                'amount' => $payment['amount'],
                'transaction_type' => $transaction_type,
                'payment_date' => $payment_date,
                'notes' => $notes,
                'customer_id' => $customer_id,
                'location_id' => $location_id
            ];

            $format = array('%s', '%d', '%s', '%f', '%s', '%s', '%s', '%d', '%d');

            $success = $wpdb->insert($table_name, $layaway_data, $format);
            if (!$success) {
                throw new Exception("Failed to insert payment: " . $wpdb->last_error);
            }
        }

        $salespeople_arr = mji_get_salespeople();
        $salesperson = array_find($salespeople_arr, function ($value) use ($salesperson_id) {
            return $value->id == $salesperson_id;
        });
        $layaway_sum = get_layaway_sum($customer_id, $location_id);
        $response = [
            'salesperson' => $salesperson->first_name . ' ' . $salesperson->last_name,
            'reference_num' => $reference_num,
            'payment_date' => $payment_date,
            'payments' => $payments,
            'layaway_sum' => $layaway_sum,
            'transaction_type' => $transaction_type
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
    $sku_history_table =  $wpdb->prefix . 'mji_product_sku_history';

    $query = $wpdb->prepare("
            SELECT u.id, u.wc_product_id, u.wc_product_variant_id, u.sku, u.retail_price, u.location_id, u.status
            FROM {$table_name} AS u
            LEFT JOIN {$sku_history_table} AS h
            ON h.unit_id = u.id
            WHERE (u.sku LIKE %s OR h.old_sku LIKE %s)
            ", $search_query, $search_query);
    $result = $wpdb->get_row($query);

    if (!empty($result)) {
        $unit_id = $result->id;
        $location_id = $result->location_id;
        $product_id = $result->wc_product_id;
        $product_variant_id = $result->wc_product_variant_id;
        $sku = $result->sku;
        $status = $result->status;
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
            'location_id' => $location_id,
            'title' => get_the_title($product_id),
            'image_url' => esc_url(wp_get_attachment_image_url(get_post_thumbnail_id($product_id), 'thumbnail')),
            'sku' => $sku,
            'status' => $status,
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
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(['message' => 'Invalid JSON']);
    }
    $services_data = json_decode(stripslashes($post_data['services']));

    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(['message' => 'Invalid JSON']);
    }

    if (empty($items_data) && empty($services_data)) {
        wp_send_json_error(['message' => 'Empty items and services']);
    }
    return [$items_data, $services_data];
}

function calculate_sale_totals($items_data, $services_data, $exclude_gst = false, $exclude_pst = false)
{
    define('GST_RATE', 0.05);
    define('PST_RATE', 0.07);
    define('FLOAT_COMPARE_EPSILON', 0.01);

    $subtotal = 0;
    foreach ($items_data as $item) {
        $subtotal += floatval($item->price_after_discount);
    }
    foreach ($services_data as $service) {
        $subtotal += floatval($service->retailPrice);
    }
    $subtotal = round($subtotal, 2);
    $gst = $exclude_gst ? 0 : round($subtotal * GST_RATE, 2);
    $pst = $exclude_pst ? 0 : round($subtotal * PST_RATE, 2);
    $total = round($subtotal + $gst + $pst, 2);

    return compact('subtotal', 'gst', 'pst', 'total');
}

function get_payments($post_data, $expected_total, $customer_id, $location_id)
{
    $payment_methods = ['cash', 'cheque', 'debit', 'visa', 'master_card', 'amex', 'discover', 'bank_draft', 'cup', 'alipay', 'wire', 'layaway',  'credit'];
    $payments = [];
    $payment_total = 0;

    // If gift then no payments.
    if ($expected_total == 0) return $payments;
    foreach ($payment_methods as $method) {
        $amount = floatval($post_data[$method] ?? 0);

        if ($method === "layaway" && $amount > 0) {
            $total_layaway = get_layaway_sum($customer_id, $location_id);

            if ($total_layaway < $amount) {
                wp_send_json_error(['message' => 'Layaway payment is greater than the amount customer has']);
            }
        }
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

function insert_order_and_items($order_data, $items_data, $services_data, $payments, $location_id)
{
    global $wpdb;
    $wpdb->query('START TRANSACTION');
    $deducted_stock = [];

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

        // If gift then no payments.
        if (!empty($payments)) {
            // Insert payments
            foreach ($payments as $payment) {
                $success = $wpdb->insert($wpdb->prefix . 'mji_payments', [
                    'order_id' => $order_id,
                    'amount' => $payment['amount'],
                    'method' => $payment['method'],
                    'payment_date' => $order_data["created_at"],
                    'customer_id' => $order_data["customer_id"],
                    'salesperson_id' => $order_data["salesperson_id"],
                    'transaction_type' => match ($payment['method']) {
                        'layaway' => 'layaway_redemption',
                        'credit'  => 'credit_redemption',
                        default   => 'purchase',
                    },
                    'reference_num' => $order_data['reference_num'],
                    'location_id' => $location_id
                ]);
                if (!$success) {
                    throw new RuntimeException("Failed to insert payment: " . $wpdb->last_error);
                }
            }
        }

        // Insert order items and update inventory
        foreach ($items_data as $item) {

            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT status, location_id FROM {$wpdb->prefix}mji_product_inventory_units WHERE id = %d",
                    $item->unit_id
                )
            );

            if ($row->status == 'in_stock') {

                if ($location_id != $row->location_id) {
                    throw new RuntimeException("Failed to insert order item for {$item->title} due to the item location and order location being different.");
                }

                $success = $wpdb->insert($wpdb->prefix . 'mji_order_items', [
                    'order_id' => $order_id,
                    'product_inventory_unit_id' => $item->unit_id,
                    'sale_price' => $item->price_after_discount,
                    'discount_amount' => $item->discount_amount,
                    'created_at' => $order_data['created_at']
                ]);

                if (!$success) {
                    throw new RuntimeException("Failed to insert order item for {$item->title}: " . $wpdb->last_error);
                }

                $success = $wpdb->update(
                    $wpdb->prefix . 'mji_product_inventory_units',
                    ['status' => 'sold', 'sold_date' => $order_data['created_at']],
                    ['id' => $item->unit_id]
                );

                if ($success === false) {
                    throw new RuntimeException("Failed to update inventory for {$item->title}: " . $wpdb->last_error);
                }

                // UPDATE WOOCOMMERCE STOCK 
                $product_id = $item->product_variant_id ?: $item->product_id;
                $product = wc_get_product($product_id);

                if (!$product) {
                    throw new RuntimeException("WooCommerce product not found for {$item->title}");
                }

                $current_stock = $product->get_stock_quantity();

                if ($current_stock === null) {
                    throw new RuntimeException("WooCommerce stock is not managed for {$item->title}");
                }

                if ($current_stock <= 0) {
                    throw new RuntimeException("WooCommerce stock is already 0 for {$item->title}");
                }

                $qty_to_deduct =  1;
                $result = wc_update_product_stock($product_id, $qty_to_deduct, 'decrease');
                if ($result === false) {
                    throw new RuntimeException("Failed to decrease WooCommerce stock for {$item->title} (ID: {$product_id}).");
                }
                $deducted_stock[] = $product_id;
            } else {
                throw new RuntimeException("Item {$item->title} is already sold or is reserved.");
            }
        }

        foreach ($services_data as $service) {

            $success = $wpdb->insert($wpdb->prefix . 'mji_services', [
                'order_id' => $order_id,
                'category' => $service->category,
                'description' => $service->description,
                'cost_price' => $service->costPrice,
                'sold_price' => $service->retailPrice,
                'location_id' => $location_id
            ]);

            if (!$success) {
                throw new RuntimeException("Failed to insert service for {$service->category}: " . $wpdb->last_error);
            }
        }

        $wpdb->query('COMMIT');
    } catch (Exception $e) {

        // restore WooCommerce stock
        foreach ($deducted_stock as $product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                $product->set_stock_quantity($product->get_stock_quantity() + 1);
                $product->save();
            }
        }
        custom_log($e->getMessage());
        $wpdb->query('ROLLBACK');
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

function finalizeSale()
{
    $customer_id = intval($_POST['customer_id']);
    $salesperson_id = intval($_POST['salesperson']);
    $location_id = intval($_POST['location']);
    $reference_num = sanitize_text_field($_POST['reference']);
    $created_at = sanitize_text_field($_POST['date']);

    [$items_data, $services_data] = validate_sale_input($_POST);
    $totals = calculate_sale_totals($items_data, $services_data, !empty($_POST['exclude_gst']), !empty($_POST['exclude_pst']));
    $payments = get_payments($_POST, $totals['total'], $customer_id, $location_id);

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

    insert_order_and_items($order_data, $items_data, $services_data, $payments, $location_id);

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
    $salesperson = array_find($salespeople, function ($value) use ($salesperson_id) {
        return $value->id == $salesperson_id;
    });

    $salesperson_name = $salesperson->first_name . ' ' . $salesperson->last_name;

    wp_send_json_success([
        'items' => $items_data,
        'services' => $services_data,
        'totals' => $totals,
        'payments' => $payments,
        'reference_num' => $reference_num,
        'salesperson_name' => $salesperson_name,
        'date' => $created_at
    ]);
}
