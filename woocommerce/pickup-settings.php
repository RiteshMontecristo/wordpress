<?php
// Admin settings page for pickup location hours, open days, and holidays.
// Accessible at WooCommerce → Pickup Settings.

add_action('admin_menu', 'mji_register_pickup_settings_page');
function mji_register_pickup_settings_page(): void
{
    add_submenu_page(
        'woocommerce',
        'Pickup Settings',
        'Pickup Settings',
        'manage_woocommerce',
        'mji-pickup-settings',
        'mji_render_pickup_settings_page'
    );
}

function mji_pickup_defaults(): array
{
    return [
        'locations' => [
            [
                'name'      => 'Montecristo Richmond',
                'hours'     => 'Sun–Sat &nbsp;10:00 AM – 6:00 PM',
                'open_days' => [1, 2, 3, 4, 5, 6, 7],
            ],
            [
                'name'      => 'Montecristo Downtown',
                'hours'     => 'Tue–Sat &nbsp;10:30 AM – 5:00 PM',
                'open_days' => [2, 3, 4, 5, 6],
            ],
        ],
        'holidays' => [
            '2025-12-25', '2025-12-26',
            '2026-01-01', '2026-02-16', '2026-04-03', '2026-05-18',
            '2026-07-01', '2026-08-03', '2026-09-07', '2026-10-12',
            '2026-11-11', '2026-12-25', '2026-12-26',
        ],
    ];
}

function mji_get_pickup_settings(): array
{
    $saved = get_option('mji_pickup_settings');
    return $saved ?: mji_pickup_defaults();
}

// ── Save handler ──────────────────────────────────────────────────────────────

add_action('admin_post_mji_save_pickup_settings', 'mji_save_pickup_settings');
function mji_save_pickup_settings(): void
{
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Unauthorized.');
    }
    check_admin_referer('mji_pickup_settings_nonce');

    $raw_locations = $_POST['mji_locations'] ?? [];
    $locations = [];
    foreach ($raw_locations as $loc) {
        $name = sanitize_text_field($loc['name'] ?? '');
        if (!$name) continue;
        $open_days = array_map('absint', (array) ($loc['open_days'] ?? []));
        $open_days = array_values(array_filter($open_days, fn($d) => $d >= 1 && $d <= 7));
        $locations[] = [
            'name'      => $name,
            'hours'     => sanitize_text_field($loc['hours'] ?? ''),
            'open_days' => $open_days,
        ];
    }

    $raw_holidays = $_POST['mji_holidays'] ?? [];
    $holidays = [];
    foreach ($raw_holidays as $date) {
        $date = sanitize_text_field($date);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $holidays[] = $date;
        }
    }
    sort($holidays);

    update_option('mji_pickup_settings', [
        'locations' => $locations,
        'holidays'  => array_values(array_unique($holidays)),
    ]);

    wp_redirect(add_query_arg(['page' => 'mji-pickup-settings', 'updated' => '1'], admin_url('admin.php')));
    exit;
}

// ── Admin page render ─────────────────────────────────────────────────────────

function mji_render_pickup_settings_page(): void
{
    if (!current_user_can('manage_woocommerce')) return;

    $settings  = mji_get_pickup_settings();
    $locations = $settings['locations'];
    $holidays  = $settings['holidays'];
    $days      = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
    ?>
    <div class="wrap">
        <h1>Pickup Settings</h1>
        <p style="color:#666;max-width:640px">
            Controls the hours and "ready for pickup" date shown on the checkout page for each location.
            Changes take effect immediately — no cache to clear.
        </p>

        <?php if (!empty($_GET['updated'])): ?>
            <div class="notice notice-success is-dismissible"><p><strong>Settings saved.</strong></p></div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="mji_save_pickup_settings">
            <?php wp_nonce_field('mji_pickup_settings_nonce'); ?>

            <!-- ── Locations ─────────────────────────────────────────────── -->
            <h2>Pickup Locations</h2>
            <p style="color:#666;max-width:640px;margin-top:0">
                The <strong>Location Name</strong> must match exactly what WooCommerce shows in the checkout
                (WooCommerce → Settings → Shipping → Local Pickup).
            </p>

            <?php foreach ($locations as $i => $loc): ?>
                <div style="background:#fff;border:1px solid #ddd;border-radius:4px;padding:16px 20px;margin-bottom:20px;max-width:680px">
                    <h3 style="margin-top:0"><?php echo esc_html($loc['name']); ?></h3>
                    <table class="form-table" style="margin:0">
                        <tr>
                            <th style="width:160px"><label>Location Name</label></th>
                            <td>
                                <input type="text"
                                    name="mji_locations[<?php echo $i; ?>][name]"
                                    value="<?php echo esc_attr($loc['name']); ?>"
                                    class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th><label>Hours</label></th>
                            <td>
                                <input type="text"
                                    name="mji_locations[<?php echo $i; ?>][hours]"
                                    value="<?php echo esc_attr($loc['hours']); ?>"
                                    class="regular-text">
                                <p class="description">Shown in checkout. Example: <code>Tue–Sat 10:00 AM – 5:00 PM</code></p>
                            </td>
                        </tr>
                        <tr>
                            <th><label>Open Days</label></th>
                            <td style="padding-top:10px">
                                <?php foreach ($days as $num => $label): ?>
                                    <label style="margin-right:14px;white-space:nowrap">
                                        <input type="checkbox"
                                            name="mji_locations[<?php echo $i; ?>][open_days][]"
                                            value="<?php echo $num; ?>"
                                            <?php checked(in_array($num, $loc['open_days'], true)); ?>>
                                        <?php echo $label; ?>
                                    </label>
                                <?php endforeach; ?>
                                <p class="description" style="margin-top:6px">Uncheck days the store is closed. Used to calculate the next available pickup date.</p>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php endforeach; ?>

            <!-- ── Holidays ──────────────────────────────────────────────── -->
            <h2 style="margin-top:32px">
                Holidays
                <span style="font-size:13px;font-weight:400;color:#666">— all locations closed</span>
            </h2>
            <p style="color:#666;max-width:640px;margin-top:0">
                The next available pickup date skips these dates for every location.
                Remember to add the following year's holidays each December.
            </p>

            <div id="mji-holidays" style="max-width:420px">
                <?php foreach ($holidays as $date): ?>
                    <div class="mji-holiday-row" style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                        <input type="date" name="mji_holidays[]" value="<?php echo esc_attr($date); ?>"
                            style="padding:5px 8px;border:1px solid #ddd;border-radius:3px">
                        <button type="button" class="button mji-remove-holiday">Remove</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="button" id="mji-add-holiday" style="margin-top:4px">+ Add Holiday</button>

            <p style="margin-top:32px">
                <?php submit_button('Save Settings', 'primary', 'submit', false); ?>
            </p>
        </form>
    </div>

    <script>
    document.getElementById('mji-add-holiday').addEventListener('click', function () {
        var row = document.createElement('div');
        row.className = 'mji-holiday-row';
        row.style.cssText = 'display:flex;align-items:center;gap:8px;margin-bottom:8px';
        row.innerHTML = '<input type="date" name="mji_holidays[]" style="padding:5px 8px;border:1px solid #ddd;border-radius:3px"> '
                      + '<button type="button" class="button mji-remove-holiday">Remove</button>';
        document.getElementById('mji-holidays').appendChild(row);
        row.querySelector('input').focus();
    });

    document.getElementById('mji-holidays').addEventListener('click', function (e) {
        if (e.target.classList.contains('mji-remove-holiday')) {
            e.target.closest('.mji-holiday-row').remove();
        }
    });
    </script>
    <?php
}
