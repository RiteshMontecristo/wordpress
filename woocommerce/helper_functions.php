<?php

function toggleFavourite()
{
    if (isset($_POST["productId"]) && isset($_POST["userId"]) && isset($_POST["favourite"])) {
        $product_id = $_POST["productId"];
        $user_id = $_POST["userId"];
        $favourite = $_POST["favourite"];

        $wishlist = get_user_meta($user_id, 'wishlist', true) ?: [];
        if ($favourite == "false") {
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
    $CAPTCHA_SERCRET = '6LeQyf4qAAAAALpHBHQW-1-NQfrZJNvVoGXZ8Ha1';

    // Prepare POST request to Google
    $url = 'https://www.google.com/recaptcha/api/siteverify';

    $data = [
        'secret' => $CAPTCHA_SERCRET,
        'response' => $captcha_token,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ]
    ];

    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    $result = json_decode($response, true);

    return $result;
}
