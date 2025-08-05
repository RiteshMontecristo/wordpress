<?php

use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Subtotal;

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
                        <label for="cheque">cheque:</label>
                        <input type="number" step="0.01" id="cheque" name="cheque">
                    </div>

                    <div>
                        <label for="debit">debit:</label>
                        <input type="number" step="0.01" id="debit" name="debit">
                    </div>

                    <div>
                        <label for="visa">visa:</label>
                        <input type="number" step="0.01" id="visa" name="visa">
                    </div>

                    <div>
                        <label for="master_card">master_card:</label>
                        <input type="number" step="0.01" id="master_card" name="master_card">
                    </div>

                    <div>
                        <label for="amex">amex:</label>
                        <input type="number" step="0.01" id="amex" name="amex">
                    </div>

                    <div>
                        <label for="discover">discover:</label>
                        <input type="number" step="0.01" id="discover" name="discover">
                    </div>

                    <div>
                        <label for="travel_cheque">travel_cheque:</label>
                        <input type="number" step="0.01" id="travel_cheque" name="travel_cheque">
                    </div>

                    <div>
                        <label for="cup">cup:</label>
                        <input type="number" step="0.01" id="cup" name="cup">
                    </div>

                    <div>
                        <label for="alipay">alipay:</label>
                        <input type="number" step="0.01" id="alipay" name="alipay">
                    </div>

                </div>

                <div>
                    <div>
                        <label for="layaway-reference">Reference Number:</label>
                        <input type="text" id="layaway-reference" name="layaway_reference" required>
                    </div>
                    <div>
                        <label for="salesperson">Salesperson:</label>
                        <select name="salesperson" id="salesperson">
                            <option value="" required>Select Salesperson</option>
                            <?php
                            global $wpdb;
                            $table_name = $wpdb->prefix . 'mji_salespeople';
                            $salespeople = $wpdb->get_results("SELECT * FROM $table_name");

                            foreach ($salespeople as $salesperson) {
                                echo "<option value='{$salesperson->id}'>{$salesperson->first_name} {$salesperson->last_name}</option>";
                            }
                            ?>
                        </select>
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

            <button id="printReceipt">Print Receipt</button>

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
            <div class="cart-items"> </div>

            <h3>Finalize Sale</h3>

            <form name="finalize-sale" method="post">

                <div class="payment-methods">
                    <div>
                        <label for="cash">Cash:</label>
                        <input type="number" min="0" step="0.01" id="cash" name="cash">
                    </div>

                    <div>
                        <label for="cheque">cheque:</label>
                        <input type="number" min="0" step="0.01" id="cheque" name="cheque">
                    </div>

                    <div>
                        <label for="debit">debit:</label>
                        <input type="number" min="0" step="0.01" id="debit" name="debit">
                    </div>

                    <div>
                        <label for="visa">visa:</label>
                        <input type="number" min="0" step="0.01" id="visa" name="visa">
                    </div>

                    <div>
                        <label for="master_card">master_card:</label>
                        <input type="number" min="0" step="0.01" id="master_card" name="master_card">
                    </div>

                    <div>
                        <label for="amex">amex:</label>
                        <input type="number" min="0" step="0.01" id="amex" name="amex">
                    </div>

                    <div>
                        <label for="discover">discover:</label>
                        <input type="number" min="0" step="0.01" id="discover" name="discover">
                    </div>

                    <div>
                        <label for="travel_cheque">travel_cheque:</label>
                        <input type="number" min="0" step="0.01" id="travel_cheque" name="travel_cheque">
                    </div>

                    <div>
                        <label for="cup">cup:</label>
                        <input type="number" min="0" step="0.01" id="cup" name="cup">
                    </div>

                    <div>
                        <label for="alipay">alipay:</label>
                        <input type="number" min="0" step="0.01" id="alipay" name="alipay">
                    </div>

                    <div>
                        <label for="layaway">layaway:</label>
                        <input type="number" min="0" step="0.01" id="layaway" name="layaway">
                    </div>

                </div>

                <div>
                    <div>
                        <label for="reference">Reference Number:</label>
                        <input type="text" id="reference" name="reference" required>
                    </div>
                    <div>
                        <label for="salesperson">Salesperson:</label>
                        <select name="salesperson_id" id="salesperson">
                            <option value="" required>Select Salesperson</option>
                            <?php
                            global $wpdb;
                            $table_name = $wpdb->prefix . 'mji_salespeople';
                            $salespeople = $wpdb->get_results("SELECT * FROM $table_name");

                            foreach ($salespeople as $salesperson) {
                                echo "<option value='{$salesperson->id}'>{$salesperson->first_name} {$salesperson->last_name}</option>";
                            }
                            ?>
                        </select>
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

    </div>
<?php }

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

function get_layaway_sum()
{
    if (!isset($_GET['customer_id'])) {
        return wp_send_json_error('Customer ID is required');
    }

    $customer_id = intval($_GET['customer_id']);

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

    $balance = !is_null($result->net_layaway_balance) ? (float)$result->net_layaway_balance : 0.0;
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

    custom_log($query);
    $layaway_items = $wpdb->get_results($query);
    custom_log($layaway_items);

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

    $reference_num   = sanitize_text_field($_POST['layaway_reference']);
    $salesperson_id = sanitize_text_field($_POST['salesperson']);
    $payment_date        = sanitize_text_field($_POST['layaway_date']);
    $notes       = sanitize_textarea_field($_POST['layaway_notes']);
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
                'reference_num'   => $reference_num,
                'salesperson_id' => $salesperson_id,
                'method'      => $payment['method'],
                'amount'      => $payment['amount'],
                'transaction_type' => 'layaway_deposit',
                'payment_date'        => $payment_date,
                'notes'       => $notes,
                'customer_id' => $customer_id,
            ];

            $format = array('%s', '%d', '%s', '%f', '%s', '%s', '%s', '%d');

            $success = $wpdb->insert($table_name, $layaway_data, $format);
            if (!$success) {
                throw new Exception("Failed to insert payment: " . $wpdb->last_error);
            }
        }

        $salesperson = $wpdb->get_row($wpdb->prepare("SELECT first_name, last_name FROM {$wpdb->prefix}mji_salespeople WHERE id = %d", $salesperson_id));

        $response = [
            'salesperson' => $salesperson->first_name . ' ' . $salesperson->last_name,
            'reference_num' => $reference_num,
            'payment_date' => $payment_date,
            'payments' => $payments
        ];

        $wpdb->query('COMMIT');
        wp_send_json_success($response);
    } catch (Exception $e) {
        // Rollback on error
        $wpdb->query('ROLLBACK');
        custom_log("Error: " . $e->getMessage());
        wp_send_json_error(['message' => 'Something went wrong, please try again later.']);
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


    // Add filter to modify the query
    // add_filter('posts_where', function ($where) use ($search_query) {
    //     global $wpdb;
    //     $where .= " OR {$wpdb->posts}.post_title LIKE '%$search_query%'"; // Search by product name
    //     return $where;
    // });

    // // Get products by the custom meta key 'mc_repeatable_sku' (or product name)
    // $args = array(
    //     'post_type' => 'product',
    //     'posts_per_page' => -1, // Fetch all products
    //     'post_status' => 'publish',
    //     'meta_query' => array(
    //         array(
    //             'key' => 'new_repeatable_sku_field', // Search by the custom meta key 'mc_repeatable_sku'
    //             'value' => $search_query, // Compare with search query
    //             'compare' => 'LIKE' // Use LIKE to match the value partially
    //         ),
    //     ),
    // );

    // $query = new WP_Query($args);

    // $number_of_post = 0;

    // if ($query->have_posts()) {
    //     $html = '';
    //     $html .= '<h2>Matching Products</h2>';
    //     $html .= '<div class="pos-search-container">';

    //     while ($query->have_posts()) {
    //         $query->the_post();

    //         // Get the product object
    //         $post_type = get_post_type(get_the_ID());

    //         if ($post_type !== "product")
    //             continue;

    //         $number_of_post++;
    //         $product_id = get_the_ID();
    //         $product = wc_get_product($product_id);

    //         $html .= "<div data-sku='" . $product_id . "' class='pos-item'>";
    //         $html .= '<strong id="title">' . get_the_title() . '</strong>';
    //         $html .= '<br />';
    //         $group_values = get_post_meta($product_id, 'new_repeatable_sku_field', true);

    //         $image_url = wp_get_attachment_image_url(get_post_thumbnail_id($product_id));

    //         $html .= "<img src='" . $image_url . "' />";

    //         if (is_array($group_values)) {

    //             $html .= "<select id='pos-product'>";
    //             // Check if the group values exist
    //             if ($product->is_type('variable')) {
    //                 foreach ($group_values as $item) {
    //                     $html .= "<option value='" . $product_id . " " . $item["sku_text"] . " " . $item["sku_variation"] . "'>SKU: " . $item["sku_text"];
    //                     if (isset($item["sku_variation"])) {
    //                         $variation_id = $item["sku_variation"];
    //                         $variation = wc_get_product($variation_id);
    //                         $attributes = $variation->get_attributes();
    //                         $html .= " Product: " . implode(", ", $attributes);
    //                     }
    //                     $html .= "</option>";
    //                 }
    //             } else {
    //                 foreach ($group_values as $item) {
    //                     $html .= "<option value='" . $product_id . " " . $item["sku_text"] . "'>SKU: " . $item["sku_text"];
    //                     $html .= "</option>";
    //                 }
    //             }
    //             $html .= "</select>";
    //         }

    //         $html .= "<button id='pos-buy-btn'>Add Product</button>";
    //         $html .= "</div>";
    //     }

    //     if ($number_of_post == 0) {
    //         $html .= "<div>No products found</div>";
    //     }
    //     $html .= '</div>';

    //     wp_reset_postdata(); // Reset the query

    //     wp_send_json_success($html);
    // } else {
    //     wp_send_json_success("<div>No products found</div>");
    // }
}

add_action('wp_ajax_searchProducts', 'searchProducts');

function finalizeSale()
{
    global $wpdb;
    define('GST_RATE', 0.05);
    define('PST_RATE', 0.08);
    define('FLOAT_COMPARE_EPSILON', 0.01);


    $payment_table_name = $wpdb->prefix . 'mji_payments';
    $order_table_name = $wpdb->prefix . 'mji_orders';
    $order_items_table_name = $wpdb->prefix . 'mji_order_items';
    $product_inventory_units = $wpdb->prefix . 'mji_product_inventory_units';

    $customer_id = isset($_POST['customer_id']) ? sanitize_text_field($_POST['customer_id']) : null;
    $reference = isset($_POST['reference']) ? sanitize_text_field($_POST['reference']) : null;
    $salesperson_id = isset($_POST['salesperson_id']) ? sanitize_text_field($_POST['salesperson_id']) : null;
    $subtotal = isset($_POST['subtotal']) ? floatval($_POST['subtotal']) : 0;
    $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : null;
    $total = isset($_POST['total']) ? floatval($_POST['total']) : 0;
    $items = isset($_POST['items']) ? stripslashes($_POST['items']) : "";
    $gst = isset($_POST['gst']) ? floatval($_POST['gst']) : 0;
    $pst = isset($_POST['pst']) ? floatval($_POST['pst']) : 0;
    $exclude_gst = isset($_POST['exclude_gst']) ? true : false;
    $exclude_pst = isset($_POST['exclude_pst']) ? true : false;

    $missing_fields = [];

    if (!$customer_id)    $missing_fields[] = 'customer_id';
    if (!$reference)      $missing_fields[] = 'reference';
    if (!$salesperson_id) $missing_fields[] = 'salesperson_id';
    if (!$date)           $missing_fields[] = 'date';
    if (!$subtotal)       $missing_fields[] = 'subtotal';
    if (!$total)          $missing_fields[] = 'total';

    if (!empty($missing_fields)) {
        wp_send_json_error([
            'message' => 'Missing required fields: ' . implode(', ', $missing_fields)
        ]);
    }

    if (!empty($date) && !DateTime::createFromFormat('Y-m-d', $date)) {
        wp_send_json_error(['message' => 'Invalid date format. Expected YYYY-MM-DD.']);
    }

    $items_data = json_decode($items);

    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(['message' => 'Invalid JSON', 'error' => json_last_error_msg()]);
    }

    if (empty($items_data)) {
        wp_send_json_error(['message' => 'No items were sent.']);
    }

    if ($exclude_gst && $gst !== floatval(0)) {
        wp_send_json_error(['message' => 'GST was excluded but the gst amount was not 0']);
    }

    if ($exclude_pst && $pst !== floatval(0)) {
        wp_send_json_error(['message' => 'PST was excluded but the pst amount was not 0']);
    }

    $calculated_subtotal = 0;

    foreach ($items_data as $i => $item) {
        $i += 1;
        if (!isset($item->unit_id, $item->price_after_discount, $item->title, $item->sku, $item->image_url, $item->discount_amount, $item->discount_percent)) {
            wp_send_json_error(['message' => "Item at index {$i} is missing required data."]);
        }
        $price = floatval($item->price_after_discount);
        $calculated_subtotal += $price;
    }

    $calculated_subtotal = round($calculated_subtotal, 2);
    $calculated_gst = $exclude_gst ? 0 : round($calculated_subtotal * 'GST_RATE', 2);
    $calculated_pst = $exclude_pst ? 0 : round($calculated_subtotal * 'PST_RATE', 2);

    $calculated_total = round($calculated_subtotal + $calculated_gst + $calculated_pst, 2);

    if (abs($subtotal - $calculated_subtotal) > FLOAT_COMPARE_EPSILON) {
        wp_send_json_error(['message' => 'Subtotal mismatch between client sent data and calculated by server.']);
    }

    if (abs($gst - $calculated_gst) > FLOAT_COMPARE_EPSILON) {
        wp_send_json_error(['message' => 'GST mismatch between client sent data and calculated by server.']);
    }

    if (abs($pst - $calculated_pst) > FLOAT_COMPARE_EPSILON) {
        wp_send_json_error(['message' => 'PST mismatch between client sent data and calculated by server.']);
    }

    if (abs($total - $calculated_total) > FLOAT_COMPARE_EPSILON) {
        wp_send_json_error(['message' => 'Total mismatch between client sent data and calculated by server.']);
    }

    $payment_methods = ['cash', 'cheque', 'debit', 'visa', 'master_card', 'amex', 'discover', 'travel_cheque', 'cup', 'alipay', 'layaway'];
    $payments = [];
    $payment_total = 0;

    foreach ($payment_methods as $method) {
        $amount = isset($_POST[$method]) ? floatval($_POST[$method]) : 0;
        if ($amount > 0) {
            $payments[] = [
                'method' => $method,
                'amount' => $amount
            ];
            $payment_total += $amount;
        }
    }

    if (empty($payments)) {
        wp_send_json_error(['message' => 'No valid payments entered.']);
        wp_die();
    }

    if (abs($payment_total - $calculated_total) > FLOAT_COMPARE_EPSILON) {
        wp_send_json_error(['message' => 'Payment total and calculated total doesn\'t match in server.']);
    }

    // Start transaction
    $wpdb->query('START TRANSACTION');
    try {
    } catch (Exception $e) {
        // Rollback on error
        $wpdb->query('ROLLBACK');
        custom_log("Error: " . $e->getMessage());
        wp_send_json_error(['message' => 'Something went wrong, please try again later.']);
    }
}

add_action('wp_ajax_finalizeSale', 'finalizeSale');
