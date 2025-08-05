<?php


$keepExpoloringArr = array(
    array(
        "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1717451405/rolex/rolex-servicing-page-assets-landscape/keep-exploring-landscape/rolex-keep-exploring-discover-rolex-shoot_shop_geneva_retail_campaign_logo_pub-2-iso_01-landscape.jpg",
        "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1717451407/rolex/rolex-servicing-page-assets-portrait/keep-exploring-portrait/rolex-keep-exploring-discover-rolex-shoot_shop_geneva_retail_campaign_logo_pub-2-iso_01_portrait.jpg",
        "title" => "Discover Rolex",
        "url" => "/rolex"
    ),
    array(
        "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1717451405/rolex/rolex-servicing-page-assets-landscape/keep-exploring-landscape/rolex-keep-exploring-rolex-watches-1945_oyster_perpetual_datejust_1802jva_m126333_0010_1802jva_002-landscape.jpg",
        "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1717451406/rolex/rolex-servicing-page-assets-portrait/keep-exploring-portrait/rolex-keep-exploring-rolex-watches-1945_oyster_perpetual_datejust_1802jva_m126333_0010_1802jva_002-portrait.jpg",
        "title" => "Rolex Watches",
        "url" => "/rolex/watches"
    ),
    array(
        "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1743531629/rolex/rolex-servicing-page-assets-landscape/keep-exploring-landscape/keep-exploring-new-watches-landscape.jpg",
        "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1743531968/rolex/rolex-servicing-page-assets-portrait/keep-exploring-portrait/keep-exploring-new-watches-portrait.jpg",
        "title" => "New Watches 2025",
        "url" => "/rolex/new-watches"
    ),
    array(
        "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1717451403/rolex/rolex-servicing-page-assets-landscape/keep-exploring-landscape/rolex-keep-exploring-watchmaking-2023_watchmaking_features_manifesto_still_digital_master_rgb-landscape.jpg",
        "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1717451406/rolex/rolex-servicing-page-assets-portrait/keep-exploring-portrait/rolex-keep-exploring-watchmaking-2023_watchmaking_features_manifesto_still_digital_master_rgb-portrait.jpg",
        "title" => "Watchmaking",
        "url" => "/rolex/watchmaking"
    ),
    array(
        "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1717451404/rolex/rolex-servicing-page-assets-landscape/keep-exploring-landscape/rolex-keep-exploring-servicing-2234_rolex-sav_retailers_176_controle-final_mise_heure_v2-landscape.jpg",
        "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1717451407/rolex/rolex-servicing-page-assets-portrait/keep-exploring-portrait/rolex-keep-exploring-servicing-2234_rolex-sav_retailers_176_controle-final_mise_heure_v2-portrait.jpg",
        "title" => "Servicing",
        "url" => "/rolex/servicing"
    ),
    array(
        "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1726006312/rolex/discover-rolex-page-assets-landscape/rolex-keep-exploring-hub-world-of-rolex-wim23ac_17669_landscape.jpg",
        "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1726006385/rolex/discover-rolex-page-assets-portrait/rolex-keep-exploring-hub-world-of-rolex-wim23ac_17669_portrait.jpg",
        "title" => "World of Rolex",
        "url" => "/rolex/world-of-rolex"
    ),
    array(
        "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1717451404/rolex/rolex-servicing-page-assets-landscape/keep-exploring-landscape/rolex-keep-exploring-contact-us-A7404109-landscape.jpg",
        "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1717451407/rolex/rolex-servicing-page-assets-portrait/keep-exploring-portrait/rolex-keep-exploring-contact-us-A7404109-portrait.jpg",
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
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1744220175/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-landscape/rolex-collections-keep-exploring-land-dweller-landscape.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1744220212/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-portrait/rolex-collections-keep-exploring-land-dweller-portrait.jpg",
                    "title" => "Land-Dweller",
                    "url" => "/rolex/land-dweller"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575900/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-landscape/rolex-collections-keep-exploring-day-date-landscape_wyrnjs.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575907/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-portrait/rolex-collections-keep-exploring-day-date-portrait_bhgo5i.jpg",
                    "title" => "Day-Date",
                    "url" => "/rolex/day-date"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575903/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-landscape/rolex-collections-keep-exploring-sky-dweller-landscape_mqdgw1.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575907/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-portrait/rolex-collections-keep-exploring-sky-dweller-portrait_crdgvm.jpg",
                    "title" => "Sky-Dweller",
                    "url" => "/rolex/sky-dweller"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575903/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-landscape/rolex-collections-keep-exploring-lady-datejust-landscape_ai3kpf.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575908/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-portrait/rolex-collections-keep-exploring-lady-datejust-portrait_totv2m.jpg",
                    "title" => "Lady-Datejust",
                    "url" => "/rolex/lady-datejust"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575903/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-landscape/rolex-collections-keep-exploring-datejust-landscape_kgocys.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575904/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-portrait/rolex-collections-keep-exploring-datejust-portrait_wfrvrs.jpg",
                    "title" => "Datejust",
                    "url" => "/rolex/datejust"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1744220173/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-landscape/rolex-collections-keep-exploring-oyster-perpetual-landscape.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1744220209/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-portrait/rolex-collections-keep-exploring-oyster-perpetual-portrait.jpg",
                    "title" => "Oyster Perpetual",
                    "url" => "/rolex/oyster-perpetual"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1744220172/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-landscape/rolex-collections-keep-exploring-cosmograph-daytona-landscape.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1744220214/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-portrait/rolex-collections-keep-exploring-cosmograph-daytona-portrait.jpg",
                    "title" => "Cosmograph Daytona",
                    "url" => "/rolex/cosmograph-daytona"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575902/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-landscape/rolex-collections-keep-exploring-submariner-landscape_f0pily.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575908/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-portrait/rolex-collections-keep-exploring-submariner-portrait_fpze98.jpg",
                    "title" => "Submariner",
                    "url" => "/rolex/submariner"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575899/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-landscape/rolex-collections-keep-exploring-sea-dweller-landscape_qxdgh2.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575904/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-portrait/rolex-collections-keep-exploring-sea-dweller-portrait_iu5914.jpg",
                    "title" => "Sea-Dweller",
                    "url" => "/rolex/sea-dweller"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575900/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-landscape/rolex-collections-keep-exploring-deepsea-landscape_dkhfq1.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575906/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-portrait/rolex-collections-keep-exploring-deepsea-portrait_emj5fp.jpg",
                    "title" => "Deepsea",
                    "url" => "/rolex/deepsea"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575902/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-landscape/rolex-collections-keep-exploring-gmt-master-II-landscape_mwwu4a.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575905/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-portrait/rolex-collections-keep-exploring-gmt-master-II-portrait_wlk0za.jpg",
                    "title" => "GMT-Master II",
                    "url" => "/rolex/gmt-master-ii"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575902/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-landscape/rolex-collections-keep-exploring-yacht-master-landscape_rqvyui.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575905/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-portrait/rolex-collections-keep-exploring-yacht-master-portrait_cdflae.jpg",
                    "title" => "Yacht-Master",
                    "url" => "/rolex/yacht-master"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1744220174/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-landscape/rolex-collections-keep-exploring-explorer-landscape.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1744220211/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-portrait/rolex-collections-keep-exploring-explorer-portrait.jpg",
                    "title" => "Explorer",
                    "url" => "/rolex/explorer"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575901/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-landscape/rolex-collections-keep-exploring-air-king-landscape_k5qfm3.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1716575905/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-portrait/rolex-collections-keep-exploring-air-king-portrait_ekczow.jpg",
                    "title" => "Air-King",
                    "url" => "/rolex/air-king"
                ),
                array(
                    "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1744220176/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-landscape/rolex-collections-keep-exploring-1908-landscape.jpg",
                    "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1744220208/rolex/rolex-collection-pages-assets/rolex-keep-exploring/keep-exploring-assets-portrait/rolex-collections-keep-exploring-1908-portrait.jpg",
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