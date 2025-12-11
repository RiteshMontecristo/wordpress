<?php

$keepExpoloringArr = array(
    array(
        "desktop_url" => "/wp-content/uploads/rolex/keep-exploring/landscape/rolex-keep-exploring-discover-rolex-shoot_shop_geneva_retail_campaign_logo_pub-2-iso_01_landscape.webp",
        "mobile_url" => "/wp-content/uploads/rolex/keep-exploring/portrait/rolex-keep-exploring-discover-rolex-shoot_shop_geneva_retail_campaign_logo_pub-2-iso_01-portrait.webp",
        "title" => "Discover Rolex",
        "url" => "/rolex"
    ),
    array(
        "desktop_url" => "/wp-content/uploads/rolex/keep-exploring/landscape/rolex-keep-exploring-rolex-watches-1945_oyster_perpetual_datejust_1802jva_m126333_0010_1802jva_002-landscape.webp",
        "mobile_url" => "/wp-content/uploads/rolex/keep-exploring/portrait/rolex-keep-exploring-rolex-watches-1945_oyster_perpetual_datejust_1802jva_m126333_0010_1802jva_002-portrait.webp",
        "title" => "Rolex Watches",
        "url" => "/rolex/watches"
    ),
    array(
        "desktop_url" => "/wp-content/uploads/rolex/keep-exploring/landscape/rolex-keep-exploring-new-watches-2025_m127334-0001_2501fj_003-landscape.webp",
        "mobile_url" => "/wp-content/uploads/rolex/keep-exploring/portrait/rolex-keep-exploring-new-watches-2025_m127334-0001_2501fj_003-portrait.webp",
        "title" => "New Watches 2025",
        "url" => "/rolex/new-watches"
    ),
    array(
        "desktop_url" => "/wp-content/uploads/rolex/keep-exploring/landscape/rolex-keep-exploring-rolex-accessories-rolexcufflinks_2403jva_001-landscape.webp",
        "mobile_url" => "/wp-content/uploads/rolex/keep-exploring/portrait/rolex-keep-exploring-rolex-accessories-rolexcufflinks_2403jva_001-portrait.webp",
        "title" => "Rolex Accessories",
        "url" => "/rolex/accessories"
    ),
    array(
        "desktop_url" => "/wp-content/uploads/rolex/keep-exploring/landscape/rolex-keep-exploring-watchmaking-2023%20watchmaking%20features%20manifesto%20still_digital_master_rgb-landscape.webp",
        "mobile_url" => "/wp-content/uploads/rolex/keep-exploring/portrait/rolex-keep-exploring-watchmaking-2023%20watchmaking%20features%20manifesto%20still_digital_master_rgb-portrait.webp",
        "title" => "Watchmaking",
        "url" => "/rolex/watchmaking"
    ),
    array(
        "desktop_url" => "/wp-content/uploads/rolex/keep-exploring/landscape/rolex-keep-exploring-servicing-2234_rolex-sav_retailers_176_controle-final_mise_heure_v2-landscape.webp",
        "mobile_url" => "/wp-content/uploads/rolex/keep-exploring/portrait/rolex-keep-exploring-servicing-2234_rolex-sav_retailers_176_controle-final_mise_heure_v2-portrait.webp",
        "title" => "Servicing",
        "url" => "/rolex/servicing"
    ),
    array(
        "desktop_url" => "/wp-content/uploads/rolex/keep-exploring/landscape/rolex-keep-exploring-hub-world-of-rolex-jb1_2564_070525_landscape.webp",
        "mobile_url" => "/wp-content/uploads/rolex/keep-exploring/portrait/rolex-keep-exploring-hub-world-of-rolex-jb1_2564_070525_portrait.webp",
        "title" => "World of Rolex",
        "url" => "/rolex/world-of-rolex"
    ),
    array(
        "desktop_url" => "/wp-content/uploads/rolex/keep-exploring/landscape/rolex-keep-exploring-contact-us-A7404109-landscape.webp",
        "mobile_url" => "/wp-content/uploads/rolex/keep-exploring/portrait/rolex-keep-exploring-contact-us-A7404109-portrait.webp",
        "title" => "Contact us",
        "url" => "/rolex/contact-richmond"
    )
);

$current_post_id = get_the_ID();
$taxonomies = get_the_terms($current_post_id, 'rolex_category');

// get the url
$url = get_permalink($current_post_id);

// Split the url on "/"
$splitUrlArr = explode('/', $url);

// removing the first 3 and last element
$paramArr = array_slice($splitUrlArr, 3, -1);

// changing the param to string from array
$param = "/" . join("/", $paramArr);

// To show the active state in keep exploring
$airaCurrent = '';

if ($taxonomies) {
    foreach ($taxonomies as $taxonomy) {

        if ($taxonomy->slug == "watches") {
            $keepExpoloringArr = array(
                array(
                    "desktop_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/landscape/rolex-collections-keep-exploring-land-dweller-landscape.webp",
                    "mobile_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/portrait/rolex-collections-keep-exploring-land-dweller-portrait.webp",
                    "title" => "Land-Dweller",
                    "url" => "/rolex/land-dweller"
                ),
                array(
                    "desktop_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/landscape/rolex-collections-keep-exploring-day-date-landscape.webp",
                    "mobile_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/portrait/rolex-collections-keep-exploring-day-date-portrait.webp",
                    "title" => "Day-Date",
                    "url" => "/rolex/day-date"
                ),
                array(
                    "desktop_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/landscape/rolex-collections-keep-exploring-sky-dweller-landscape.webp",
                    "mobile_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/portrait/rolex-collections-keep-exploring-sky-dweller-portrait.webp",
                    "title" => "Sky-Dweller",
                    "url" => "/rolex/sky-dweller"
                ),
                array(
                    "desktop_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/landscape/rolex-collections-keep-exploring-lady-datejust-landscape.webp",
                    "mobile_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/portrait/rolex-collections-keep-exploring-lady-datejust-portrait.webp",
                    "title" => "Lady-Datejust",
                    "url" => "/rolex/lady-datejust"
                ),
                array(
                    "desktop_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/landscape/rolex-collections-keep-exploring-datejust-landscape.webp",
                    "mobile_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/portrait/rolex-collections-keep-exploring-datejust-portrait.webp",
                    "title" => "Datejust",
                    "url" => "/rolex/datejust"
                ),
                array(
                    "desktop_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/landscape/rolex-collections-keep-exploring-oyster-perpetual-landscape.webp",
                    "mobile_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/portrait/rolex-collections-keep-exploring-oyster-perpetual-portrait.webp",
                    "title" => "Oyster Perpetual",
                    "url" => "/rolex/oyster-perpetual"
                ),
                array(
                    "desktop_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/landscape/rolex-collections-keep-exploring-cosmograph-daytona-landscape.webp",
                    "mobile_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/portrait/rolex-collections-keep-exploring-cosmograph-daytona-portrait.webp",
                    "title" => "Cosmograph Daytona",
                    "url" => "/rolex/cosmograph-daytona"
                ),
                array(
                    "desktop_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/landscape/rolex-collections-keep-exploring-submariner-landscape.webp",
                    "mobile_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/portrait/rolex-collections-keep-exploring-submariner-portrait.webp",
                    "title" => "Submariner",
                    "url" => "/rolex/submariner"
                ),
                array(
                    "desktop_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/landscape/rolex-collections-keep-exploring-sea-dweller-landscape.webp",
                    "mobile_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/portrait/rolex-collections-keep-exploring-sea-dweller-portrait.webp",
                    "title" => "Sea-Dweller",
                    "url" => "/rolex/sea-dweller"
                ),
                array(
                    "desktop_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/landscape/rolex-collections-keep-exploring-deepsea-landscape.webp",
                    "mobile_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/portrait/rolex-collections-keep-exploring-deepsea-portrait.webp",
                    "title" => "Deepsea",
                    "url" => "/rolex/deepsea"
                ),
                array(
                    "desktop_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/landscape/rolex-collections-keep-exploring-gmt-master-II-landscape.webp",
                    "mobile_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/portrait/rolex-collections-keep-exploring-gmt-master-II-portrait.webp",
                    "title" => "GMT-Master II",
                    "url" => "/rolex/gmt-master-ii"
                ),
                array(
                    "desktop_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/landscape/rolex-collections-keep-exploring-yacht-master-landscape.webp",
                    "mobile_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/portrait/rolex-collections-keep-exploring-yacht-master-portrait.webp",
                    "title" => "Yacht-Master",
                    "url" => "/rolex/yacht-master"
                ),
                array(
                    "desktop_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/landscape/rolex-collections-keep-exploring-explorer-landscape.webp",
                    "mobile_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/portrait/rolex-collections-keep-exploring-explorer-portrait.webp",
                    "title" => "Explorer",
                    "url" => "/rolex/explorer"
                ),
                array(
                    "desktop_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/landscape/rolex-collections-keep-exploring-air-king-landscape.webp",
                    "mobile_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/portrait/rolex-collections-keep-exploring-air-king-portrait.webp",
                    "title" => "Air-King",
                    "url" => "/rolex/air-king"
                ),
                array(
                    "desktop_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/landscape/rolex-collections-keep-exploring-1908-landscape.webp",
                    "mobile_url" => "/wp-content/uploads/rolex/watches-collection-pages/keep-exploring/portrait/rolex-collections-keep-exploring-1908-portrait.webp",
                    "title" => "1908",
                    "url" => "/rolex/1908"
                )
            );
        }

        if ($taxonomy->slug == "new-watches") {
            $keepExpoloringArr = array(
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1743616592/rolex/new-watches-2025/new-watches-2025-landscape/rolex-new-watches-2025-land-dweller_m127334-0001_2501fj_001-landscape.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1743616653/rolex/new-watches-2025/new-watches-2025-portrait/rolex-new-watches-2025-land-dweller_m127334-0001_2501fj_001_portrait.jpg",
                    "title" => "Rolex Land-Dweller",
                    "url" => "/rolex/new-watches/land-dweller"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1743616592/rolex/new-watches-2025/new-watches-2025-landscape/rolex-new-watches-2025-gmt-master-ii_m126729vtnr-0001_2501stojan_001-landscape.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1743616653/rolex/new-watches-2025/new-watches-2025-portrait/rolex-new-watches-2025-gmt-master-ii_m126729vtnr-0001_2501stojan_001-portrait.jpg",
                    "title" => "Rolex GMT-Master II",
                    "url" => "/rolex/new-watches/gmt-master-ii"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1743616592/rolex/new-watches-2025/new-watches-2025-landscape/rolex-new-watches-2025-oyster-perpetual_m276200-0008_2501stojan_001-landscape.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1743616654/rolex/new-watches-2025/new-watches-2025-portrait/rolex-new-watches-2025-oyster-perpetual_m276200-0008_2501stojan_001-portrait.jpg",
                    "title" => "Rolex Oyster Perpetual",
                    "url" => "/rolex/new-watches/oyster-perpetual"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1743616592/rolex/new-watches-2025/new-watches-2025-landscape/rolex-new-watches-2025-perpetual-1908_m52508-0008_2501stojan_001-landscape.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1743616654/rolex/new-watches-2025/new-watches-2025-portrait/rolex-new-watches-2025-perpetual-1908_m52508-0008_2501stojan_001-portrait.jpg",
                    "title" => "Rolex 1908",
                    "url" => "/rolex/new-watches/1908"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1743616592/rolex/new-watches-2025/new-watches-2025-landscape/rolex-new-watches-2025-datejust-31_m278288rbr-0041_2501stojan_001-landscape.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1743616653/rolex/new-watches-2025/new-watches-2025-portrait/rolex-new-watches-2025-datejust-31_m278288rbr-0041_2501stojan_001-portrait.jpg",
                    "title" => "Rolex Datejust",
                    "url" => "/rolex/new-watches/datejust"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1743616592/rolex/new-watches-2025/new-watches-2025-landscape/rolex-new-watches-2025-new-dials_m126518ln-0014_2501stojan_001-landscape.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1743616654/rolex/new-watches-2025/new-watches-2025-portrait/rolex-new-watches-2025-new-dials_m126518ln-0014_2501stojan_001-portrait.jpg",
                    "title" => "Exclusive Dials",
                    "url" => "/rolex/new-watches/exclusive-dials"
                )
            );
        }
    }
}
?>



<section class="splide keep-exploring grid-nospace" id="keepExploring" aria-labelledby="carousel-heading">
    <h2 class="headline36 brown" id="carousel-heading">Keep exploring</h2>

    <nav class="splide__track">
        <ul class="splide__list">
            <?php
            foreach ($keepExpoloringArr as $keepExploring) {
                $airaCurrent = '';

                if ($keepExploring['url'] == $param) {
                    $airaCurrent = 'aria-current="page"';
                }

            ?>
                <a href="<?php echo $keepExploring["url"] ?>" class="splide__slide">
                    <div class='img-cover'>
                        <?php echo do_shortcode('[responsive_image img_class="exclude-litespeed-lazyload" desktop_image_url="' . $keepExploring["desktop_url"] . '" mobile_image_url="' . $keepExploring["mobile_url"] . '" alt_text="' . $keepExploring["title"] . '"]') ?>
                    </div>
                    <p <?php echo $airaCurrent ?> class="body20Bold brown">
                        <?php echo $keepExploring["title"] ?>
                    </p>
                </a>
            <?php
            }
            ?>
        </ul>
    </nav>
</section>


<noscript>
    <style>
        .splide {
            display: none !important;
        }

        .splide__track {
            overflow: auto;
        }

        .splide__slide {
            flex-basis: 50%;
        }

        @media (min-width: 768px) {
            .splide__slide {
                flex-basis: 33.33%;
            }
        }
    </style>


    <section class="keep-exploring grid-nospace" id="keepExploring" aria-labelledby="carousel-heading">
        <h2 class="headline36 brown" id="carousel-heading">Keep exploring</h2>

        <nav class="splide__track">
            <ul class="splide__list">
                <?php
                foreach ($keepExpoloringArr as $keepExploring) {
                    $airaCurrent = '';

                    if ($keepExploring['url'] == $param) {
                        $airaCurrent = 'aria-current="page"';
                    }

                ?>
                    <a href="<?php echo $keepExploring["url"] ?>" class="splide__slide">
                        <div class='img-cover'>
                            <?php echo do_shortcode('[responsive_image desktop_image_url="' . $keepExploring["desktop_url"] . '" mobile_image_url="' . $keepExploring["mobile_url"] . '" alt_text="' . $keepExploring["title"] . '"]') ?>
                        </div>
                        <p <?php echo $airaCurrent ?> class="body20Bold brown">
                            <?php echo $keepExploring["title"] ?>
                        </p>
                    </a>
                <?php
                }
                ?>
            </ul>
        </nav>
    </section>
</noscript>