<?php

function inventory_page()
{ ?>
    <div class="wrap inventory-sales">
        <h1 class="wp-heading-inline">Inventory Management</h1>

        <!-- Store selection modal -->
        <div id="store-modal" class="modal" style="display:none;">
            <div class="modal-content customer-store-modal">
                <h2>Select Store</h2>
                <p>Choose a location to continue.</p>
                <div class="store-btn-group">
                    <?php
                    $locations = mji_get_locations();
                    foreach ($locations as $location) {
                        echo "<button class='store-btn button button-hero' data-id='" . esc_attr($location->id) . "' data-name='" . esc_attr($location->name) . "'><span class='dashicons dashicons-store'></span>" . esc_html($location->name) . "</button>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Active store bar -->
        <div id="top-store" class="customer-store-bar" style="display:none;">
            <div class="store-bar-info">
                <span class="dashicons dashicons-store"></span>
                <span class="store-bar-label">Store:</span>
                <strong id="store-name"></strong>
            </div>
            <button id="change-store-btn" class="button">Change Store</button>
        </div>

        <!-- Customer banner (shown after selecting a customer) -->
        <div id="customerDetails" class="sales-customer-card hidden">
            <div class="sales-customer-info">
                <div class="sales-customer-avatar"></div>
                <div class="sales-customer-meta">
                    <strong id="customer-name"></strong>
                    <span id="customer-address"></span>
                    <span id="layawaySum" class="sales-balance-pill"></span>
                </div>
            </div>
            <div class="sales-action-bar">
                <button type="button" id="viewProducts" class="button button-primary">
                    <span class="dashicons dashicons-search"></span> Search Products
                </button>
                <button type="button" id="viewCart" class="button">
                    <span class="dashicons dashicons-cart"></span> View Cart
                </button>
                <button type="button" id="viewLayaway" class="button">
                    <span class="dashicons dashicons-money-alt"></span> Layaway / Credit
                </button>
                <button type="button" id="addService" class="button">
                    <span class="dashicons dashicons-hammer"></span> Add Service
                </button>
            </div>
        </div>

        <!-- Customer search -->
        <div class="sales-section" id="search-customer">
            <h2>Select Customer</h2>
            <div class="customer-toolbar">
                <form name="search-customer" method="get" class="customer-search-form">
                    <div class="search-input-wrap">
                        <span class="dashicons dashicons-search"></span>
                        <input id="search" type="text" name="search" placeholder="Search by name or phone…"
                            class="customer-search-input">
                    </div>
                    <button type="submit" id="search-btn" class="button">Search</button>
                </form>
            </div>
            <div id="search-customer-results"></div>
        </div>

        <!-- Layaway / Credit history -->
        <div class="layaway-details hidden" id="layawayDetails">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference #</th>
                        <th>Transaction Type</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th>Notes</th>
                        <th>Salesperson</th>
                    </tr>
                </thead>
                <tbody id="layawayItems"></tbody>
                <tfoot>
                    <tr>
                        <td colspan="6"><strong>Total:</strong></td>
                        <td id="layaway-total">0.00 CAD</td>
                    </tr>
                </tfoot>
            </table>
            <div style="margin-top:12px;">
                <button id="addLayaway" class="button button-primary">+ Add Layaway / Credit</button>
            </div>
        </div>

        <!-- Add Layaway / Credit form -->
        <div class="add-layaway hidden" id="addLayawayForm">
            <h2>Add Layaway / Credit</h2>
            <form name="add-layaway" method="post">

                <div class="sales-form-section">
                    <h3 class="sales-form-section-title">Payment Methods</h3>
                    <div class="payment-methods-grid">
                        <div class="payment-method-field">
                            <label for="cash">Cash</label>
                            <input type="number" step="0.01" id="cash" name="cash" placeholder="0.00">
                        </div>
                        <div class="payment-method-field">
                            <label for="cheque">Cheque</label>
                            <input type="number" step="0.01" id="cheque" name="cheque" placeholder="0.00">
                        </div>
                        <div class="payment-method-field">
                            <label for="debit">Debit / Interac</label>
                            <input type="number" step="0.01" id="debit" name="debit" placeholder="0.00">
                        </div>
                        <div class="payment-method-field">
                            <label for="visa">Visa</label>
                            <input type="number" step="0.01" id="visa" name="visa" placeholder="0.00">
                        </div>
                        <div class="payment-method-field">
                            <label for="master_card">Mastercard</label>
                            <input type="number" step="0.01" id="master_card" name="master_card" placeholder="0.00">
                        </div>
                        <div class="payment-method-field">
                            <label for="amex">Amex</label>
                            <input type="number" step="0.01" id="amex" name="amex" placeholder="0.00">
                        </div>
                        <div class="payment-method-field">
                            <label for="bank_draft">Bank Draft</label>
                            <input type="number" step="0.01" id="bank_draft" name="bank_draft" placeholder="0.00">
                        </div>
                        <div class="payment-method-field">
                            <label for="cup">Cup</label>
                            <input type="number" step="0.01" id="cup" name="cup" placeholder="0.00">
                        </div>
                        <div class="payment-method-field">
                            <label for="alipay">Alipay</label>
                            <input type="number" step="0.01" id="alipay" name="alipay" placeholder="0.00">
                        </div>
                        <div class="payment-method-field">
                            <label for="wire">Wire</label>
                            <input type="number" step="0.01" id="wire" name="wire" placeholder="0.00">
                        </div>
                        <div class="payment-method-field">
                            <label for="credit">Credit</label>
                            <input type="number" step="0.01" id="credit" name="credit" placeholder="0.00">
                        </div>
                    </div>
                </div>

                <div class="sales-form-section">
                    <h3 class="sales-form-section-title">Transaction Details</h3>
                    <div class="sales-meta-grid">
                        <div class="sales-meta-field">
                            <label for="layaway-reference">Reference Number</label>
                            <input type="text" id="layaway-reference" name="layaway_reference" required>
                        </div>
                        <div class="sales-meta-field">
                            <label for="salesperson">Salesperson</label>
                            <?= mji_salesperson_dropdown() ?>
                        </div>
                        <div class="sales-meta-field">
                            <label for="layaway-date">Date</label>
                            <input type="date" id="layaway-date" name="layaway_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="sales-meta-field">
                            <label for="transaction_type">Deposit Type</label>
                            <select name="transaction_type" id="transaction_type">
                                <option value="layaway_deposit">Layaway</option>
                                <option value="credit_deposit">Credit</option>
                            </select>
                        </div>
                        <div class="sales-meta-field sales-meta-field--full">
                            <label for="layaway-notes">Notes</label>
                            <textarea id="layaway-notes" name="layaway_notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" id="submit-layaway" class="button button-primary button-large">Submit Payment</button>
            </form>
        </div>

        <div class="layaway-receipt receipt-content hidden" id="layawayReceipt"></div>
        <button class="hidden button button-primary" id="layawayPrintReceipt">Print Receipt</button>

        <!-- Product search -->
        <div class="sales-section hidden" id="search-products">
            <h2>Search Products</h2>
            <div class="customer-toolbar">
                <form name="search-products" method="post" class="customer-search-form">
                    <div class="search-input-wrap">
                        <span class="dashicons dashicons-search"></span>
                        <input id="search-products" type="text" name="search-products" placeholder="Search by SKU…"
                            class="customer-search-input">
                    </div>
                    <button type="submit" id="search-product-btn" class="button">Search</button>
                </form>
            </div>
            <div id="search-product-results"></div>
        </div>

        <!-- Edit item modal -->
        <div id="edit-item-modal" class="modal hidden">
            <div class="modal-content sales-wide-modal">
                <h3>Edit Item</h3>
                <p><strong id="edit-item-title"></strong></p>
                <p class="sales-edit-meta">
                    SKU: <span id="edit-item-sku"></span>
                    &nbsp;|&nbsp;
                    Price: <span id="edit-item-price"></span>
                </p>
                <div class="sales-edit-fields">
                    <label>
                        Discount ($)
                        <input type="number" id="edit-discount-amt" min="0" step="0.01" placeholder="0.00" />
                    </label>
                    <label>
                        Discount (%)
                        <input type="number" id="edit-discount-pct" min="0" step="0.01" placeholder="0.00" />
                    </label>
                    <label class="sales-edit-field--full">
                        Price After Discount
                        <input type="number" id="edit-price-after-discount" min="0" step="0.01" />
                    </label>
                </div>
                <div class="modal-actions">
                    <button id="cancel-edit" class="button">Cancel</button>
                    <button id="save-edit" class="button button-primary">Save</button>
                </div>
            </div>
        </div>

        <!-- Service modal -->
        <div id="service-modal" class="modal hidden">
            <form name="add-service" class="modal-content sales-wide-modal">
                <div class="service-modal-header">
                    <img src="<?= esc_url(wc_placeholder_img_src('thumbnail')) ?>" alt="Service" class="service-modal-img">
                    <h3>Services &amp; Repairs</h3>
                </div>
                <div class="sales-service-fields">
                    <div class="sales-meta-field">
                        <label for="category">Category</label>
                        <select name="category" id="category">
                            <option value="watch_service">Watch Service</option>
                            <option value="jewellery_service">Jewellery Service</option>
                            <option value="shipping">Shipping</option>
                        </select>
                    </div>
                    <div class="sales-meta-field">
                        <label for="costPrice">Cost Price</label>
                        <input type="number" id="costPrice" min="0" step="0.01" required />
                    </div>
                    <div class="sales-meta-field">
                        <label for="retailPrice">Retail Price</label>
                        <input type="number" id="retailPrice" min="0" step="0.01" required />
                    </div>
                    <div class="sales-meta-field sales-meta-field--full">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" id="cancel" class="button">Cancel</button>
                    <button type="submit" id="add" class="button button-primary">Add Service</button>
                </div>
            </form>
        </div>

        <!-- Cart & Finalize Sale -->
        <div class="cart hidden" id="cart">
            <h2>Cart</h2>
            <div class="cart-items">
                <p class="cart-empty-msg">No items in the cart. Please add by searching products.</p>
            </div>

            <h2>Finalize Sale</h2>
            <form name="finalize-sale" method="post">
                <div class="finalize-sale-grid">
                    <!-- LEFT: Payment Methods -->
                    <div class="finalize-sale-left">
                        <div class="sales-form-section">
                            <h3 class="sales-form-section-title">Transaction Details</h3>
                            <div class="sales-meta-grid sales-meta-grid--2col">
                                <div class="sales-meta-field">
                                    <label for="reference">Reference Number</label>
                                    <input type="text" id="reference" name="reference" required>
                                </div>
                                <div class="sales-meta-field">
                                    <label for="salesperson">Salesperson</label>
                                    <?= mji_salesperson_dropdown() ?>
                                </div>
                                <div class="sales-meta-field sales-meta-field--full">
                                    <label for="sales-date">Date</label>
                                    <input type="date" id="sales-date" name="date" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="sales-meta-field sales-meta-field--full">
                                    <label for="notes">Notes</label>
                                    <textarea id="notes" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="sales-form-section">
                            <h3 class="sales-form-section-title">Payment Methods</h3>
                            <div class="payment-methods-grid">
                                <div class="payment-method-field">
                                    <label for="cash">Cash</label>
                                    <input type="number" min="0" step="0.01" id="cash" name="cash" placeholder="0.00">
                                </div>
                                <div class="payment-method-field">
                                    <label for="cheque">Cheque</label>
                                    <input type="number" min="0" step="0.01" id="cheque" name="cheque" placeholder="0.00">
                                </div>
                                <div class="payment-method-field">
                                    <label for="debit">Debit / Interac</label>
                                    <input type="number" min="0" step="0.01" id="debit" name="debit" placeholder="0.00">
                                </div>
                                <div class="payment-method-field">
                                    <label for="visa">Visa</label>
                                    <input type="number" min="0" step="0.01" id="visa" name="visa" placeholder="0.00">
                                </div>
                                <div class="payment-method-field">
                                    <label for="master_card">Mastercard</label>
                                    <input type="number" min="0" step="0.01" id="master_card" name="master_card"
                                        placeholder="0.00">
                                </div>
                                <div class="payment-method-field">
                                    <label for="amex">Amex</label>
                                    <input type="number" min="0" step="0.01" id="amex" name="amex" placeholder="0.00">
                                </div>
                                <div class="payment-method-field">
                                    <label for="bank_draft">Bank Draft</label>
                                    <input type="number" min="0" step="0.01" id="bank_draft" name="bank_draft"
                                        placeholder="0.00">
                                </div>
                                <div class="payment-method-field">
                                    <label for="cup">Cup</label>
                                    <input type="number" min="0" step="0.01" id="cup" name="cup" placeholder="0.00">
                                </div>
                                <div class="payment-method-field">
                                    <label for="alipay">Alipay</label>
                                    <input type="number" min="0" step="0.01" id="alipay" name="alipay" placeholder="0.00">
                                </div>
                                <div class="payment-method-field">
                                    <label for="wire">Wire</label>
                                    <input type="number" min="0" step="0.01" id="wire" name="wire" placeholder="0.00">
                                </div>
                                <div id="layawayContainer"></div>
                                <div id="creditContainer"></div>
                            </div>
                        </div>
                    </div><!-- end finalize-sale-left -->

                    <!-- RIGHT: Order Summary + Transaction Details -->
                    <div class="finalize-sale-right">

                        <div class="sales-form-section">
                            <h3 class="sales-form-section-title">Order Summary</h3>
                            <div class="sales-totals-grid">
                                <div class="sales-total-row">
                                    <label for="subtotal">Subtotal</label>
                                    <input type="number" readonly name="subtotal" id="subtotal">
                                </div>
                                <div class="sales-total-row">
                                    <label for="gst">GST (5%)</label>
                                    <input type="number" readonly name="gst" id="gst">
                                </div>
                                <div class="sales-total-row">
                                    <label for="pst">PST (7%)</label>
                                    <input type="number" readonly name="pst" id="pst">
                                </div>
                                <div class="sales-tax-toggles">
                                    <label class="sales-toggle-label">
                                        <input type="checkbox" name="exclude_gst" id="exclude-gst">
                                        Exclude GST
                                    </label>
                                    <label class="sales-toggle-label">
                                        <input type="checkbox" name="exclude_pst" id="exclude-pst">
                                        Exclude PST
                                    </label>
                                </div>
                                <div class="sales-total-row sales-total-row--grand">
                                    <label for="total">Total</label>
                                    <input type="number" readonly name="total" id="total">
                                </div>
                                <div class="sales-total-row">
                                    <label for="remaining">Remaining</label>
                                    <input type="number" readonly id="remaining" step="0.01" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="finalize-sale-footer">
                            <button type="submit" id="submit-sale" class="button button-primary button-large">Finalize
                                Sale</button>
                        </div>
                    </div><!-- end finalize-sale-right -->

                </div><!-- end finalize-sale-grid -->

            </form>
        </div>

        <div class="sales-result hidden" id="saleResult">
            <h2>Sale Receipt</h2>
            <div id="receiptContent" class="receipt-content"></div>
            <button id="salesPrintReceipt" class="button button-primary">Print Receipt</button>
            <a href="admin.php?page=inventory-management" class="button">Enter New Sale</a>
        </div>
    <?php
}

function search_customer()
{
    check_ajax_referer('mji_inventory_nonce', 'nonce');

    if (!isset($_GET['search_value']) && !isset($_GET['location_id'])) {
        return wp_send_json_error('Search and Location value is required');
    }
    $search = sanitize_text_field($_GET['search_value']);
    $location_id = absint($_GET['location_id']);
    $result = customer_table("inventory", $search, 50, 1, $location_id);
    wp_send_json_success($result);
}

add_action('wp_ajax_search_customer', 'search_customer');

function get_layaway_sum($customer_id = null, $location_id = null)
{
    if (defined('DOING_AJAX') && DOING_AJAX) {
        check_ajax_referer('mji_inventory_nonce', 'nonce');
    }

    $customer = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : $customer_id;
    $location = isset($_GET['location_id']) ? intval($_GET['location_id']) : $location_id;

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
                WHEN transaction_type IN ('layaway_redemption', 'refund') THEN amount
                ELSE 0
            END)
        ) AS net_layaway_balance
    FROM {$table_name}
    WHERE transaction_type IN ('layaway_deposit', 'layaway_redemption', 'refund')
    AND customer_id = %d
    AND location_id = %d
    AND (layaway_id IS NOT NULL OR credit_id IS NOT NULL)
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
                WHEN transaction_type IN ('credit_redemption', 'refund') THEN amount
                ELSE 0
            END)
        ) AS net_layaway_balance
    FROM {$table_name}
    WHERE transaction_type IN ('credit_deposit', 'credit_redemption', 'refund')
    AND customer_id = %d
    AND location_id = %d
    AND (layaway_id IS NOT NULL OR credit_id IS NOT NULL)
    ", $customer, $location);

    $result = $wpdb->get_row($query);
    $credit_balance = !is_null($result->net_layaway_balance) ? (float) $result->net_layaway_balance : 0.0;

    $balance = [
        "layaway" => $layaway_balance,
        "credit" => $credit_balance
    ];

    if ($customer_id && $location_id)
        return $balance;
    return wp_send_json_success($balance);
}

add_action('wp_ajax_getLayawaySum', 'get_layaway_sum');

// Display all the layway history from payemnts i.e. deposit and redeem
function get_layaway_list()
{
    check_ajax_referer('mji_inventory_nonce', 'nonce');

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
    $salespeople_table = $wpdb->prefix . 'mji_salespeople';

    $query = $wpdb->prepare("
        SELECT payment_date, reference_num, transaction_type, method, amount, notes, s.first_name as salesperson_first_name, s.last_name as salesperson_last_name
        FROM {$table_name} p
        LEFT JOIN {$salespeople_table} s
        ON p.salesperson_id = s.id
        WHERE transaction_type IN ('layaway_deposit', 'layaway_redemption', 'credit_deposit', 'credit_redemption', 'refund')
        AND customer_id = %d
        AND location_id = %d
        AND (layaway_id IS NOT NULL OR credit_id IS NOT NULL)
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
    check_ajax_referer('mji_inventory_nonce', 'nonce');

    global $wpdb;
    $payment_methods = ['cash', 'cheque', 'debit', 'visa', 'master_card', 'amex', 'bank_draft', 'cup', 'alipay', 'wire', 'credit'];
    $payments = [];
    $payments_table = $wpdb->prefix . 'mji_payments';
    $layaway_table = $wpdb->prefix . 'mji_layaways';
    $credit_table = $wpdb->prefix . 'mji_credits';
    $total_sum = 0;

    foreach ($payment_methods as $method) {
        $amount = isset($_POST[$method]) ? floatval($_POST[$method]) : 0;
        if ($amount > 0) {
            $total_sum += $amount;
            $payments[] = [
                'method' => $method,
                'amount' => $amount
            ];
        }
    }

    if (empty($payments)) {
        wp_send_json_error(['message' => 'No valid payments entered.']);
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
    }

    $wpdb->query('START TRANSACTION');

    try {

        $layaway_id = null;
        $credit_id = null;
        if ($transaction_type == "layaway_deposit") {

            $layaway_data = [
                'reference_num' => $reference_num,
                'created_at' => $payment_date,
                'status' => "active",
                'total_amount' => $total_sum,
                'remaining_amount' => $total_sum,
                'customer_id' => $customer_id,
                'location_id' => $location_id
            ];

            $format = array('%s', '%s', '%s', '%f', '%f', '%d', '%d');

            $success = $wpdb->insert($layaway_table, $layaway_data, $format);

            if (!$success) {
                if (strpos($wpdb->last_error, 'Duplicate entry') !== false) {
                    throw new Exception("Reference number already exists.");
                } else {
                    throw new Exception("Failed to insert payment: " . $wpdb->last_error);
                }
            }

            $layaway_id = $wpdb->insert_id;
        } else {

            $credit_data = [
                'reference_num' => $reference_num,
                'created_at' => $payment_date,
                'status' => "active",
                'total_amount' => $total_sum,
                'remaining_amount' => $total_sum,
                'customer_id' => $customer_id,
                'location_id' => $location_id
            ];

            $format = array('%s', '%s', '%s', '%f', '%f', '%d', '%d');

            $success = $wpdb->insert($credit_table, $credit_data, $format);

            if (!$success) {
                if (strpos($wpdb->last_error, 'Duplicate entry') !== false) {
                    throw new Exception("Reference number already exists.");
                } else {
                    throw new Exception("Failed to insert payment: " . $wpdb->last_error);
                }
            }

            $credit_id = $wpdb->insert_id;
        }

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
                'location_id' => $location_id,
                'layaway_id' => $layaway_id,
                'credit_id' => $credit_id
            ];

            $format = array('%s', '%d', '%s', '%f', '%s', '%s', '%s', '%d', '%d', '%d', '%d');

            $success = $wpdb->insert($payments_table, $layaway_data, $format);
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
            'transaction_type' => $transaction_type,
            'notes' => $notes
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

// Get layaway that is active and hasn't been redeemed
function get_active_layaway_list($customer_id = null, $location_id = null)
{
    if (defined('DOING_AJAX') && DOING_AJAX) {
        check_ajax_referer('mji_inventory_nonce', 'nonce');
    }

    $customer = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : $customer_id;
    $location = isset($_GET['location_id']) ? intval($_GET['location_id']) : $location_id;

    if (!$customer) {
        return wp_send_json_error('Customer ID is required');
    }

    if (!$location) {
        return wp_send_json_error('Location ID is required');
    }

    global $wpdb;

    $table_name = $wpdb->prefix . 'mji_layaways';

    $query = $wpdb->prepare("
        SELECT id, reference_num, remaining_amount
        FROM {$table_name}
        WHERE status = 'active'
        AND customer_id = %d
        AND location_id = %d
    ", $customer, $location);

    $layaway_items = $wpdb->get_results($query);

    if ($customer_id && $location_id)
        return $layaway_items;

    return wp_send_json_success($layaway_items);
}

add_action('wp_ajax_getActiveLayaway', 'get_active_layaway_list');


// Get layaway that is active and hasn't been redeemed
function get_active_credit_list($customer_id = null, $location_id = null)
{
    if (defined('DOING_AJAX') && DOING_AJAX) {
        check_ajax_referer('mji_inventory_nonce', 'nonce');
    }

    $customer = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : $customer_id;
    $location = isset($_GET['location_id']) ? intval($_GET['location_id']) : $location_id;

    if (!$customer) {
        return wp_send_json_error('Customer ID is required');
    }

    if (!$location) {
        return wp_send_json_error('Location ID is required');
    }

    global $wpdb;

    $table_name = $wpdb->prefix . 'mji_credits';

    $query = $wpdb->prepare("
        SELECT id, reference_num, remaining_amount
        FROM {$table_name}
        WHERE status = 'active'
        AND customer_id = %d
        AND location_id = %d
    ", $customer, $location);

    $credit_items = $wpdb->get_results($query);

    if ($customer_id && $location_id)
        return $credit_items;

    return wp_send_json_success($credit_items);
}

add_action('wp_ajax_getActiveCredit', 'get_active_credit_list');

function searchProducts()
{
    check_ajax_referer('mji_inventory_nonce', 'nonce');

    if (!isset($_GET['search_product']) || empty(trim($_GET['search_product']))) {
        wp_send_json_error(array("message" => "search field not set"));
    }

    $search_query = sanitize_text_field($_GET['search_product']);

    global $wpdb;
    $table_name = $wpdb->prefix . 'mji_product_inventory_units';
    $sku_history_table = $wpdb->prefix . 'mji_product_sku_history';

    $query = $wpdb->prepare("
            SELECT u.id, u.wc_product_id, u.wc_product_variant_id, u.sku, u.retail_price, u.location_id, u.status, u.serial, u.name, u.image_id
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
        $serial = $result->serial;
        $name = $result->name;
        $image_url = mji_get_unit_image_url($result, "thumbnail");
        $variation_detail = "";

        $product = wc_get_product($product_id);
        $price = $result->retail_price;

        if (!$product) {
            $response_data = array(
                'unit_id' => $unit_id,
                'product_id' => $product_id,
                'product_variant_id' => $product_variant_id,
                'location_id' => $location_id,
                'title' => $name,
                'image_url' => esc_url($image_url),
                'sku' => $sku,
                'status' => $status,
                'serial' => $serial,
                'variation_detail' => $variation_detail,
                'price' => $price
            );

            wp_send_json_success($response_data);
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
            'image_url' => esc_url($image_url),
            'sku' => $sku,
            'status' => $status,
            'serial' => $serial,
            'variation_detail' => $variation_detail,
            'price' => $price
        );

        wp_send_json_success($response_data);
    } else {
        wp_send_json_error(array("message" => "No products found"));
    }
}

add_action('wp_ajax_searchProducts', 'searchProducts');

function validate_sale_input($post_data)
{
    $required_fields = ['customer_id', 'reference', 'salesperson', 'subtotal', 'total', 'date'];
    $missing_fields = [];

    foreach ($required_fields as $field) {
        if (empty($post_data[$field]))
            $missing_fields[] = $field;
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

function get_remaining_layaway_balance($layaway_id, $customer_id, $location_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'mji_layaways';

    $layaway = $wpdb->get_row($wpdb->prepare(
        "SELECT reference_num, remaining_amount
         FROM {$table} 
         WHERE id = %d AND customer_id = %d AND location_id = %d",
        $layaway_id,
        $customer_id,
        $location_id
    ));

    if (!$layaway) {
        wp_send_json_error([
            'message' => "Layaway #$layaway_id not found or does not belong to this customer/location."
        ]);
        return new WP_Error('invalid_layaway', ".");
    }

    return $layaway;
}

function get_remaining_credit_balance($credit_id, $customer_id, $location_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'mji_credits';

    $credit = $wpdb->get_row($wpdb->prepare(
        "SELECT reference_num, remaining_amount
         FROM {$table} 
         WHERE id = %d AND customer_id = %d AND location_id = %d",
        $credit_id,
        $customer_id,
        $location_id
    ));

    if (!$credit) {
        wp_send_json_error([
            'message' => "Credit #$credit_id not found or does not belong to this customer/location."
        ]);
        return new WP_Error('invalid_credit', ".");
    }

    return $credit;
}

function get_payments($post_data, $expected_total, $customer_id, $location_id)
{
    $payment_methods = ['cash', 'cheque', 'debit', 'visa', 'master_card', 'amex', 'bank_draft', 'cup', 'alipay', 'wire'];
    $payments = [];
    $payment_total = 0;

    // If gift then no payments.
    if ($expected_total == 0)
        return $payments;

    foreach ($payment_methods as $method) {
        $amount = floatval($post_data[$method] ?? 0);

        if ($amount > 0) {
            $payments[] = ['method' => $method, 'amount' => $amount];
            $payment_total += $amount;
        }
    }

    // Adding layaways and credit to the payment method
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'layaway-') === 0 && !empty($value)) {
            // Extract the ID: e.g., 'layaway-4' → 4
            $layaway_id = (int) substr($key, strlen('layaway-'));
            $layaway_amount = (float) $value;

            if ($layaway_amount > 0) {
                $layaway = get_remaining_layaway_balance($layaway_id, $customer_id, $location_id);
                $reference_num = $layaway->reference_num;
                $layaway_balance = $layaway->remaining_amount;

                if ($layaway_amount > $layaway_balance) {
                    wp_send_json_error(['message' => 'Layaway used more than available']);
                } else {
                    $payment_total += $layaway_amount;
                    $status = $layaway_balance - $layaway_amount == 0 ? "redeemed" : "active";

                    $payments[] = [
                        'layaway_id' => $layaway_id,
                        'reference_num' => $reference_num,
                        'method' => 'layaway',
                        'amount' => $layaway_amount,
                        'status' => $status
                    ];
                }
            }
        }

        if (strpos($key, 'credit-') === 0 && !empty($value)) {
            $credit_id = (int) substr($key, strlen('credit-'));
            $credit_amount = (float) $value;

            if ($credit_amount > 0) {
                $credit = get_remaining_credit_balance($credit_id, $customer_id, $location_id);
                $reference_num = $credit->reference_num;
                $credit_id_balance = $credit->remaining_amount;

                if ($credit_amount > $credit_id_balance) {
                    wp_send_json_error(['message' => 'credit_id used more than available']);
                } else {
                    $payment_total += $credit_amount;
                    $status = $credit_id_balance - $credit_amount == 0 ? "redeemed" : "active";

                    $payments[] = [
                        'credit_id' => $credit_id,
                        'reference_num' => $reference_num,
                        'method' => 'credit',
                        'amount' => $credit_amount,
                        'status' => $status
                    ];
                }
            }
        }
    }

    if (empty($payments))
        wp_send_json_error(['message' => 'No valid payments entered']);
    $paidCents     = (int) round($payment_total * 100);
    $expectedCents = (int) round($expected_total * 100);
    if (abs($paidCents - $expectedCents) > 1) {
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

                if ($payment['method'] == 'layaway') {
                    $layaway_id = $payment['layaway_id'];
                    $amount = $payment['amount'];

                    $layaway_row = $wpdb->get_row($wpdb->prepare(
                        "SELECT remaining_amount FROM {$wpdb->prefix}mji_layaways WHERE id = %d FOR UPDATE",
                        $layaway_id
                    ));
                    if (!$layaway_row || $amount > $layaway_row->remaining_amount) {
                        throw new RuntimeException("Layaway #$layaway_id has insufficient balance.");
                    }
                    $status = round($layaway_row->remaining_amount - $amount, 2) == 0 ? 'redeemed' : 'active';

                    $sql = $wpdb->prepare(
                        "UPDATE {$wpdb->prefix}mji_layaways
                         SET remaining_amount = remaining_amount - %f, status = %s
                        WHERE id = %d",
                        $amount,
                        $status,
                        $layaway_id
                    );

                    if ($wpdb->query($sql) === false) {
                        throw new RuntimeException("Failed to update layaway #$layaway_id: " . $wpdb->last_error);
                    }
                } elseif ($payment['method'] == 'credit') {
                    $credit_id = $payment['credit_id'];
                    $amount = $payment['amount'];

                    $credit_row = $wpdb->get_row($wpdb->prepare(
                        "SELECT remaining_amount FROM {$wpdb->prefix}mji_credits WHERE id = %d FOR UPDATE",
                        $credit_id
                    ));
                    if (!$credit_row || $amount > $credit_row->remaining_amount) {
                        throw new RuntimeException("Credit #$credit_id has insufficient balance.");
                    }
                    $status = round($credit_row->remaining_amount - $amount, 2) == 0 ? 'redeemed' : 'active';

                    $sql = $wpdb->prepare(
                        "UPDATE {$wpdb->prefix}mji_credits
                         SET remaining_amount = remaining_amount - %f, status = %s
                        WHERE id = %d",
                        $amount,
                        $status,
                        $credit_id
                    );

                    if ($wpdb->query($sql) === false) {
                        throw new RuntimeException("Failed to update credit #$credit_id: " . $wpdb->last_error);
                    }
                }

                $success = $wpdb->insert($wpdb->prefix . 'mji_payments', [
                    'order_id' => $order_id,
                    'amount' => $payment['amount'],
                    'method' => $payment['method'],
                    'payment_date' => $order_data["created_at"],
                    'customer_id' => $order_data["customer_id"],
                    'salesperson_id' => $order_data["salesperson_id"],
                    'transaction_type' => match ($payment['method']) {
                        'layaway' => 'layaway_redemption',
                        'credit' => 'credit_redemption',
                        default => 'purchase',
                    },
                    'reference_num' => $order_data['reference_num'],
                    'location_id' => $location_id,
                    'layaway_id' => match ($payment['method']) {
                        'layaway' => $payment['layaway_id'],
                        default => NULL,
                    },
                    'credit_id' => match ($payment['method']) {
                        'credit' => $payment['credit_id'],
                        default => NULL,
                    },
                    'notes' => ''
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
                    "SELECT status, location_id FROM {$wpdb->prefix}mji_product_inventory_units WHERE id = %d FOR UPDATE",
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

                $success = $wpdb->insert($wpdb->prefix . 'mji_inventory_status_history', [
                    'inventory_unit_id' => $item->unit_id,
                    'from_status' => 'in_stock',
                    'to_status' => 'sold',
                    'reference_num' => $order_data['reference_num'],
                    'created_at' => $order_data['created_at'],
                    'notes' => $order_data['notes']
                ]);

                if (!$success) {
                    throw new RuntimeException("Failed to insert status history for {$item->title}: " . $wpdb->last_error);
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
                if ($product_id) {
                    $product = wc_get_product($product_id);
                    if (!$product) {
                        throw new RuntimeException("WooCommerce product not found for {$item->title}");
                    }
                    if ($product->get_stock_quantity() === null) {
                        throw new RuntimeException("WooCommerce stock is not managed for {$item->title} — enable stock management in WooCommerce first.");
                    }
                    $result = wc_update_product_stock($product_id, 1, 'decrease');
                    if ($result === false) {
                        throw new RuntimeException("Failed to decrease WooCommerce stock for {$item->title} (ID: {$product_id}).");
                    }
                    $deducted_stock[] = $product_id;
                }
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
        custom_log($e->getMessage());
        $wpdb->query('ROLLBACK');
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

function finalizeSale()
{
    check_ajax_referer('mji_inventory_nonce', 'nonce');

    $customer_id = intval($_POST['customer_id']);
    $salesperson_id = intval($_POST['salesperson']);
    $location_id = intval($_POST['location']);

    if (empty($location_id)) {
        wp_send_json_error(['message' => 'Location is required. Please select a store before finalizing the sale.']);
    }
    $reference_num = sanitize_text_field($_POST['reference']);
    $created_at = sanitize_text_field($_POST['date']);
    $notes = sanitize_text_field($_POST['notes']);

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
        'notes' => $notes,
        'location_id' => $location_id
    ];

    insert_order_and_items($order_data, $items_data, $services_data, $payments, $location_id);

    if (!empty($items_data)) {
        global $wpdb;
        $unit_ids     = array_map(fn($item) => (int) $item->unit_id, $items_data);
        $placeholders = implode(',', array_fill(0, count($unit_ids), '%d'));
        $rows         = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, description FROM {$wpdb->prefix}mji_product_inventory_units WHERE id IN ($placeholders)",
                $unit_ids
            )
        );
        $desc_map = array_column($rows, 'description', 'id');
        foreach ($items_data as $item) {
            $item->description = $desc_map[$item->unit_id] ?? '';
        }
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
        'date' => $created_at,
        'notes' => $notes
    ]);
}

add_action('wp_ajax_finalizeSale', 'finalizeSale');
