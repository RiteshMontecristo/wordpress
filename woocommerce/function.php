<?php

require_once get_stylesheet_directory() . '/woocommerce/helper_functions.php';

add_filter('xmlrpc_enabled', '__return_false');

// Changing the length of the excerpt and their content
function my_excerpt_length($length)
{
    return 50;
}
add_filter('excerpt_length', 'my_excerpt_length', 999);

function mc_excerpt_more($more)
{
    if (!is_single()) {
        // $more = sprintf(
        //     '... <a class="read-more" href="%1$s">%2$s</a>',
        //     get_permalink(get_the_ID()),
        //     'Read More'
        // );
        $more = "...";
    }

    return $more;
}
add_filter('excerpt_more', 'mc_excerpt_more');

function add_cookie_manager_popup()
{
    require_once get_stylesheet_directory() . '/template-parts/cookie-manager.php';
}
add_action('wp_footer', 'add_cookie_manager_popup');

// Add Missing Alt Text Images menu item
function add_missing_alt_menu()
{
    add_menu_page(
        'Missing Alt Text Images',         // Page title
        'Missing Alt Text',                // Menu title
        'manage_options',                  // Capability
        'missing-alt-images',              // Menu slug
        'render_missing_alt_page',          // Function to render the page
        'dashicons-warning',               // Icon
        58                                 // Position (after Media)
    );
}
add_action('admin_menu', 'add_missing_alt_menu', 999);

// Add a sender email field to General Settings to customize the sender email address
function custom_email_settings_init()
{
    add_settings_section(
        'custom_email_section',
        __('Custom Email Settings', 'textdomain'),
        'custom_email_section_callback',
        'general'
    );

    add_settings_field(
        'custom_sender_email',
        __('Sender Email Address', 'textdomain'),
        'custom_sender_email_render',
        'general',
        'custom_email_section'
    );

    register_setting('general', 'custom_sender_email', array('sanitize_callback' => 'sanitize_email'));
}
add_action('admin_init', 'custom_email_settings_init');

function custom_email_section_callback()
{
    echo '<p>' . __('Select the email address used for outgoing emails.', 'textdomain') . '</p>';
}

function custom_sender_email_render()
{
    $email = get_option('custom_sender_email');
    echo "<input type='email' name='custom_sender_email' value='" . esc_attr($email) . "' class='regular-text' />";
}

function render_missing_alt_page()
{
    global $wpdb;

    // Query posts of type 'attachment' with image MIME types and no alt text
    $query = $wpdb->prepare("
        SELECT ID, post_title, guid
        FROM {$wpdb->posts}
        WHERE post_type = 'attachment'
        AND post_mime_type LIKE 'image/%'
        AND ID NOT IN (
            SELECT DISTINCT(post_id)
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_wp_attachment_image_alt'
            AND meta_value IS NOT NULL
            AND meta_value != ''
        )
    ");

    $images = $wpdb->get_results($query);

?>
    <div class="wrap">
        <h1>Images Missing Alt Text</h1>
        <p>List of images uploaded to media library that are missing alt text.</p>

        <?php if (!empty($images)) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>URL</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($images as $image) : ?>
                        <tr>
                            <td><?php echo esc_html($image->ID); ?></td>
                            <td><?php echo esc_html($image->post_title); ?></td>
                            <td><code><?php echo esc_url($image->guid); ?></code></td>
                            <td><a href="<?php echo esc_url(admin_url('post.php?post=' . $image->ID . '&action=edit')); ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><em>No images found missing alt text.</em></p>
        <?php endif; ?>
    </div>
<?php
}

// For the youtube videos
function responsive_video($atts)
{
    // Grabbing the attributes values
    $atts = shortcode_atts(
        array(
            'embed_code' => '',
        ),
        $atts,
        'response_video'
    );

    $maxres_url = "https://img.youtube.com/vi/{$atts['embed_code']}/maxresdefault.jpg";
    // High-quality thumbnail URL as fallback
    $hq_url = "https://img.youtube.com/vi/{$atts['embed_code']}/hqdefault.jpg";

    // Check if the max resolution thumbnail exists
    $maxres_headers = @get_headers($maxres_url);


    // If max resolution thumbnail exists, return it, otherwise use hqdefault
    if ($maxres_headers && strpos($maxres_headers[0], '200') !== false) {
        $imgUrl = $maxres_url;
    } else {
        $imgUrl = $hq_url;
    }

    ob_start();
?>

    <div class="grid">
        <div class="youtube-video-container" id="youtubeVideo" data-video-id="<?php echo esc_attr($atts['embed_code']); ?>">

            <div class="video-thumbnail">
                <img src="<?php echo $imgUrl ?>" alt="Video Thumbnail" style="width: 100%;">

                <button class="video-play-btn">
                    <svg version="1.1" id="Calque_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                        viewBox="0 0 15 15" style="enable-background:new 0 0 15 15;" xml:space="preserve">
                        <path id="icons_x2F_play" class="st0" d="M13.6,7.5L1.4,15V0L13.6,7.5z" />
                    </svg>

                </button>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('facade_video', 'responsive_video');

// Removing the sidebar
function remove_sidebar_from_non_category_pages()
{
    if (!is_tax('product_cat')) { // Checks if it’s NOT a product category page
        remove_action('storefront_sidebar', 'storefront_get_sidebar', 10);
    }
}
add_action('wp', 'remove_sidebar_from_non_category_pages');

add_filter('automatic_updater_disabled', '__return_false');

function sync_product_skus($post_id, $post, $update)
{

    if ($post->post_type !== 'product') {
        return;
    }

    global $wpdb;

    // Delete existing entries for this product
    $wpdb->delete('wp_product_skus', array('product_id' => $post_id));

    // Fetch the new SKU data
    $skus = get_post_meta($post_id, 'new_repeatable_sku_field', true);
    if (!empty($skus)) {
        foreach ($skus as $sku_data) {
            $sku_text = isset($sku_data['sku_text']) ? $sku_data['sku_text'] : '';
            $sku_variation = isset($sku_data['sku_variation']) ? $sku_data['sku_variation'] : '';

            // Insert the data into the new table
            $wpdb->insert('wp_product_skus', array(
                'product_id' => $post_id,
                'sku_text' => $sku_text,
                'sku_variation' => $sku_variation,
            ));
        }
    }
}

add_action('save_post', 'sync_product_skus', 99, 3);

// START OF ALL PRODUCTS CATEGORY PAGES
// ======================================

require_once get_stylesheet_directory() . '/woocommerce/product_category.php';

// START OF SINGLE PRODUCT PAGE
// ======================================

require_once get_stylesheet_directory() . '/woocommerce/product.php';

// Blog pages
function load_more_blogs()
{

    $page = isset($_GET['page']) ? $_GET['page'] : 0;

    $blog_post_per_page = 6;
    $offset = $blog_post_per_page * $page + 1;
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => $blog_post_per_page,
        'offset' => $offset,
        'post_status' => 'publish'
    );
    $blog_query = new WP_Query($args);
    $total_blog_posts = $blog_query->found_posts;
    $remaining_blog_posts = $total_blog_posts - $offset - $blog_post_per_page;
    $display_load_button = $remaining_blog_posts > 0 ? true : false;

    ob_start();
    if ($blog_query->have_posts()) {

        while ($blog_query->have_posts()) {
            $blog_query->the_post();
    ?>
            <article class="blog-article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <div class="blog-content">
                    <!-- Display Post Title -->
                    <h2 class="post-title">
                        <?php the_title(); ?>
                    </h2>
                    <!-- Display Excerpt or Content -->
                    <div class="post-excerpt">
                        <p>
                            <?php echo wp_trim_words(get_the_excerpt(), 50);
                            ?>
                        </p>
                    </div>

                    <a href="<?php echo get_permalink() ?>" class="btn">Read more</a>
                </div>

                <!-- Display Featured Image -->
                <?php if (has_post_thumbnail()): ?>
                    <div class="post-thumbnail blog-thumbnail">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail('medium');
                            ?>
                        </a>
                    </div>
                <?php endif; ?>

            </article>
<?php
        }
    }

    $html = ob_get_clean();

    wp_send_json(array('html' => $html, 'display_load_button' => $display_load_button));

    die();
}

add_action('wp_ajax_load_more_blogs', 'load_more_blogs'); // For logged-in users
add_action('wp_ajax_nopriv_load_more_blogs', 'load_more_blogs'); // For non-logged-in user

// remove_action("after_setup_theme", "storefront_single_post_top");
remove_action("after_setup_theme", "storefront_single_post_bottom");

// Take all the /shop to our homepage
function redirect_shop_to_home()
{
    if (strpos($_SERVER['REQUEST_URI'], '/shop') === 0) { // Checks if URL starts with /shop
        wp_redirect(home_url()); // Redirect to homepage
        exit;
    }
    if (is_category('blog')) { // Checks if the current category is 'blog'
        wp_redirect(home_url('/blog')); // Redirect to the blog page URL
        exit;
    }
}
add_action('template_redirect', 'redirect_shop_to_home');

// CONTACT PAGES

function contact_us()
{
    // Verify the nonce
    if (!isset($_POST['contact_us_nonce']) || !wp_verify_nonce($_POST['contact_us_nonce'], 'contact_us_nonce')) {
        wp_send_json_error(array('message' => 'Server error while trying to send email, Please try again later.'));
        return;
    }

    $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    $firstName = isset($_POST['firstName']) ? sanitize_text_field($_POST['firstName']) : '';
    $lastName = isset($_POST['lastName']) ? sanitize_text_field($_POST['lastName']) : '';
    $preferredContact = isset($_POST['preferredContact']) ? sanitize_text_field($_POST['preferredContact']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $customerMessage = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
    $terms = isset($_POST['terms']) ? sanitize_textarea_field($_POST['terms']) : '';
    $captcha_token = isset($_POST['g-recaptcha-response']) ? sanitize_textarea_field($_POST['g-recaptcha-response']) : '';

    // Validation checks
    if (empty($firstName)) {
        $errors[] = "First name is required.";
    }
    if (empty($lastName)) {
        $errors[] = "Last name is required.";
    }
    if ($preferredContact == 'email' && (empty($email) || !is_email($email))) {
        $errors[] = "A valid email address is required.";
    }
    if ($preferredContact === 'phone' && empty($phone)) {
        $errors[] = "Phone number is required.";
    }
    if (empty($customerMessage)) {
        $errors[] = "Message is required.";
    }
    if (empty($terms)) {
        $errors[] = "terms is required.";
    }
    if (empty($captcha_token)) {
        $errors[] = "captcha_token is required.";
    }

    // If there are errors, send them back
    if (!empty($errors)) {
        wp_send_json_error(['errors' => $errors]);
        return;
    }

    $result = captcha_verify($captcha_token);

    if ($result['success'] && $result['action'] === 'contact_us' && $result['score'] >= 0.5) {
        $to = get_option('custom_sender_email', get_option('admin_email'));

        $subject = 'Contact Form Submission';
        $message = "Customer reached out to us with the following information:\r\n\r\n";
        $message .= "Name: $title $firstName $lastName\r\n";
        $message .= "Preferred Contact: $preferredContact\r\n";

        if (!empty($email)) {
            $message .= "Email: $email\r\n";
        }
        if (!empty($phone)) {
            $message .= "Phone: $phone\r\n";
        }

        $message .= "\r\nMessage:\r\n$customerMessage\r\n\r\n";
        $message .= "--\r\n";
        $message .= "This message was sent via the Montecristo Jewellers contact form.";
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        $mail_sent = wp_mail($to, $subject, $message, $headers);

        if ($mail_sent) {

            if ($preferredContact == "phone") {
                wp_send_json_success(array('message' => 'Email sent successfully.'));
            } else {
                $to = $email;
                $subject = 'Contact Form Submission';
                $message = "Dear $firstName $lastName,\r\n\r\n";
                $message .= "Thank you for reaching out to us. One of our agents will get in touch with you via $preferredContact as soon as possible.\r\n\r\n";
                $message .= "Best regards,\r\n";
                $message .= "Montecristo Jewellers";


                $mail_customer = wp_mail($to, $subject, $message, $headers);

                if ($mail_customer) {
                    wp_send_json_success(array('message' => 'Email sent successfully.'));
                } else {
                    // Email failed to send
                    error_log("The user {$email} tried to reach us in rolex but there was a server error.");
                    wp_send_json_error(array('message' => 'Server error while trying to send email, Please try again later.'));
                }
            }
        } else {
            // Email failed to send
            wp_send_json_error(array('message' => 'Server error while trying to send an email, Please try again later.'));
        }
    } else {
        wp_send_json_error(array('message' => 'Server error while trying to send an email, Please try again later.'));
    }
}

add_action('wp_ajax_contact_us', 'contact_us'); // For logged-in users
add_action('wp_ajax_nopriv_contact_us', 'contact_us'); // For non-logged-in user

function appointment()
{
    // Verify the nonce
    if (!isset($_POST['appointment_nonce']) || !wp_verify_nonce($_POST['appointment_nonce'], 'appointment_nonce')) {
        wp_send_json_error(['error' => 'Invalid nonce.']);
        return;
    }

    $firstName = isset($_POST['firstName']) ? sanitize_text_field($_POST['firstName']) : '';
    $lastName = isset($_POST['lastName']) ? sanitize_text_field($_POST['lastName']) : '';
    $preferredContact = isset($_POST['preferredContact']) ? sanitize_text_field($_POST['preferredContact']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $store = isset($_POST['store']) ? sanitize_text_field($_POST['store']) : '';
    $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
    $time = isset($_POST['time']) ? sanitize_text_field($_POST['time']) : '';
    $img = isset($_FILES['img']) ? $_FILES['img'] : '';
    $attachments = [];


    // Validation checks
    if (empty($firstName)) {
        $errors[] = "First name is required.";
    }
    if (empty($lastName)) {
        $errors[] = "Last name is required.";
    }
    if ($preferredContact == 'email' && (empty($email) || !is_email($email))) {
        $errors[] = "A valid email address is required.";
    }
    if ($preferredContact === 'phone' && empty($phone)) {
        $errors[] = "Phone number is required.";
    }
    if (empty($store)) {
        $errors[] = "Store is required.";
    }
    if (empty($date)) {
        $errors[] = "Date is required.";
    }
    if (empty($time)) {
        $errors[] = "Time is required.";
    }
    if (!empty($img)) {
        foreach ($_FILES['img']['tmp_name'] as $key => $tmp_name) {
            $file_path = $tmp_name;
            $file_name = $_FILES['img']['name'][$key];

            // Define the target path in the uploads directory
            $target_path = ABSPATH . 'wp-content/uploads/' . basename($file_name);

            // Move the uploaded file to the uploads directory
            if (move_uploaded_file($file_path, $target_path)) {
                // $attachments[] = $public_url;
                $attachments[] = ABSPATH . 'wp-content/uploads/' . basename($file_name);
            } else {
                echo "Error uploading file: " . $file_name . "<br>";
            }
        }
    }

    // If there are errors, send them back
    if (!empty($errors)) {
        wp_send_json_error(['errors' => $errors]);
        return;
    }

    $to = get_option('admin_email');
    $subject = 'Appointment Form Submission';
    $message_admin  = "Customer reached out to book an appointment with us with the following information:\r\n\r\n";
    $message_admin .= "Name: $firstName $lastName\r\n";
    $message_admin .= "Preferred Contact: $preferredContact\r\n";
    $message_admin .= "Email: $email\r\n";
    $message_admin .= "Phone: $phone\r\n";
    $message_admin .= "Store: $store\r\n";
    $message_admin .= "Date: $date\r\n";
    $message_admin .= "Time: $time\r\n";
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $mail_sent = wp_mail($to, $subject, $message_admin, $headers, $attachments);

    if ($mail_sent) {
        if (!empty($email)) {
            $to = $email;
            $subject = 'Appointment Form Submission';
            $message_customer  = "Dear $firstName $lastName,\r\n\r\n";
            $message_customer .= "Thank you for reaching out to us. One of our agents will get in touch with you via $preferredContact as soon as possible.\r\n\r\n";
            $message_customer .= "Best regards,\r\n";
            $message_customer .= "Montecristo Jewellers";

            $mail_sent = wp_mail($to, $subject, $message_customer, $headers);
        }
        // Delete the files after sending
        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                unlink($attachment);
            }
        }
        wp_send_json_success(array('message' => 'Email sent successfully.'));
    } else {
        // Email failed to send
        error_log("The user {$email} tried to reach us in rolex but there was a server error.");
        wp_send_json_error(array('message' => 'Server error while trying to send email, Please try again later.'));
    }
}

add_action('wp_ajax_appointment', 'appointment'); // For logged-in users
add_action('wp_ajax_nopriv_appointment', 'appointment'); // For non-logged-in user

// CUSTOMIZE PAGE
function customize_contact_us()
{
    // Verify the nonce
    if (!isset($_POST['customize_nonce']) || !wp_verify_nonce($_POST['customize_nonce'], 'customize_nonce')) {
        wp_send_json_error(array('message' => 'Server error while trying to send email, Please try again later.'));
        return;
    }

    $firstName = isset($_POST['firstName']) ? sanitize_text_field($_POST['firstName']) : '';
    $lastName = isset($_POST['lastName']) ? sanitize_text_field($_POST['lastName']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $jewelleryPiece = isset($_POST['jewelleryPiece']) ? sanitize_text_field($_POST['jewelleryPiece']) : '';
    $inspiration = isset($_POST['inspiration']) ? sanitize_textarea_field($_POST['inspiration']) : '';
    $montecristoPiece = isset($_POST['montecristoPiece']) ? sanitize_text_field($_POST['montecristoPiece']) : '';
    $material = isset($_POST['material']) ? sanitize_text_field($_POST['material']) : '';
    $gemstone = isset($_POST['gemstone']) ? sanitize_text_field($_POST['gemstone']) : '';
    $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    $captcha_token = isset($_POST['g-recaptcha-response']) ? sanitize_textarea_field($_POST['g-recaptcha-response']) : '';

    // Validation checks
    if (empty($firstName)) {
        $errors[] = "First name is required.";
    }
    if (empty($lastName)) {
        $errors[] = "Last name is required.";
    }
    if ((empty($email) || !is_email($email))) {
        $errors[] = "A valid email address is required.";
    }
    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }
    if (empty($jewelleryPiece)) {
        $errors[] = "Jewellery Piece is required.";
    }
    if (empty($inspiration)) {
        $errors[] = "Inspiration is required.";
    }
    if (empty($captcha_token)) {
        $errors[] = "captcha_token is required.";
    }

    // If there are errors, send them back
    if (!empty($errors)) {
        wp_send_json_error(['errors' => $errors]);
        return;
    }

    $result = captcha_verify($captcha_token);

    if ($result['success'] && $result['action'] === 'customize_us' && $result['score'] >= 0.5) {

        $to = get_option('custom_sender_email', get_option('admin_email'));
        $subject = 'Cusotmize Jewellery Form Submission';
        $message = "Customer reached out to customize jewellery with the following information:\r\n\r\n";
        $message .= "Name: $title $firstName $lastName\r\n";
        $message .= "Email: $email\r\n";
        $message .= "Phone: $phone\r\n";
        $message .= "Montecristo Piece: $montecristoPiece\r\n";
        $message .= "Material: $material\r\n";
        $message .= "Gemstone: $gemstone\r\n";
        $message .= "Design Inspiration: $inspiration\r\n";
        $headers = array('Content-Type: text/plain; charset=UTF-8');

        $mail_sent = wp_mail($to, $subject, $message, $headers);
        if ($mail_sent) {

            $to = $email;
            $subject = 'Customize Jewellery Form Submission';

            $message = "Dear $title $firstName $lastName,\r\n\r\n";
            $message .= "Thank you for reaching out to us to create your customized jewellery. One of our agents will get in touch with you as soon as possible.\r\n\r\n";
            $message .= "Best regards,\r\n";
            $message .= "Montecristo Jewellers";

            $customerEmail = wp_mail($to, $subject, $message, $headers);

            if ($customerEmail) {
                wp_send_json_success(array('message' => 'Email sent successfully.'));
            } else {
                // Email failed to send
                wp_send_json_error(array('message' => 'Server error while trying to send email, Please try again later.'));
            }
        } else {
            // Email failed to send
            error_log("The user {$email} tried to reach us in rolex but there was a server error.");
            wp_send_json_error(array('message' => 'Server error while trying to send email, Please try again later.'));
        }
    } else {
        wp_send_json_error(array('message' => 'Server error while trying to send email, Please try again later.'));
    }
}

add_action('wp_ajax_customize_contact_us', 'customize_contact_us'); // For logged-in users
add_action('wp_ajax_nopriv_customize_contact_us', 'customize_contact_us'); // For non-logged-in user

// WOOCOMMERCE ACCOUNT PAGES

// removing download menu in the sidebar
add_filter('woocommerce_account_menu_items', 'remove_my_account_links');
function remove_my_account_links($menu_links)
{
    unset($menu_links['downloads']); // Remove the Downloads link
    return $menu_links;
}

add_filter('woocommerce_account_menu_items', 'add_favourite_link');
function add_favourite_link($menu_links)
{
    // Reorder the menu items
    $new_menu = [];
    foreach ($menu_links as $key => $label) {
        if ($key === 'dashboard') {
            $new_menu[$key] = $label;
            $new_menu['favourite'] = 'Favourite'; // Add "Favourites" after "Orders"
        } else {
            $new_menu[$key] = $label;
        }
    }
    return $new_menu;
}

add_action('woocommerce_account_favourite_endpoint', 'favourite_content');
function favourite_content()
{
    $user_id = get_current_user_id();
    $favourite = get_user_meta($user_id, 'wishlist', true) ?: [];
    echo '<h2>Favourite</h2>';

    if (count($favourite) > 0) {
        $query = new WC_Product_Query([
            'include' => $favourite,
            'limit' => -1,
        ]);
        $products = $query->get_products();

        // Container for favourite products
        echo '<div class="favourite-container">';

        foreach ($products as $product) {
            echo '<div class="favourite-item">';
            echo '<div class="product-info">';
            // Product image
            $image_url = wp_get_attachment_url($product->get_image_id());
            echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($product->get_name()) . '" title="' . esc_attr($product->get_name()) . '" />';


            echo '<div class="product-detail">';
            // Product categories
            $terms = wp_get_post_terms($product->get_id(), 'product_cat');
            if (!empty($terms)) {
                foreach ($terms as $term) {
                    // Check for child categories and exclude specific categories
                    $has_children = get_term_children($term->term_id, 'product_cat');
                    if ($has_children && !in_array($term->name, ['Watches', 'Jewellery', 'Designer'])) {
                        echo '<h3 class="brand">' . esc_html($term->name) . '</h3>';
                    }
                }
            }
            // Product name
            echo '<p class="product-name">' . esc_html($product->get_name()) . '</p>';
            echo '</div>'; // close product detail div
            echo '</div>'; // close product-info div

            echo '<div class="product-cta">';
            // Stock status
            if ($product->get_stock_status() === 'instock') {
                echo '
                    <form class="cart" method="post" action="' . esc_url(wc_get_cart_url()) . '">
                        <button type="submit" 
                                name="add-to-cart" 
                                value="' . esc_attr($product->get_id()) . '" 
                                class="add-to-cart single_add_to_cart_button btn">
                            Add to cart
                        </button>
                    </form>
                    ';
            } else {
                echo '<a href="' . esc_url($product->get_permalink()) . '" class="btn learn-more">Learn more</a>';
            }

            // Additional button with SVG icon
            echo '<button data-user="' . $user_id . '" data-product="' . $product->get_id() . '" class="remove-fav">';
            echo '<svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">';
            echo '<path d="M1 4.5H3M3 4.5H19M3 4.5V18.5C3 19.0304 3.21071 19.5391 3.58579 19.9142C3.96086 20.2893 4.46957 20.5 5 20.5H15C15.5304 20.5 16.0391 20.2893 16.4142 19.9142C16.7893 19.5391 17 19.0304 17 18.5V4.5M6 4.5V2.5C6 1.96957 6.21071 1.46086 6.58579 1.08579C6.96086 0.710714 7.46957 0.5 8 0.5H12C12.5304 0.5 13.0391 0.710714 13.4142 1.08579C13.7893 1.46086 14 1.96957 14 2.5V4.5M8 9.5V15.5M12 9.5V15.5" stroke="#555555" stroke-linecap="round" stroke-linejoin="round" />';
            echo '</svg>';
            echo '</button>';
            echo "</div>"; // close product-cta div

            echo '</div>'; // Close favourite-item div
        }

        echo '
        <div class="popup">
            <p>Are you sure you want to remove it from favourite?<?p>
            <div>
                <button id="yes-btn" class="btn btn-red">Yes</button>
                <button id="no-btn" class="btn">No</button>
            </div>
        </div>
        ';

        echo '</div>'; // Close favourite-container div
    } else {
        // Message when no favourites are found
        echo '<p class="no-favourites">No Favourites.</p>';
    }
}

add_action('init', 'add_favourite_endpoint');
function add_favourite_endpoint()
{
    add_rewrite_endpoint('favourite', EP_ROOT | EP_PAGES);
}

// permalink flush when changing to this theme
function flush_rewrite_rules_on_activation()
{
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'flush_rewrite_rules_on_activation');

// NEWSLETTER SUBSCRIBE FORM
add_action('after_setup_theme', 'handle_newsletter_subscribe_form');

function handle_newsletter_subscribe_form()
{
    if (!isset($_POST['newsletter_subscribe'])) {
        return;
    }

    $email = sanitize_email($_POST['newsletter_email']);

    if (!is_email($email)) {
        custom_log('Invalid email address: ' . $email);
        wp_redirect(add_query_arg('subscribed', '0', wp_get_referer()));
        exit;
    }

    if (!class_exists(\MailPoet\API\API::class)) {
        custom_log('MailPoet API class not found.');
        return;
    }

    $mailpoet_api = \MailPoet\API\API::MP('v1');

    try {
        $list = $mailpoet_api->getLists(['name' => 'Newsletter mailing list'])[0] ?? null;

        if (!$list) {
            custom_log('MailPoet list not found.');
            return;
        }
        $list_id = $list['id'];

        try {
            $get_subscriber = $mailpoet_api->getSubscriber($email);
        } catch (Exception $e) {
            $get_subscriber = null;
        }

        if (!$get_subscriber) {
            $mailpoet_api->addSubscriber([
                'email' => $email,
            ], array($list_id));
        } else {
            try {
                $mailpoet_api->subscribeToList($email, $list_id);
            } catch (Exception $e) {
                custom_log('MailPoet error: ' . $e->getMessage());
                wp_redirect(add_query_arg('subscribed', '0', wp_get_referer()));
                exit;
            }
        }

        wp_redirect(add_query_arg('subscribed', '1', wp_get_referer()));
        exit;
    } catch (Exception $e) {
        custom_log('MailPoet error: ' . $e);
        custom_log('MailPoet error: ' . $e->getMessage());
        wp_redirect(add_query_arg('subscribed', '0', wp_get_referer()));
        exit;
    }
}

// TESTING AND WORKIGN ON INVENTORY MANAGMENT
function sendPosData()
{
    $message = '';
    if (isset($_POST['products']) && !empty($_POST['products'])) {
        $products = $_POST['products'];
        foreach ($products as $product) {
            // splits into product_id, sku_text, variation_product_id
            $split_product = explode(" ", $product);
            $split_product_len = count($split_product);

            // If its vairable product
            if ($split_product_len === 3) {
                // reduce the product quantity
                $reduced = reduce_product_quantity($split_product[2]);

                // If wasnt able to reduce the quantity, save the message so we can inform someone to manually reduce it
                if (!$reduced["success"]) {
                    $message .= "Was not able to reduce product quantity for SKU: " . $split_product[1];
                }

                // remove the sku
                $sku_removed = remove_sku($split_product);

                // If wasnt able to remove the sku, save the message so we can inform someone to manually remove it
                if (!$sku_removed["success"]) {
                    $message .= "Was not able to remove product SKU: " . $split_product[1];
                }
            }
            // if its normal product
            else if ($split_product_len === 2) {

                // reduce the product quantity
                $reduced = reduce_product_quantity($split_product[0]);

                // If wasnt able to reduce the quantity, save the message so we can inform someone.
                if (!$reduced["success"]) {
                    $message .= "Was not able to reduce product quantity for SKU: " . $split_product[1];
                }

                // remove the sku
                $sku_removed = remove_sku($split_product);

                // If wasnt able to remove the sku, save the message so we can inform someone to manually remove it
                if (!$sku_removed["success"]) {
                    $message .= "Was not able to remove product SKU: " . $split_product[1];
                }
            }
            // if no sku number is there 
            else {
                // reduce the product quantity
                $reduced = reduce_product_quantity($split_product[0]);

                // If wasnt able to reduce the quantity, save the message so we can inform someone.
                if (!$reduced["success"]) {
                    $message .= "Was not able to reduce product quantity";
                }

                $message .= "This product didn't have any SKU option, Please look into this";
            }
        }
    }
    echo $message;
    wp_send_json(array(" Testssss" => "TEST"));
}

add_action('wp_ajax_sendPosData', 'sendPosData'); // For logged-in users

// To log issues when i cant echo out in the frontend
function custom_log($message)
{
    $log_file = WP_CONTENT_DIR . '/custom_logs/custom_log.txt'; // Path to your log file

    // Ensure the directory exists
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }

    if (is_array($message) || is_object($message)) {
        $message = print_r($message, true); // Use print_r for readability
    } else {
        $message = (string) $message; // Convert scalar values to string
    }
    // Prepare the message with the current date and time
    $message = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;

    // Write the message to the log file
    error_log($message, 3, $log_file);  // 3 is the "append" flag
}

// Footer Menu 
class Walker_Nav_Menu_As_H2 extends Walker_Nav_Menu
{
    function start_el(&$output, $item, $depth = 0, $args = [], $id = 0)
    {
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item));

        $output .= '<li class="' . esc_attr($class_names) . '">';

        // Check if the item is a custom link with URL "#"
        if ($item->url == '#' && $depth === 0) {
            $output .= '<h2>' . esc_html($item->title) . '</h2>';
        } else {
            $attributes = '';
            $attributes .= !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
            $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
            $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
            $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';

            $output .= '<a' . $attributes . '>';
            $output .= esc_html($item->title);
            $output .= '</a>';
        }
    }

    function end_el(&$output, $item, $depth = 0, $args = [])
    {
        $output .= "</li>\n";
    }
}

// Footer menus
function footer_register_menus()
{
    register_nav_menus(
        array(
            'footer-menu' => __('Footer Menu'),
        )
    );
}
add_action('init', 'footer_register_menus');

// Use the ACF description for meta description in categories instead of normal description
add_filter('rank_math/frontend/description', function ($description) {
    if (is_category() || is_tax()) {
        $term = get_queried_object();

        // Get ACF field value
        $acf_desc = get_field('description', $term->taxonomy . '_' . $term->term_id);

        // If ACF description exists, use it instead of default
        if ($acf_desc) {
            return esc_html($acf_desc);
        }
    }

    return $description;
});

add_filter('rank_math/frontend/title', function ($title) {
    if (is_category() || is_tax()) {
        $term = get_queried_object();
        $acf_title = get_field('title', $term->taxonomy . '_' . $term->term_id);

        if ($acf_title) {
            return esc_html($acf_title);
        }
    }

    return $title;
});

function add_meta_keywords_tag()
{
    if (! function_exists('rank_math')) {
        return;
    }

    $keyword = '';

    // For singular posts/pages
    if (is_singular()) {
        $keyword = get_post_meta(get_the_ID(), 'rank_math_focus_keyword', true);
    }

    if (! empty($keyword)) {
        echo '<meta name="keywords" content="' . esc_attr($keyword) . '">' . "\n";
    }
}
add_action('wp_head', 'add_meta_keywords_tag');
// add_action('template_redirect', 'redirect_brands');

// function redirect_brands() {
//     // Get current URL path
//     $request_uri = trim($_SERVER['REQUEST_URI'], '/');
//     custom_log('Request URI: ' . $request_uri);

//     // Match /brands/{slug}
//     if (preg_match('#^brands/([^/]+)$#', $request_uri, $matches)) {

//         custom_log($matches);
//         $slug = $matches[1];

//         // Build target URL
//         $target_url = home_url('/designer/' . $slug);

//         // Do the redirect
//         wp_redirect($target_url, 301);
//         exit;
//     }
// }