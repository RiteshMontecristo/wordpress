<header class="rolex-header">
    <a class="skip-link screen-reader-text" href="#rolex-page">Skip to content</a>
    <nav class="rolex-nav grid">
        <a class="rolex-plaque-link" href="/rolex">
            <img width="120" height="60" class="rolex-plaque" title="rolex-plaque" src="/wp-content/uploads/rolex/Rolex-retailer-plaque-120x60_en_i9uvq9.webp" alt="Rolex plaque" />
        </a>


        <button class="menu-button" id="menu-button" aria-label="Menu">
            <span>Menu</span>
            <svg role="img" aria-hidden="true" id="menu-icon" enable-background="new 0 0 15 15" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg" width="12px" height="12px">
                <path d="m7.5 11.6-7.5-8.2h15z"></path>
            </svg>
        </button>

        <!-- Desktop Menu -->
        <?php
        if (has_nav_menu('rolex-menu')) {
            wp_nav_menu(array(
                'theme_location'  => 'rolex-menu',
                'menu_id'         => 'rolex-dekstop-menu',
                'menu_class'      => 'rolex-desktop-menu-class grid',
            ));
        } ?>
    </nav>

    <!-- Mobile menu -->
    <?php
    if (has_nav_menu('rolex-menu')) {
        wp_nav_menu(array(
            'theme_location'  => 'rolex-menu',
            'menu_id'         => 'rolex-menu',
            'menu_class'      => 'rolex-menu-class grid rolex-mobile',
            'container_id'    => 'mobileMenuContainer',
            'container_class' => 'mobile-menu-container menu-hide'
        ));
    } ?>

    <!-- Breadcrumbs -->
    <?php
    $post_type = get_post_type();
    // Check if ACF is installed and active
    if (function_exists('get_field')) {

        if (get_field("current_title") || $post_type == 'mc-rolex-product') {

            $i = 2;
            $itemList = [];
    ?>
            <div class="breadcrumb grid-nospace">
                <div class="breadcrumb-inner-container desktop">
                    <a class="fixed14" href="/rolex/">Rolex</a>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="10px" height="10px" viewBox="0 0 12 12" version="1.1">
                        <g>
                            <path d="M 9.601562 6 L 8.558594 7.121094 L 3.679688 12 L 2.480469 10.800781 L 7.359375 5.921875 L 2.398438 1.121094 L 3.601562 0 Z M 9.601562 6 "></path>
                        </g>
                    </svg>
                    <?php
                    $itemList[] = [
                        "@type" => "ListItem",
                        "position" => 1,
                        "name" => "Rolex",
                        "item" => "https://montecristo1978.com/rolex/"
                    ]; ?>

                    <!-- If we are in rolex product we need to insert the extra breadcrumb -->
                    <?php if ($post_type === 'mc-rolex-product') { ?>
                        <a class="fixed14" href="/rolex/watches">Rolex watches</a>
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="10px" height="10px" viewBox="0 0 12 12" version="1.1">
                            <g>
                                <path d="M 9.601562 6 L 8.558594 7.121094 L 3.679688 12 L 2.480469 10.800781 L 7.359375 5.921875 L 2.398438 1.121094 L 3.601562 0 Z M 9.601562 6 "></path>
                            </g>
                        </svg>
                    <?php
                        $itemList[] = [
                            "@type" => "ListItem",
                            "position" => $i,
                            "name" => "Rolex watches",
                            "item" => "https://montecristo1978.com/rolex/watches"
                        ];
                        $i++;
                    } ?>

                    <!-- Rolex Category Terms -->
                    <?php
                    $rolex_terms = get_the_terms(get_the_ID(), 'rolex_category');
                    if ($rolex_terms && !is_wp_error($rolex_terms)) {
                    ?>
                        <a class="fixed14" href="/rolex/<?php echo $rolex_terms[0]->slug ?>">
                            <?php echo esc_html($rolex_terms[0]->name); ?>
                        </a>
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="10px" height="10px" viewBox="0 0 12 12" version="1.1">
                            <g>
                                <path d="M 9.601562 6 L 8.558594 7.121094 L 3.679688 12 L 2.480469 10.800781 L 7.359375 5.921875 L 2.398438 1.121094 L 3.601562 0 Z M 9.601562 6 "></path>
                            </g>
                        </svg>
                    <?php
                        $itemList[] = [
                            "@type" => "ListItem",
                            "position" => $i,
                            "name" => esc_html($rolex_terms[0]->name),
                            "item" => "https://montecristo1978.com/rolex/" . $rolex_terms[0]->slug
                        ];
                        $i++;
                    } ?>

                    <!-- Rolex Product Category Terms -->
                    <?php
                    $rolex_product_terms = get_the_terms(get_the_ID(), 'rolex_product_category');
                    if ($rolex_product_terms && !is_wp_error($rolex_product_terms)) {
                    ?>
                        <a class="fixed14" href="/rolex/<?php echo $rolex_product_terms[0]->slug ?>">
                            <?php echo esc_html($rolex_product_terms[0]->name); ?>
                        </a>
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="10px" height="10px" viewBox="0 0 12 12" version="1.1">
                            <g>
                                <path d="M 9.601562 6 L 8.558594 7.121094 L 3.679688 12 L 2.480469 10.800781 L 7.359375 5.921875 L 2.398438 1.121094 L 3.601562 0 Z M 9.601562 6 "></path>
                            </g>
                        </svg>
                    <?php
                        $itemList[] = [
                            "@type" => "ListItem",
                            "position" => $i,
                            "name" => esc_html($rolex_product_terms[0]->name),
                            "item" => "https://montecristo1978.com/rolex/" . $rolex_product_terms[0]->slug
                        ];
                        $i++;
                    } ?>

                    <!-- Current Page Title -->
                    <span class="active fixed14"><?php the_field("current_title") ?></span>
                    <?php
                    $itemList[] = [
                        "@type" => "ListItem",
                        "position" => $i,
                        "name" => get_field("current_title")
                    ];
                    ?>
                </div>

                <!-- MOBILE BREADCRUMBS -->
                <div class="breadcrumb-inner-container mobile">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="10px" height="10px" viewBox="0 0 12 12" version="1.1">
                        <g>
                            <path d="M 9.601562 6 L 8.558594 7.121094 L 3.679688 12 L 2.480469 10.800781 L 7.359375 5.921875 L 2.398438 1.121094 L 3.601562 0 Z M 9.601562 6 "></path>
                        </g>
                    </svg>
                    <?php if (get_the_terms(get_the_ID(), 'rolex_category') || get_the_terms(get_the_ID(), 'rolex_product_category')) { ?>
                        <!-- Rolex Page Category Terms -->
                        <?php
                        $rolex_terms = get_the_terms(get_the_ID(), 'rolex_category');
                        if ($rolex_terms && !is_wp_error($rolex_terms)) {
                        ?>
                            <a class="fixed14" href="/rolex/<?php echo $rolex_terms[0]->slug ?>">
                                <?php echo esc_html($rolex_terms[0]->name); ?>
                            </a>
                        <?php } ?>

                        <!-- Rolex Product Category Terms -->
                        <?php
                        $rolex_product_terms = get_the_terms(get_the_ID(), 'rolex_product_category');
                        if ($rolex_product_terms && !is_wp_error($rolex_product_terms)) {
                        ?>
                            <a class="fixed14" href="/rolex/<?php echo $rolex_product_terms[0]->slug ?>">
                                <?php echo esc_html($rolex_product_terms[0]->name); ?>
                            </a>
                        <?php }
                    } else {
                        ?>
                        <a href="/rolex" class="fixed14">Rolex</a>
                    <?php
                    } ?>

                </div>

            </div>
            <script type="application/ld+json">
                {
                    "@context": "https://schema.org",
                    "@type": "BreadcrumbList",
                    "itemListElement": <?php echo json_encode($itemList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
                }
            </script>
    <?php

        }
    } else {
        echo "Please install and activate acf plugin as it is required";
    }
    ?>
</header>