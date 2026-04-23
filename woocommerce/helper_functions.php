<?php

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


function reduce_product_quantity($product_id)
{
    try {
        $product = wc_get_product($product_id);
        if ($product->get_stock_quantity() > 0) {
            $product->set_stock_quantity($product->get_stock_quantity() - 1);
            $product->save();
            return array("success" => true);
        } else {
            return array("success" => false);
        }
    } catch (Exception $e) {
        return array("success" => false);
    }
}

function remove_sku($split_product)
{
    global $wpdb;
    try {
        $sku_data = get_post_meta($split_product[0], 'new_repeatable_sku_field', true);
        $sku_data_len = count($sku_data);

        $filtered_data = array_values(array_filter($sku_data, function ($var) use ($split_product) {
            return $var["sku_text"] !== $split_product[1];
        }));

        $filtered_data_len = count($filtered_data);

        if ($filtered_data_len === $sku_data_len) {
            return array("success" => false);
        }

        update_post_meta($split_product[0], 'new_repeatable_sku_field', $filtered_data);
        $wpdb->delete(
            'wp_product_skus',
            array(
                'sku_text' => $split_product[1],
            ),
            array(
                '%s', // Format specifier for the sku_text value
            )
        );

        return array("success" => true);
    } catch (Exception $e) {
        return array("success" => false);
    }
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
