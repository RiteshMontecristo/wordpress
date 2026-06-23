<?php

/**
 * Returns the list of ISO country codes a product may be sold to.
 * Empty array = no restriction (worldwide).
 * Checks product-level override first; falls back to brand default.
 */
function mji_get_product_allowed_countries(int $product_id): array {
    $override = get_post_meta($product_id, 'mji_country_override', true) ?: 'default';

    // Sentinel value — callers must check this separately via mji_is_product_not_online().
    if ($override === 'not_online') return [];

    if ($override === 'worldwide') return [];

    if ($override === 'specific') {
        $countries = get_post_meta($product_id, 'mji_product_allowed_countries', true);
        return is_array($countries) ? array_values(array_filter($countries)) : [];
    }

    // 'default' — inherit from brand term
    $brand_term_id = (int) get_post_meta($product_id, 'rank_math_primary_product_brand', true);
    if (!$brand_term_id) return [];

    $countries = get_term_meta($brand_term_id, 'mji_brand_allowed_countries', true);
    return is_array($countries) ? array_values(array_filter($countries)) : [];
}

/**
 * Returns true if the product has been explicitly marked "not available online"
 * at the individual product level, regardless of its brand setting.
 */
function mji_is_product_not_online(int $product_id): bool {
    return get_post_meta($product_id, 'mji_country_override', true) === 'not_online';
}

function toggleFavourite()
{
    if (isset($_POST["productId"]) && isset($_POST["favourite"])) {
        check_ajax_referer('toggle_favourite_nonce', 'nonce');
        $product_id = absint($_POST["productId"]);
        $user_id = get_current_user_id();
        $favourite = sanitize_text_field($_POST["favourite"]);

        $wishlist = get_user_meta($user_id, 'wishlist', true) ?: [];
        if ($favourite === "false") {
            $wishlist[] = $product_id;
        } else {
            $wishlist = array_diff($wishlist, [$product_id]);
        }
        update_user_meta($user_id, 'wishlist', $wishlist);
        wp_send_json_success(["wishlist" => $wishlist]);
    } else {
        wp_send_json_error(["error" => "Product ID required"]);
    }
}
add_action('wp_ajax_toggleFavourite', 'toggleFavourite'); // For logged-in users

function mji_check_rate_limit(string $action, int $limit = 10): bool
{
    $ip     = WC_Geolocation::get_ip_address();
    // time returns the unix value and when divided by hours and floored the value only changes hourly so in 1 hour they can only run 10 times hence rate limiting the customer
    $bucket = (int) floor(time() / HOUR_IN_SECONDS);
    $key    = 'mji_rl_' . $action . '_' . md5($ip) . '_' . $bucket;
    $count  = (int) get_transient($key);
    if ($count >= $limit) {
        return false;
    }
    set_transient($key, $count + 1, HOUR_IN_SECONDS + 60);
    return true;
}

function captcha_verify($captcha_token)
{

    if (!defined('MJI_RECAPTCHA_SECRET')) {
        error_log('MJI: MJI_RECAPTCHA_SECRET is not configured in wp-config.php. reCAPTCHA skipped.');
        return ['success' => false, 'score' => 0];
    }

    $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
        'body' => [
            'secret'   => MJI_RECAPTCHA_SECRET,
            'response' => $captcha_token,
            'remoteip' => WC_Geolocation::get_ip_address(),
        ]
    ]);
    
    if (is_wp_error($response)) {
        error_log('MJI: reCAPTCHA request failed: ' . $response->get_error_message());
        return ['success' => false, 'score' => 0];
    }

    return json_decode(wp_remote_retrieve_body($response), true);
}
