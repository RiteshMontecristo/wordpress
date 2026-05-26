# Developer Notes — Montecristo Jewellers Theme

Last updated: 2026-05-25. This is the working TODO list. See `CLAUDE.md` for architecture + conventions.

Legend: 🔴 critical · 🟠 high · 🟡 medium · ⚪ low · ✅ done

---

## 1. Security

| ID | Status | Summary |
|----|--------|---------|
| S1 | ✅ | Unauthenticated file upload in `appointment()` — fixed via `wp_handle_upload()` + MIME whitelist |
| S2 | ✅ | IDOR in `toggle_favourite` — user ID from `get_current_user_id()`, `wp_ajax_nopriv` removed |
| S3 | ✅ | reCAPTCHA silently disabled — `captcha_verify()` now fails closed if secret undefined or HTTP error |
| S4 | ✅ | Missing nonces on all 8 sales AJAX handlers — `check_ajax_referer` added to each |
| S5 | ✅ | Missing nonces on inventory unit handlers — added to delete, create, update_status |
| S6 | ✅ | Missing nonces on refund/return handlers — added to all 4 handlers in `find.php` |
| S7 | ✅ | CSRF on customer delete — per-customer nonce via `wp_nonce_url` |
| S8 | ✅ | `load_more_blogs` — `absint()` on page param; no nonce needed (read-only public endpoint) |
| S9 | ✅ | Email-based admin auth — acceptable as-is; S10 is the right fix |
| S10 | 🔴 | **Role-based access** — everything gates on `manage_options`. Need `mji_salesperson`, `mji_manager`, `mji_admin` roles with granular capabilities |
| S11 | ✅ | Fresh-install table setup broken — `mji_create_all_tables()` now iterates 20-table manifest in FK-safe order |
| S12 | ✅ | Stored XSS in admin POS — all DB-sourced `innerHTML` now goes through `esc()` helper |
| S13 | ✅ | SQL dump committed to repo — removed |

---

## 2. Bugs — correctness

| ID | Status | Summary | File |
|----|--------|---------|------|
| B1 | ✅ | Race condition in `finalizeSale` — `SELECT FOR UPDATE` + status re-check added | `sales.php` |
| B2 | ✅ | Refund status bug — `elseif` chain replaced with explicit terminal state handling | `find.php` |
| B3 | ✅ | Float equality on refund — amounts now compared in integer cents | `find.php` |
| B4 | ✅ | Negative payments inflate totals — guarded by positivity check | `sales.php` |
| B5 | ✅ | Wrong `$_GET` key on customer search — uses `$_GET['location_id']` | `sales.php` |
| B6 | ✅ | `trade_in` payment method — removed from payment methods list | `sales.php` |
| B7 | ✅ | Tax rate constants duplicated — `GST_RATE`/`PST_RATE` now defined once at top of `functions.php` | `functions.php` |
| B8 | ✅ | `sleep(5)` retry loop — removed | `functions.php` |
| B9 | ✅ | `esc_sql()` usage — replaced everywhere with `$wpdb->prepare()` | various |
| B10 | ✅ | Hardcoded `wp_product_skus` table — dead code deleted | `functions.php` |
| B11 | ✅ | Fatal on missing `jewellery` term — guard added before `term_id` access | `products_sidebar.php` |
| B12 | ✅ | Submit button stuck disabled after validation fail — re-enabled on all early-return paths | `layaway.js` |
| B13 | ✅ | `dismantled` missing from unit status dropdown — added to UI and handler whitelist | `product_units.php` |
| B14 | ✅ | reCAPTCHA score threshold too low — raised to `>= 0.7`, honeypot added | `function.php` |
| B15 | ✅ | Raw `$_SERVER['REMOTE_ADDR']` — all IP lookups via `WC_Geolocation::get_ip_address()` | various |
| B16 | ✅ | Unescaped attributes on return forms — fixed | `find.php` |
| B17 | ✅ | Hardcoded error-email recipient — resolved | various |
| B18 | ✅ | Per-IP rate limit on contact/appointment — wired | `function.php` |
| B19 | ✅ | `custom_log()` world-readable — dir 0750, file 0640 | `functions.php` |
| B20 | ✅ | Unreachable `wp_die()` after `wp_send_json_*` — removed | various |
| B21 | ✅ | Customer delete confirm dialog copy-paste wrong text — fixed | `customer.php` |
| B22 | 🟡 | Duplicate `id=` attributes in customer list loop — `id="firstName"` etc. rendered per row | `customer.php:252–258` |
| B23 | ⚪ | Typo "prooducts" in empty cart message | `cart.js:91` |
| B24 | ✅ | Dead test variable `let test = "EXORT"` — removed | `print.js` |
| B25 | ✅ | Unused MailPoet import — removed | `product_units.php` |
| B26 | ⚪ | Variable shadowing: `foreach ($salespeople as $salespeople)` | `salespeople.php:74` |
| B27 | ✅ | `adjust_stock_after_order` was a no-op — fully reimplemented in `online.php` on `online-sale` branch with `FOR UPDATE`, transaction, status history, and payment record | `online.php` |
| B28 | 🟡 | `products_collections` schema drift — table has `product_id` but planned migration references `inventory_unit_id`. Resolve before running migrations in `CLAUDE.md §4` | `functions.php:687–708` |
| B29 | ⚪ | Swallowed exceptions in `customer.php` log `$wpdb->last_error` instead of `$e->getMessage()` | `customer.php:453` |
| B30 | ✅ | Layaway refund race condition — balance re-read with `FOR UPDATE` inside transaction, response uses `$fresh->remaining_amount` | `find.php` |
| B31 | ✅ | Credit null check missing before refund — `wp_send_json_error` added if credit not found | `find.php` |
| B32 | ✅ | Credit deposit duplicate reference gives confusing error — same duplicate-entry check as layaway now applied | `sales.php` |
| B33 | ✅ | Cancelled orders included in sales reports — `o.status != 'cancelled'` added to report queries | `reports.php` |
| B34 | ✅ | WC payment gateway unmapped — Stripe card brand + debit/credit funding now read from order meta; unmapped gateways log and default to visa | `online.php` |
| B35 | ✅ | Refund subtotal derived by subtraction (could go negative from rounding) — now summed directly from refund line items | `online.php` |

---

## 3. Performance

| ID | Status | Summary | File |
|----|--------|---------|------|
| P1 | 🟠 | N+1 in customer list — `get_active_layaway_list()` + `get_active_credit_list()` once per row | `customer.php:229–241` |
| P2 | 🟠 | N+1 in products sidebar — `get_terms()` inside loop | `products_sidebar.php:17–35` |
| P3 | 🟡 | Long transient on image sizes — shorten TTL or invalidate on media update | `functions.php:145` |
| P4 | 🟡 | Synchronous `get_headers()` image check per shortcode render — cache the result | `functions.php:170` |
| P5 | 🟠 | `mji_locations` transient never cleared — no `delete_transient()` on save/delete | `functions.php:935` |

---

## 4. Refactoring

| ID | Status | Summary |
|----|--------|---------|
| R1 | 🟡 | Consolidate 4 near-identical `mji_*_dropdown()` functions into `mji_build_select($config)` |
| R2 | 🟡 | Parameterize `get_active_layaway_list()` / `get_active_credit_list()` — differ only by table |
| R3 | 🟡 | Parameterize `create_refund_layaway()` / `create_refund_credit()` — ~200-line near-clones |
| R4 | 🟡 | Extract return form HTML partial — credit vs refund forms are ~200 lines of near-identical HTML |
| R5 | 🟡 | `mji_get_filter_params()` helper — shared `$_GET` sanitization across `reports.php` and `find.php` |
| R6 | 🟡 | Split `find.php` (3,040+ lines) into `find/sales.php`, `find/layaway.php`, `find/refund.php`, `find/returns.php` |
| R7 | 🟡 | `products_sidebar.php`: `<sidebar>` invalid HTML, hardcoded slugs, duplicate IDs, unescaped `$heading`, typo "Uncategoriezed", dead commented blocks |
| R8 | 🟡 | Naming consistency — functions missing `mji_` prefix (`get_brand_name`, `get_payments`, etc.) |
| R9 | 🟡 | `get_active_layaway_list()` mixed return type — sometimes returns array, sometimes calls `wp_send_json_*` |
| R10 | 🟡 | Dead code — commented-out Gifts filter + Reset button in `products_sidebar.php`, stale blocks in `cmb2/fields.php` |
| R11 | ⚪ | Replace `@` error suppression with proper conditionals — `functions.php:160,170,178` |
| R12 | ⚪ | Replace `==` with `===` — systematic sweep, especially around money, IDs, status strings |

---

## 5. Architecture

| ID | Status | Summary |
|----|--------|---------|
| A1 | 🟠 | Extract inventory into a plugin — business logic inside theme means a theme swap kills the POS |
| A2 | 🟠 | Schema version + migration runner — needed before running `wc_product_id`/`image_id`/`full_name` migrations on live DB |
| A3 | 🟠 | First integration tests — zero tests; start with `calculate_sale_totals()` (GST, PST, discount) |
| A4 | 🟡 | Repository classes — `MJI_Order_Repository` etc., introduce incrementally |
| A5 | 🟡 | REST API — `wp-json/mji/v1/...` would unblock accounting export and mobile lookup |
| A6 | 🟡 | Structured logging — replace `custom_log()` with JSON-line + daily rotation |
| A7 | 🟡 | Backup/restore — nightly `mysqldump` of `wp_mji_*` + documented restore script |

---

## 6. Online-sale branch — specific items

These apply to `inventory/online.php` on the `online-sale` branch only.

| ID | Status | Summary |
|----|--------|---------|
| O1 | ✅ | `adjust_stock_after_order` signature — takes `($order_id, $order)` hooked at priority 10 with 2 args |
| O2 | ✅ | WC payment gateway mapping — Stripe debit/credit/brand detection; unmapped gateways log + default visa |
| O3 | ✅ | Refund subtotal rounding — summed from line items directly, not `total - taxes` |
| O4 | ✅ | Refund idempotency — `WRET-{order}-{refund_id}` reference checked before inserting |
| O5 | ✅ | HST splitting — federal 5% / provincial remainder split by tax label for ON/Atlantic orders |
| O6 | ✅ | Processing order cancellation blocked — `woocommerce_valid_order_statuses_for_cancel` filter returns `[]` |
| O7 | ✅ | WC order deleted/trashed — `mji_handle_wc_order_deleted` logs and emails staff |
| O8 | ✅ | Cancelled order status change — only handles `pending/on-hold → cancelled`; uses `FOR UPDATE`; soft-cancel on MJI order |
| O9 | 🟡 | No unit found for WC product — throws `RuntimeException` which triggers staff email, but no UI fallback for staff to manually link units |

---

## 7. Fresh issue list — Opus review 2026-05-25

New bugs found by reading the current code from scratch. Items already in sections 1–6 are not repeated.

---

### 🔴 F1 — `delete_refund` double-credits on multi-account refunds
**File:** `find.php:2236–2271`
Loops all payment rows for the refund, sums `$total_amount`, then adds the **entire sum** to every credit/layaway account referenced. If a refund touched both a layaway and a credit, each account gets the full total — money is doubled. If multiple distinct layaway IDs appear in the refund, only the last one's ID is kept.
**Fix:** sum amounts per account ID, then update each account separately inside the loop.

---

### 🔴 F2 — `create_refund_credit` reads balance before transaction — race condition
**File:** `find.php:3235, 3263`
Credit row is read at line 3235 **before** `START TRANSACTION` at line 3263. Two concurrent refund requests both pass the `$refund_total > $credit->remaining_amount` check, then both deduct, overdrawing the credit. The layaway path correctly re-reads with `FOR UPDATE` inside the transaction — credit path does not.
**Fix:** mirror `create_refund_layaway` — move the credit SELECT inside the transaction with `FOR UPDATE` and use the fresh value.

---

### 🟠 F3 — `edit_layaway` edits payment amounts but never updates layaway/credit balance
**File:** `find.php:3622`
Handler updates `mji_payments` rows when staff edits a deposit amount, but never touches `mji_layaways.total_amount` or `remaining_amount`. Staff edits a $500 deposit down to $300 → payment row is $300, layaway still says $500.
**Fix:** after updating payment rows, recalculate `total_amount` as `SUM(amount)` from all payments for that layaway_id and UPDATE the layaway row.

---

### 🟠 F4 — `edit_sale` cascade reference rename not scoped to `order_id`
**File:** `find.php:3447`
`$wpdb->update($payments_table, ['reference_num' => $new], ['reference_num' => $old])` updates ALL payment rows with the old reference across the entire table — including refund payment rows and layaway payments that happen to share the reference. This breaks the refund→payment linkage in reports.
**Fix:** add `['order_id' => $order_id]` to the WHERE array.

---

### 🟠 F5 — `edit_layaway` reference uniqueness check blocks the current account
**File:** `find.php:3646–3660`
Before renaming a reference, the code checks if the reference already exists in `mji_payments`. The current account's own payments match this check, so changing a reference to anything is blocked because the old reference is "already in use" by itself.
**Fix:** exclude the current `layaway_id` (or `credit_id`) from the uniqueness check.

---

### 🟠 F6 — `delete_layaway` and `delete_credit` leave open transaction on early return
**File:** `find.php:2009, 2127`
Both functions call `START TRANSACTION` then check if `total_amount == remaining_amount`. If not (account has been used), they `return wp_send_json_error(...)` without calling `ROLLBACK`. The connection holds an open transaction until request end. Under high load this delays lock release.
**Fix:** call `$wpdb->query('ROLLBACK')` before the early `wp_send_json_error` return.

---

### 🟠 F7 — Null fatal on deleted salesperson or location objects
**File:** `sales.php:680`, `find.php:1191`, `find.php:2897`, `reports.php:1192`, `reports.php:856`
Multiple places do `$salesperson->first_name` or `$location_obj->name` after `array_find()` / `get_row()` with no null check. If a salesperson or location was deleted after a sale was recorded, any page that renders that sale fatals.
**Fix:** add `if (!$salesperson) { ... fallback string ... }` guards at each access point.

---

### 🟠 F8 — `mji_insert_unit_history` failure exits via `wp_send_json_error` inside transaction without ROLLBACK
**File:** `product_units.php:761, 803`
If the status history INSERT fails, the code calls `wp_send_json_error()` which ends the request. The surrounding `try/catch` never fires. The unit row was already inserted; it now exists with no history row.
**Fix:** throw a `RuntimeException` inside the failure check so the `catch` block can `ROLLBACK`.

---

### 🟠 F10 — Redeem report sums only first payment method per redemption row
**File:** `reports.php:1732`
`$total_credit_redeemed += (float) ($payments[0]['amount'] ?? 0)` — only adds the first element. If staff split a redemption across two methods, the second amount is silently dropped from the footer total.
**Fix:** sum all elements in `$payments` array, not just `[0]`.

---

### 🟠 F11 — Refund report "Total Original Amount" double-counts multi-refund orders
**File:** `reports.php:2027`
`$total_original += $orig['amount']` runs once per refund row. An order refunded twice has its original total added twice to the footer.
**Fix:** track which `order_id` values have already been counted and skip duplicates.

---

### 🟠 F12 — WC variation parent stock not synced after unit deletion
**File:** `product_units.php:506`
`wc_update_product_stock($variant_id, -1, 'decrease')` decrements variant stock but doesn't call `WC_Product_Variable::sync_stock_status()`. `create_inventory_units` at line 775 does call sync — delete doesn't, leaving the parent's stock status stale.
**Fix:** call `wc_get_product($product_id)->sync_stock_status()` after the variant stock update, or use `wc_update_product_stock` on the parent with a recalculated total.

---

### 🟠 F13 — `block_product_deletion_if_in_inventory` fires AFTER trash, not before
**File:** `product_units.php:1294–1317`
Hooked to `wp_trash_post` action which fires after the post is already trashed. The redirect runs, but the deletion already happened.
**Fix:** use the `pre_trash_post` filter which can return a `WP_Error` to block the operation before it occurs.

---

### 🟡 F14 — Customer count query passes 11 unused params to `$wpdb->prepare()` — `_doing_it_wrong` notices
**File:** `customer.php:151–170`
The `$where` string already has placeholders substituted from a previous `$wpdb->prepare()` call. The second `prepare()` call receives 11 `$like` args against a string with no placeholders, triggering WP's `_doing_it_wrong` notice on every customer list load in WP 6.2+.
**Fix:** refactor to build the WHERE clause once without double-preparing.

---

### 🟡 F15 — Supplier insert has no uniqueness guard — duplicates accumulate
**File:** `product_units.php:610–619`
When staff type in a new supplier name, the code inserts without checking if the name already exists. The `mji_suppliers` table has no unique index on `name`. Duplicate entries ("ABC Watches", "ABC watches") pile up in the dropdown.
**Fix:** `SELECT id FROM mji_suppliers WHERE name = %s` first; insert only if not found. Or add a `UNIQUE` index on `mji_suppliers.name`.

---

### 🟡 F16 — `delete_layaway`/`delete_credit` use `==` on decimal strings from MySQL
**File:** `find.php:2009, 2068`
`$layaway->total_amount == $layaway->remaining_amount` compares strings like `"100.00"`. PHP loose comparison works here but sub-cent DB values could break this.
**Fix:** compare as cents: `(int)round($a * 100) === (int)round($b * 100)`.

---


### 🟡 F18 — Customer edit form uses `Miss.` but add form stores `Miss` — prefix doesn't pre-select on edit
**File:** `customer.php:599–607`
The prefix dropdown in the edit form has `value="Miss."` but the stored value from the add form is `"Miss"` (no period). When editing such a customer the prefix dropdown shows blank.
**Fix:** normalise to `Miss` (no period) in both the edit form option and as a migration for existing rows.

---

### 🟡 F19 — `is_naN` typo — wrong function name
**File:** `find.php:2308`
PHP function is `is_nan()`. `is_naN()` works in practice (PHP function lookup is case-insensitive) but breaks IDE support and linters.
**Fix:** lowercase to `is_nan()`.

---

### ⚪ F20 — Broken `</p>` tag in favourite popup email HTML
**File:** `woocommerce/function.php:781`
`<?p>` typo (missing `/`). The popup renders with unclosed tag, slightly breaking layout.
**Fix:** change to `</p>`.

---

### ⚪ F21 — Server-side missing validation for negative payment amounts in `add_layaway`
**File:** `sales.php:566–578`
`floatval()` accepts negatives. HTML `min="0"` is client-side only. A crafted request could send negative amounts that partially bypass the `$amount > 0` filter if mixed with positive ones, producing an incorrect `$total_sum`.
**Fix:** add `if ($amount <= 0) continue;` (or `< 0`) consistently in the loop.

---

## 8. N-series (new items from prior pass)

| ID | Status | Summary |
|----|--------|---------|
| N2 | 🟡 | `products_collections` schema drift — see B28 |

---

## 9. Online-sale testing checklist

Run these manually on the staging/local site before merging `online-sale` into `main`. Check each box only after you've confirmed the result in both WooCommerce and the MJI POS (find.php / reports).

**Pre-requisites**
- [ ] `online-sale` branch is checked out and active theme
- [ ] At least one product in WooCommerce with a matching MJI inventory unit (same SKU or variant ID) with status `in_stock`
- [ ] Stripe is configured in test mode (use Stripe test card numbers)
- [ ] Zebra is not required — just check the DB and POS UI

---

### A. New online order — happy path
- [ ] Place a WooCommerce order with a **Stripe credit card** (Visa test card `4242 4242 4242 4242`)
- [ ] Check `wp_mji_orders` — one new row with `reference_num = WEB-{order number}`
- [ ] Check `wp_mji_order_items` — correct unit linked, `sale_price` matches WC line item price
- [ ] Check `wp_mji_product_inventory_units` — unit status flipped to `sold`, `sold_date` set
- [ ] Check `wp_mji_inventory_status_history` — one row: `in_stock → sold`
- [ ] Check `wp_mji_payments` — method = `visa`, amount = order total
- [ ] POS Find page — invoice appears, shows correct customer, items, payment

### B. Payment method mapping
- [ ] Place order with **Stripe debit card** (`4000 0566 5566 5556`) — payment row method = `debit`
- [ ] Place order with **Stripe Mastercard** (`5555 5555 5555 4444`) — payment row method = `master_card`
- [ ] Place order with **Cash on Delivery** (COD) — payment row method = `cash`
- [ ] Check `custom_log.txt` — no "unmapped gateway" or "unknown brand" warnings for the above

### C. Shipping
- [ ] Place an order that includes a shipping method (e.g. flat rate)
- [ ] Check `wp_mji_services` — one row with `category = 'shipping'`, `sold_price` = shipping total
- [ ] Check `wp_mji_orders` — `subtotal` does **not** include shipping (shipping is in services)

### D. Customer creation
- [ ] Place order as a **new WooCommerce customer** (no existing MJI customer record)
- [ ] Check `wp_mji_customers` — new row created with correct name, email, phone, address
- [ ] Place a **second order as the same customer** — no duplicate customer row created
- [ ] Place order as a **guest** (no WC account) — customer row still created from billing details

### E. Idempotency — no double-processing
- [ ] Trigger `woocommerce_checkout_order_processed` twice for the same order (simulate by calling the hook manually or by saving the order twice in WC admin)
- [ ] Check `wp_mji_orders` — still only **one** row for that `reference_num`

### F. Refund — with line items selected
- [ ] Issue a WooCommerce refund for a **specific line item** (check the item checkbox in WC refund UI)
- [ ] Check `wp_mji_returns` — one row, `reference_num = WRET-{order}-{refund_id}`
- [ ] Check `wp_mji_return_items` — correct unit linked
- [ ] Check `wp_mji_product_inventory_units` — unit status flipped back to `in_stock`, `sold_date = NULL`
- [ ] Check `wp_mji_inventory_status_history` — `sold → in_stock` row added
- [ ] Check `wp_mji_payments` — one refund payment row, `transaction_type = refund`, correct amount
- [ ] Check `wp_mji_returns.subtotal` = sum of refunded line item prices (not `total − taxes`)
- [ ] POS Find page — refund appears on the invoice

### G. Refund — amount only (no line items)
- [ ] Issue a WooCommerce refund entering **only a dollar amount**, no line items ticked
- [ ] Check `wp_mji_returns` — row created, `subtotal` = refund amount (no items to sum, just shipping/amount)
- [ ] Check `wp_mji_return_items` — **empty** (no items to return)
- [ ] Check `wp_mji_product_inventory_units` — unit status **unchanged** (staff must update manually)
- [ ] Check `custom_log.txt` — "no line items specified" warning logged

### H. Refund — shipping included
- [ ] Issue a refund that includes the shipping amount
- [ ] Check `wp_mji_return_services` — one row linking to the original `mji_services` shipping record
- [ ] Check `wp_mji_returns.subtotal` includes the shipping refund amount

### I. Refund — payment method
- [ ] Issue a refund on a Stripe debit card order — `wp_mji_payments` refund row method = `debit`
- [ ] Issue a refund on a Visa order — method = `visa`

### J. Partial refund then second refund
- [ ] Place an order with two items
- [ ] Refund item 1 only
- [ ] Refund item 2 in a second WC refund
- [ ] Check each refund has its own `WRET-*` row in `wp_mji_returns`
- [ ] Check neither refund attempts to re-return item 1 (already-returned guard works)

### K. Order cancellation
- [ ] Place an order (status: `pending` or `on-hold`)
- [ ] Cancel it from WC admin
- [ ] Check `wp_mji_orders` — `status = 'cancelled'`
- [ ] Check `wp_mji_product_inventory_units` — unit status flipped back to `in_stock`
- [ ] Check `wp_mji_inventory_status_history` — `sold → in_stock` row added
- [ ] Confirm cancelling an already-`processing`/`completed` order is **blocked** in WC (no cancel button shown)

### L. Order deleted/trashed in WC admin
- [ ] Trash a WC order that has a matching MJI record
- [ ] Check `custom_log.txt` — "order trashed" warning logged
- [ ] Check staff notification email received (check `wp_mail` or mailhog)
- [ ] MJI records are **not** automatically deleted (staff must handle manually)

### M. Error cases — confirm staff email sent and no partial data
- [ ] Simulate "Online Store salesperson not found" (temporarily break `mji_get_online_salesperson_id`) — confirm staff email sent, no MJI order row created
- [ ] Simulate "customer could not be created" — confirm staff email, no MJI row
- [ ] Simulate a DB error mid-transaction (temporarily break a table name) — confirm ROLLBACK (no partial data), error logged
| N3 | 🟡 | Fresh install: location/salesperson dropdowns empty, POS unusable — seed routine needed after `mji_create_all_tables()` |
