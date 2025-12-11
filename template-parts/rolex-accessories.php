<?php
if (function_exists('get_field')) {
    $rmc_number = get_field('rmc');
    $model_name = get_field('model_name');
    $ranking = get_field('ranking');
    $spec_material1 = get_field('spec_material1');
    $spec_material2 = get_field('spec_material2');
    $spec_material3 = get_field('spec_material3');
    $intro_title = get_field('intro_title');
    $intro_text = get_field('intro_text');
    $intro_assets = get_field('intro_assets');
    $h1 = get_field('h1');
    $price = get_field('price');

    $arr = $arr = preg_split('/\r\n|\r|\n/', $h1);

    $alt = "Rolex $model_name in $spec_material1 $spec_material2 $spec_material3, $rmc_number at Montecristo Jewellers";
    $html = "";
    function getUserIP()
    {
        // Check for shared internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        // Check if IP is passed from a proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            // Normal IP address from remote address
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    $ip = getUserIP();

    function isUserInCanada($ip)
    {
        $access_token = "f98538be4b68ee";
        $url = "http://ipinfo.io/{$ip}/json?token={$access_token}";

        $response = file_get_contents($url);
        $details = json_decode($response);

        return isset($details->country) && $details->country === "CA";
    }

    if (isUserInCanada($ip)) {
        $html = '
			<div class="body20Light black price hidden" id="price">' . number_format($price) . ' CAD
				<svg enable-background="new 0 0 15 15" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg" width="15px" height="15px">
					<g>
						<path d="m7.5 0c-4.1 0-7.5 3.4-7.5 7.5s3.4 7.5 7.5 7.5 7.5-3.4 7.5-7.5-3.4-7.5-7.5-7.5zm0 14.1c-3.6 0-6.6-3-6.6-6.6s3-6.6 6.6-6.6 6.6 3 6.6 6.6-3 6.6-6.6 6.6z"></path>
						<path d="m8.4 10.3c-.1.2-.3.4-.5.6-.1.1-.4.1-.4-.1l1.8-5.1h-3l-.3.9h1.2l-1.2 3.5-.3.8c-.1.3-.1.5 0 .8.3.5.9.5 1 .5s.4 0 .8-.2c.7-.4 1.2-1 1.4-1.4"></path>
						<circle cx="8.5" cy="3.8" r="1"></circle>
					</g>
				</svg>
				<p class="price-info">Suggested retail price exclusive of tax. The suggested retail price can be modified at any time without notice.</p>
			</div>';
    }
} else {
    echo "<p>Please activate ACF fields</p>";
}
?>

<section class="grid-nospace watch-cover">
    <?php echo do_shortcode('[responsive_image 
				desktop_image_url="/wp-content/uploads/rolex/rolex-cufflink-assets/rolex-cufflink-asset-packshot/rolex-accessories-' . $rmc_number . '-packshot.webp" 
				mobile_image_url="/wp-content/uploads/rolex/rolex-cufflink-assets/rolex-cufflink-asset-packshot/rolex-accessories-' . $rmc_number . '-packshot.webp" 
				alt_text="' . $alt . '" loading="eager"]');
    ?>

    <div class="intro-text">
        <h1 data-rmc="<?php echo $rmc_number ?>" data-family="cufflinks">
            <p class="headline50 brown"><?php echo $model_name ?>&nbsp;</p>
            <p class="body20Light"><?php echo $arr[1] ?>&nbsp;</p>
            <p class="body20Light">RMC <?php echo $arr[2] ?>&nbsp;</p>
        </h1>

        <?php echo $html ?>
        <div class="cta">
            <a href="tel:+1-604-263-3611" class="teritary-cta" title="+1-604-263-3611">
                <svg width="36px" height="36px" viewBox="0 0 36 36" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <title>phone-default</title>
                    <g id="phone-default" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <circle id="Oval" fill="#fff" cx="18" cy="18" r="18"></circle>
                        <path d="M15.9763138,16.3148829 L15.9229709,16.3348865 C16.2830355,17.6017807 17.0098327,19.0620429 18.4300877,20.3889478 L18.5367735,20.2955977 C19.0902062,19.8088437 19.9103535,19.7088257 20.3704361,20.0755582 L21.1772477,20.7890197 C21.6239946,21.1490843 21.6106588,21.7891993 21.0905654,22.2826212 C20.8105152,22.6093465 18.8034881,24.6630486 15.76961,19.8821902 C12.5890389,14.841285 14.5027158,13.4610372 15.329531,13.1143082 C15.3428667,13.1076404 15.3562024,13.1076404 15.3695382,13.1009725 C15.3895417,13.0943046 15.4162132,13.0876368 15.4362168,13.0743011 C15.4428847,13.0743011 15.4562204,13.0676332 15.4628882,13.0676332 C15.5829098,13.027626 15.6562563,13.0142903 15.6562563,13.0142903 C15.6562563,13.0142903 15.6562563,13.0142903 15.6562563,13.0209581 C16.2030211,12.9209402 16.649768,13.1876547 16.8164646,13.6344016 C16.8164646,13.6344016 17.5699333,15.9014754 15.9763138,16.3148829 Z" id="Path" fill="#452C1E" fill-rule="nonzero"></path>
                    </g>
                </svg>
                <span>+1-604-263-3611</span>
            </a>

            <a href="#contact-us" class="teritary-cta" title="Send a message">
                <svg width="36px" height="36px" viewBox="0 0 36 36" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <title>mail-default</title>
                    <g id="mail-default" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <circle id="Oval" fill="#fff" cx="18" cy="18" r="18"></circle>
                        <g id="icons/plus" transform="translate(13, 14)" fill="#452C1E" fill-rule="nonzero">
                            <g id="mail">
                                <path d="M0,0 L10,0 L10,0.0666666667 L5.06666667,3.86666667 L0,0 Z M5.06666667,5.33333333 L0,1.53333333 L0,8 L10,8 L10,1.6 L5.06666667,5.33333333 Z" id="Shape"></path>
                            </g>
                        </g>
                    </g>
                </svg>
                <span>Message</span>
            </a>

            <a href="/rolex/contact-richmond" class="teritary-cta" title="Get direction">
                <svg xmlns="http://www.w3.org/2000/svg" width="36px" height="36px" viewBox="0 0 36 36" class="fill-rolex-brown group-hover:fill-rolex-green">
                    <!-- Full circle background -->
                    <circle id="Oval" fill="#fff" cx="18" cy="18" r="18"></circle>

                    <!-- Group the inner icon and center it -->
                    <g transform="translate(12.5, 11)">
                        <g clip-path="url(#clip0_7_2859)">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.07302 5.36391C3.07302 3.98014 4.196 2.85983 5.5771 2.85983C6.95821 2.85983 8.08119 3.98281 8.08119 5.36391C8.08119 6.74502 6.96087 7.868 5.5771 7.868C4.19334 7.868 3.07302 6.74502 3.07302 5.36391ZM0.240234 5.3621C0.240234 6.46379 0.98534 7.94336 1.91938 9.36704C3.6145 11.6822 5.60499 14 5.60499 14C5.60499 14 7.63008 11.6396 9.29061 9.36704C10.2246 7.94602 10.9671 6.46645 10.9671 5.36476C10.9644 2.4003 8.56413 0 5.60233 0C2.64054 0 0.240234 2.4003 0.240234 5.3621Z"></path>
                        </g>
                        <defs>
                            <clipPath id="clip0_7_2859">
                                <rect width="11" height="14" fill="white"></rect>
                            </clipPath>
                        </defs>
                    </g>
                </svg>
                <span>Find us</span>
            </a>

        </div>
    </div>
</section>

<!-- Accessoreis Spec -->
<section class="grid-nospace watch-spec f4-background-container">

    <div class="spec-columns mobile-spec">
        <div class="spec-column">
            <div class="spec-item">
                <p class="body20Bold brown">RMC</p>
                <p class="body20Light black"><?= esc_html($rmc_number); ?></p>
            </div>
            <div class="spec-item">
                <p class="body20Bold brown">Model</p>
                <p class="body20Light black"><?= esc_html($model_name); ?></p>
            </div>
        </div>

        <div class="spec-column">
            <div class="spec-item">
                <p class="body20Bold brown">Collection</p>
                <p class="body20Light black">Rolex accessories</p>
            </div>
            <div class="spec-item">
                <p class="body20Bold brown">material</p>
                <p class="body20Light black"><?= esc_html($spec_material2); ?></p>
            </div>
        </div>
    </div>

    <div class="spec-columns desktop-spec">
        <div class="spec-column">
            <div class="spec-item">
                <p class="body20Bold brown">RMC</p>
                <p class="body20Light black"><?= strtoupper($rmc_number); ?></p>
            </div>
            <div class="spec-item">
                <p class="body20Bold brown">Material</p>
                <p class="body20Light black"><?= esc_html($spec_material2); ?></p>
            </div>
        </div>
        <div class="spec-column">
            <div class="spec-item">
                <p class="body20Bold brown">Collection</p>
                <p class="body20Light black">Rolex accessories</p>
            </div>
        </div>
        <div class="spec-column">
            <div class="spec-item">
                <p class="body20Bold brown">Model</p>
                <p class="body20Light black"><?= esc_html($model_name); ?></p>
            </div>
        </div>
    </div>

</section>

<!-- Body text  -->
<section class="grid-nospace product-body-text">

    <div class="feature">
        <h2 class="headline50 brown"><?= $intro_title ?></h2>
        <p class="body20Light black"><?= $intro_text ?></p>

        <?php
        $desktop_image_url = "/wp-content/uploads/rolex/rolex-cufflink-assets/rolex-cufflink-asset-background/rolex-cufflink-asset-background-landscape/rolex-accessories-$rmc_number-landscape.webp";
        $mobile_image_url = "/wp-content/uploads/rolex/rolex-cufflink-assets/rolex-cufflink-asset-background/rolex-cufflink-asset-background-portrait/rolex-accessories-$rmc_number-portrait.webp";
        echo do_shortcode('[responsive_image desktop_image_url="' . $desktop_image_url . '" mobile_image_url="' . $mobile_image_url . '" alt_text="' . $alt . '"]')
        ?>
    </div>

</section>

<!-- Contact Accordion -->
<section id="contact-us" class="accordion grid-nospace f9-background-container">
    <h2 class="headline36 brown">Contact us</h2>
    <div class="divider"></div>

    <button id="accordionControls" aria-label="Accordion" class="accordion-text f9-background-container brown body20Bold">
        <p>Send us a message</p>
        <svg class="hide-icon" id="hideIcon" version="1.1" id="Calque_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
            width="15px" height="15px" viewBox="0 0 15 15" style="enable-background:new 0 0 15 15;" xml:space="preserve">
            <path id="icons_x2F_moins" fill="#000" d="M15,8.8H0V6.2h15V8.8z" />
        </svg>

        <svg class="show-icon hidden" id="showIcon" version="1.1" id="Calque_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="15px" height="15px"
            viewBox="0 0 15 15" style="enable-background:new 0 0 15 15;" xml:space="preserve">
            <path id="icons_x2F_moins" fill="#000" d="M15,8.8H8.8V15H6.2V8.8H0V6.2h6.2V0h2.5v6.2H15V8.8z" />
        </svg>
    </button>

    <?php echo do_shortcode(
        '[rolex_form 
					desktop_image_url="https://res.cloudinary.com/drfo99te6/image/upload/q_auto,f_auto/v1723245312/rolex/discover-rolex-page-assets-landscape/rolex-message-cover-landscape.jpg" 
					mobile_image_url="https://res.cloudinary.com/drfo99te6/image/upload/q_auto,f_auto/v1723057881/rolex/rolex-contact-page-assets-portrait/rolex-message-cover-portrait.jpg" 
					alt_text="contact form montecristo jewellers - rolex watches official retailer" 
		            message="I\'m interested in the ' . $model_name . ', reference ' . $rmc_number . '"
					success_img_url = "https://res.cloudinary.com/drfo99te6/image/upload/v1717530586/rolex/rolex-contact-module-message-assets-landscape/rolex-message-thank-you-landscape.jpg" success_alt="contact-form done" 
					is_modal_page="true"]'
    ) ?>

    <div class="divider"></div>
</section>

<!-- Push -->
<section class="grid-nospace f9-background-container push">
    <a class="large-img" href="/rolex/accessories">
        <picture class="">
            <source srcset="/wp-content/uploads/rolex/rolex-accessories-model-page-assets/rolex_accessories-page-assets-portrait/rolex-accessories-rolexcufflinks_2403jva_001-portrait.webp" media="(max-width: 768px)">
            <source srcset="/wp-content/uploads/rolex/rolex-accessories-model-page-assets/rolex_accessories-page-assets-landscape/rolex-accessories-rolexcufflinks_2403jva_001-landscape.webp" media="(min-width: 769px)">
            <img decoding="async" loading="lazy" src="/wp-content/uploads/rolex/rolex-accessories-model-page-assets/rolex_accessories-page-assets-landscape/rolex-accessories-rolexcufflinks_2403jva_001-landscape.webp" alt="Discover the rolex accessories" title="Discover the rolex accessories" width="100%" height="auto">
        </picture>
    </a>

    <div class="push-text">
        <p class="fixed16 brown"><strong>Collection</strong></p>
        <h2 class="headline36 brown">Discover the Rolex accessories</h2>
        <a href="/rolex/accessories" class="secondary-cta fixed14">
            Learn more
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="12px" height="12px" viewBox="0 0 12 12" version="1.1">
                <g>
                    <path d="M 9.601562 6 L 8.558594 7.121094 L 3.679688 12 L 2.480469 10.800781 L 7.359375 5.921875 L 2.398438 1.121094 L 3.601562 0 Z M 9.601562 6 "></path>
                </g>
            </svg>
        </a>
    </div>
</section>