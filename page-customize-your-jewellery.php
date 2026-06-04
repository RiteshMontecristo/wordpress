<?php

/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package storefront
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">

		<?php
		if (has_post_thumbnail()) {
			the_post_thumbnail('', ['class' => 'thumbnail-banner']);
		}
		?>
		<div class="col-full">
			<?php
			while (have_posts()) :
				the_post();

				the_content();

			endwhile; // End of the loop.
			?>
			<section id="customize-container" class="customize-container">

				<p class="copy">Let's begin crafting your dream jewellery by completing the form below. <br />Once submitted, our dedicated team will promptly connect with you to initiate the creation of your unique piece.</p>

				<form id="customize-form">
					<input type="hidden" name="customize_nonce" id="customize_nonce" value="<?php echo wp_create_nonce('customize_nonce') ?>" />
					<span>Kindly fill out the required fields (*).</span>

					<div>
						<label for="title">Title</label>
						<select name="title" id="title">
							<option selected disabled value=""></option>
							<option value="Mr">Mr</option>
							<option value="Mrs">Mrs</option>
							<option value="Miss">Miss</option>
							<option value="Mx">Mx</option>
						</select>
					</div>

					<div>
						<label for="firstName">First Name*</label>
						<input type="text" name="firstName" id="firstName" />
						<span class="error hidden" id="firstNameError">First name is required</span>
					</div>

					<div>
						<label for="lastName">Last Name*</label>
						<input type="text" name="lastName" id="lastName" />
						<span class="error hidden" id="lastNameError">Last name is required</span>
					</div>

					<div>
						<label for="phone">Phone*</label>
						<input type="tel" name="phone" id="phone" />
						<span class="error hidden" id="phoneError">Phone is required</span>
					</div>

					<div>
						<label for="email">Email*</label>
						<input type="email" name="email" id="email" />
						<span class="error hidden" id="emailError">Email is required</span>
					</div>

					<div class="preferred-contact">
						<label for="preferred-contact">Preferred Contact Method *</label>
						<select id="preferred-contact" name="preferredContact" required>
							<option value="" selected disabled>Select One</option>
							<option value="email">Email</option>
							<option value="phone">Phone</option>
							<option value="store">Visit a Store</option>
						</select>
						<span class="error hidden" id="preferredContactError">Please select the contact method you prefer</span>
					</div>

					<div class="store-selection hidden" id="store-selection">
						<span class="store-selection-label">Select a Store *</span>
						<div class="store-options">
							<label class="store-option">
								<input type="radio" name="preferredStore" value="downtown">
								<span class="store-info">
									<strong>Downtown Vancouver</strong>
									<small>406 Hornby St, Vancouver, BC V6C 0A6</small>
								</span>
							</label>
							<label class="store-option">
								<input type="radio" name="preferredStore" value="richmond">
								<span class="store-info">
									<strong>Richmond</strong>
									<small>6551 Number 3 Rd Unit 1564, Richmond, BC V6Y 2B6</small>
								</span>
							</label>
						</div>
						<span class="error hidden" id="storeSelectionError">Please select a store</span>
					</div>

					<div>
						<label for="jewelleryPiece">What jewellery piece would you like made? *</label>
						<select name="jewelleryPiece" id="jewelleryPiece">
							<option selected disabled value=""></option>
							<option value="Bracelets">Bracelets</option>
							<option value="Earrings">Earrings</option>
							<option value="Engagement Rings">Engagement Rings</option>
							<option value="Pendants & Necklace">Pendants & Necklace</option>
							<option value="Rings">Rings</option>
							<option value="Wedding Bands">Wedding Bands</option>
						</select>
						<span class="error hidden" id="jewelleryPieceError">Jewellery piece is required</span>
					</div>

					<div>
						<label for="montecristoPiece">Which Montecristo piece would you like to customize?</label>
						<select name="montecristoPiece" id="montecristoPiece">
							<option selected disabled value=""></option>
							<option value="Ballerina">Ballerina</option>
							<option value="C">C</option>
							<option value="Cleopatra">Cleopatra</option>
							<option value="Fiore">Fiore</option>
							<option value="Gioia">Gioia</option>
							<option value="Halo">Halo</option>
							<option value="Honeycomb">Honeycomb</option>
							<option value="Jean">Jean</option>
							<option value="Luna">Luna</option>
							<option value="Orchid">Orchid</option>
							<option value="Rombo">Rombo</option>
							<option value="Tennis">Tennis</option>
							<option value="Tondo">Tondo</option>
							<option value="Tu Sei">Tu Sei</option>
						</select>
						<span class="error hidden" id="montecristoPieceError">Montecristo piece is required</span>
					</div>

					<div>
						<label for="material">Material Preference</label>
						<input type="text" name="material" id="material" />
					</div>

					<div>
						<label for="gemstone">Gemstone Preference</label>
						<input type="text" name="gemstone" id="gemstone" />
					</div>

					<div class="inspiration-container">
						<label for="inspiration"> Tell us more about your design inspiration. *</label>
						<textarea name="inspiration" id="inspiration" rows="3"></textarea>
						<span class="error hidden" id="inspirationError">Design Inspiration is required</span>
					</div>


					<!-- Honeypot — hidden from real users, bots will fill it -->
					<div class="honeypot-field" aria-hidden="true">
						<label for="website">Website</label>
						<input type="text" name="website" id="website" tabindex="-1" autocomplete="off" />
					</div>

					<div class="customize-footer">
						<div>
							<div class="terms">
								<input id="terms" name="terms" type="checkbox">
								<label for="terms">I have read and accepted the terms and conditions and privacy policy. *</label>
							</div>
							<span class="error hidden" id="termsError">Terms and conditions is required</span>
						</div>

						<button class="btn" name="sendMessage" id="sendMessage">Send Message</button>
					</div>
					<p class="recaptcha-disclosure">This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank" rel="noopener noreferrer">Terms of Service</a> apply.</p>

					<ul id="serverError" class="server-error">
					</ul>

				</form>

				<div id="customizeSuccess" class="customize-success">
					<h3>Request Received</h3>
					<p>Thank you for sharing your vision with us. Our jewellery specialists have received your request and will reach out to you soon to begin bringing your dream piece to life.</p>
				</div>
			</section>

		</div>

	</main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
