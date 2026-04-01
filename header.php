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
            },
            "department": [{
                    "@type": "Place",
                    "name": "Richmond Centre",
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
    <?php wp_head(); ?>
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
                        <img width="250px" height="40px" fetchpriority="high" src="<?php echo get_stylesheet_directory_uri() . "/assets/MJI1978-dal-min.svg"  ?> " class="custom-logo exclude-litespeed-lazyload" title="Montecristo Jewellers" alt="Montecristo Jewellers" decoding="async"></a>
                 
                    <button id="site-navigation-menu-toggle" class="menu-toggle" aria-controls="site-navigation" aria-expanded="false"><span></span></button>
                </div>
            </div>

            <div class="storefront-primary-navigation">
                <div class="col-full">
                    <nav id="site-navigation" class="main-navigation" role="navigation" aria-label="Primary Navigation">

                        <div class="primary-navigation">
                            <ul class="nav-menu">
                                <li class="has-children-menu">
                                    <span>Jewellery</span>
                                    <ul class="children-menu-container">
                                        <li>
                                            <a href="/montecristo">Montecristo</a>
                                            <ul class="children-menu-container">
                                                <li><a href="/jewellery/bracelets">Bracelets</a></li>
                                                <li><a href="/jewellery/earrings">Earrings</a></li>
                                                <li><a href="/jewellery/pendants-necklaces">Pendants & Necklaces</a></li>
                                                <li><a href="/jewellery/rings">Rings</a></li>
                                                <li><a href="/jewellery/wedding-bands">Wedding Bands</a></li>
                                            </ul>
                                        </li>
                                        <li><a href="/designer/cammillifirenze">Cammilli Firenze</a></li>
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

                    </nav><!-- #site-navigation -->
                </div>
            </div>

            <div id="mobilePrimaryNavigationContainer" class="hidden mobile-primary-navigation-container">

                <nav>
                    <button id="mobileMenuCloseBtn" class="mobile-menu-close-btn" aria-label="close menu"></button>
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'container_class' => 'mobile-primary-navigation',
                    )); ?>

                    <a class="mobile-logo" href="<?php echo esc_url(home_url('/')); ?>" class="custom-logo-link" rel="home" aria-current="page"><img src="<?php echo get_stylesheet_directory_uri() . "/assets/logo.webp" ?> " class="custom-logo" title="Montecristo Jewellers" alt="Montecristo Jewellers" decoding="async"></a>
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
