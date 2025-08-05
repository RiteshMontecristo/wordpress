<?php

/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package storefront
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
    <link rel="preconnect" href="https://use.typekit.net" crossorigin>
    <link rel="preconnect" href="https://p.typekit.net" crossorigin>
    <link rel="stylesheet" href="https://p.typekit.net/p.css?s=1&k=rnn7cvr&ht=tk&f=53891.53892.53893.53895&a=282868177&app=typekit&e=css" media="all">

    <?php
    $retailer_name = "Montecristo Jewellers";
    // SEO
    if (is_singular('mc-rolex-product')) {
        if (function_exists('get_field')) {
            $model_name = get_field('model_name');
            $spec_material = get_field('spec_material');
            $diameter =  explode(",", get_field('spec_model_case'))[1];
            $diameter = preg_match("/\d/", $model_name) == 0 ?  explode(",", get_field('spec_model_case'))[1] : "";

            echo '<meta name="description" content="Discover the Rolex ' . $model_name . $diameter . ' watch in ' . $spec_material . ' at ' .  $retailer_name . ', an Official Rolex Retailer authorised to sell and maintain Rolex watches.">';
            echo '<meta name="keywords" content="Rolex ' . $model_name . ', ' . $model_name . ', ' . $model_name . ' watch, Rolex ' . $model_name . ' watch, ' . $spec_material . ' watch, Rolex in ' . $spec_material . ', Rolex ' . $diameter . ' ' . $spec_material . ' ">';
        }
    }

    // if (is_singular('mc-rolex')) {
    //     $title = get_field('current_title');

    //     // Rolex Watches
    //     if (has_term('watches', 'rolex_category')) {

    //         echo "<meta name=\"description\" content=\"Browse Rolex " . $title . " watches online at " . $retailer_name . ", an Official Authorised Retailer of men's and ladies Rolex watches. Discover more at " . $retailer_name . ".\">";
    //         echo "<meta name='keywords' content='Rolex " . $title . " Watches, Rolex " . $title . "'>";
    //     }

    //     // Rolex New Watches
    //     else if (has_term('new-watches', 'rolex_category')) {

    //         $is_exclusive = stripos(get_the_title(), 'exclusive dial') !== false;

    //         if ($is_exclusive) {
    //             echo "<meta name=\"description\" content=\"Discover the exclusive new dials of the Rolex Cosmograph Daytona, GMT‑Master II and Sky‑Dweller at Montecristo Jewellers in Richmond, BC.\">";
    //             echo "<meta name='keywords' content='Rolex 2025, New Rolex Dials, Cosmograph Daytona, GMT‑Master II, Sky‑Dweller, Exclusive Rolex'>";
    //         } else {
    //             echo "<meta name=\"description\" content=\"" . $retailer_name . " introduces the new Rolex " . $title . ", the latest in Swiss luxury watches by Rolex. Discover the unique features of this new watch now.\">";
    //             echo "<meta name='keywords' content='New " . $title . ", New Rolex " . $title . ", New Rolex " . $title . "  Watches'>";
    //         }
    //     }

    //     // Rolex Blogs
    //     else if (has_term('world-of-rolex', 'rolex_category')) {;
    //         echo "<meta name=\"description\" content=\"Discover " . $title . ". Stay up-to-date with the latest Rolex news | " . $retailer_name . "\">";
    //         echo "<meta name='keywords' content='$title'>";
    //     } else {
    //         $keywords = get_field('keywords');
    //         echo "<meta name='keywords' content='$keywords'>";
    //     }
    // }

    $current_url = $_SERVER['REQUEST_URI'];
    $pattern = '#^/rolex/(servicing|history|team|showroom|montecristo-jewellers|contact-richmond)/?$#';

    if (preg_match($pattern, $current_url)) {

    ?>
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "JewelryStore",
                "name": "Montecristo Jewellers Inc",
                "address": {
                    "@type": "PostalAddress",
                    "streetAddress": "3055 Kingsway",
                    "addressLocality": "Vancouver",
                    "addressRegion": "BC",
                    "postalCode": "V5R 5J8",
                    "addressCountry": "CA"
                },
                "telephone": "(604) 899-8866",
                "url": "<?php echo get_site_url(); ?>",
                "logo": "<?php echo get_stylesheet_directory_uri(); ?>/assets/logo.webp",
                "image": "<?php echo get_stylesheet_directory_uri(); ?>/assets/montecristo-symbol-white.png",
                "sameAs": [
                    "https://www.facebook.com/montecristojewellers",
                    "https://www.instagram.com/montecristojewellers",
                    "https://www.linkedin.com/company/montecristojewellers",
                    "https://www.youtube.com/@montecristojewellers",
                    "https://www.pinterest.ca/montecristojewellers"
                ],
                "department": [{
                        "@type": "Place",
                        "name": "Richmond Centre - Official Rolex Retailer",
                        "telephone": "(604) 263-3611",
                        "email": "o@montecristo1978.com",
                        "address": {
                            "@type": "PostalAddress",
                            "streetAddress": "#1564 6551 No.3 RD",
                            "addressLocality": "Richmond",
                            "addressRegion": "BC",
                            "postalCode": "V6Y 2B6",
                            "addressCountry": "CA"
                        },
                        "openingHoursSpecification": [{
                                "@type": "OpeningHoursSpecification",
                                "closes": "19:00:00",
                                "dayOfWeek": "https://schema.org/Sunday",
                                "opens": "11:00:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "21:00:00",
                                "dayOfWeek": "https://schema.org/Monday",
                                "opens": "10:00:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "21:00:00",
                                "dayOfWeek": "https://schema.org/Tuesday",
                                "opens": "10:00:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "21:00:00",
                                "dayOfWeek": "https://schema.org/Wednesday",
                                "opens": "10:00:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "21:00:00",
                                "dayOfWeek": "https://schema.org/Thursday",
                                "opens": "10:00:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "21:00:00",
                                "dayOfWeek": "https://schema.org/Friday",
                                "opens": "10:00:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "21:00:00",
                                "dayOfWeek": "https://schema.org/Saturday",
                                "opens": "10:00:00"
                            }
                        ],
                        "priceRange": "$$$"
                    },
                    {
                        "@type": "Place",
                        "name": "Montecristo Jewellers",
                        "telephone": "(604) 899-8866",
                        "email": "d@montecristo1978.com",
                        "address": {
                            "@type": "PostalAddress",
                            "streetAddress": "406 Hornby St",
                            "addressLocality": "Vancouver",
                            "addressRegion": "BC",
                            "postalCode": "V6C 1C8",
                            "addressCountry": "CA"
                        },
                        "openingHoursSpecification": [{
                                "@type": "OpeningHoursSpecification",
                                "closes": "00:00:00",
                                "dayOfWeek": "https://schema.org/Sunday",
                                "opens": "00:00:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "21:30:00:00",
                                "dayOfWeek": "https://schema.org/Monday",
                                "opens": "10:30:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "17:30:00:00",
                                "dayOfWeek": "https://schema.org/Tuesday",
                                "opens": "10:30:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "17:30:00:00",
                                "dayOfWeek": "https://schema.org/Wednesday",
                                "opens": "10:30:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "17:30:00:00",
                                "dayOfWeek": "https://schema.org/Thursday",
                                "opens": "10:30:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "17:30:00:00",
                                "dayOfWeek": "https://schema.org/Friday",
                                "opens": "10:30:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "17:30:00:00",
                                "dayOfWeek": "https://schema.org/Saturday",
                                "opens": "10:30:00"
                            }
                        ],
                        "priceRange": "$$$"
                    },
                    {
                        "@type": "Place",
                        "name": "Montecristo Jewellers",
                        "telephone": "(604) 325-2116",
                        "email": "m@montecristo1978.com",
                        "address": {
                            "@type": "PostalAddress",
                            "streetAddress": "2134A-4700 KINGSWAY",
                            "addressLocality": "Burnaby",
                            "addressRegion": "BC",
                            "postalCode": "V5H 4M1",
                            "addressCountry": "CA"
                        },
                        "openingHoursSpecification": [{
                                "@type": "OpeningHoursSpecification",
                                "closes": "10:00:00",
                                "dayOfWeek": "https://schema.org/Sunday",
                                "opens": "10:00:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "21:00:00:00",
                                "dayOfWeek": "https://schema.org/Monday",
                                "opens": "10:00:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "21:00:00:00",
                                "dayOfWeek": "https://schema.org/Tuesday",
                                "opens": "10:00:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "21:00:00:00",
                                "dayOfWeek": "https://schema.org/Wednesday",
                                "opens": "10:00:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "21:00:00:00",
                                "dayOfWeek": "https://schema.org/Thursday",
                                "opens": "10:00:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "21:00:00:00",
                                "dayOfWeek": "https://schema.org/Friday",
                                "opens": "10:00:00"
                            },
                            {
                                "@type": "OpeningHoursSpecification",
                                "closes": "21:00:00:00",
                                "dayOfWeek": "https://schema.org/Saturday",
                                "opens": "10:00:00"
                            }
                        ],
                        "priceRange": "$$$"
                    }
                ]
            }
        </script>
    <?php } else { ?>
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "Organization",
                "name": "Montecristo Jewellers Inc",
                "telephone": "(604) 899-8866",
                "url": "<?php echo get_site_url(); ?> ",
                "logo": "<?php echo get_stylesheet_directory_uri(); ?>/assets/logo.webp",
                "sameAs": [
                    "https://www.facebook.com/montecristojewellers",
                    "https://www.instagram.com/montecristojewellers",
                    "https://www.linkedin.com/company/montecristojewellers",
                    "https://www.youtube.com/@montecristojewellers",
                    "https://www.pinterest.ca/montecristojewellers"
                ],
                "foundingDate": "1978",
                "founders": [{
                    "@type": "Person",
                    "name": "Pasquale Cusano"
                }],
                "description": "Montecristo Jewellers specializes in high-end watches and jewelry, offering an exquisite range of luxury items.",
                "email": "corporate@montecristo1978.com",
                "address": {
                    "@type": "PostalAddress",
                    "streetAddress": "3055 Kingsway",
                    "addressLocality": "Vancouver",
                    "addressRegion": "BC",
                    "postalCode": "V5R 5J8",
                    "addressCountry": "CA"
                }
            }
        </script>
    <?php }

    wp_head(); ?>
</head>

<body <?php body_class(); ?>>

    <?php wp_body_open(); ?>

    <?php do_action('storefront_before_site'); ?>

    <div id="page" class="hfeed site">
        <?php do_action('storefront_before_header'); ?>

        <header id="masthead" class="site-header" role="banner">

            <div class="col-full">
                <a class="skip-link screen-reader-text" href="#site-navigation">Skip to navigation</a>
                <a class="skip-link screen-reader-text" href="#content">Skip to content</a>

                <div class="first-header">

                    <!-- <div class="mobile-icons" role="navigation" aria-label="Menu Icons">
                        <div class="shopping-bag-icon">
                            <a href="<?php echo wc_get_cart_url(); ?>" aria-label="View your shopping cart">
                                <img height="25" width="25" src="<?php echo get_stylesheet_directory_uri() . "/assets/icons/cart.svg" ?>" alt="Shopping Cart Icon" />
                            </a>
                        </div>
                        <a href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>" aria-label="Account">
                            <img height="25" width="25" src="<?php echo get_stylesheet_directory_uri() . "/assets/icons/account.svg" ?>" alt="Account Icon" />
                        </a>
                    </div> -->

                    <a href="<?php echo esc_url(home_url('/')); ?>" class="custom-logo-link" rel="home" aria-current="page">
                        <img width="250px" height="40px" fetchpriority="high" src="<?php echo get_stylesheet_directory_uri() . "/assets/MJI1978-dal-min.svg"  ?> " class="custom-logo exclude-litespeed-lazyload" alt="Montecristo Jewellers Homepage" decoding="async"></a>

                    <a href="/rolex">
                        <iframe id="rolex_retailer" title="Rolex Official Retailer" src="https://static.rolex.com/retailers/clock/?colour=gold&amp;apiKey=5321aee1ae68296fa0140a49a8d7de53&amp;lang=en" class="rolex-clock exclude-litespeed-lazyload" scrolling="NO" frameborder="NO"></iframe>
                    </a>

                    <button id="site-navigation-menu-toggle" class="menu-toggle" aria-controls="site-navigation" aria-expanded="false"><span></span></button>
                </div>
            </div>

            <div class="storefront-primary-navigation">
                <div class="col-full">
                    <nav id="site-navigation" class="main-navigation" role="navigation" aria-label="Primary Navigation">

                        <div class="primary-navigation">
                            <ul class="nav-menu">
                                <li class="has-children-menu">
                                    <span>Rolex</span>
                                    <ul class="children-menu-container rolex-menu-container">
                                        <div>
                                            <li><a href="/rolex">Discover Rolex</a></li>
                                            <li><a href="/rolex/watches">Rolex Watches</a></li>
                                            <li><a href="/rolex/new-watches">New Watches 2025</a></li>
                                            <li><a href="/rolex/watchmaking">Watchmaking</a></li>
                                            <li><a href="/rolex/servicing">Servicing</a></li>
                                            <li><a href="/rolex/world-of-rolex">World of Rolex</a></li>
                                            <li><a href="/rolex/montecristo-jewellers">Rolex at Montecristo</a></li>
                                            <li><a href="/rolex/contact-richmond">Contact Us</a></li>
                                        </div>
                                        <div>
                                            <li><a href="/rolex/air-king">Rolex Air-King</a></li>
                                            <li><a href="/rolex/cosmograph-daytona">Rolex Cosmograph Daytona</a></li>
                                            <li><a href="/rolex/datejust">Rolex Datejust</a></li>
                                            <li><a href="/rolex/deepsea">Rolex Deepsea</a></li>
                                            <li><a href="/rolex/day-date">Rolex Day-Date</a></li>
                                            <li><a href="/rolex/explorer">Rolex Explorer</a></li>
                                            <li><a href="/rolex/lady-datejust">Rolex Lady-Datejust</a></li>
                                            <li><a href="/rolex/land-dweller">Rolex Land-Dweller</a></li>
                                        </div>
                                        <div>
                                            <li><a href="/rolex/oyster-perpetual">Rolex Oyster Perpetual</a></li>
                                            <li><a href="/rolex/sea-dweller">Rolex Sea-Dweller</a></li>
                                            <li><a href="/rolex/sky-dweller">Rolex Sky-Dweller</a></li>
                                            <li><a href="/rolex/submariner">Rolex Submariner</a></li>
                                            <li><a href="/rolex/yacht-master">Rolex Yacht-Master</a></li>
                                            <li><a href="/rolex/gmt-master-ii">Rolex GMT-Master II</a></li>
                                            <li><a href="/rolex/1908">Rolex 1908</a></li>
                                        </div>
                                    </ul>
                                </li>
                                <li class="has-children-menu">
                                    <span>Jewellery</span>
                                    <ul class="children-menu-container">
                                        <li>
                                            <a href="/montecristo">Montecristo</a>
                                            <ul class="children-menu-container">
                                                <li><a href="/jewellery/bracelets">Bracelets</a></li>
                                                <li><a href="/jewellery/earrings">Earrings</a></li>
                                                <li><a href="/jewellery/engagement-rings">Engagement Rings</a></li>
                                                <li><a href="/jewellery/pendants-necklaces">Pendants & Necklaces</a></li>
                                                <li><a href="/jewellery/rings">Rings</a></li>
                                                <li><a href="/jewellery/wedding-bands">Wedding Bands</a></li>
                                            </ul>
                                        </li>
                                        <li><a href="/designer/annamaria-cammilli">Annamaria Cammilli</a></li>
                                        <li><a href="/designer/faberge">Fabergé</a></li>
                                        <li><a href="/designer/messika">Messika</a></li>
                                        <li><a href="/designer/mikimoto">Mikimoto</a></li>
                                        <li><a href="/designer/pomellato">Pomellato</a></li>
                                        <li><a href="/designer/roberto-coin">Roberto Coin</a></li>
                                        <li><a href="/wellendorff">Wellendorff</a></li>
                                    </ul>
                                </li>
                                <li class="has-children-menu">
                                    <span>Watches</span>
                                    <ul class="children-menu-container">
                                        <li><a href="/rolex">Rolex</a></li>
                                        <li><a href="/watches/bellross/">Bell&Ross</a></li>
                                        <li><a href="/watches/blancpain">Blancpain</a></li>
                                        <li><a href="/watches/breguet">Breguet</a></li>
                                        <li><a href="/watches/corum">Corum</a></li>
                                        <li><a href="/watches/faberge-watches">Fabergé</a></li>
                                        <li><a href="/watches/girard-perregaux">Girard-Perregaux</a></li>
                                        <li><a href="/watches/glashutte-original">Glashutte Original</a></li>
                                        <li><a href="/watches/longines">Longines</a></li>
                                        <li><a href="/watches/mido">Mido</a></li>
                                        <li><a href="/watches/omega">OMEGA</a></li>
                                    </ul>
                                </li>
                                <li class="has-children-menu">
                                    <a href="/boutiques"><span>Boutiques</span></a>
                                    <ul class="children-menu-container">
                                        <li><a href="/boutiques/richmond">Richmond</a></li>
                                        <li><a href="/boutiques/downtown">Downtown Vancouver</a></li>
                                        <li><a href="/boutiques/metrotown">Metrotown</a></li>
                                    </ul>
                                </li>
                                <li><a href="/our-story"><span>About Us</span></a></li>
                                <li><a href="/contact"><span>Contact Us</span></a></li>
                            </ul>
                        </div>

                        <!-- <div class="menu-icons" role="navigation" aria-label="Menu Icons"> -->
                        <!-- <div class="shopping-bag-icon">
                                <a href="<?php echo wc_get_cart_url(); ?>" aria-label="View your shopping cart">
                                    <img height="25" width="25" src="<?php echo get_stylesheet_directory_uri() . "/assets/icons/cart.svg" ?>" alt="Shopping Cart Icon" />
                                </a>
                            </div>

                            <a href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>" aria-label="Account">
                                <img height="25" width="25" src="<?php echo get_stylesheet_directory_uri() . "/assets/icons/account.svg" ?>" alt="Account Icon" />
                            </a> -->

                        <!-- <div class="search-container">
                                <button id="headerSearchBtn" aria-label="Open Search" class="search-toggle">
                                    <img height="25" width="25" src="<?php echo get_stylesheet_directory_uri() . "/assets/icons/search.svg" ?>" alt="Search Icon" />
                                </button>
                                <div class="search-form hidden" id="searchForm">
                                    <div class="widget woocommerce widget_product_search">
                                        <form role="search" method="get" class="woocommerce-product-search" action="<?php echo esc_url(home_url('/')); ?>">
                                            <label class="screen-reader-text" for="woocommerce-product-search-field-0">Search for:</label>
                                            <input type="search" id="woocommerce-product-search-field-0" class="search-field" placeholder="Search..." value="" name="s" aria-required="true" />
                                            <button type="submit" value="Search" class="">Search</button>
                                            <input type="hidden" name="post_type" value="product" />
                                        </form>
                                    </div>
                                </div>
                            </div> -->
                        <!-- </div> -->

                    </nav><!-- #site-navigation -->
                </div>
            </div>

            <div id="mobilePrimaryNavigationContainer" class="hidden mobile-primary-navigation-container">

                <nav>
                    <button id="mobileMenuCloseBtn" class="mobile-menu-close-btn" aria-label="close menu"></button>
                    <a class="mobile-rolex-clock" href="/rolex">
                        <iframe id="rolex_retailer" title="Rolex Official Retailer" src="https://static.rolex.com/retailers/clock/?colour=gold&amp;apiKey=5321aee1ae68296fa0140a49a8d7de53&amp;lang=en" class="rolex-clock" scrolling="NO" frameborder="NO"></iframe>
                    </a>
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'container_class' => 'mobile-primary-navigation',
                    )); ?>

                    <!-- <div class="mobile-search-form" id="mobileSearchForm">
                        <div class="widget woocommerce widget_product_search">
                            <form role="search" method="get" class="woocommerce-product-search" action="<?php echo esc_url(home_url('/')); ?>">
                                <label class="screen-reader-text" for="woocommerce-product-search-field-0">Search for:</label>
                                <input type="search" id="woocommerce-product-search-field-0-mobile" class="search-field" placeholder="Search..." value="" name="s" aria-required="true" />
                                <button type="submit" value="Search" class="">Search</button>
                                <input type="hidden" name="post_type" value="product" />
                            </form>
                        </div>
                    </div> -->
                    <a class="mobile-logo" href="<?php echo esc_url(home_url('/')); ?>" class="custom-logo-link" rel="home" aria-current="page"><img src="<?php echo get_stylesheet_directory_uri() . "/assets/logo.webp" ?> " class="custom-logo" alt="Montecristo Jewellers Homepage" decoding="async"></a>
                </nav>

            </div>
        </header>

        <?php
        /**
         * Functions hooked in to storefront_before_content
         *
         * @hooked storefront_header_widget_region - 10
         * @hooked woocommerce_breadcrumb - 10
         */
        // do_action('storefront_before_content');
        ?>

        <div id="content" class="site-content" tabindex="-1">
            <?php

            do_action('storefront_content_top');
