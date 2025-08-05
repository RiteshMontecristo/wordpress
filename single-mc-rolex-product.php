<?php

/**
 * The template for displaying all single rolex product posts.
 *
 * @package storefront
 */

get_header();

// Rolex Header
get_template_part('template-parts/rolex-header');

?>

<main id="rolex-page" data-pagetype="Product" class="site-main rolex-main f9-background-container" role="main">

	<?php

	$productList = array(
		"air-king" => array(
			"title" => "Rolex Air-King",
			"subtitle" => "Take to the skies",
			"desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1716575919/rolex/rolex-collection-pages-assets/rolex-collection-page-air-king/rolex-collection-page-air-king-assets-landscape/rolex-air-king-cover-m126900-0001_2210jva_001-landscape_zhikir.jpg",
			"mobile_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1721246761/rolex/rolex-model-page-editorial-assets-portrait/rolex-collection_banner-air-king-m126900-0001_2210jva_001-portrait.jpg",
		),
		"cosmograph daytona" => array(
			"title" => "The Rolex Cosmograph Daytona collection",
			"subtitle" => "The triumph of endurance",
			"desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1716575913/rolex/rolex-collection-pages-assets/rolex-collection-page-cosmograph-daytona/rolex-collection-page-cosmograph-daytona-assets-landscape/rolex-cosmograph-daytona-cover-m126506-0001_2301jva_002-landscape_t6plzn.jpg",
			"mobile_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1721246762/rolex/rolex-model-page-editorial-assets-portrait/rolex-collection_banner-cosmograph-daytona-m126509-0001_2402jva_001-portrait.jpg",
		),
		"datejust" => array(
			"title" => "The Rolex Datejust collection",
			"subtitle" => "Make a date of a day",
			"desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1716575922/rolex/rolex-collection-pages-assets/rolex-collection-page-datejust/rolex-collection-page-datejust-assets-landscape/rolex-datejust-M126234-0051_2210jva_001-landscape_p9c9e5.jpg",
			"mobile_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1721246763/rolex/rolex-model-page-editorial-assets-portrait/rolex-collection_banner-datejust-m126234-0051_2210jva_001-portrait.jpg",
		),
		"deepsea" => array(
			"title" => "The Rolex Deepsea collection",
			"subtitle" => "Extreme divers’ watches",
			"desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1721246760/rolex/rolex-model-page-editorial-assets-landscape/rolex-collection_banner-rolex-deepsea-m136668lb-0001_2312jva_001_rvb.jpg",
			"mobile_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1721246764/rolex/rolex-model-page-editorial-assets-portrait/rolex-collection_banner-rolex-deepsea-m136668lb-0001_2312jva_001_rvb-portrait.jpg",
		),
		"day-date" => array(
			"title" => "The Rolex Day-Date collection",
			"subtitle" => "Ideal accomplishments",
			"desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1716575968/rolex/rolex-collection-pages-assets/rolex-collection-page-day-date/rolex-collection-page-day-date-assets-landscape/rolex-day-date-cover-m228235-0055_2402jva_001-landscape_kgcj9g.jpg",
			"mobile_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1721246762/rolex/rolex-model-page-editorial-assets-portrait/rolex-collection_banner-day-date-m228235-0055_2402jva_001-portrait.jpg",
		),
		"explorer" => array(
			"title" => "The Rolex Explorer Collection",
			"subtitle" => "Adventure on a grand scale",
			"desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1721246760/rolex/rolex-model-page-editorial-assets-landscape/rolex-collection_banner-rolex-deepsea-m136668lb-0001_2312jva_001_rvb.jpg",
			"mobile_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1721246762/rolex/rolex-model-page-editorial-assets-portrait/rolex-collection_banner-explorer-m124273-0001_2210jva_001-portrait.jpg",
		),
		"lady-datejust" => array(
			"title" => "The Rolex Lady-Datejust collection",
			"subtitle" => "Graceful elegance",
			"desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1716577964/rolex/rolex-collection-pages-assets/rolex-collection-page-lady-datejust/rolex-collection-page-lady-datejust-assets-landscape/rolex-lady-datejust-cover-m279135RBR-0001_2301jva_001-landscape_g1n3be.jpg",
			"mobile_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1721246763/rolex/rolex-model-page-editorial-assets-portrait/rolex-collection_banner-lady-datejust-m279135rbr-0001_2301jva_001-portrait.jpg",
		),
		"land-dweller" => array(
			"title" => "The Rolex Land-Dweller collection",
			"subtitle" => "Opening new horizons",
			"desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1744300910/rolex/rolex-model-page-editorial-assets-landscape/rolex-collection_banner-land-dweller-m127334-0001_2503-landscape-landscape.jpg",
			"mobile_url" => "https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1744301116/rolex/rolex-model-page-editorial-assets-portrait/rolex-collection_banner-land-dweller-m127334-0001_2503-portrait.jpg",
		),
		"oyster perpetual" => array(
			"title" => "The Rolex Oyster Perpetual collection",
			"subtitle" => "Make the world your Oyster",
			"desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1716577858/rolex/rolex-collection-pages-assets/rolex-collection-page-oyster-perpetual/rolex-collection-page-oyster-perpetual-assets-landscape/rolex-oyster-perpetual-cover-m124300-0001_2210jva_001-landscape_js0xuo.jpg",
			"mobile_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1721246762/rolex/rolex-model-page-editorial-assets-portrait/rolex-collection_banner-oyster-perpetual-m124300-0001_2210jva_001-portrait.jpg",
		),
		"sea-dweller" => array(
			"title" => "The Rolex Sea-Dweller collection",
			"subtitle" => "Citizen of the deep",
			"desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1716577973/rolex/rolex-collection-pages-assets/rolex-collection-page-sea-dweller/rolex-collection-page-sea-dweller-assets-landscape/rolex-seadweller-cover-m124060-0001-0002_2210jva_001-landscape_wfvjei.jpg",
			"mobile_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1721246762/rolex/rolex-model-page-editorial-assets-portrait/rolex-collection_banner-sea-dweller-m126603-0001_2210jva_0011-portrait_1.jpg",
		),
		"sky-dweller" => array(
			"title" => "The Rolex Sky-Dweller collection",
			"subtitle" => "High-flying",
			"desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1716577967/rolex/rolex-collection-pages-assets/rolex-collection-page-sky-dweller/rolex-collection-page-sea-dweller-assets-landscape/rolex-sky-dweller-cover-m336935-0008_2312jva_001-landscape_cmvt7q.jpg",
			"mobile_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1721246762/rolex/rolex-model-page-editorial-assets-portrait/rolex-collection_banner-sky-dweller-m336935-0008_2312jva_001_rvb-portrait.jpg",
		),
		"submariner" => array(
			"title" => "The Rolex Submariner collection",
			"subtitle" => "Deep confidence",
			"desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1716577980/rolex/rolex-collection-pages-assets/rolex-collection-page-submariner/rolex-collection-page-submariner-assets-landscape/rolex-submariner-cover-m124060-0001-0002_2210jva_001-landscape_d0zfx7.jpg",
			"mobile_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1721246761/rolex/rolex-model-page-editorial-assets-portrait/rolex-collection_banner-submariner-m124060-0001-0002_2210jva_001-portrait.jpg",
		),
		"yacht-master" => array(
			"title" => "The Rolex Yacht-Master collection",
			"subtitle" => "Marine character",
			"desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1716575911/rolex/rolex-collection-pages-assets/rolex-collection-page-yacht-master/rolex-collection-page-yacht-master-assets-landscape/rolex-yacht-master-cover-M226627-0001_2301jva_001-landscape_foa08d.jpg",
			"mobile_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1721246765/rolex/rolex-model-page-editorial-assets-portrait/rolex-collection_banner-yacht-master-m226627-0001_2301jva_001-portrait.jpg",
		),
		"gmt-master ii" => array(
			"title" => "The Rolex GMT-Master II collection",
			"subtitle" => "Keep track of ties",
			"desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1716577973/rolex/rolex-collection-pages-assets/rolex-collection-page-gmt-master-II/rolex-collection-page-gmt-master-ii-assets-landscape/rolex-gmt-master-II-cover-m126710GRNR-0003_2312jva_001-landscape_xmoiyu.jpg",
			"mobile_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1721246764/rolex/rolex-model-page-editorial-assets-portrait/rolex-collection_banner-gmt-master-2-m126710grnr-0003_2312jva_001_rvb-portrait.jpg",
		),
		"1908" => array(
			"title" => "The Rolex Perpetual 1908 collection",
			"subtitle" => "Cutting-edge classicism",
			"desktop_url" => "https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1716575898/rolex/rolex-collection-pages-assets/rolex-collection-page-1908/rolex-collection-page-1908-assets-landscape/rolex-perpetual-1908-m52506-0002_2312jva_001-landscape_ygtwas.jpg",
			"mobile_url" => "https://res.cloudinary.com/drfo99te6/image/upload/v1721246761/rolex/rolex-model-page-editorial-assets-portrait/rolex-collection_banner-1908-push-m52506-0002_2312jva_001_rvb-portrait.jpg",
		)
	);
	// Check if ACF plugin is active
	if (function_exists('get_field')) {

		// Define the field name or key
		$family_handle = get_field('family_handle');
		$model_name = get_field('model_name');
		$model_heading = get_field('model_heading');
		$rmc_number = get_field('rmc_number');
		$price = get_field('price');
		$spec_reference = get_field('spec_reference');
		$spec_material = get_field('spec_material');
		$spec_model_case = get_field('spec_model_case');
		$spec_water_resistance = get_field('spec_water_resistance');
		$spec_bezel = get_field('spec_bezel');
		$spec_dial = get_field('spec_dial');
		$spec_bracelet = get_field('spec_bracelet');
		$spec_movement = get_field('spec_movement');
		$spec_calibre = get_field('spec_calibre');
		$spec_power_reserve = get_field('spec_power_reserve');
		$spec_certification = get_field('spec_certification');
		$brochure = get_field('brochure');
		$feature1_title = get_field('feature1_title');
		$feature1_text = get_field('feature1_text');
		$feature1_asset = get_field('feature1_asset');
		$feature2_title = get_field('feature2_title');
		$feature2_text = get_field('feature2_text');
		$feature2_asset = get_field('feature2_asset');
		$feature3_title = get_field('feature3_title');
		$feature3_text = get_field('feature3_text');
		$feature3_asset = get_field('feature3_asset');

		$family_handle_data = strtolower(str_replace(' ', '-', $family_handle));
		$watch_gallery = array(
			array(
				'desktop' => "https://res.cloudinary.com/drfo99te6/f_auto,q_auto/v1/rolex/model-gallery/slide1-landscape/" . $rmc_number . ".webp",
				'mobile' => "https://res.cloudinary.com/drfo99te6/f_auto,q_auto/v1/rolex/model-gallery/slide1-portrait/" . $rmc_number . ".webp"
			),
			array(
				'desktop' => "https://res.cloudinary.com/drfo99te6/f_auto,q_auto/v1/rolex/model-gallery/slide2-landscape/" . $rmc_number . ".webp",
				'mobile' => "https://res.cloudinary.com/drfo99te6/f_auto,q_auto/v1/rolex/model-gallery/slide2-potrait/" . $rmc_number . ".webp"
			),
			array(
				'desktop' => "https://res.cloudinary.com/drfo99te6/f_auto,q_auto/v1/rolex/model-gallery/slide3-landscape/" . $rmc_number . ".webp",
				'mobile' => "https://res.cloudinary.com/drfo99te6/f_auto,q_auto/v1/rolex/model-gallery/slide3-portrait/" . $rmc_number . ".webp"
			),
			array(
				'desktop' => "https://res.cloudinary.com/drfo99te6/f_auto,q_auto/v1/rolex/model-gallery/slide4-landscape/" . $rmc_number . ".webp",
				'mobile' => "https://res.cloudinary.com/drfo99te6/f_auto,q_auto/v1/rolex/model-gallery/slide4-portrait/" . $rmc_number . ".webp"
			)
		);
		$arr = $arr = preg_split('/\r\n|\r|\n/', $model_heading);
		$alt = "Rolex " . $family_handle . " in " .  $spec_material . " " .  $rmc_number . "- Montecristo Jewellers";

		// grabbing the product to change the push component depending upon the product family type
		$product = $productList[strtolower($family_handle)];

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

		$html = "";
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
	?>

		<!-- Products Schema -->

		<script type="application/ld+json">
			{
				"@context": "https://schema.org",
				"@type": "Product",
				"description": "<?php echo $arr[2] ?>",
				"name": "<?php echo $model_name ?>",
				"image": "<?php echo 'https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1716941421/rolex/upright_watches_assets/desktop/' . $rmc_number . '_drp-upright-bba-with-shadow.webp'; ?>",
				"sku": "<?php echo $rmc_number; ?>",
				"mpn": "",
				"brand": {
					"@type": "Brand",
					"name": "Rolex"
				},
				"offers": {
					"@type": "Offer",
					"priceSpecification": {
						"@type": "PriceSpecification",
						"price": <?php echo $price ?>,
						"priceCurrency": "CAD"
					}
				}

			}
		</script>

		<!-- Watch Cover -->
		<section class="grid-nospace watch-cover">
			<?php echo do_shortcode('[responsive_image 
				desktop_image_url="https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1/rolex/upright_watches_assets/upright_watch_assets/' . $rmc_number . '.webp" 
				mobile_image_url="https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1/rolex/upright_watches_assets/upright_watch_assets_portrait/' . $rmc_number . '.webp" 
				alt_text="' . $alt . '" loading="eager"]');
			?>
			<div id="watch-cover-splide" class="splide">
				<div class="splide__track">
					<ul class="splide__list">
						<?php
						echo '<li class="splide__slide">';
						echo do_shortcode('[responsive_image 
  desktop_image_url="https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1/rolex/upright_watches_assets/upright_watch_assets/' . $rmc_number . '.webp" 
  mobile_image_url="https://res.cloudinary.com/drfo99te6/q_auto,f_auto/v1/rolex/upright_watches_assets/upright_watch_assets_portrait/' . $rmc_number . '.webp" 
  alt_text="' . $alt . '" loading="eager"]');
						echo '</li>';


						foreach ($watch_gallery as $key => $gallery) {
							echo '<li class="splide__slide">';
							echo do_shortcode('[responsive_image desktop_image_url="' . $gallery['desktop'] . '" mobile_image_url="' . $gallery['mobile'] . '" alt_text="' . $alt . '"]');
							echo '</li>';
						}
						?>
					</ul>
				</div>
			</div>

			<div class="intro-text">
				<h1 data-rmc="<?php echo $rmc_number ?>" data-family="<?php echo $family_handle_data ?>">
					<p class="body24Bold brown"><?php echo $arr[0] ?>&nbsp;</p>
					<p class="headline50 brown"><?php echo $model_name ?>&nbsp;</p>
					<p class="body20Light"><?php echo $arr[2] ?>&nbsp;</p>
					<p class="body20Light"><?php echo $arr[3] ?></p>
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

		<!-- Watch Gallery -->
		<section class="grid-nospace watch-gallery">
			<?php
			foreach ($watch_gallery as $key => $gallery) {
				echo '<div class="watch-item" id="gallery-' . $key . '">';
				echo do_shortcode('[responsive_image desktop_image_url="' . $gallery['desktop'] . '" mobile_image_url="' . $gallery['mobile'] . '" alt_text="' . $alt . '"]');
				echo '</div>';
			}
			?>
		</section>

		<!-- Watch Lightbox -->
		<section id="watch-lightbox" class="watch-lightbox splide grid-nospace f9-background-container">
			<!-- Close button -->
			<button class="close-button" id="close-lightbox" aria-label="Close">
				<svg version="1.1" id="Calque_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 15 15" style="enable-background:new 0 0 15 15;/* color: red; *//* background: green; */" xml:space="preserve">
					<path class="st0" d="M15,8.8H8.8V15H6.2V8.8H0V6.2h6.2V0h2.5v6.2H15V8.8z"></path>
				</svg>
			</button>

			<div class="splide__container">
				<div class="splide__track">
					<ul class="splide__list">
						<?php
						foreach ($watch_gallery as $key => $gallery) {
							echo '<li class="splide__slide" id="gallery-' . $key . '">';
							echo do_shortcode('[responsive_image desktop_image_url="' . $gallery['desktop'] . '" mobile_image_url="' . $gallery['mobile'] . '" alt_text="' . $alt . '"]');
							echo '</li>';
						}
						?>
					</ul>
				</div>
				<div class="splide__arrows">
					<button class="splide__arrow splide__arrow--prev" aria-label="Previous slide">
						<svg focusable="false" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="12px" height="12px" viewBox="0 0 12 12" version="1.1">
							<g>
								<path d="M 9.601562 6 L 8.558594 7.121094 L 3.679688 12 L 2.480469 10.800781 L 7.359375 5.921875 L 2.398438 1.121094 L 3.601562 0 Z M 9.601562 6 "></path>
							</g>
						</svg>
					</button>
					<button class="splide__arrow splide__arrow--next" aria-label="Next slide">
						<svg focusable="false" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="12px" height="12px" viewBox="0 0 12 12" version="1.1">
							<g>
								<path d="M 9.601562 6 L 8.558594 7.121094 L 3.679688 12 L 2.480469 10.800781 L 7.359375 5.921875 L 2.398438 1.121094 L 3.601562 0 Z M 9.601562 6 "></path>
							</g>
						</svg>
					</button>
				</div>
			</div>
		</section>

		<!-- Watch Key Selling points -->
		<section class="grid-nospace key-selling">
			<?php
			$accordion_selling_points = array(
				array(
					'title' => 'Rolex Guarantee',
					'content' => 'To ensure the precision and reliability of its timepieces, Rolex submits each watch after assembly to a stringent series of tests. All new Rolex watches purchased from one of the brand’s Official Retailers come with a five-year international guarantee. When you buy a Rolex, the Official Retailer fills out and dates the Rolex guarantee card that certifies your watch’s authenticity.'
				),
				array(
					'title' => 'The green seal',
					'content' => 'The five-year guarantee which applies to all Rolex models is coupled with the green seal, a symbol of its status as a Superlative Chronometer. This exclusive designation attests that the watch has successfully undergone a series of specific final controls by Rolex in its own laboratories according to its own criteria, in addition to the official COSC certification of its movement.'
				),
				array(
					'title' => 'Rolex presentation box',
					'content' => 'Every Rolex is delivered in a beautiful green presentation box that is both protector and keeper of the jewel that nests inside it. As the presentation box is also a symbol of giving, it is important, if you are purchasing a gift, that the recipient’s first contact with their Rolex sets the stage for revealing what lies within.'
				),
			);
			echo do_shortcode('[responsive_image desktop_image_url="https://res.cloudinary.com/drfo99te6/image/upload/v1743695898/rolex/model-gallery/rolex_guarantee_desktop.jpg" mobile_image_url="https://res.cloudinary.com/drfo99te6/image/upload/v1743695898/rolex/model-gallery/rolex_guarantee_mobile.jpg" alt_text="Rolex International Warranty"]')
			?>
			<div class="accordion" role="region" aria-label="Key Selling Points">

				<?php
				foreach ($accordion_selling_points as $key => $accordion_items) {
					$aria_expanded = ($key === 0) ? 'true' : 'false';
				?>
					<div class="accordion-item" id="keyPointsAccordion">
						<button class="f9-background-container" id="accordion-button-<?php echo $key + 1 ?>" aria-expanded="<?php echo $aria_expanded ?>" aria-controls="accordion-content-<?php echo $key + 1 ?>">
							<div class="body24Bold"><?php echo $accordion_items['title'] ?></div>
							<svg class="icon-minus" width="16px" height="16px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" aria-hidden="true" focusable="false">
								<path fill="currentColor" d="M432 256c0 17.7-14.3 32-32 32L48 288c-17.7 0-32-14.3-32-32s14.3-32 32-32l352 0c17.7 0 32 14.3 32 32z"></path>
							</svg>
							<svg class="icon-plus" width="16px" height="16px" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" aria-hidden="true" focusable="false">
								<path fill="currentColor" d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 144L48 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l144 0 0 144c0 17.7 14.3 32 32 32s32-14.3 32-32l0-144 144 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-144 0 0-144z"></path>
							</svg>
						</button>
						<div class="accordion-content body20Light" id="accordion-content-<?php echo $key + 1 ?>"><?php echo $accordion_items['content'] ?></div>
					</div>
				<?php
				}; ?>
			</div>

		</section>

		<!-- Watch Spec -->
		<section class="grid-nospace watch-spec f4-background-container">

			<?php
			$specs = [
				['label' => 'Reference',         'value' => get_field('spec_reference')],
				['label' => 'Model case',        'value' => get_field('spec_model_case')],
				['label' => 'Bezel',             'value' => get_field('spec_bezel')],
				['label' => 'Water-resistance',  'value' => get_field('spec_water_resistance')],
				['label' => 'Movement',          'value' => get_field('spec_movement')],
				['label' => 'Calibre',           'value' => get_field('spec_calibre')],
				['label' => 'Power reserve',     'value' => get_field('spec_power_reserve')],
				['label' => 'Bracelet',          'value' => get_field('spec_bracelet')],
				['label' => 'Dial',              'value' => get_field('spec_dial')],
				['label' => 'Certification',     'value' => get_field('spec_certification')],
			];

			function chunk_specs($array, $columns)
			{
				return array_chunk($array, ceil(count($array) / $columns));
			}

			?>
			<div class="spec-columns mobile-spec">
				<?php foreach (chunk_specs($specs, 2) as $column): ?>
					<div class="spec-column">
						<?php foreach ($column as $spec): ?>
							<div class="spec-item">
								<p class="body20Bold brown"><?php echo esc_html($spec['label']); ?></p>
								<p class="body20Light black"><?php echo esc_html($spec['value']); ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="spec-columns desktop-spec">
				<?php foreach (chunk_specs($specs, 3) as $column): ?>
					<div class="spec-column">
						<?php foreach ($column as $spec): ?>
							<div class="spec-item">
								<p class="body20Bold brown"><?php echo esc_html($spec['label']); ?></p>
								<p class="body20Light black"><?php echo esc_html($spec['value']); ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="border-top"></div>

			<div class="brochure">
				<a id="downloadBrochure" class="primary-cta" href="<?php echo $brochure ?>" download target="_blank" title="<?php echo $spec_reference ?>" isdownload="true" type="secondary">
					<svg enable-background="new 0 0 15 15" viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg">
						<path d="m15 10v5h-1-1-11-2v-2-3h2v3h11v-3zm-9.5-.5 2 2 2-2 2-2h-3v-7.5h-.5-1-.5v7.5h-3z"></path>
					</svg>
					Download Brochure
				</a>
			</div>
		</section>

		<!-- Body text  -->
		<section class="grid-nospace product-body-text f9-background-container">
			<?php
			$features = [
				[
					'title' => $feature1_title,
					'text' => $feature1_text,
					'asset' => $feature1_asset,
				],
				[
					'title' => $feature2_title,
					'text' => $feature2_text,
					'asset' => $feature2_asset,
				],
				[
					'title' => $feature3_title,
					'text' => $feature3_text,
					'asset' => $feature3_asset,
				]
			];

			foreach ($features as $index => $feature) {

				$image_path = 'https://res.cloudinary.com/drfo99te6/q_auto/f_auto/v1717174410/rolex/specs_assets/';
				$desktop_image_url = $image_path . explode(",", $feature['asset'])[0] . '.webp';
				$mobile_image_url = $image_path . trim(explode(",", $feature['asset'])[1]) . '.webp';
			?>
				<div class="feature">
					<h2 class="headline50 brown"><?php echo $feature['title'] ?></h2>
					<p class="body20Light black"><?php echo $feature['text'] ?></p>
					<?php
					echo do_shortcode('[responsive_image desktop_image_url="' . $desktop_image_url . '" mobile_image_url="' . $mobile_image_url . '" alt_text="' . $alt . '"]')
					?>
				</div>
			<?php
			}
			?>
		</section>


		<!-- Model Availability -->
		<section class="grid-nospace f9-background-container" id="model-availability">
			<div class="model-availablity f4-background-container">

				<svg height="60px" width="60px" enable-background="new 0 0 456.6 494.7" viewBox="0 0 456.6 494.7" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
					<linearGradient id="a" gradientUnits="userSpaceOnUse" x1="35.557" x2="461.0639" y1="183.426" y2="330.8533">
						<stop offset="0" stop-color="#f9f4eb"></stop>
						<stop offset=".0889" stop-color="#f8f2e7"></stop>
						<stop offset=".1699" stop-color="#f6ecdd"></stop>
						<stop offset=".2479" stop-color="#f3e3cd"></stop>
						<stop offset=".3236" stop-color="#efd6b5"></stop>
						<stop offset=".3376" stop-color="#edd3b0"></stop>
						<stop offset=".4198" stop-color="#eccfa9"></stop>
						<stop offset=".4801" stop-color="#ebcaa0"></stop>
						<stop offset=".7029" stop-color="#e1bc8b"></stop>
						<stop offset=".8098" stop-color="#dcb480"></stop>
						<stop offset=".8437" stop-color="#ca9e70"></stop>
						<stop offset=".8929" stop-color="#b98960"></stop>
						<stop offset=".9442" stop-color="#af7d57"></stop>
						<stop offset="1" stop-color="#ac7954"></stop>
					</linearGradient>
					<linearGradient id="b" gradientUnits="userSpaceOnUse" x1="-8.0354" x2="430.5085" y1="206.6448" y2="284.9258">
						<stop offset="0" stop-color="#e8c798"></stop>
						<stop offset=".1386" stop-color="#e8c89b"></stop>
						<stop offset=".2413" stop-color="#ebcda4"></stop>
						<stop offset=".3158" stop-color="#edd3b0"></stop>
						<stop offset=".3985" stop-color="#ecd0a9"></stop>
						<stop offset=".4825" stop-color="#e9ca9d"></stop>
						<stop offset=".7567" stop-color="#e7c89a"></stop>
						<stop offset=".7693" stop-color="#c8a77f"></stop>
						<stop offset=".7898" stop-color="#a07e5e"></stop>
						<stop offset=".8108" stop-color="#816046"></stop>
						<stop offset=".8322" stop-color="#6a4b35"></stop>
						<stop offset=".8541" stop-color="#5c3d2b"></stop>
						<stop offset=".8767" stop-color="#533624"></stop>
						<stop offset=".9014" stop-color="#503322"></stop>
					</linearGradient>
					<linearGradient id="c" gradientUnits="userSpaceOnUse" x1="111.3558" x2="409.3732" y1="206.8396" y2="311.6709">
						<stop offset=".1197" stop-color="#f3dfbd"></stop>
						<stop offset=".2571" stop-color="#f0dbb9"></stop>
						<stop offset=".3824" stop-color="#e7cfac"></stop>
						<stop offset=".5029" stop-color="#d9ba96"></stop>
						<stop offset=".6199" stop-color="#c69f7a"></stop>
						<stop offset=".6909" stop-color="#b98b67"></stop>
					</linearGradient>
					<linearGradient id="d" gradientUnits="userSpaceOnUse" x1="-7.2722" x2="431.2716" y1="205.2322" y2="283.5132">
						<stop offset="0" stop-color="#e8c798"></stop>
						<stop offset=".1386" stop-color="#e8c89b"></stop>
						<stop offset=".2413" stop-color="#ebcda4"></stop>
						<stop offset=".3158" stop-color="#edd3b0"></stop>
						<stop offset=".3985" stop-color="#ecd0a9"></stop>
						<stop offset=".4825" stop-color="#e9ca9d"></stop>
						<stop offset=".7143" stop-color="#e7c89a"></stop>
						<stop offset=".7485" stop-color="#efd8b7"></stop>
						<stop offset=".7844" stop-color="#f5e5cf"></stop>
						<stop offset=".8213" stop-color="#faefe0"></stop>
						<stop offset=".8596" stop-color="#fdf5ea"></stop>
						<stop offset=".9014" stop-color="#fef7ee"></stop>
					</linearGradient>
					<linearGradient id="e" gradientUnits="userSpaceOnUse" x1="229.9764" x2="209.9776" y1="118.4817" y2="537.7627">
						<stop offset=".5807" stop-color="#fcfbe9" stop-opacity="0"></stop>
						<stop offset=".6278" stop-color="#eee6d0" stop-opacity=".1806"></stop>
						<stop offset=".7253" stop-color="#ccb592" stop-opacity=".5544"></stop>
						<stop offset=".8415" stop-color="#a37b4c"></stop>
						<stop offset=".843" stop-color="#9e764b"></stop>
						<stop offset=".8578" stop-color="#6e543e"></stop>
						<stop offset=".8731" stop-color="#4a3a31"></stop>
						<stop offset=".8886" stop-color="#2f2624"></stop>
						<stop offset=".9043" stop-color="#1c1718"></stop>
						<stop offset=".9204" stop-color="#0f0c0d"></stop>
						<stop offset=".9371" stop-color="#070506"></stop>
						<stop offset=".9552" stop-color="#050304"></stop>
					</linearGradient>
					<linearGradient id="f" gradientUnits="userSpaceOnUse" x1="-8.0354" x2="430.5085" y1="206.6448" y2="284.9258">
						<stop offset="0" stop-color="#e8c798"></stop>
						<stop offset=".1025" stop-color="#e8c89b"></stop>
						<stop offset=".1785" stop-color="#ebcda4"></stop>
						<stop offset=".2336" stop-color="#edd3b0"></stop>
						<stop offset=".3571" stop-color="#ecd0a9"></stop>
						<stop offset=".4825" stop-color="#e9ca9d"></stop>
						<stop offset=".796" stop-color="#e7c89a"></stop>
						<stop offset=".8884" stop-color="#967456"></stop>
						<stop offset=".964" stop-color="#62432f"></stop>
						<stop offset="1" stop-color="#503322"></stop>
					</linearGradient>
					<linearGradient id="g" gradientUnits="userSpaceOnUse" x1="392.616" x2="25.1207" y1="181.8093" y2="331.512">
						<stop offset=".8033" stop-color="#fcfbe9" stop-opacity="0"></stop>
						<stop offset=".8124" stop-color="#eee6d0" stop-opacity=".1806"></stop>
						<stop offset=".8313" stop-color="#ccb592" stop-opacity=".5544"></stop>
						<stop offset=".8538" stop-color="#a37b4c"></stop>
						<stop offset=".8552" stop-color="#9e764b"></stop>
						<stop offset=".8684" stop-color="#6e543e"></stop>
						<stop offset=".882" stop-color="#4a3a31"></stop>
						<stop offset=".8958" stop-color="#2f2624"></stop>
						<stop offset=".9098" stop-color="#1c1718"></stop>
						<stop offset=".9242" stop-color="#0f0c0d"></stop>
						<stop offset=".939" stop-color="#070506"></stop>
						<stop offset=".9552" stop-color="#050304"></stop>
					</linearGradient>
					<radialGradient id="h" cx="30.7999591" cy="146.5000498" fx="23.5845225" fy="166.477831" gradientUnits="userSpaceOnUse" r="32.8896207">
						<stop offset="0" stop-color="#fffefa"></stop>
						<stop offset=".2484" stop-color="#fefdf8"></stop>
						<stop offset=".4327" stop-color="#fefbf1"></stop>
						<stop offset=".4522" stop-color="#fdfaf0"></stop>
						<stop offset=".5126" stop-color="#f6efe2" stop-opacity=".8656"></stop>
						<stop offset=".6196" stop-color="#e4d2be" stop-opacity=".6273"></stop>
						<stop offset=".7605" stop-color="#c8a789" stop-opacity=".3137"></stop>
						<stop offset=".9014" stop-color="#ac7954" stop-opacity="0"></stop>
					</radialGradient>
					<radialGradient id="i" cx="234.5903" cy="322.8481" fx="237.449" fy="343.873" gradientTransform="matrix(-.8158 .8158 -.8937 -.8937 599.003 165.6998)" gradientUnits="userSpaceOnUse" r="32.8549">
						<stop offset="0" stop-color="#fffefa"></stop>
						<stop offset=".192" stop-color="#fefdf7"></stop>
						<stop offset=".3528" stop-color="#fdfaf0"></stop>
						<stop offset=".3928" stop-color="#fcf6e9"></stop>
						<stop offset=".4512" stop-color="#f7ead6"></stop>
						<stop offset=".5205" stop-color="#efd7b8"></stop>
						<stop offset=".5438" stop-color="#ecd0ac"></stop>
						<stop offset=".7972" stop-color="#714d38"></stop>
					</radialGradient>
					<radialGradient id="j" cx="-357.8369" cy="226.2444" fx="-352.8831" fy="247.1477" gradientTransform="matrix(-1.1203 0 0 -1.2159 -173.5847 310.3775)" gradientUnits="userSpaceOnUse" r="36.7659">
						<stop offset="0" stop-color="#fffefa"></stop>
						<stop offset=".1319" stop-color="#fefdf7"></stop>
						<stop offset=".2424" stop-color="#fdfaf0"></stop>
						<stop offset=".303" stop-color="#fcf6e9"></stop>
						<stop offset=".3915" stop-color="#f7ead6"></stop>
						<stop offset=".4966" stop-color="#efd7b8"></stop>
						<stop offset=".5319" stop-color="#ecd0ac"></stop>
						<stop offset=".6768" stop-color="#835d48"></stop>
						<stop offset=".9497" stop-color="#8c5c3d"></stop>
					</radialGradient>
					<radialGradient id="k" cx="-333.5293" cy="415.2615" fx="-340.7357" fy="440.5472" gradientTransform="matrix(-1.2082 -.2093 .2174 -1.2545 -158.298 521.4379)" gradientUnits="userSpaceOnUse" r="41.0564">
						<stop offset=".031" stop-color="#fffefa"></stop>
						<stop offset=".095" stop-color="#fcf9f3"></stop>
						<stop offset=".1884" stop-color="#f4eadf"></stop>
						<stop offset=".3" stop-color="#e7d4bf"></stop>
						<stop offset=".4246" stop-color="#d6b594"></stop>
						<stop offset=".4395" stop-color="#d4b18e"></stop>
						<stop offset=".5697" stop-color="#d7b694"></stop>
						<stop offset=".7467" stop-color="#6d422f"></stop>
					</radialGradient>
					<radialGradient id="l" cx="-277.0912" cy="427.1031" fx="-287.0761" fy="451.4519" gradientTransform="matrix(-1.1863 -.2055 .2055 -1.1863 6.4434 599.3594)" gradientUnits="userSpaceOnUse" r="41.0938">
						<stop offset=".0436" stop-color="#fdf6e0"></stop>
						<stop offset=".1115" stop-color="#fbf1d9"></stop>
						<stop offset=".2107" stop-color="#f4e3c5"></stop>
						<stop offset=".3285" stop-color="#eacea5"></stop>
						<stop offset=".3893" stop-color="#e4c092"></stop>
						<stop offset=".6319" stop-color="#8c5c3d"></stop>
						<stop offset=".6701" stop-color="#372623"></stop>
					</radialGradient>
					<path d="m416.4 119.7s-20.2 1.8-24 26.7c0 0 0 13 5.9 21.6l2.2 3.1s1 1.4-.6 5.1l-73.7 171.2-2.4 4.1s-2.6 4.1-9.6 1.4c0 0-6.1-2.4-4.7-13.4l20.2-146.6 13-87.7s.7-3.4 3.4-4.2c.7-.2 1.4-.5 2.1-1 4.3-2.6 16.9-11.8 17.5-27.7 0 0 3-16.9-11.6-27.5-10.4-7.6-24.4-8.5-35.4-1.9-6.4 3.8-12.3 10.4-13.7 22 0 0-3.1 13.2 4.5 24.4 0 0 5.3 6.3 6.7 7.3 0 0 1.6 1.4 1.2 3.1l-44.2 207.6s-3.1 12.6-13.4 12.8c0 0-7.1 1.4-8.5-10.6l-6.1-129.9-5.5-112.1s.6-5.5 4.7-7.9c0 0 15.5-11.8 10.8-30.7 0 0-4.7-29.3-37.9-23.4 0 0-24.6 7.3-17.5 40.7 0 0 2.4 9.4 11.4 14.9 0 0 5.5 2.4 5.5 9.6l-8.5 236.3s.8 13.8-12.2 14.7c0 0-7.7.8-12.4-23.2l-45.5-193.5s-2.5-11.2 3-16.1c0 0 17.6-22.9-2.4-44.4 0 0-16.7-16.3-38.7-1.2 0 0-15.7 9.8-9.4 35.6 0 0 1.5 10.7 15.2 18.1 0 0 7.9 2.6 8.5 9.5l27.9 186.1s5.7 42.5 4.3 50.9c0 0 .8 7.5-7.3 9.8 0 0-6.1 1.4-12.8-15.5l-69.1-157.9s-4.3-10.8 0-15.7c0 0 16.3-26.1-8.6-41.3 0 0-17.9-12.2-36.2 3.7 0 0-20.2 18.3 0 41.3 0 0 4.7 6.3 16.1 9.2 0 0 6.7.2 7.7 7.5l58.6 170.2 32.6 95.5s3.2 11.6 5.1 13.2c0 0 5.3 5.7 23.2 11.4 0 0 41.9 20.8 94.9 14.4 0 0 35.2-3.3 67.4-18.7l5.1-2.9s2.6-1 4.7-9.4l33-98.1 35.8-100.3 21-61.5 4.3-10.6s2.4-6.5 9.6-8.1c0 0 22.2-4.1 22-31.3.2-0-1.6-33.2-37.2-28.7zm-160 335.8s-55.4 9.2-87.3-11.2c0 0-15.1-7.9-10.2-27.7 0 0 3.6-9.3 17.3-14.2 0 0 22.4-13 78-7.7 0 0 44.6 5.1 44.8 28.5.1.1 7.9 24.6-42.6 32.3z" fill="url(#a)" stroke="url(#b)" stroke-miterlimit="10"></path>
					<path d="m417.23 117.913s-20.2 1.8-24 26.7c0 0 0 13 5.9 21.6l2.2 3.1s1 1.4-.6 5.1l-73.7 171.2-2.4 4.1s-2.6 4.1-9.6 1.4c0 0-6.1-2.4-4.7-13.4l20.2-146.6 13-87.7s.7-3.4 3.4-4.2c.7-.2 3-1.6 3.7-2 4.3-2.6 15.4-10.8 15.9-26.7 0 0 3-16.9-11.6-27.5-10.4-7.6-24.4-8.5-35.4-1.9-6.4 3.8-12.3 10.4-13.7 22 0 0-3.1 13.2 4.5 24.4 0 0 5.3 6.3 6.7 7.3 0 0 1.6 1.4 1.2 3.1l-44.2 207.6s-3.1 12.6-13.4 12.8c0 0-7.1 1.4-8.5-10.6l-6.1-129.9-5.5-112.1s.6-5.5 4.7-7.9c0 0 15.5-11.8 10.8-30.7 0 0-3.8-28.3-37-22.4 0 0-24.7 6.2-17.6 39.6 0 0 3.4 6.8 11.3 13.6 0 0 4.8 3.8 4.8 11.1l-8.5 236.3s.8 13.8-12.2 14.7c0 0-7.7.8-12.4-23.2l-45.5-193.6s-2.5-11.2 3-16.1c0 0 17.6-22.9-2.4-44.4 0 0-16.7-16.3-38.7-1.2 0 0-15.7 9.8-9.4 35.6 0 0 1.5 10.7 15.2 18.1 0 0 7.9 2.6 8.5 9.5l27.9 186.1s5.7 42.5 4.3 50.9c0 0 .8 7.5-7.3 9.8 0 0-6.1 1.4-12.8-15.5l-69.1-157.9s-4.3-10.8 0-15.7c0 0 16.3-26.1-8.6-41.3 0 0-17.9-12.2-36.2 3.7 0 0-20.2 18.3 0 41.3 0 0 4.7 6.3 16.1 9.2 0 0 6.7.2 7.7 7.5l58.6 170.2 32.6 95.5s3.2 11.6 5.1 13.2c0 0 5.3 5.7 23.2 11.4 0 0 41.9 20.8 94.9 14.4 0 0 35.2-3.3 67.4-18.7l5.1-2.9s2.6-1 4.7-9.4l33-98.1 35.8-100.3 21-61.5 4.3-10.6s2.4-6.5 9.6-8.1c0 0 22.2-4.1 22-31.3.2 0-1.6-33.2-37.2-28.7zm-160 335.8s-55.4 9.2-87.3-11.2c0 0-15.1-7.9-10.2-27.7 0 0 3.6-9.3 17.3-14.2 0 0 22.4-13 78-7.7 0 0 44.6 5.1 44.8 28.5.1.1 7.9 24.6-42.6 32.3z" fill="url(#c)" stroke="url(#d)" stroke-miterlimit="10"></path>
					<path d="m416.4 119.7s-20.2 1.8-24 26.7c0 0 0 13 5.9 21.6l2.2 3.1s1 1.4-.6 5.1l-73.7 171.2-2.4 4.1s-2.6 4.1-9.6 1.4c0 0-6.1-2.4-4.7-13.4l20.2-146.6 13-87.7s.7-3.4 3.4-4.2c.7-.2 1.4-.5 2.1-1 4.3-2.6 16.9-11.8 17.5-27.7 0 0 3-16.9-11.6-27.5-10.4-7.6-24.4-8.5-35.4-1.9-6.4 3.8-12.3 10.4-13.7 22 0 0-3.1 13.2 4.5 24.4 0 0 5.3 6.3 6.7 7.3 0 0 1.6 1.4 1.2 3.1l-44.2 207.6s-3.1 12.6-13.4 12.8c0 0-7.1 1.4-8.5-10.6l-6.1-129.9-5.5-112.1s.6-5.5 4.7-7.9c0 0 15.5-11.8 10.8-30.7 0 0-4.7-29.3-37.9-23.4 0 0-24.6 7.3-17.5 40.7 0 0 2.4 9.4 11.4 14.9 0 0 5.5 2.4 5.5 9.6l-8.5 236.3s.8 13.8-12.2 14.7c0 0-7.7.8-12.4-23.2l-45.5-193.5s-2.5-11.2 3-16.1c0 0 17.6-22.9-2.4-44.4 0 0-16.7-16.3-38.7-1.2 0 0-15.7 9.8-9.4 35.6 0 0 1.5 10.7 15.2 18.1 0 0 7.9 2.6 8.5 9.5l27.9 186.1s5.7 42.5 4.3 50.9c0 0 .8 7.5-7.3 9.8 0 0-6.1 1.4-12.8-15.5l-69.1-157.9s-4.3-10.8 0-15.7c0 0 16.3-26.1-8.6-41.3 0 0-17.9-12.2-36.2 3.7 0 0-20.2 18.3 0 41.3 0 0 4.7 6.3 16.1 9.2 0 0 6.7.2 7.7 7.5l58.6 170.2 32.6 95.5s3.2 11.6 5.1 13.2c0 0 5.3 5.7 23.2 11.4 0 0 41.9 20.8 94.9 14.4 0 0 35.2-3.3 67.4-18.7l5.1-2.9s2.6-1 4.7-9.4l33-98.1 35.8-100.3 21-61.5 4.3-10.6s2.4-6.5 9.6-8.1c0 0 22.2-4.1 22-31.3.2-0-1.6-33.2-37.2-28.7zm-160 335.8s-55.4 9.2-87.3-11.2c0 0-15.1-7.9-10.2-27.7 0 0 3.6-9.3 17.3-14.2 0 0 22.4-13 78-7.7 0 0 44.6 5.1 44.8 28.5.1.1 7.9 24.6-42.6 32.3z" fill="url(#e)" stroke="url(#f)" stroke-miterlimit="10"></path>
					<path d="m416.4 119.7s-20.2 1.8-24 26.7c0 0 0 13 5.9 21.6l2.2 3.1s1 1.4-.6 5.1l-73.7 171.2-2.4 4.1s-2.6 4.1-9.6 1.4c0 0-6.1-2.4-4.7-13.4l20.2-146.6 13-87.7s.7-3.4 3.4-4.2c.7-.2 1.4-.5 2.1-1 4.3-2.6 16.9-11.8 17.5-27.7 0 0 3-16.9-11.6-27.5-10.4-7.6-24.4-8.5-35.4-1.9-6.4 3.8-12.3 10.4-13.7 22 0 0-3.1 13.2 4.5 24.4 0 0 5.3 6.3 6.7 7.3 0 0 1.6 1.4 1.2 3.1l-44.2 207.6s-3.1 12.6-13.4 12.8c0 0-7.1 1.4-8.5-10.6l-6.1-129.9-5.5-112.1s.6-5.5 4.7-7.9c0 0 15.5-11.8 10.8-30.7 0 0-4.7-29.3-37.9-23.4 0 0-24.6 7.3-17.5 40.7 0 0 2.4 9.4 11.4 14.9 0 0 5.5 2.4 5.5 9.6l-8.5 236.3s.8 13.8-12.2 14.7c0 0-7.7.8-12.4-23.2l-45.5-193.5s-2.5-11.2 3-16.1c0 0 17.6-22.9-2.4-44.4 0 0-16.7-16.3-38.7-1.2 0 0-15.7 9.8-9.4 35.6 0 0 1.5 10.7 15.2 18.1 0 0 7.9 2.6 8.5 9.5l27.9 186.1s5.7 42.5 4.3 50.9c0 0 .8 7.5-7.3 9.8 0 0-6.1 1.4-12.8-15.5l-69.1-157.9s-4.3-10.8 0-15.7c0 0 16.3-26.1-8.6-41.3 0 0-17.9-12.2-36.2 3.7 0 0-20.2 18.3 0 41.3 0 0 4.7 6.3 16.1 9.2 0 0 6.7.2 7.7 7.5l58.6 170.2 32.6 95.5s3.2 11.6 5.1 13.2c0 0 5.3 5.7 23.2 11.4 0 0 41.9 20.8 94.9 14.4 0 0 35.2-3.3 67.4-18.7l5.1-2.9s2.6-1 4.7-9.4l33-98.1 35.8-100.3 21-61.5 4.3-10.6s2.4-6.5 9.6-8.1c0 0 22.2-4.1 22-31.3.2-0-1.6-33.2-37.2-28.7zm-160 335.8s-55.4 9.2-87.3-11.2c0 0-15.1-7.9-10.2-27.7 0 0 3.6-9.3 17.3-14.2 0 0 23.474-14.622 79.202-8.184 0 0 43.398 5.584 43.598 28.984.1.1 7.9 24.6-42.6 32.3z" fill="url(#g)" stroke="url(#f)" stroke-miterlimit="10"></path>
					<circle cx="30.8" cy="146.5" fill="url(#h)" r="26.8"></circle>
					<path d="m99.1 90.2c-12.6-12.1-12.5-33.3-1.1-44.4 11.2-10.9 29.8-10.2 41.9 1.9s14.7 26.9 1.9 41.9c-10.2 11.8-30.4 12.4-42.7.6z" fill="url(#i)"></path>
					<path d="m199.2 34.7c0-16.5 12.9-29.7 28.1-29.7s28.1 13.5 28.1 30-7.9 30.7-28.1 30.7c-15.2-.1-28.1-13.3-28.1-31z" fill="url(#j)"></path>
					<path d="m304.8 65.1c3-17.3 18.9-29 35.6-26.1 16.6 2.9 27.7 19.2 24.7 36.5s-17.2 29.3-35.6 26.1c-16.6-3-27.9-17.8-24.7-36.5z" fill="url(#k)"></path>
					<path d="m393.3 144.5c.4-16.7 18.5-27.4 34.9-24.6s27.4 18.5 24.6 34.9-14.5 28.1-34.9 24.6c-16.4-2.7-25-14-24.6-34.9z" fill="url(#l)"></path>
					<g fill="#140a06">
						<path d="m133.6 354.8s-11.4-21.5-17.5-37l-27.3-61.5-24.8-58.5s-10-21.4-12.1-28.9c0 0-1.3-4.9 2.5-12.1 0 0 9.1-12.4 3.1-20.9 0 0 1.6 6.2-2.8 12.8 0 0-7.2 11.2-7.5 15.5 0 0-.4 7.1 5.3 15.8 0 0 7.1 12.2 8.6 16.2 0 0 57.9 133.4 58.2 134.2.3.7 9.1 21.3 14.3 24.4z"></path>
						<path d="m139.5 147.1s25.8 101.3 27.4 105.3c0 0 13.5 53.5 16.1 59.3 0 0 4.4 5.4 8.8 8 0 0-4.9-6.1-7-15.6l-16.1-68.9-21.8-86.5s-10.8-41.4-11.1-50.2c0 0-.7-4.5 2.8-10l4.6-5.9c.5-.6.9-1.2 1.2-1.9 1-1.8 3-5.5 3.7-8.5 0 0 2-14.1-6.3-14.1 0 0-.3 7.7-4.6 14.3 0 0-6.2 9.6-8.9 15.3-.9 2-1.3 4.1-1.2 6.3.2 2.8 1.5 5.8 2.4 10.8.1 0 7.9 28.9 10 42.3z"></path>
						<path d="m229.5 78.8 3.3 79.2 4.5 92.6 5.7 57.4c1.2 12.4 8.3 10.9 8.3 10.9-6.6-2.8-6-22.7-6-22.7l-5.9-109.7-6-120c0-1 0-1.9.2-2.9 1.1-6.1 3.3-8.4 3.3-8.4 6-10.6 10.9-11.1 12.5-10.9 1.1.1 4.8.7 5.4-.3 1.1-1.7 1.5-8.2 1.5-8.2-1.4-6.3-5.9-7.8-5.9-7.8.4 1.4-5.9 8.5-5.9 8.5l-12 13.8c-3.3 4.3-3.1 10-3.1 10z"></path>
						<path d="m239.3 66c-.4.6-.6 1.2-.8 1.9-.3 1.6-.9 4.9-.6 7.5l5.5 119.9 6.3 118.3s.7 4.5 6.7 6.2c0 0-5.9-2.6-5.3-16l-5.2-116.2-5.7-111.6s-1.1-7.4 1.3-13.3z"></path>
						<path d="m314.2 353.1s-4.9-2.9-4.9-10.5l6.1-84.9 12.8-95.5 7.1-59.8s-.7-5.2 3.9-9.4c.6-.6 3.7-4.5 4.2-5.1 1.7-2.3 4.9-3.5 9.7-7.6l6.9-4.6c1.5-.7 3.9 2.8 3.9 1.2.1-3.1-1.1-7.3-3.3-10.2l-3.6-5.7s-11.1 8.2-14.2 10.6c0 0-12.4 12-15.3 22.9 0 0-2 12.2-2.7 20.2l-13.5 102.1s-15.9 106.7-12.3 127.4c0-0 3.2 16.7 15.2 8.9z"></path>
						<path d="m447 142.3s-4.9-7-11.4-9c-1-.3-2.1 0-2.8.6-1.7 1.4-4.7 3.9-6.8 5.6-.8.6-1.7 1.2-2.6 1.7-2.8 1.4-10.8 6.3-17.5 17.9-.4.6-.6 1.3-.7 2.1-.2 2.2-.7 7.1-.8 9.7 0 .7 0 1.3.2 2 .3 1.2.6 4.1-1.3 7.6l-44.4 104.8-26.9 65s-7.3 17.8-21.3 30c0 0-5.6 4.7-20.9 1.8 0 0-60.4-9.5-92.6-4.9 0 0-34.6 2.4-44.2 26.6 0 0-6.4 13.1-1.6 32.2 0 0 3.3 16.4 2.4 22.4 0 0 3.3 7.3 9.1 10.4l2.2 1.8s-15.3-.7-33.5-14.4c0 0 6.4 9.8 22 14.2 0 0 24 9.3 37.7 11.5 0 0-26.9-12-31.8-20.9 0 0 0-2.2 2.9-1.6 0 0 9.5 4.5 45.5 5 0 0 34.9.6 59.6-6.9 0 0 34.8-7.8 40.4-34 0 0 9.8-36.6 19.3-52 0 0 24.2-52.2 32.9-74.8l34.9-87.4s10.2-27 11.1-29c0 0 2.9-11.5 7.5-17.8 0 0 6.3-8.8 10.5-12.4 1.1-.9 2.3-1.6 3.7-1.8 1.6-.3 4.2-.6 6.4.5 0 0 11.5 4.7 15.8 4.2.1.1 2.6-2.3-3-10.7zm-145.6 283.2s.5 14.7-20.3 23.3c0 0-20.9 14.3-77.3 8.7 0 0-41.7.2-47.6-21.5 0 0-4.054-13.133 5.25-28.42 0 0 12.113-15.76 54.313-17.56 0 0 52.294-2.525 77.395 15.775 0-.001 10.058 7.397 8.242 19.705z"></path>
						<path d="m291.2 404.1c-9.1-7.1-18.124-11.522-18.124-11.522-30.9-6-60.837-4.316-60.837-4.316-44.9 3.1-53.239 22.638-53.239 22.638l.774-5.51c-3.402 1.686-2.416 13.034-1.263 14.712 1.426-3.967.705-5.328 4.329-8.875 6.625-6.484 11.734-7.626 14.795-8.919 6.878-2.905 14.116-4.767 20.569-5.961 11.525-2.132 20.547-2.132 20.547-2.132 20.134-.483 31.591.574 37.116 1.302 2.827.372 12.068 2.64 13.256 2.977 36.093 10.233 29.907 28.93 29.907 28.93 3.721-17.952-7.83-23.324-7.83-23.324z"></path>
					</g>
					<path d="m8.8 163.6s4.4 7.7 13.8 11.4c0 0 3.7 1.5 7.3 2.4 0 0 4.1.5 6 3.6l16.5 46.4 23.5 67.9 46 133.6 8.4 24.1 2.1 6.6s1.1 3.5 3.6 4.6c0 0 16.1 10.7 41.7 17.3 0 0 18.6 7.4 50.6 8.6 0 0 36.1 1.1 60.2-9 0 0 17.7-6.7 25.6-11.1 0 0-6.3 6-34.2 15.5 0 0-31 9.4-67.3 5.9 0 0-41.1-4.8-77.7-24.7 0 0-2.9-.5-5.8-10.1l-15.1-44.7-14.3-42.7-23.7-68.7-20.9-61.3-19.1-55s-1.4-4.6-5.5-4.9c-.4 0-.7-.1-1.1-.1-3-.8-17.2-4.8-20.6-15.6z" fill="#7b4f36"></path>
					<path d="m190.4 479.8s59.3 13.6 114.1-11.1l9.5-4.2s-18.1 16.4-72.6 21.2c-.1 0-31.2 1.4-51-5.9z" fill="#684c34"></path>
					<path d="m150.9 420.9s2.1-18.3 10.1-25.7c0 0 7.3-10.7 43.3-14.9 0 0 40.7-5.1 78.3 8.6 0 0 11.9 4.5 16.9 14.8 0 0-7.8-17.6-57.2-21 0 0-27.4-2.1-52.4 3-3.5.7-6.9 1.5-10.4 2.2-5.586 2.002-24.1 5.6-28.6 33z" fill="#79593a"></path>
				</svg>

				<div class="model-text">
					<h2 class="brown headline50">Model availability</h2>
					<p class="body20Light black">All Rolex watches are assembled by hand with the utmost care to ensure exceptional quality. Such high standards naturally restrict Rolex production capacity and, at times, the demand for Rolex watches outpaces this capacity.</p>

					<p class="body20Light black">Therefore, the availability of certain models may be limited. New Rolex watches are exclusively sold by Official Rolex Retailers, who receive regular deliveries and independently manage the allocation and sales of watches to customers.</p>

					<p class="body20Light black"> Montecristo Jewellers is proud to be part of the worldwide network of Official Rolex Retailers and can provide information on the availability of Rolex watches.</p>
				</div>

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
		            message="I\'m interested in ' . $product['title'] . ', reference ' . $rmc_number . '"
					success_img_url = "https://res.cloudinary.com/drfo99te6/image/upload/v1717530586/rolex/rolex-contact-module-message-assets-landscape/rolex-message-thank-you-landscape.jpg" success_alt="contact-form done" 
					is_modal_page="true"]'
			) ?>

			<div class="divider"></div>
		</section>

		<!-- Push -->
		<section class="grid-nospace f9-background-container push">
			<a class="large-img" href="/rolex/<?php echo strtolower(str_replace(" ", "-", $family_handle)); ?>">
				<picture class="">
					<source srcset="<?php echo $product["mobile_url"] ?>" media="(max-width: 768px)">
					<source srcset="<?php echo $product["desktop_url"] ?>" media="(min-width: 769px)">
					<img decoding="async" loading="lazy" src="<?php echo $product["desktop_url"] ?>" alt="<?php echo $product["subtitle"] ?>" width="100%" height="auto">
				</picture>
			</a>

			<div class="push-text">
				<p class="fixed16 brown"><strong><?php echo $product["title"] ?></strong></p>
				<h2 class="headline36 brown"><?php echo $product["subtitle"] ?></h2>
				<a href="/rolex/<?php echo strtolower(str_replace(" ", "-", $family_handle)); ?>" class="secondary-cta fixed14">
					Learn more
					<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="12px" height="12px" viewBox="0 0 12 12" version="1.1">
						<g>
							<path d="M 9.601562 6 L 8.558594 7.121094 L 3.679688 12 L 2.480469 10.800781 L 7.359375 5.921875 L 2.398438 1.121094 L 3.601562 0 Z M 9.601562 6 "></path>
						</g>
					</svg>
				</a>
			</div>
		</section>

	<?php
	} else {
		echo 'ACF plugin is not active.';
	}
	?>

</main><!-- #main -->


<?php get_template_part('template-parts/keepexploring'); ?>
<!-- Rolex Footer -->
<?php get_template_part('template-parts/rolex-footer') ?>
<?php
get_footer();
