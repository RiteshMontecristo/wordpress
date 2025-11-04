<?php

$discoverArr = array(
    array(
        "desktop_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1762283843/rolex/discover-rolex-page-assets-landscape/M126729VTNR-0001_2501stojan_001_RVB-V2_2000x920.webp",
        "mobile_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1762283923/rolex/discover-rolex-page-assets-portrait/M126729VTNR-0001_2501stojan_001_RVB-V2_780x1050.webp",
        "title" => "Rolex",
        "subtitle" => "GMT-Master II",
        "url" => "/rolex/gmt-master-ii",
        "text_color" => "white"
    ),
    array(
        "desktop_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1756914319/rolex/discover-rolex-page-assets-landscape/RBA_SailGP_Website_Banner_FD1_3997-r_2000x920.webp",
        "mobile_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1756914659/rolex/discover-rolex-page-assets-portrait/RBA_SailGP_Website_Banner_FD1_3997-r_780x1050.webp",
        "title" => "Rolex and yachting",
        "subtitle" => "Rolex SailGP Championship",
        "url" => "/rolex/sailgp-championship",
        "text_color" => "black"
    ),
    array(
        "desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1724196662/rolex/2000x920_NO-TEXT_N-A_FF_DESKTOP_GN_STATIC_JPEG_Watchmaking.jpg",
        "mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1724196326/rolex/780x1050_NO-TEXT_N-A_FF_MOBILE_GN_STATIC_JPEG_Watchmaking.jpg",
        "title" => "Rolex and watchmaking",
        "subtitle" => "Excellence in the making",
        "url" => "/rolex/watchmaking",
        "text_color" => "black"
    )
)
?>


<section class="splide discover" id="discover" aria-labelledby="Discover Rolex">
    <div class="splide__track">
        <ul class="splide__list">
            <?php
            foreach ($discoverArr as $discover) {
            ?>
                <div class="splide__slide">
                    <?php echo do_shortcode('[responsive_image img_class="exclude-litespeed-lazyload" desktop_image_url="' . $discover["desktop_url"] . '" mobile_image_url="' . $discover["mobile_url"] . '" alt_text="' . $discover["title"] . '" loading="eager"]') ?>
                    <div class="discover-text grid-nospace <?php echo $discover["text_color"] ?>">
                        <p class="headline26 title"><?php echo $discover["title"] ?></p>
                        <p class="headline70 subtitle"><?php echo $discover["subtitle"] ?></p>
                        <a class="primary-cta" href="<?php echo $discover["url"] ?>">Discover</a>
                    </div>
                </div>
            <?php
            }
            ?>
        </ul>
    </div>
</section>


<noscript>
    <style>
        .splide {
            display: none;
        }

        .discover-slider {
            display: flex;
            flex-wrap: nowrap;
            width: 100vw;
            overflow: auto;
            scroll-snap-type: x mandatory;
        }

        .slider-container {
            position: relative;
            width: 100vw;
            flex: 0 0 auto;
            scroll-snap-align: center;
        }

        .slider-container .discover-text {
            width: 100%;
            bottom: 70px;
            position: absolute;
        }

        .slider-container .discover-text .title {
            grid-column: 1 / -1;
            grid-row: span 1;
            margin: 0;
            width: fit-content;
            color: white;
        }

        .slider-container .discover-text .subtitle {
            grid-column: 1 / -1;
            grid-row: span 1;
            margin: 0;
            width: fit-content;
            color: white;
        }

        .discover-slider .discover-text .primary-cta {
            margin: 0;
            margin-top: 20px;
            grid-column: 1 / -1;
            grid-row: span 1;
            width: fit-content;
            color: white;
        }

        @media (min-width: 768px) {
            .discover-slider .discover-text {
                bottom: 50%;
                transform: translateY(50%);
                width: 100%;
            }

            .slider-container .discover-text .title,
            .slider-container .discover-text .subtitle,
            .slider-container .discover-text .primary-cta {
                grid-column: -1 / -6;
                grid-row: span 1;
            }
        }
    </style>

    <section class="discover-slider">
        <?php
        foreach ($discoverArr as $discover) {
        ?>
            <div class="slider-container">
                <?php echo do_shortcode('[responsive_image desktop_image_url="' . $discover["desktop_url"] . '" mobile_image_url="' . $discover["mobile_url"] . '" alt_text="' . $discover["title"] . '" loading="eager"]') ?>
                <div class="discover-text grid-nospace">
                    <p class="headline26 title"><?php echo $discover["title"] ?></p>
                    <p class="headline70 subtitle"><?php echo $discover["subtitle"] ?></p>
                    <a class="primary-cta" href="<?php echo $discover["url"] ?>">Discover</a>
                </div>
            </div>
        <?php } ?>
    </section>

</noscript>