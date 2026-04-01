<?php

// Adding parent styles with our css styles
add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');

// grabbing the countries and code
include_once get_stylesheet_directory() . '/inc/countries.php';

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

function my_load_scripts()
{
    wp_enqueue_script(
        'splidejs-script',
        get_stylesheet_directory_uri() . '/splidejs/splide.min.js',
        array(),
        '1.0.0',
        array(
            'in_footer' => true,
            'strategy'  => 'defer',
        )
    );

    wp_enqueue_script(
        'normal-script',
        get_stylesheet_directory_uri() . '/scripts/index.js',
        array('splidejs-script'),
        '1.0.0',
        array(
            'in_footer' => true,
            'strategy'  => 'defer',
        )
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

    // SheetJS library from CDN
    wp_enqueue_script(
        'sheetjs',
        'https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js',
        array(),
        '0.18.7',
        true
    );

    wp_enqueue_script(
        'admin-script',
        get_stylesheet_directory_uri() . '/inventory/scripts/index.js',
        array('zebra-printer', 'zebra-printer-2', 'sheetjs'),
        '1.0.0',
        true
    );

    wp_localize_script('admin-script', 'ajax_inventory', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'sales_css_url' => get_stylesheet_directory_uri() . '/inventory/styles/sales.css',
        'find_invoice_css_url' => get_stylesheet_directory_uri() . '/inventory/styles/find_invoice.css',
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

// Adding CMB@ functions
require_once get_stylesheet_directory() . '/cmb2/fields.php';

// Adding woocommerce functions
require get_stylesheet_directory() . '/woocommerce/function.php';

require get_stylesheet_directory() . '/inventory/functions.php';

function remove_breadcrumb_for_cpt()
{
    if (!is_product()) {
        remove_action('storefront_before_content', 'woocommerce_breadcrumb', 10);
    }
}
add_action('storefront_before_content', 'remove_breadcrumb_for_cpt', 5);


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
    return $entry;
}, 10, 3);

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
        echo '<script src="https://www.google.com/recaptcha/api.js?render=6LeS14AsAAAAACGU-vMDvf6QF0nkxBY_LJbX2ljg"></script>';
    }
}
add_action('wp_footer', 'conditional_recaptcha_script');
