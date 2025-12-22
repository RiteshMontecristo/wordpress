<?php

// Adding parent styles with our css styles
add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');

function my_theme_enqueue_styles()
{
    $parent_style = 'parent-style';
    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');

    wp_enqueue_style(
        'splide-style',
        get_stylesheet_directory_uri() . '/splidejs/splide-core.min.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array($parent_style, "splide-style"),
        wp_get_theme()->get('Version')
    );
}

// Adding rolex stylesheet only if the page is rolex
function rolex_stylesheet()
{
    if (is_singular('mc-rolex') || is_singular('mc-rolex-product')) {
        wp_enqueue_style(
            'rolex-style',
            get_stylesheet_directory_uri() . '/styles/rolex.css',
            array(),
            '1.0.0'
        );
    }

    wp_enqueue_style('dashicons');
}

add_action('wp_enqueue_scripts', 'rolex_stylesheet');

function my_load_scripts()
{
    wp_enqueue_script(
        'splidejs-script',
        get_stylesheet_directory_uri() . '/splidejs/splide.min.js',
        array(),
        '1.0.0',
        true
    );

    if (is_singular('mc-rolex') || is_singular('mc-rolex-product')) {
        wp_enqueue_script(
            'rolex-script',
            get_stylesheet_directory_uri() . '/scripts/rolex.js',
            array('splidejs-script'),
            '1.0.0',
            array(
                'strategy' => 'defer'
            )
        );

        wp_localize_script('rolex-script', 'ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'),
        ));
    }

    wp_enqueue_script(
        'normal-script',
        get_stylesheet_directory_uri() . '/scripts/index.js',
        array('splidejs-script'),
        '1.0.0',
        true
    );

    wp_localize_script('normal-script', 'ajax_object_another', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'my_load_scripts');

function enqueue_admin_scripts()
{
    // Zebra Browser Print
    wp_enqueue_script(
        'zebra-printer',
        get_stylesheet_directory_uri() . '/inventory/scripts/printer/BrowserPrint-3.1.250.min.js',
        array(),
        '3.7',
        true
    );

    wp_enqueue_script(
        'zebra-printer-2',
        get_stylesheet_directory_uri() . '/inventory/scripts/printer/BrowserPrint-Zebra-1.1.250.min.js',
        array(),
        '3.7',
        true
    );

    wp_enqueue_script(
        'admin-script',
        get_stylesheet_directory_uri() . '/inventory/scripts/index.js',
        array('zebra-printer', 'zebra-printer-2'),
        '1.0.0',
        true
    );

    wp_localize_script('admin-script', 'ajax_inventory', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'sales_css_url' => get_stylesheet_directory_uri() . '/inventory/styles/sales.css',
    ));

    wp_enqueue_style(
        'admin-style',
        get_stylesheet_directory_uri() . '/inventory/styles/admin.css',
        array(),
        '1.0.0'
    );
}
add_action('admin_enqueue_scripts', 'enqueue_admin_scripts');

// making the index.js to be a module type js
function add_module_attribute($tag, $handle, $src)
{
    if ('normal-script' === $handle || $handle === 'admin-script') {
        // Add type="module" to the script tag
        $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
    }
    return $tag;
}
add_filter('script_loader_tag', 'add_module_attribute', 10, 3);

// Adding CPT functions
require get_stylesheet_directory() . '/cpt/rolex.php';

// Adding CMB@ functions
require_once get_stylesheet_directory() . '/cmb2/fields.php';

// Adding woocommerce functions
require get_stylesheet_directory() . '/woocommerce/function.php';

require get_stylesheet_directory() . '/inventory/functions.php';

// Remove breadcrumb for the rolex pages
function remove_breadcrumb_for_cpt()
{
    // if (is_singular('mc-rolex') || is_singular('mc-rolex-product') || is_product_tag() || is_product_category()) {
    //     remove_action('storefront_before_content', 'woocommerce_breadcrumb', 10);
    // }
    if (!is_product()) {
        remove_action('storefront_before_content', 'woocommerce_breadcrumb', 10);
    }
}
add_action('storefront_before_content', 'remove_breadcrumb_for_cpt', 5);

// Rolex menus
function rolex_register_menus()
{
    register_nav_menus(
        array(
            'rolex-menu' => __('Rolex Menu'),
        )
    );
}
add_action('init', 'rolex_register_menus');

// grabbign the height and width of cloudinary iamges
function get_image_sizes($image_url)
{

    $transient_key = 'img_size_' . md5($image_url);

    $cached = get_transient($transient_key);
    if ($cached && isset($cached['width']) && isset($cached['height'])) {
        return $cached;
    }

    $image_path = '';
    $image_info = false;

    // if this is a relative URL (starts with "/")
    if (strpos($image_url, '/') === 0 && !preg_match('#^https?://#i', $image_url)) {
        $image_path = wp_normalize_path(ABSPATH . ltrim($image_url, '/'));

        if (file_exists($image_path)) {
            $image_info = @getimagesize($image_path);
        } else {
            custom_log("Image not found locally: " . $image_path);
            return [
                'width'  => null,
                'height' => null
            ];
        }
    } else {
        //    Absolute URL (remote URL)
        $headers = @get_headers($image_url);
        if (!$headers || strpos($headers[0], '200') === false) {
            custom_log("Remote image not accessible: " . $image_url);
            return [
                'width'  => null,
                'height' => null
            ];
        }
        $image_info = @getimagesize($image_url);
    }

    // Validate image data
    if (!$image_info) {
        custom_log("Failed to retrieve image dimensions for: " . $image_url);
        return [
            'width'  => null,
            'height' => null
        ];
    }

    $sizes = [
        'width'  => $image_info[0],
        'height' => $image_info[1],
    ];

    // Store in transient (5 years)  Practically permanent but still self-cleaning if unused.
    set_transient($transient_key, $sizes, YEAR_IN_SECONDS * 5);

    return $sizes;
}

// SHORTCODES
function responsive_image_shortcode($atts)
{
    $atts = shortcode_atts(
        array(
            'desktop_image_url' => '',
            'mobile_image_url'  => '',
            'alt_text'          => '',
            'classname'         => '',
            'url'               => '',
            'loading'           => '',
            'img_class'         => '',
        ),
        $atts,
        'responsive_image'
    );

    $loading        = !empty($atts['loading']) ? esc_attr($atts['loading']) : 'lazy';
    $fetch_priority = ($loading === 'lazy') ? 'auto' : 'high';

    $desktop_image = esc_url($atts['desktop_image_url']);
    $mobile_image  = esc_url($atts['mobile_image_url']);
    $image_src     = $desktop_image ? $desktop_image : $mobile_image;
    $alt_text      = esc_attr($atts['alt_text']);
    $title         = $alt_text;
    $img_class     = esc_attr($atts['img_class']);
    $link_class    = esc_attr($atts['classname']);
    $url = esc_url($atts['url']);
    $width = 2400;
    $height = 920;

    if (!str_contains($image_src, "mapbox")) {
        $dimensions = get_image_sizes($image_src);
        $width    = $dimensions['width'] ?? 2400;
        $height   = $dimensions['height'] ?? 920;
    }
    if (!empty($link_class) && empty($url)) {
        $picture = '<picture class="' . $link_class . '">';
    } else {
        $picture = '<picture>';
    }

    if (!empty($mobile_image)) {
        $picture .= '<source srcset="' . $mobile_image . '" media="(max-width: 767px)" />';
    }
    if (!empty($desktop_image)) {
        $picture .= '<source srcset="' . $desktop_image . '" media="(min-width: 767px)" />';
    }

    $picture .= '<img class="' . $img_class . '" loading="' . $loading . '" fetchpriority="' . $fetch_priority . '" src="' . $image_src . '" alt="' . $alt_text . '" width="' . $width . '" height="' . $height . '" title="' . $title . '" />';
    $picture .= '</picture>';

    $output = !empty($url)
        ? '<a class="' . $link_class . '" href="' . $url . '">' . $picture . '</a>'
        : $picture;

    return $output;
}
add_shortcode('responsive_image', 'responsive_image_shortcode');

// For the youtube videos
function responsive_video_shortcode($atts)
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
        <div class="youtube-video-container" id="youtubeVideoContainer" data-video-id="<?php echo esc_attr($atts['embed_code']); ?>">

            <div class="video-thumbnail">
                <?php
                echo do_shortcode(
                    '[responsive_image   desktop_image_url="' . $imgUrl . '"    mobile_image_url="' . $imgUrl . '" alt_text="Video Thumbnail" ]'
                );
                ?>
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
add_shortcode('response_video', 'responsive_video_shortcode');


// To change the storehours in the Contact US dropdown page.
function responsive_storehours_shortcode($atts)
{

    $openingHours = array(
        'Sunday' => '11:00am - 7:00pm',
        'Monday' => '10:00am - 9:00pm',
        'Tuesday' => '10:00am - 9:00pm',
        'Wednesday' => '10:00am - 9:00pm',
        'Thursday' => '10:00am - 9:00pm',
        'Friday' => '10:00am - 9:00pm',
        'Saturday' => '10:00am - 9:00pm'
    );

    $today = date('l');

    $atts = shortcode_atts(
        $atts,
        'responsive_storehours'
    );


    $output = '<div class="storehours-container"><div class="responsive-storehours fixed16">
    <span>Open today</span>
    <button class="secondary-cta fixed16" id="openingHours">' . $openingHours[$today] . '
   <svg enable-background="new 0 0 15 15" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg" width="12px" height="12px">
    <path d="m7.5 11.6-7.5-8.2h15z"></path>
</svg>
    </button>
    </div>
    <ul class="store-hours" id="storeHours">
    ';

    foreach ($openingHours as $day => $hour) {
        $output .= '<li><span>' . $day . '</span><span>' . $hour . '</span></li>';
    }

    $output .= '</ul></div>';

    return $output;
}
add_shortcode('responsive_storehours', 'responsive_storehours_shortcode');

function accessories_grid_shortcode()
{
    ob_start(); // Start output buffering
    include_once get_stylesheet_directory() . '/template-parts/accessories-grid.php';
    return ob_get_clean(); // Return the buffered content
}
add_shortcode('accessories_grid', 'accessories_grid_shortcode');

function watch_grid_shortcode()
{
    ob_start(); // Start output buffering
    include_once get_stylesheet_directory() . '/template-parts/watch-grid.php';
    return ob_get_clean(); // Return the buffered content
}

add_shortcode('watch_grid', 'watch_grid_shortcode');

// grabbing the countries and code
include_once get_stylesheet_directory() . '/inc/countries.php';

function rolex_form_shortcode($atts)
{
    // Grabbing the attributes values
    $atts = shortcode_atts(
        array(
            'desktop_image_url' => '',
            'mobile_image_url'  => '',
            'alt_text'          => '',
            'loading'           => '',
            'message'           => '',
            'success_img_url'   => '',
            'success_alt'       => '',
            'is_modal_page'     => ''
        ),
        $atts,
        'rolex_form'
    );
    $modalPage = false;
    if (!empty($atts['is_modal_page'])) {
        $modalPage = true;
    }

    ob_start();
?>

    <div id="contactUs" class="contact-us">
        <!-- Calling the image shortcode -->
        <?php
        echo do_shortcode(
            '[responsive_image 
            desktop_image_url="' . $atts['desktop_image_url'] . '" 
            mobile_image_url="' . $atts['mobile_image_url'] . '" 
            alt_text="' . $atts['alt_text'] .  '" 
            loading="' . $atts['loading'] . '"]'
        );
        ?>

        <form id="multiStepFormRolex" class="multi-step-form rolex-form grid-nospace">
            <input
                type="text"
                name="company_name"
                tabindex="-1"
                autocomplete="off"
                style="position:absolute; left:-9999px;">
            <!-- First Step -->
            <div class="first-step" id="firstStep">

                <div>
                    <?php
                    if ($modalPage) {
                        echo '<p class="body24Bold">Send a message</p>';
                    } else {
                        echo '<h1 class="body24Bold">Send a message</h1>';
                    }
                    ?>
                    <p class="headline50">Please enter your message</p>
                </div>

                <p class="body20Light">Thank you for your interest in Rolex watches. Please enter your message below, and we will be delighted to assist you.</p>

                <div class="textarea">
                    <textarea id="message" name="message" rows="8" class="fixed16" placeholder="Enter your message"><?php echo esc_attr($atts['message']) ?></textarea>
                    <p id="messageError" class="legend16Bold error hidden">Please enter a message</p>
                </div>

                <button id="nextButton" type="button" class="primary-cta">
                    Next
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="12px" height="12px" viewBox="0 0 12 12" version="1.1">
                        <g>
                            <path d="M 9.601562 6 L 8.558594 7.121094 L 3.679688 12 L 2.480469 10.800781 L 7.359375 5.921875 L 2.398438 1.121094 L 3.601562 0 Z M 9.601562 6 "></path>
                        </g>
                    </svg>
                </button>
            </div>

            <!-- Second Step -->
            <div class="second-step" id="secondStep">
                <button id="backButton" type="button" class="secondary-cta">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="12px" height="12px" viewBox="0 0 12 12" version="1.1">
                        <g>
                            <path d="M 9.601562 6 L 8.558594 7.121094 L 3.679688 12 L 2.480469 10.800781 L 7.359375 5.921875 L 2.398438 1.121094 L 3.601562 0 Z M 9.601562 6 "></path>
                        </g>
                    </svg>
                    Back
                </button>

                <div class="contact-intro">
                    <p class="body24Bold">Send a message
                    <p>
                    <p class="headline50">Please enter your contact information</p>
                </div>

                <div class="title">
                    <label for="title">Title</label>
                    <select name="title" id="title">
                        <option value="" selected></option>
                        <option value="Mr.">Mr.</option>
                        <option value="Mrs.">Mrs.</option>
                        <option value="Miss">Miss</option>
                    </select>
                </div>

                <div class="first">
                    <label id="firstNameLabel" for="firstName">First name*</label>
                    <input type="text" name="firstName" id="firstName" />
                    <span id="firstNameError" class="legend16Bold error hidden">Please enter your first name</span>
                </div>

                <div class="last">
                    <label id="lastNameLabel" for="lastName">Last name*</label>
                    <input type="text" name="lastName" id="lastName" />
                    <span id="lastNameError" class="legend16Bold error hidden">Please enter your last name</span>
                </div>

                <div class="email">
                    <label id="emailLabel" for="email">Email address*</label>
                    <input type="email" name="email" id="email" />
                    <span id="emailError" class="legend16Bold error hidden">Please include a valid email address</span>
                </div>

                <span class="label">and/or</span>

                <div class="code">
                    <label for="code">Code</label>
                    <?php phoneCode(); ?>
                </div>

                <div class="phone">
                    <label for="phone">Phone number</label>
                    <input type="number" name="phone" id="phone" maxlength="10" />
                </div>

                <div class="country">
                    <label for="country">Country of residence</label>
                    <?php countrySelector(); ?>
                </div>

                <p class="mandatory bold14Light">* Mandatory Information</p>

                <div class="terms">
                    <input type="checkbox" name="terms" id="terms" />
                    <label for="terms">
                        <svg version="1.1" id="checkbox-svg" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                            viewBox="0 0 15 15" style="enable-background:new 0 0 15 15;" xml:space="preserve" fill="#127749">
                            <g>
                                <path class="st0" d="M7.5,1.5c3.3,0,6,2.7,6,6s-2.7,6-6,6s-6-2.7-6-6S4.2,1.5,7.5,1.5 M7.5,0C3.4,0,0,3.4,0,7.5S3.4,15,7.5,15
		S15,11.6,15,7.5S11.6,0,7.5,0L7.5,0z" />
                            </g>
                        </svg>
                        <span>I have read and accepted the <a href="/privacy-policy"> terms and conditions </a> and <a href="/privacy-policy"> policy </a>.</span>
                    </label>
                    <br />
                    <span id="termsError" class="legend16Bold error hidden">Please accept the terms and conditions</span>
                </div>
                <button id="submitButton" class="primary-cta">Send</button>
            </div>

            <!-- Third step -->
            <div class="third-step" id="thirdStep">

                <div class="contact-intro">
                    <p class="body24Bold">Send a message</p>
                    <p class="headline50">Thank you</p>
                </div>

                <div class="contact-message">
                    <p class="body20Bold black">
                        Your message has been successfully sent to the Rolex team at Montecristo Jewellers.
                    </p>
                    <p class="body20Light">One of our Rolex sales advisors will be reviewing your request and responding as soon as possible.</p>
                </div>

                <div class="contact-complete">
                    <a href="/rolex" class="primary-cta">Done</a>
                    <!-- Calling the success image shortcode -->
                    <?php
                    echo do_shortcode('[responsive_image desktop_image_url="' . $atts['success_img_url'] . '" mobile_image_url="' . $atts['success_img_url'] . '" alt_text="' . $atts['success_alt'] . '"]') ?>
                </div>
            </div>
            <script src="https://www.google.com/recaptcha/api.js?render=6LeQyf4qAAAAAAWm4dRwb-HQ55gjfYYzwVNMIZMI"></script>
        </form>

        <noscript>
            <style>
                .contact-us #multiStepFormRolex {
                    display: none !important;
                }
            </style>


            <form id="noJs" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="rolex-form grid-nospace">
                <div class="first-step" id="firstStep">
                    <div>
                        <p class="body24Bold">Send a message</p>
                        <p class="headline50">Please enter your message</p>
                    </div>

                    <p class="body20Light">Thank you for your interest in Rolex watches. Please enter your message and we will reply to you soon.</p>

                    <textarea id="message" name="message" rows="8" class="legend16Light" placeholder="Enter your message"><?php echo esc_attr($atts['message']) ?></textarea>

                </div>

                <div class="second-step" id="secondStep">

                    <div class="title">
                        <label for="title">Title</label>
                        <input type="text" name="title" id="title" />
                    </div>

                    <div class="first">
                        <label id="firstNameLabel" for="firstName">First name*</label>
                        <input required type="text" name="firstName" id="firstName" />
                    </div>

                    <div class="last">
                        <label id="lastNameLabel" for="lastName">Last name*</label>
                        <input required type="text" name="lastName" id="lastName" />
                    </div>

                    <div class="email">
                        <label id="emailLabel" for="email">Email address*</label>
                        <input required type="email" name="email" id="email" />
                    </div>

                    <span class="label">and/or</span>

                    <div class="code">
                        <label for="code">Code</label>
                        <input type="text" name="code" id="code" />
                    </div>

                    <div class="phone">
                        <label for="phone">Phone number</label>
                        <input type="number" name="phone" id="phone" />
                    </div>

                    <div class="country">
                        <label for="country">Country of residence</label>
                        <input type="text" name="country" id="country" />
                    </div>

                    <p class="mandatory bold14Light">* Mandatory Information</p>

                    <div class="terms">
                        <input required type="checkbox" name="terms" id="terms" />
                        <label for="terms">I have read and accepted the <a href="/privacy-policy"> terms and conditions </a> and <a href="/privacy-policy"> policy</a>.</label>
                    </div>


                    <input type="hidden" name="action" value="send_email">
                    <input type="hidden" name="rolex-nojs-nonce" value="<?php echo wp_create_nonce('rolex-nojs-nonce'); ?>" />
                    <button id="submitButton" class="primary-cta">Submit</button>
                </div>
            </form>

        </noscript>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('rolex_form', 'rolex_form_shortcode');

// ROLEX CONTACT FORM
function handle_send_email()
{
    if (! empty($_POST['company_name'])) {
        wp_send_json_success([
            'message' => 'Thank you! Your message has been sent.'
        ]);
    }

    $missing_fields = array();
    // Check must have fields to see if its empty or not
    if (empty($_POST['firstName'])) {
        $missing_fields[] = 'firstName';
    }
    if (empty($_POST['lastName'])) {
        $missing_fields[] = 'lastName';
    }
    if (empty($_POST['email'])) {
        $missing_fields[] = 'email';
    }
    if (empty($_POST['terms'])) {
        $missing_fields[] = 'terms';
    }
    if (empty($_POST['g-recaptcha-response'])) {
        $response = array(
            'success' => false,
            'message' => 'CAPTCHA verification failed. Please try again.'
        );
        wp_send_json($response);
        return;
    }

    // If there are missing fields, send an error response with details
    if (!empty($missing_fields)) {
        $response = array(
            'success' => false,
            'message' => 'The following fields are missing: ' . implode(', ', $missing_fields)
        );
        wp_send_json($response);
        return;
    }


    $CAPTCHA_SERCRET = '6LeQyf4qAAAAALpHBHQW-1-NQfrZJNvVoGXZ8Ha1';

    // Prepare POST request to Google
    $url = 'https://www.google.com/recaptcha/api/siteverify';

    $data = [
        'secret' => $CAPTCHA_SERCRET,
        'response' => $_POST['g-recaptcha-response'],
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


    if ($result['success'] && $result['action'] === 'rolex_contact_us' && $result['score'] >= 0.5) {
        // Sanitize and process the data 
        $message = sanitize_textarea_field($_POST['message']);
        $firstName = sanitize_text_field($_POST['firstName']);
        $lastName = sanitize_text_field($_POST['lastName']);
        $email = sanitize_email($_POST['email']);
        $code = sanitize_text_field($_POST['code']);
        $phone = sanitize_text_field($_POST['phone']);
        $country = sanitize_text_field($_POST['country']);

        // Prepare email content
        $to = get_option('custom_sender_email', get_option('admin_email'));
        $subject = 'New Form Submission';
        $headers = array('Content-Type: text/plain; charset=UTF-8');

        // Email message
        $email_message =
            "Customer reached out to us with the following information:\n\n" .
            "First Name: $firstName\n" .
            "Last Name: $lastName\n" .
            "Email: $email\n" .
            (!empty($code) ? "Code: $code\n" : '') .
            (!empty($phone) ? "Phone: $phone\n" : '') .
            (!empty($country) ? "Country: $country\n" : '') .
            (!empty($message) ? "Message: $message\n" : '');

        // Send the email
        $sent = wp_mail($to, $subject, $email_message, $headers);

        // Prepare JSON response
        if ($sent) {
            $auto_response =
                "Dear " . $firstName . " " . $lastName . ",\n\n" .
                "Thanks for contacting us!\n\n" .
                "One of our team members will be contacting you within 24 hours.\n\n" .
                "Best Regards,\n" .
                "Montecristo Jewellers";

            wp_mail($email, "Montecristo Jewellers Form Submission Confirmation", $auto_response, $headers);

            $response = array(
                'success' => true,
                'message' => 'Form submitted successfully. Thank you!'
            );
        } else {
            $response = array(
                'success' => false,
                'message' => 'There was a problem sending the email. Please try again later.'
            );
            error_log("The user {$email} tried to reach us in rolex but there was a server error.");
        }

        // Send JSON response
        wp_send_json($response);
    } else {
        $response = array(
            'success' => false,
            'message' => 'CAPTCHA verification failed. Please try again.'
        );
        wp_send_json($response);
        return;
    }
}
add_action('wp_ajax_send_email', 'handle_send_email');
add_action('wp_ajax_nopriv_send_email', 'handle_send_email');


function handle_send_email_nojs()
{
    if (isset($_POST['rolex-nojs-nonce'])) {
        $missing_fields = array();
        // Check must have fields to see if its empty or not
        if (empty($_POST['firstName'])) {
            $missing_fields[] = 'firstName';
        }
        if (empty($_POST['lastName'])) {
            $missing_fields[] = 'lastName';
        }
        if (empty($_POST['email'])) {
            $missing_fields[] = 'email';
        }

        if (empty($_POST['terms'])) {
            $missing_fields[] = 'terms';
        }

        // If there are missing fields, send an error response with details
        if (!empty($missing_fields)) {
            $response = array(
                'success' => false,
                'message' => 'The following fields are missing: ' . implode(', ', $missing_fields)
            );
            wp_send_json($response);
            return wp_redirect(home_url('/rolex/contact-form'));;
        }

        // Sanitize and process the data 
        $message = sanitize_textarea_field($_POST['message']);
        $firstName = sanitize_text_field($_POST['firstName']);
        $lastName = sanitize_text_field($_POST['lastName']);
        $email = sanitize_email($_POST['email']);
        $code = sanitize_text_field($_POST['code']);
        $phone = sanitize_text_field($_POST['phone']);
        $country = sanitize_text_field($_POST['country']);

        // Prepare email content
        $to = get_option('custom_sender_email', get_option('admin_email'));
        $subject = 'New Form Submission';
        $headers = array('Content-Type: text/plain; charset=UTF-8');

        // Email message
        $email_message =
            "First Name: $firstName\n" .
            "Last Name: $lastName\n" .
            "Email: $email\n" .
            (!empty($code) ? "Code: $code\n" : '') .
            (!empty($phone) ? "Phone: $phone\n" : '') .
            (!empty($country) ? "Country: $country\n" : '') .
            (!empty($message) ? "Message: $message\n" : '');

        // Send the email
        $sent = wp_mail($to, $subject, $email_message, $headers);
        $sent = true;
        // Prepare JSON response
        if ($sent) {
            return wp_redirect(home_url('/rolex/contact-form'));;
        } else {
            error_log("The user {$email} tried to reach us in rolex but there was a server error.");
            return wp_redirect(home_url('/rolex/contact-form'));;
        }
    }
}
add_action('admin_post_send_email', 'handle_send_email_nojs');
add_action('admin_post_nopriv_send_email', 'handle_send_email_nojs');

function load_more_products()
{
    $paged = $_POST['page'];
    $term = sanitize_text_field($_POST['term']);

    $rolex_product = new WP_Query(array(
        'post_type' => 'mc-rolex-product',
        'posts_per_page' => 6,
        'paged' => $paged,
        'fields' => 'ids',
        'tax_query' => array(
            array(
                'taxonomy' => 'rolex_product_category',
                'field'    => 'slug',
                'terms'    => $term
            ),
        ),
        'orderby' => 'ID',
        'order' => 'asc'
    ));

    if ($rolex_product->have_posts()) {
        ob_start();
        while ($rolex_product->have_posts()) {
            $rolex_product->the_post();

            $fields = get_fields();
            $model_name = $fields['model_name'];
            $spec_model_case = $fields['spec_model_case'];
            $rmc_number = $fields['rmc_number'];
            $family_handle = $fields['family_handle'];
            $spec_material = $fields['spec_material'];

            $alt = "Rolex " . $family_handle . " in " .  $spec_material . " " .  $rmc_number . "- Montecristo Jewellers";
            $mobile_image_url = "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1716941421/rolex/upright_watches_assets/upright_watch_assets/" . $rmc_number . ".webp";
            $desktop_image_url = "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1716941421/rolex/upright_watches_assets/upright_watch_assets/" . $rmc_number . ".webp";

    ?>
            <a class="f4-background-container" href="<?php esc_url(the_permalink()) ?>">
                <div class="height"></div>
                <div class="height-container">
                    <picture>
                        <source srcset="<?php echo esc_url($mobile_image_url); ?>" media="(max-width: 767px)">
                        <source srcset="<?php echo esc_url($desktop_image_url); ?>" media="(min-width: 767px)">
                        <img decoding="async" loading="lazy" src="<?php echo esc_url($desktop_image_url); ?>" alt="<?php echo $alt; ?>" width="100%" height="auto">
                    </picture>
                </div>
                <div class="watch-info">
                    <p class="legend16Bold brown">Rolex</p>
                    <p class="body24Bold brown"><?php echo $model_name; ?></p>
                    <p class="legend16Light"><?php echo $spec_model_case; ?></p>
                </div>
            </a>
    <?php
        }
        $posts_html = ob_get_clean();
        // Total posts matching the query
        $total_posts = $rolex_product->found_posts;
        // Total post visible till now
        $visible_posts = 6 * ($paged - 1);

        // Total post remianing including the one we are sending right now
        $posts_remaining = $total_posts - $visible_posts;

        $response_data = array(
            'html' => $posts_html,
            'posts_remaining' => $posts_remaining
        );

        // Return the HTML and additional data as a JSON response
        wp_send_json($response_data);
    }

    // Restore original Post Data
    wp_reset_postdata();

    die(); // Important to terminate the script
}

add_action('wp_ajax_load_more_products', 'load_more_products'); // For logged-in users
add_action('wp_ajax_nopriv_load_more_products', 'load_more_products'); // For non-logged-in users

// Adding aria label to the menu to show the active class
function add_aria_current_for_taxonomy($atts, $item, $args, $depth)
{
    // Check if you're on a rolex page who has taxonomy
    if ((is_singular('mc-rolex') && has_term('', 'rolex_category'))) {
        $terms = wp_get_post_terms(get_the_ID(), 'rolex_category');

        // Check if the current post taxonomy matches with the navigation menu name
        if ($item->title == $terms[0]->name) {
            // Add a custom attributes to the html element
            $atts['aria-current'] = 'page';
        }
    }

    // Check if you're on a rolex product page
    if (is_singular('mc-rolex-product')) {

        // Check if menu is Rolex watches to add custom attributes to the html element
        if ($item->title == 'Rolex Watches') {
            $atts['aria-current'] = 'page';
        }
    }

    return $atts;
}
add_filter('nav_menu_link_attributes', 'add_aria_current_for_taxonomy', 10, 4);

// GTM SCRIPTS
function add_gtm_to_head()
{
    ?>
    <!-- Google Tag Manager -->
    <script>
        if (window.location.hostname !== 'lime-emu-121884.hostingersite.local') {
            (function(w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({
                    'gtm.start': new Date().getTime(),
                    event: 'gtm.js'
                });
                var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s),
                    dl = l != 'dataLayer' ? '&l=' + l : '';
                j.async = true;
                j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', 'GTM-MQ42VSZ8');
        }
    </script>
    <!-- End Google Tag Manager -->
<?php }

add_action('wp_footer', 'add_gtm_to_head');

function add_gtm_noscript()
{
?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MQ42VSZ8"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
}
add_action('wp_footer', 'add_gtm_noscript');

// Adding data type attribute in the h1 element
function add_data_attribute_to_h1($content)
{

    if (is_singular('mc-rolex')) {
        // Get the current post object
        global $post;

        // Only need to add data attributes to the watches family page
        if (has_term(array('watches', 'new-watches'), 'rolex_category', $post->ID)) {

            // Get the title of the post or a custom field value
            $data_value = get_field("current_title", $post->ID); // Replace with your custom field key

            $data_value = strtolower(str_replace(' ', '-', $data_value));

            // Replace the <h1> tag with a data attribute
            $content = str_replace('<h1', '<h1 data-family="' . esc_attr($data_value) . '"', $content);
        }
    }
    return $content;
}
add_filter('the_content', 'add_data_attribute_to_h1');

// Adding custom coded images in the sitemap
add_filter('rank_math/sitemap/entry', function ($entry, $type, $object) {

    // adding shortcoded response images
    if (has_shortcode($object->post_content, 'responsive_image')) {
        $pattern = get_shortcode_regex(array('responsive_image'));
        if (preg_match_all("/$pattern/", $object->post_content, $matches)) {
            foreach ($matches[3] as $shortcode) {
                $shortcode_atts = shortcode_parse_atts($shortcode);
                if (isset($shortcode_atts['desktop_image_url'])) {
                    $entry['images'][] = array(
                        'src' => esc_url($shortcode_atts['desktop_image_url']),
                        'title' => esc_attr($object->post_title),
                        'alt' => esc_attr($shortcode_atts['alt_text']),
                    );
                }
                if (isset($shortcode_atts['mobile_image_url'])) {
                    $entry['images'][] = array(
                        'src' => esc_url($shortcode_atts['mobile_image_url']),
                        'title' => esc_attr($object->post_title),
                        'alt' => esc_attr($shortcode_atts['alt_text']),
                    );
                }
            }
        }
    }

    // adding shortcoded form images
    if (has_shortcode($object->post_content, 'rolex_form')) {
        $pattern = get_shortcode_regex(array('rolex_form'));
        if (preg_match_all("/$pattern/", $object->post_content, $matches)) {
            foreach ($matches[3] as $shortcode) {
                $shortcode_atts = shortcode_parse_atts($shortcode);
                if (isset($shortcode_atts['desktop_image_url'])) {
                    $entry['images'][] = array(
                        'src' => esc_url($shortcode_atts['desktop_image_url']),
                        'title' => esc_attr($object->post_title),
                        'alt' => esc_attr($shortcode_atts['alt_text']),
                    );
                }
                if (isset($shortcode_atts['mobile_image_url'])) {
                    $entry['images'][] = array(
                        'src' => esc_url($shortcode_atts['mobile_image_url']),
                        'title' => esc_attr($object->post_title),
                        'alt' => esc_attr($shortcode_atts['alt_text']),
                    );
                }
            }
        }
    }

    // adding
    if ($type == 'post' && $object->post_type === 'mc-rolex-product') {

        $rmc_number = get_field('rmc_number', $object->ID);
        $family_handle = get_field('family_handle', $object->ID);
        $spec_material = get_field('spec_material', $object->ID);
        $feature1_asset = get_field('feature1_asset', $object->ID);
        $feature2_asset = get_field('feature2_asset', $object->ID);
        $feature3_asset = get_field('feature3_asset', $object->ID);

        $alt = "Rolex " . $family_handle . " in " .  $spec_material . " " .  $rmc_number . "- Montecristo Jewellers";



        // Image 1: Rolex upright watch
        $entry['images'][] = [
            'src' => "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1716941421/rolex/upright_watches_assets/desktop/" . $rmc_number . "_drp-upright-bba-with-shadow.webp",
            'title' => get_the_title($object->ID),
            'alt' => $alt,
        ];

        // Image 2: Rolex specs
        $entry['images'][] = [
            'src' => "https://res.cloudinary.com/drfo99te6/f_auto,q_auto/v1/rolex/specs_assets/desktop/" . $rmc_number . "_cor-specs.webp",
            'title' => get_the_title($object->ID),
            'alt' => $alt,
        ];

        // Image 3: Model feature 1
        $entry['images'][] = [
            'src' => "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1717174410/rolex/model_feature_assets/desktop/" . $feature1_asset,
            'title' => get_the_title($object->ID),
            'alt' => $alt,
        ];

        // Image 4: Model feature 2
        $entry['images'][] = [
            'src' => "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1717174410/rolex/model_feature_assets/desktop/" . $feature2_asset,
            'title' => get_the_title($object->ID),
            'alt' => $alt,
        ];

        // Image 5: Model feature 3
        $entry['images'][] = [
            'src' => "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1717174410/rolex/model_feature_assets/desktop/" . $feature3_asset,
            'title' => get_the_title($object->ID),
            'alt' => $alt,
        ];

        // Image 6: Presentation box
        $entry['images'][] = [
            'src' => "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1717179082/rolex/watches_assets_inbox/mobile/" . $rmc_number . "_presentation-box.webp",
            'title' => get_the_title($object->ID),
            'alt' => $alt,
        ];
    }

    return $entry;
}, 10, 3);


// custom search for rolex
function custom_search_query($query)
{
    if ($query->is_search() && !is_admin() && $query->is_main_query()) {

        $search_term = strtolower(trim($query->get('s')));
        $search_term = str_replace('-', ' ', $search_term);      // dashes → spaces
        $search_term = preg_replace('/\s+/', ' ', $search_term); // multiple spaces → single
        $search_term = trim($search_term);

        // Exact match redirects
        $exact_redirects = [
            'rolex watches'        => '/rolex/watches',
            'rolex new watches'    => '/rolex/new-watches',
            'rolex 2025'           => '/rolex/new-watches',
            'rolex watchmaking'    => '/rolex/watchmaking',
            'rolex servicing'      => '/rolex/servicing',
            'rolex'                => '/rolex',
            'world of rolex'       => '/rolex/world-of-rolex',
            'rolex article'        => '/rolex/world-of-rolex',
        ];

        if (array_key_exists($search_term, $exact_redirects)) {
            wp_redirect($exact_redirects[$search_term]);
            exit();
        }

        // Partial match redirects (str_contains)
        $partial_redirects = [
            'montecristo'                     => '/rolex/montecristo-jewellers',
            'land dweller 2025'               => '/rolex/new-watches/land-dweller',
            'new land dweller'                => '/rolex/new-watches/land-dweller',
            'gmt master ii 2025'              => '/rolex/new-watches/gmt-master-ii',
            'new gmt master ii'               => '/rolex/new-watches/gmt-master-ii',
            '1908 2025'                       => '/rolex/new-watches/1908',
            'new 1908'                        => '/rolex/new-watches/1908',
            'oyster perpetual 2025'           => '/rolex/new-watches/oyster-perpetual',
            'new oyster perpetual'            => '/rolex/new-watches/oyster-perpetual',
            'datejust 2025'                   => '/rolex/new-watches/datejust',
            'new datejust'                    => '/rolex/new-watches/datejust',
            'exclusive dials'                 => '/rolex/new-watches/exclusive-dials',
            'day date'                        => '/rolex/day-date',
            'lady datejust'                   => '/rolex/lady-datejust',
            'datejust'                        => '/rolex/datejust',
            'sky dweller'                     => '/rolex/sky-dweller',
            'oyster perpetual'                => '/rolex/oyster-perpetual',
            'air king'                        => '/rolex/air-king',
            'gmt master ii'                   => '/rolex/gmt-master-ii',
            'sea dweller'                     => '/rolex/sea-dweller',
            'submariner'                      => '/rolex/submariner',
            'cosmograph daytona'              => '/rolex/cosmograph-daytona',
            'yacht master'                    => '/rolex/yacht-master',
            'explorer'                        => '/rolex/explorer',
            'deepsea'                         => '/rolex/deepsea',
            '1908'                            => '/rolex/1908',
            'land dweller'                    => '/rolex/land-dweller',
        ];

        foreach ($partial_redirects as $keyword => $redirect_url) {
            if (str_contains($search_term, $keyword)) {
                wp_redirect($redirect_url);
                exit();
            }
        }

        if (str_contains($search_term, 'rolex')) {
            wp_redirect('/rolex');
            exit();
        }
    }
}
add_action('pre_get_posts', 'custom_search_query');

// add_filter('rank_math/sitemap/enable_caching', '__return_false');

function add_span_to_primary_menu_items($items, $args)
{
    // Only apply this modification to the primary menu with 'mobile-primary-navigation' class
    if ($args->container_class === 'mobile-primary-navigation') {
        foreach ($items as $item) {
            if (in_array('menu-item-has-children', $item->classes)) {
                // Add a span to menu items with submenus
                $item->title .= '</a><svg class="submenu-toogle" role="img" enable-background="new 0 0 15 15" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg" width="10px" height="10px">
                <path d="m7.5 11.6-7.5-8.2h15z"></path>
            </svg>';
            }
        }
    }
    return $items;
}
add_filter('wp_nav_menu_objects', 'add_span_to_primary_menu_items', 10, 2);

// add google recaptcha script
function conditional_recaptcha_script()
{
    // Get current URL path
    $current_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

    // Check if path contains 'contact' or 'customize'
    if (
        (stripos($current_path, 'contact') !== false || stripos($current_path, 'customize') !== false)  &&
        stripos($current_path, 'rolex/contact-richmond') === false
    ) {
        // Output the script
        echo '<script src="https://www.google.com/recaptcha/api.js?render=6LeQyf4qAAAAAAWm4dRwb-HQ55gjfYYzwVNMIZMI"></script>';
    }
}
add_action('wp_footer', 'conditional_recaptcha_script');

// TO remove - combination of from the rolex product titles
function remove_phrase_from_mc_rolex_titles()
{
    global $wpdb;

    $phrase = ' - combination of';
    $post_type = 'mc-rolex-product';

    $query = $wpdb->prepare("
        UPDATE {$wpdb->posts}
        SET post_title = REPLACE(post_title, %s, '')
        WHERE post_type = %s
          AND post_status IN ('publish', 'draft', 'pending')
    ", $phrase, $post_type);

    $wpdb->query($query);
}
add_action('init', 'remove_phrase_from_mc_rolex_titles');

// Adding Rolex clock script
function add_rolex_clock_script()
{
    if (is_front_page() && !is_admin()) {
    ?>
        <script id="rlxSmartClock">
            (function(b, c, a, d, f, g, h, k, l, m, n) {
                b[d] = b[d] || function(p) {
                    delete b[d];
                    p.create(c.getElementById(f), [g, h, k, l, m, n])
                };
                var e = c.getElementsByTagName(a)[0];
                a = c.createElement(a);
                a.async = !0;
                a.src = "//clock.rolex.com/smart-clock/static/js/invoker.js";
                e.parentNode.insertBefore(a, e)
            })(window, document, "script", "rlxSmrtClck", "rlxSmartClock", "686eab0328edbc35d853b0340c2280ba", "en", "https://montecristo1978.com/rolex/", "richright", "dark", "gold");
        </script>
<?php
    }
}
add_action('wp_footer', 'add_rolex_clock_script');
