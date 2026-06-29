<?php

require_once get_stylesheet_directory() . '/woocommerce/helper_functions.php';

// Returns the direct child category of 'montecristo' that a product belongs to.
function get_montecristo_sub_brand(int $product_id = 0): string
{
    if (!$product_id) $product_id = get_the_ID();
    $parent = get_term_by('slug', 'montecristo', 'product_cat');
    if (!$parent) return '';
    $terms = wp_get_post_terms($product_id, 'product_cat');
    if (is_wp_error($terms)) return '';
    foreach ($terms as $term) {
        if ((int) $term->parent === (int) $parent->term_id) {
            return $term->name;
        }
    }
    return '';
}
require_once ABSPATH . 'wp-admin/includes/file.php';

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

add_filter('single_product_archive_thumbnail_size', function() {
    return 'large';
});

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
    $context = stream_context_create(['http' => ['timeout' => 3]]);
    $maxres_headers = get_headers($maxres_url, false, $context);

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


// Hide flat rate shipping when free shipping is available (orders over $1000)
add_filter('woocommerce_package_rates', 'mji_hide_flat_rate_if_free_shipping_available', 10, 2);
function mji_hide_flat_rate_if_free_shipping_available($rates, $package)
{
    $has_free = array_filter($rates, fn($rate) => $rate->method_id === 'free_shipping');
    if (!empty($has_free)) {
        return $has_free;
    }
    return $rates;
}

// Removing the sidebar
function remove_sidebar_from_non_category_pages()
{
    if (!is_tax('product_cat')) { // Checks if it’s NOT a product category page
        remove_action('storefront_sidebar', 'storefront_get_sidebar', 10);
    }
}
add_action('wp', 'remove_sidebar_from_non_category_pages');

add_filter('automatic_updater_disabled', '__return_false');

// START OF ALL PRODUCTS CATEGORY PAGES
// ======================================

require_once get_stylesheet_directory() . '/woocommerce/product_category.php';

// START OF SINGLE PRODUCT PAGE
// ======================================

require_once get_stylesheet_directory() . '/woocommerce/product.php';

// Blog pages
function load_more_blogs()
{

    $page = isset($_GET['page']) ? absint($_GET['page']) : 0;

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
    if (!mji_check_rate_limit('contact_us')) {
        wp_send_json_error(['message' => 'Too many requests. Please try again later.']);
        return;
    }
    // Honeypot
    if (! empty($_POST['website'])) {
        wp_send_json_success(['message' => 'Thank you for your message!']);
        exit;
    }

    $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    $firstName = isset($_POST['firstName']) ? sanitize_text_field($_POST['firstName']) : '';
    $lastName = isset($_POST['lastName']) ? sanitize_text_field($_POST['lastName']) : '';
    $preferredContact = isset($_POST['preferredContact']) ? sanitize_text_field($_POST['preferredContact']) : '';
    $preferredStore = isset($_POST['preferredStore']) ? sanitize_text_field($_POST['preferredStore']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $street = isset($_POST['street']) ? sanitize_text_field($_POST['street']) : '';
    $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
    $province = isset($_POST['province']) ? sanitize_text_field($_POST['province']) : '';
    $postalCode = isset($_POST['postalCode']) ? sanitize_text_field($_POST['postalCode']) : '';
    $country = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : '';
    $customerMessage = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
    $inquiryType = isset($_POST['inquiry_type']) && $_POST['inquiry_type'] === 'custom_jewellery' ? 'custom_jewellery' : 'contact';
    $product_url = isset($_POST['product_url']) ? esc_url_raw($_POST['product_url']) : '';
    $terms = isset($_POST['terms']) && $_POST['terms'] === '1';
    $captcha_token = isset($_POST['g-recaptcha-response']) ? sanitize_textarea_field($_POST['g-recaptcha-response']) : '';
    $errors = [];

    $store_labels = [
        'downtown' => 'Downtown Vancouver — 406 Hornby St, Vancouver, BC V6C 0A6',
        'richmond' => 'Richmond — 6551 Number 3 Rd Unit 1564, Richmond, BC V6Y 2B6',
    ];

    // Validation checks
    if (empty($firstName)) {
        $errors[] = "First name is required.";
    }
    if (empty($lastName)) {
        $errors[] = "Last name is required.";
    }
    if ($preferredContact === 'store' && empty($preferredStore)) {
        $errors[] = "Please select a store.";
    }
    if ($preferredContact == 'email' && (empty($email) || !is_email($email))) {
        $errors[] = "A valid email address is required.";
    }
    if ($preferredContact === 'phone' && empty($phone)) {
        $errors[] = "Phone number is required.";
    }
    if (empty($city)) {
        $errors[] = "City is required.";
    }
    if (empty($province)) {
        $errors[] = "Province is required.";
    }
    if (empty($country)) {
        $errors[] = "Country is required.";
    }
    if (empty($customerMessage)) {
        $errors[] = "Message is required.";
    }
    if (!$terms) {
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

    if ($result['success'] && $result['action'] === 'contact_us' && ($result['score'] ?? 0) >= 0.7) {
        $to = get_option('custom_sender_email', get_option('admin_email'));

        $subject = $inquiryType === 'custom_jewellery'
            ? 'Custom Jewellery Enquiry'
            : 'Contact Form Submission';
        $message = "Customer reached out to us with the following information:\r\n\r\n";
        $message .= "Enquiry Type: " . ($inquiryType === 'custom_jewellery' ? 'Handcraft Your Custom Jewellery' : 'General Contact') . "\r\n";
        $message .= "Name: $title $firstName $lastName\r\n";
        $message .= "Preferred Contact: $preferredContact\r\n";
        if ($preferredContact === 'store' && isset($store_labels[$preferredStore])) {
            $message .= "Preferred Store: " . $store_labels[$preferredStore] . "\r\n";
        }
        $message .= "Email: $email\r\n";
        $message .= "Phone: $phone\r\n";
        $message .= "Street: $street\r\n";
        $message .= "City: $city\r\n";
        $message .= "Postal code: $province\r\n";
        $message .= "Country: $country\r\n";

        if ($product_url) {
            $message .= "Product URL: $product_url\r\n";
        }
        $message .= "\r\nMessage:\r\n$customerMessage\r\n\r\n";
        $message .= "--\r\n";
        $message .= "This message was sent via the Montecristo Jewellers contact form.";
        $headers = array('Content-Type: text/plain; charset=UTF-8', "From: Montecristo Jewellers <$to>");
        $mail_sent = wp_mail($to, $subject, $message, $headers);

        if ($mail_sent) {

            if ($preferredContact === "phone") {
                wp_send_json_success(array('message' => 'Email sent successfully.'));
            } else {
                $to = $email;
                $subject = 'Contact Form Submission';
                $message = "Dear $firstName $lastName,\r\n\r\n";
                if ($preferredContact === 'store' && isset($store_labels[$preferredStore])) {
                    $message .= "Thank you for reaching out to us. We look forward to seeing you at our " . $store_labels[$preferredStore] . " location. One of our team members will be in touch to confirm.\r\n\r\n";
                } else {
                    $message .= "Thank you for reaching out to us. One of our agents will get in touch with you via $preferredContact as soon as possible.\r\n\r\n";
                }
                if ($inquiryType === 'custom_jewellery' && $product_url) {
                    $message .= "Product you enquired about: $product_url\r\n\r\n";
                }
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
    if (!mji_check_rate_limit('appointment')) {
        wp_send_json_error(['error' => 'Too many requests. Please try again later.']);
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
    $errors = [];

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
        $max_file_size = 2 * 1024 * 1024; // 2MB   
        $upload_overrides = [
            'test_form' => false, // we use this cause we are using custom forms and not the wordpress forms
            'mimes' => [
                'jpg|jpeg|jpe' => 'image/jpeg',
                'png'          => 'image/png',
                'gif'          => 'image/gif',
                'webp'         => 'image/webp',
            ],
        ];

        foreach ($_FILES['img']['name'] as $key => $value) {

            if ($_FILES['img']['error'][$key] !== UPLOAD_ERR_OK) {
                continue;
            }

            $file = [
                'name'     => sanitize_file_name($_FILES['img']['name'][$key]),
                'type'     => $_FILES['img']['type'][$key],
                'tmp_name' => $_FILES['img']['tmp_name'][$key], //tmp_name means temporary parth where the file is stored on the server
                'error'    => $_FILES['img']['error'][$key],
                'size'     => $_FILES['img']['size'][$key],
            ];

            if ($_FILES['img']['size'][$key] > $max_file_size) {
                $errors[] = "File {$file['name']} exceeds 2MB limit.";
                continue;
            }

            $movefile = wp_handle_upload($file, $upload_overrides);

            if ($movefile && !isset($movefile['error'])) {
                // returns the full server path - need the server path and not url as we need to delete it later and unlink doesnt delete with url
                $attachments[] = $movefile['file'];
            } else {
                error_log("Upload error: " . $movefile['error']);
                $errors[] = "File upload error:" . $movefile['error'];
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

// =============================================
// APPOINTMENT MODAL
// =============================================

add_action('wp_ajax_mji_appointment_modal', 'mji_appointment_modal');
add_action('wp_ajax_nopriv_mji_appointment_modal', 'mji_appointment_modal');
function mji_appointment_modal()
{
    if (!check_ajax_referer('mji_appointment_modal_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Security check failed.']);
    }

    if (!mji_check_rate_limit('appointment_modal')) {
        wp_send_json_error(['message' => 'Too many requests. Please try again later.']);
    }

    $firstName       = sanitize_text_field($_POST['firstName'] ?? '');
    $lastName        = sanitize_text_field($_POST['lastName'] ?? '');
    $appointmentType = sanitize_text_field($_POST['appointmentType'] ?? '');
    $store           = sanitize_text_field($_POST['store'] ?? '');
    $date            = sanitize_text_field($_POST['date'] ?? '');
    $time            = sanitize_text_field($_POST['time'] ?? '');
    $email           = sanitize_email($_POST['email'] ?? '');
    $phone           = sanitize_text_field($_POST['phone'] ?? '');
    $message         = sanitize_textarea_field($_POST['message'] ?? '');

    $errors = [];

    if (empty($firstName))  $errors[] = 'First name is required.';
    if (empty($lastName))   $errors[] = 'Last name is required.';
    if (!in_array($appointmentType, ['in-store', 'virtual'], true)) $errors[] = 'Please select an appointment type.';
    if ($appointmentType === 'in-store' && empty($store)) $errors[] = 'Please select a store.';
    if (empty($date))       $errors[] = 'Preferred date is required.';
    if (empty($time))       $errors[] = 'Preferred time is required.';
    if (!is_email($email))  $errors[] = 'A valid email address is required.';
    if (empty($phone))      $errors[] = 'Phone number is required.';

    if (!empty($errors)) {
        wp_send_json_error(['errors' => $errors]);
    }

    $type_label  = $appointmentType === 'in-store' ? 'In-Store' : 'Virtual';
    $store_names = [
        'downtown'  => 'Downtown Vancouver',
        'richmond'  => 'Richmond Centre',
        'metrotown' => 'Metropolis at Metrotown',
    ];
    $store_label = $appointmentType === 'in-store' ? ($store_names[$store] ?? $store) : '';

    $admin_body  = "A customer has requested a {$type_label} appointment:\r\n\r\n";
    $admin_body .= "Name: {$firstName} {$lastName}\r\n";
    $admin_body .= "Type: {$type_label}\r\n";
    if ($store_label) $admin_body .= "Store: {$store_label}\r\n";
    $admin_body .= "Date: {$date}\r\n";
    $admin_body .= "Time: {$time}\r\n";
    $admin_body .= "Email: {$email}\r\n";
    $admin_body .= "Phone: {$phone}\r\n";
    if ($message) $admin_body .= "Message: {$message}\r\n";

    $store_email = get_option('custom_sender_email', get_option('admin_email'));
    $headers     = [
        'Content-Type: text/plain; charset=UTF-8',
        "From: Montecristo Jewellers <{$store_email}>",
    ];

    $mail_sent = wp_mail($store_email, "Appointment Request - {$type_label}", $admin_body, $headers);

    if (!$mail_sent) {
        wp_send_json_error(['message' => 'Server error. Please try again later.']);
        return;
    }

    $cust_body  = "Dear {$firstName} {$lastName},\r\n\r\n";
    $cust_body .= "Thank you for requesting a {$type_label} appointment with Montecristo Jewellers. ";
    $cust_body .= "One of our team members will confirm your appointment details with you shortly.\r\n\r\n";
    if ($store_label) $cust_body .= "Store: {$store_label}\r\n";
    $cust_body .= "Requested Date: {$date}\r\nRequested Time: {$time}\r\n\r\n";
    $cust_body .= "Best regards,\r\nMontecristo Jewellers";

    wp_mail($email, 'Appointment Request Received - Montecristo Jewellers', $cust_body, $headers);
    wp_send_json_success();
}

// CUSTOMIZE PAGE
function customize_contact_us()
{
    // Honeypot
    if (! empty($_POST['website'])) {
        wp_send_json_success(['message' => 'Thank you for your message!']);
        exit;
    }

    if (!mji_check_rate_limit('customize_contact_us')) {
        wp_send_json_error(['error' => 'Too many requests. Please try again later.']);
        return;
    }

    // Verify the nonce
    if (!isset($_POST['customize_nonce']) || !wp_verify_nonce($_POST['customize_nonce'], 'customize_nonce')) {
        wp_send_json_error(array('message' => 'Server error while trying to send email, Please try again later.'));
        return;
    }

    $firstName = isset($_POST['firstName']) ? sanitize_text_field($_POST['firstName']) : '';
    $lastName = isset($_POST['lastName']) ? sanitize_text_field($_POST['lastName']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $preferredContact = isset($_POST['preferredContact']) ? sanitize_text_field($_POST['preferredContact']) : '';
    $preferredStore = isset($_POST['preferredStore']) ? sanitize_text_field($_POST['preferredStore']) : '';
    $jewelleryPiece = isset($_POST['jewelleryPiece']) ? sanitize_text_field($_POST['jewelleryPiece']) : '';
    $inspiration = isset($_POST['inspiration']) ? sanitize_textarea_field($_POST['inspiration']) : '';
    $montecristoPiece = isset($_POST['montecristoPiece']) ? sanitize_text_field($_POST['montecristoPiece']) : '';
    $product_url      = isset($_POST['product_url']) ? esc_url_raw($_POST['product_url']) : '';
    $material = isset($_POST['material']) ? sanitize_text_field($_POST['material']) : '';
    $gemstone = isset($_POST['gemstone']) ? sanitize_text_field($_POST['gemstone']) : '';
    $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    $captcha_token = isset($_POST['g-recaptcha-response']) ? sanitize_textarea_field($_POST['g-recaptcha-response']) : '';

    $store_labels = [
        'downtown' => 'Downtown Vancouver — 406 Hornby St, Vancouver, BC V6C 0A6',
        'richmond' => 'Richmond — 6551 Number 3 Rd Unit 1564, Richmond, BC V6Y 2B6',
    ];

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
    if (empty($preferredContact)) {
        $errors[] = "Preferred contact method is required.";
    }
    if ($preferredContact === 'store' && empty($preferredStore)) {
        $errors[] = "Please select a store.";
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

    $score = $result['score'] ?? 0;
    if ($score < 0.9) {
        custom_log("reCAPTCHA low score on customize form: {$score} — action: " . ($result['action'] ?? 'unknown'));
    }

    if ($result['success'] && $result['action'] === 'customize_contact_us' && $score >= 0.7) {

        $to = get_option('custom_sender_email', get_option('admin_email'));
        $subject = 'Customize Jewellery Form Submission';
        $message = "Customer reached out to customize jewellery with the following information:\r\n\r\n";
        $message .= "Name: $title $firstName $lastName\r\n";
        $message .= "Email: $email\r\n";
        $message .= "Phone: $phone\r\n";
        $message .= "Preferred Contact: $preferredContact\r\n";
        if ($preferredContact === 'store' && isset($store_labels[$preferredStore])) {
            $message .= "Preferred Store: " . $store_labels[$preferredStore] . "\r\n";
        }
        $message .= "Montecristo Piece: $montecristoPiece\r\n";
        if ($product_url) {
            $message .= "Product URL: $product_url\r\n";
        }
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
            if ($product_url) {
                $message .= "Product you enquired about: $product_url\r\n\r\n";
            }
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
            <p>Are you sure you want to remove it from favourite?</p>
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

    // Honeypot check
    if (!empty($_POST['newsletter_hp'])) {
        wp_redirect(add_query_arg('subscribed', '1', wp_get_referer()));
        exit;
    }

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

// To log issues when i cant echo out in the frontend
function custom_log($message)
{
    $log_file = WP_CONTENT_DIR . '/custom_logs/custom_log.txt';

    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0750, true);
    }

    if (is_array($message) || is_object($message)) {
        $message = print_r($message, true);
    } else {
        $message = (string) $message;
    }
    $message = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;

    error_log($message, 3, $log_file);
    chmod($log_file, 0640);
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

// WooCommerce Admin Analytics
// add_filter('woocommerce_admin_disabled', '__return_true');

// Woocoommerce marketing notices and analytics queries
add_filter('woocommerce_admin_features', function ($features) {
    return array_filter($features, function ($feature) {
        return !in_array($feature, ['marketing', 'analytics']);
    });
});

add_filter('woocommerce_allow_marketplace_suggestions', '__return_false');


// Run update checks only when opening the updates page
if (! isset($_GET['force-check'])) {
    remove_action('admin_init', 'wp_version_check');
    remove_action('admin_init', 'wp_update_plugins');
    remove_action('admin_init', 'wp_update_themes');
}

remove_filter('pre_http_request', 'hostinger_use_proxy_services', 10);

add_filter('pre_http_request', function ($pre, $args, $url) {
    // Only run in admin, skip AJAX/cron
    if (!is_admin() || wp_doing_ajax() || defined('DOING_CRON')) return $pre;

    // BLOCK: WooPayments incentives
    if (strpos($url, 'public-api.wordpress.com/wpcom/v2/wcpay/incentives') !== false) {
        return ['body' => '{"incentives":[]}', 'response' => ['code' => 204], 'headers' => []];
    }

    // BLOCK: MailPoet translation checks
    if (strpos($url, 'translate.wordpress.com/api/translations-updates/mailpoet') !== false) {
        return ['body' => '{}', 'response' => ['code' => 200], 'headers' => []];
    }

    // BLOCK: Hostinger proxy calls (you use wp-admin for updates)
    if (strpos($url, 'wpapi.hostinger.io') !== false) {
        // Return proper empty structure based on endpoint type
        if (strpos($url, 'plugins/update-check') !== false) {
            $body = '{"plugins":{},"no_update":{},"translations":[]}';
        } elseif (strpos($url, 'themes/update-check') !== false) {
            $body = '{"themes":{},"no_update":{},"translations":[]}';
        } elseif (strpos($url, 'core/version-check') !== false) {
            $body = '{"offers":[],"translations":[]}';
        } else {
            $body = '{}';
        }
        return ['body' => $body, 'response' => ['code' => 200], 'headers' => []];
    }

    // BLOCK: Jetpack sync (if unused)
    if (strpos($url, 'jetpack.wordpress.com/xmlrpc.php') !== false) {
        return ['body' => '', 'response' => ['code' => 200], 'headers' => []];
    }

    // ALLOW: Direct WordPress.org update checks (for wp-admin updates)
    if (
        strpos($url, 'api.wordpress.org') !== false &&
        (strpos($url, 'version-check') !== false || strpos($url, 'update-check') !== false)
    ) {
        return $pre;
    }

    // ALLOW: Critical license checks
    foreach (['connect.advancedcustomfields.com', 'update.wpallimport.com', 'bridge.mailpoet.com'] as $allow)
        if (strpos($url, $allow) !== false) return $pre;

    return $pre;
}, 9, 3);

// ─── Brand online-selling control ─────────────────────────────────────────────
// Checkbox on Products > Brands term page.
// Unchecking makes all products of that brand visible in the catalogue but not purchasable.

add_action('product_brand_add_form_fields', function () {
    ?>
    <div class="form-field">
        <label for="mji_sellable_online">Sellable online</label>
        <input type="checkbox" id="mji_sellable_online" name="mji_sellable_online" value="1">
        <p>Uncheck to hide the Add to Cart button for all products under this brand.</p>
    </div>
    <?php
});

add_action('product_brand_edit_form_fields', function (WP_Term $term) {
    $meta    = get_term_meta($term->term_id, 'mji_sellable_online', true);
    $checked = ($meta === '1') ? 'checked' : '';
    ?>
    <tr class="form-field">
        <th scope="row"><label for="mji_sellable_online">Sellable online</label></th>
        <td>
            <input type="checkbox" id="mji_sellable_online" name="mji_sellable_online" value="1" <?= $checked ?>>
            <p class="description">Uncheck to hide the Add to Cart button for all products under this brand.</p>
        </td>
    </tr>
    <?php
});

function mji_save_brand_sellable(int $term_id): void {
    if (!current_user_can('manage_woocommerce')) {
        return;
    }
    update_term_meta($term_id, 'mji_sellable_online', isset($_POST['mji_sellable_online']) ? '1' : '0');
}
add_action('created_product_brand', 'mji_save_brand_sellable');
add_action('edited_product_brand',  'mji_save_brand_sellable');

// ─── Brand country default ─────────────────────────────────────────────────────

add_action('admin_enqueue_scripts', function (): void {
    $screen = get_current_screen();
    if (!$screen || $screen->taxonomy !== 'product_brand') return;
    wp_enqueue_script('wc-enhanced-select');
    wp_enqueue_style('woocommerce_admin_styles');
    wp_add_inline_script('wc-enhanced-select', '
        jQuery(function ($) {
            $("#mji_brand_allowed_countries").select2({
                placeholder: "Search for a country…",
                width: "100%",
                allowClear: true
            });
        });
    ');
});

add_action('product_brand_add_form_fields', function () {
    $countries = WC()->countries->get_countries();
    ?>
    <div class="form-field">
        <label for="mji_brand_allowed_countries">Allowed Countries</label>
        <select id="mji_brand_allowed_countries" name="mji_brand_allowed_countries[]"
                multiple class="wc-enhanced-select" style="width:100%"
                data-placeholder="Search for a country…">
            <?php foreach ($countries as $code => $name) : ?>
                <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($name); ?></option>
            <?php endforeach; ?>
        </select>
        <p>Leave empty to allow all countries. Only applies when "Sellable online" is checked.</p>
    </div>
    <?php
}, 15);

add_action('product_brand_edit_form_fields', function (WP_Term $term) {
    $countries = WC()->countries->get_countries();
    $saved     = get_term_meta($term->term_id, 'mji_brand_allowed_countries', true) ?: [];
    ?>
    <tr class="form-field">
        <th scope="row"><label for="mji_brand_allowed_countries">Allowed Countries</label></th>
        <td>
            <select id="mji_brand_allowed_countries" name="mji_brand_allowed_countries[]"
                    multiple class="wc-enhanced-select" style="width:100%"
                    data-placeholder="Search for a country…">
                <?php foreach ($countries as $code => $name) : ?>
                    <option value="<?php echo esc_attr($code); ?>" <?php selected(in_array($code, (array) $saved, true)); ?>>
                        <?php echo esc_html($name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description">Leave empty to allow all countries. Only applies when "Sellable online" is checked.</p>
        </td>
    </tr>
    <?php
}, 15);

function mji_save_brand_countries(int $term_id): void {
    if (!current_user_can('manage_woocommerce')) return;
    $countries = isset($_POST['mji_brand_allowed_countries'])
        ? array_map('sanitize_text_field', (array) $_POST['mji_brand_allowed_countries'])
        : [];
    update_term_meta($term_id, 'mji_brand_allowed_countries', array_values(array_filter($countries)));
}
add_action('created_product_brand', 'mji_save_brand_countries');
add_action('edited_product_brand',  'mji_save_brand_countries');

// ─── Product-level country override ───────────────────────────────────────────

add_action('add_meta_boxes', function () {
    add_meta_box(
        'mji_country_availability',
        'Country Availability',
        'mji_render_country_metabox',
        'product',
        'normal',
        'default'
    );
});

function mji_render_country_metabox(WP_Post $post): void {
    $override  = get_post_meta($post->ID, 'mji_country_override', true) ?: 'default';
    $saved     = (array) (get_post_meta($post->ID, 'mji_product_allowed_countries', true) ?: []);
    $countries = WC()->countries->get_countries();
    wp_nonce_field('mji_country_availability', 'mji_country_nonce');
    ?>
    <p style="margin-bottom:8px">
        <label style="display:block;margin-bottom:4px">
            <input type="radio" name="mji_country_override" value="default" <?php checked($override, 'default'); ?>>
            Use brand default
        </label>
        <label style="display:block;margin-bottom:4px">
            <input type="radio" name="mji_country_override" value="worldwide" <?php checked($override, 'worldwide'); ?>>
            Available worldwide
        </label>
        <label style="display:block;margin-bottom:4px">
            <input type="radio" name="mji_country_override" value="specific" <?php checked($override, 'specific'); ?>>
            Specific countries only
        </label>
        <label style="display:block">
            <input type="radio" name="mji_country_override" value="not_online" <?php checked($override, 'not_online'); ?>>
            <strong>Not available online</strong> <span style="color:#c0392b">— hides Add to Cart for this product regardless of brand</span>
        </label>
    </p>
    <div id="mji-country-select" style="<?php echo $override !== 'specific' ? 'display:none' : ''; ?>margin-top:8px">
        <select id="mji_product_allowed_countries"
                name="mji_product_allowed_countries[]"
                multiple
                class="wc-enhanced-select"
                data-placeholder="Search for a country…"
                style="width:100%">
            <?php foreach ($countries as $code => $name) : ?>
                <option value="<?php echo esc_attr($code); ?>" <?php selected(in_array($code, $saved, true)); ?>>
                    <?php echo esc_html($name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <script>
    (function () {
        var radios = document.querySelectorAll('input[name="mji_country_override"]');
        var wrap   = document.getElementById('mji-country-select');

        radios.forEach(function (r) {
            r.addEventListener('change', function () {
                var show = r.value === 'specific';
                wrap.style.display = show ? '' : 'none';
                if (show && typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                    jQuery('#mji_product_allowed_countries').select2({
                        placeholder: 'Search for a country…',
                        width: '100%',
                        allowClear: true
                    });
                }
            });
        });

        // Init immediately if already set to specific on page load
        if (wrap.style.display !== 'none' && typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            jQuery('#mji_product_allowed_countries').select2({
                placeholder: 'Search for a country…',
                width: '100%',
                allowClear: true
            });
        }
    })();
    </script>
    <?php
}

add_action('save_post_product', function (int $post_id): void {
    if (!isset($_POST['mji_country_nonce']) ||
        !wp_verify_nonce($_POST['mji_country_nonce'], 'mji_country_availability')) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    $override = sanitize_text_field($_POST['mji_country_override'] ?? 'default');
    if (!in_array($override, ['default', 'worldwide', 'specific', 'not_online'], true)) $override = 'default';
    update_post_meta($post_id, 'mji_country_override', $override);

    if ($override === 'specific') {
        $countries = array_map('sanitize_text_field', (array) ($_POST['mji_product_allowed_countries'] ?? []));
        update_post_meta($post_id, 'mji_product_allowed_countries', array_values(array_filter($countries)));
    } else {
        delete_post_meta($post_id, 'mji_product_allowed_countries');
    }
});

// ─── Checkout hard-block ───────────────────────────────────────────────────────

add_action('woocommerce_checkout_process', function (): void {
    // Use shipping country; fall back to billing if "ship to same address" is checked.
    $ship_to_billing = !isset($_POST['ship_to_different_address']) || !$_POST['ship_to_different_address'];
    $country = $ship_to_billing
        ? sanitize_text_field(wp_unslash($_POST['billing_country'] ?? ''))
        : sanitize_text_field(wp_unslash($_POST['shipping_country'] ?? ''));

    if (empty($country)) return;

    foreach (WC()->cart->get_cart() as $item) {
        $product    = $item['data'];
        $product_id = $product->get_parent_id() ?: $product->get_id();
        $allowed    = mji_get_product_allowed_countries($product_id);

        if (empty($allowed) || in_array($country, $allowed, true)) continue;

        wc_add_notice(
            sprintf(
                '"%s" is not available for delivery to your country. Please remove it from your cart to continue.',
                esc_html($product->get_name())
            ),
            'error'
        );
    }
});

// =============================================
// ADD TO CART MODAL
// =============================================

// Prepend Montecristo sub-brand before the product name in the classic cart template.
add_filter('woocommerce_cart_item_name', function ($name, $cart_item, $cart_item_key) {
    $sub_brand = get_montecristo_sub_brand((int) $cart_item['product_id']);
    if (!$sub_brand) return $name;
    return '<span class="montecristo-sub-brand">' . esc_html($sub_brand) . '</span> ' . $name;
}, 10, 3);

// Output a JS map for block-cart-subbrand.js. Keyed two ways so both the cart
// block (href match) and the checkout order summary (name match fallback) work:
//   paths: { '/product/black-bay': 'Tudor' }
//   names: { 'tudor black bay pro': 'Tudor' }
add_action('wp_footer', function () {
    if (!WC()->cart || WC()->cart->is_empty()) return;

    $paths = [];
    $names = [];
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id = (int) $cart_item['product_id'];
        $sub_brand  = get_montecristo_sub_brand($product_id);
        if (!$sub_brand) continue;
        $product = wc_get_product($product_id);
        if (!$product) continue;
        $parsed = wp_parse_url($product->get_permalink());
        $path   = rtrim($parsed['path'] ?? '', '/');
        if ($path) $paths[$path] = $sub_brand;
        $names[strtolower($product->get_name())] = $sub_brand;
    }

    if (empty($paths) && empty($names)) return;
    echo '<script>window.mjiBlockCartSubBrands = ' . wp_json_encode(
        ['paths' => $paths, 'names' => $names],
        JSON_HEX_TAG | JSON_UNESCAPED_UNICODE
    ) . ';</script>';
}, 5);

// Capture the last added product in the WC session and flag that the modal
// should be shown on the next page load (since add-to-cart uses a full POST).
add_action('woocommerce_add_to_cart', 'mc_store_last_added_product', 10, 6);
function mc_store_last_added_product($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
{
    WC()->session->set('mc_last_added_product_id', $variation_id ?: $product_id);
    WC()->session->set('mc_last_added_qty', $quantity);
    WC()->session->set('mc_modal_pending', true);
}

// Build modal data array from a product ID (shared by footer and fragment filter).
function mc_build_cart_modal_data(int $product_id): ?array
{
    $product = wc_get_product($product_id);
    if (!$product) return null;

    $attrs = [];
    if ($product instanceof WC_Product_Variation) {
        foreach ($product->get_variation_attributes() as $key => $slug) {
            $taxonomy = str_replace('attribute_', '', $key);
            $term     = get_term_by('slug', $slug, $taxonomy);
            $attrs[]  = $term ? $term->name : $slug;
        }
    }

    $brand_terms = get_the_terms($product->get_id(), 'product_brand');
    if (!$brand_terms || is_wp_error($brand_terms)) {
        $parent_id   = $product instanceof WC_Product_Variation ? $product->get_parent_id() : $product->get_id();
        $brand_terms = get_the_terms($parent_id, 'product_brand');
    }
    $brand = (!empty($brand_terms) && !is_wp_error($brand_terms)) ? $brand_terms[0]->name : '';

    $qty        = (int) WC()->session->get('mc_last_added_qty', 1);
    $image_id   = $product->get_image_id();
    $parent_id  = ($product instanceof WC_Product_Variation) ? $product->get_parent_id() : $product->get_id();
    $sub_brand  = get_montecristo_sub_brand($parent_id);

    return [
        'name'       => $product->get_name(),
        'image'      => $image_id ? wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail') : wc_placeholder_img_src('woocommerce_thumbnail'),
        'price'      => html_entity_decode(wp_strip_all_tags(wc_price($product->get_price())), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'subtotal'   => html_entity_decode(wp_strip_all_tags(WC()->cart->get_cart_subtotal()), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'qty'        => $qty,
        'attributes' => implode(', ', array_filter($attrs)),
        'brand'      => $brand,
        'sub_brand'  => $sub_brand,
        'cart_url'   => wc_get_cart_url(),
    ];
}

// Render the modal shell and, when a page-reload add-to-cart just happened,
// pass the product data inline so JS can open the modal immediately.
add_action('wp_footer', 'mc_cart_modal_html');
function mc_cart_modal_html()
{
    if (is_admin()) return;
    ?>
    <div id="mc-cart-modal-overlay" class="mc-cart-modal-overlay" aria-hidden="true">
        <div id="mc-cart-modal" class="mc-cart-modal" role="dialog" aria-modal="true" aria-labelledby="mc-cart-modal-heading">
            <button id="mc-cart-modal-close" class="mc-cart-modal-close" aria-label="<?php esc_attr_e('Close', 'woocommerce'); ?>">&#x2715;</button>
            <h2 id="mc-cart-modal-heading" class="mc-cart-modal-title"><?php esc_html_e('Added to cart', 'woocommerce'); ?></h2>
            <p id="mc-cart-modal-message" class="mc-cart-modal-message" style="display:none"></p>
            <div class="mc-cart-modal-product">
                <img id="mc-cart-modal-img" src="" alt="" class="mc-cart-modal-img">
                <div class="mc-cart-modal-info">
                    <p id="mc-cart-modal-name" class="mc-cart-modal-name"></p>
                    <p id="mc-cart-modal-attrs" class="mc-cart-modal-attrs"></p>
                    <p id="mc-cart-modal-brand" class="mc-cart-modal-brand"></p>
                    <p id="mc-cart-modal-qty" class="mc-cart-modal-qty"></p>
                </div>
                <p id="mc-cart-modal-price" class="mc-cart-modal-price"></p>
            </div>
            <hr class="mc-cart-modal-divider">
            <div class="mc-cart-modal-subtotal-row">
                <span><?php esc_html_e('Subtotal', 'woocommerce'); ?></span>
                <span id="mc-cart-modal-subtotal"></span>
            </div>
            <a id="mc-cart-modal-view-cart" href="<?php echo esc_url(wc_get_cart_url()); ?>" class="mc-cart-modal-btn"><?php esc_html_e('View cart', 'woocommerce'); ?></a>
            <button id="mc-cart-modal-continue" class="mc-cart-modal-continue"><?php esc_html_e('Continue shopping', 'woocommerce'); ?></button>
        </div>
    </div>
    <?php

    // Pass nonce + AJAX URL to JS (used by the AJAX add-to-cart handler).
    echo '<script>window.mcCart = ' . wp_json_encode([
        'nonce'        => wp_create_nonce('mc_add_to_cart'),
        'notify_nonce' => wp_create_nonce('mji_notify_me_nonce'),
        'ajax_url'     => admin_url('admin-ajax.php'),
    ], JSON_HEX_TAG) . ';</script>';

    // Page-reload fallback: if a product was just added via a non-JS POST,
    // output its data so the modal still opens on the new page.
    if (WC()->session && WC()->session->get('mc_modal_pending')) {
        $product_id = (int) WC()->session->get('mc_last_added_product_id');
        $data       = $product_id ? mc_build_cart_modal_data($product_id) : null;

        WC()->session->set('mc_modal_pending', false);

        if ($data) {
            echo '<script>window.mcCartModalData = ' . wp_json_encode($data, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) . ';</script>';
        }
    }
}

// Replace the default top-of-page "Product added" notice with nothing.
add_filter('wc_add_to_cart_message_html', '__return_empty_string');


// Detect when a sold-individually product is already in cart and the user tries
// to add it again. Intercept BEFORE WooCommerce adds its own error notice, store
// a session flag, and return false so WC silently aborts the add.
add_filter('woocommerce_add_to_cart_validation', 'mc_detect_already_in_cart', 5, 3);
function mc_detect_already_in_cart($passed, $product_id, $quantity)
{
    // AJAX path handles its own validation — only run this for page-reload POSTs.
    if (!$passed || wp_doing_ajax()) return $passed;

    $product = wc_get_product($product_id);
    if (!$product || !$product->is_sold_individually()) return $passed;

    foreach (WC()->cart->get_cart() as $item) {
        if ((int) $item['product_id'] === (int) $product_id) {
            WC()->session->set('mc_already_in_cart_id', $product_id);
            WC()->session->set('mc_already_in_cart_pending', true);
            return false;
        }
    }

    return $passed;
}

// On the next page load after an "already in cart" attempt, output product data
// for the JS modal and clear the pending flag.
add_action('wp_footer', 'mc_already_in_cart_modal_data');
function mc_already_in_cart_modal_data()
{
    if (is_admin() || !WC()->session || !WC()->session->get('mc_already_in_cart_pending')) return;

    $product_id = (int) WC()->session->get('mc_already_in_cart_id');
    WC()->session->set('mc_already_in_cart_pending', false);

    if (!$product_id) return;

    $data = mc_build_cart_modal_data($product_id);
    if ($data) {
        $data['title']   = __('Already in your cart', 'woocommerce');
        $data['message'] = __('This item is already in your cart.', 'woocommerce');
        echo '<script>window.mcAlreadyInCartData = ' . wp_json_encode($data, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) . ';</script>';
    }
}

// AJAX add-to-cart — validates stock, adds item, returns modal data as JSON.
// JS intercepts the form submit so no page reload is needed.
add_action('wp_ajax_mc_add_to_cart', 'mc_ajax_add_to_cart');
add_action('wp_ajax_nopriv_mc_add_to_cart', 'mc_ajax_add_to_cart');
function mc_ajax_add_to_cart()
{
    check_ajax_referer('mc_add_to_cart', 'nonce');

    $product_id   = absint($_POST['product_id'] ?? 0);
    $variation_id = absint($_POST['variation_id'] ?? 0);
    $quantity     = max(1, absint($_POST['quantity'] ?? 1));

    if (!$product_id) {
        wp_send_json_error(['message' => __('Invalid product.', 'woocommerce')]);
    }

    $modal_id = $variation_id ?: $product_id;
    $check    = wc_get_product($modal_id);
    if (!$check) {
        wp_send_json_error(['message' => __('Product not found.', 'woocommerce')]);
    }

    // Sold-individually check
    if ($check->is_sold_individually()) {
        foreach (WC()->cart->get_cart() as $item) {
            $match = $variation_id ? (int) $item['variation_id'] : (int) $item['product_id'];
            if ($match === (int) $modal_id) {
                WC()->session->set('mc_last_added_qty', (int) $item['quantity']);
                $data            = mc_build_cart_modal_data($modal_id) ?? [];
                $data['title']   = __('Already in your cart', 'woocommerce');
                $data['message'] = __('This item is already in your cart.', 'woocommerce');
                wp_send_json_error($data);
                return;
            }
        }
    }

    // Stock quantity check
    if ($check->managing_stock()) {
        $stock   = (int) $check->get_stock_quantity();
        $in_cart = 0;
        foreach (WC()->cart->get_cart() as $item) {
            $match = $variation_id ? (int) $item['variation_id'] : (int) $item['product_id'];
            if ($match === (int) $modal_id) {
                $in_cart = (int) $item['quantity'];
                break;
            }
        }
        if (($in_cart + $quantity) > $stock) {
            WC()->session->set('mc_last_added_qty', $in_cart);
            $data            = mc_build_cart_modal_data($modal_id) ?? [];
            $data['title']   = __('Limited stock', 'woocommerce');
            $data['message'] = $stock === 0
                ? __('Sorry, this item is out of stock.', 'woocommerce')
                : sprintf(
                    /* translators: 1: available stock 2: quantity already in cart */
                    __('Only %1$d available — you already have %2$d in your cart.', 'woocommerce'),
                    $stock,
                    $in_cart
                );
            wp_send_json_error($data);
            return;
        }
    }

    // Add to cart
    WC()->session->set('mc_last_added_qty', $quantity);
    $key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id);

    if ($key === false) {
        $notices = wc_get_notices('error');
        wc_clear_notices();
        $msg             = !empty($notices) ? wp_strip_all_tags($notices[0]['notice']) : __('Could not add to cart.', 'woocommerce');
        $data            = mc_build_cart_modal_data($modal_id) ?? [];
        $data['title']   = __('Unable to add', 'woocommerce');
        $data['message'] = $msg;
        wp_send_json_error($data);
        return;
    }

    // AJAX handles the modal itself — clear the page-reload flag so the cart
    // page doesn't reopen the modal when the user clicks "View Cart".
    WC()->session->set('mc_modal_pending', false);

    $data = mc_build_cart_modal_data($modal_id);
    wp_send_json_success($data);
}

// =============================================
// NOTIFY ME — OUT OF STOCK
// =============================================

// Remove WC add-to-cart for out-of-stock products so our button replaces it at the same slot.
add_action('woocommerce_single_product_summary', function () {
    global $product;
    if (!$product || $product->is_in_stock()) return;
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
}, 5);

// Output "Out of stock" availability text at priority 30 for purchasable out-of-stock products.
// The "Notify When Back in Stock" button itself is rendered inside single_page_contact()
// in product.php, placed between Contact Us and the icon links.
add_action('woocommerce_single_product_summary', 'mji_single_notify_me_button', 30);
function mji_single_notify_me_button()
{
    global $product;
    if (!$product || $product->is_in_stock() || !$product->is_purchasable()) return;
    echo wp_kses_post(wc_get_stock_html($product));
}

// AJAX handler — rate-limited, validates email, emails the store.
add_action('wp_ajax_mji_notify_me', 'mji_notify_me');
add_action('wp_ajax_nopriv_mji_notify_me', 'mji_notify_me');
function mji_notify_me()
{
    check_ajax_referer('mji_notify_me_nonce', 'nonce');

    if (!mji_check_rate_limit('notify_me', 5)) {
        wp_send_json_error(['message' => __('Too many requests. Please try again later.', 'woocommerce')]);
    }

    $email      = sanitize_email($_POST['email'] ?? '');
    $product_id = absint($_POST['product_id'] ?? 0);

    if (!is_email($email)) {
        wp_send_json_error(['message' => __('Please enter a valid email address.', 'woocommerce')]);
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error(['message' => __('Product not found.', 'woocommerce')]);
    }

    $to      = get_option('custom_sender_email') ?: get_option('admin_email');
    $subject = sprintf('Stock notification request — %s', $product->get_name());
    $body    = sprintf(
        "A customer would like to be notified when the following item is back in stock:\n\nProduct: %s\nURL: %s\n\nCustomer email: %s",
        $product->get_name(),
        get_permalink($product_id),
        $email
    );

    wp_mail($to, $subject, $body, ['Content-Type: text/plain; charset=UTF-8']);
    wp_send_json_success();
}

// =============================================
// CHECKOUT: REMOVE CART ITEMS NOT SHIPPABLE TO SELECTED COUNTRY
// =============================================

/**
 * Checks every cart item against the given country code.
 * Removes ineligible items and returns their names so a notice can be shown.
 */
function mji_remove_ineligible_cart_items(string $country): array {
    if (!$country || !WC()->cart) return [];

    $removed = [];

    foreach (WC()->cart->get_cart() as $key => $item) {
        /** @var WC_Product $product */
        $product    = $item['data'];
        $product_id = $product->get_parent_id() ?: $product->get_id();

        // Skip items with no country restrictions (worldwide).
        $allowed = mji_get_product_allowed_countries($product_id);
        if (empty($allowed)) continue;

        if (!in_array($country, $allowed, true)) {
            $removed[] = $product->get_name();
            WC()->cart->remove_cart_item($key);
        }
    }

    return $removed;
}

// Note: the store-API and check-cart-items auto-removal hooks have been removed.
// Country restrictions are now enforced only at checkout (woocommerce_checkout_process above),
// so customers can keep restricted items in their cart and ship to a recipient in an allowed country.

/**
 * Display the session notice (set by the Store API hook above) on the next render.
 * The checkout block doesn't support wc_add_notice mid-request, so we queue it
 * in the session and output it on the next page render via wp_footer.
 */
add_action('wp_footer', function (): void {
    if (!is_checkout() || !WC()->session) return;

    $removed = WC()->session->get('mji_checkout_removed_items');
    if (empty($removed)) return;

    WC()->session->set('mji_checkout_removed_items', null);

    $country = WC()->customer
        ? (WC()->customer->get_shipping_country() ?: WC()->customer->get_billing_country())
        : '';
    $country_name = $country
        ? (WC()->countries->countries[$country] ?? $country)
        : 'your region';

    $names = implode(', ', array_map('esc_html', $removed));
    $message = sprintf(
        'The following item(s) were removed because they cannot be shipped to %s: <strong>%s</strong>.',
        esc_html($country_name),
        $names
    );

    echo '<script>
    (function() {
        var notice = document.createElement("div");
        notice.className = "woocommerce-error mji-shipping-notice";
        notice.style.cssText = "margin:1rem 0;padding:1rem 1.5rem;background:#fdf2f2;border-left:4px solid #c0392b;color:#333;border-radius:4px";
        notice.innerHTML = ' . wp_json_encode($message) . ';
        var checkout = document.querySelector(".wp-block-woocommerce-checkout, .woocommerce-checkout");
        if (checkout) checkout.prepend(notice);
    })();
    </script>';
});
