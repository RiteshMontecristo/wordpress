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


					<ul id="serverError" class="server-error">
					</ul>

				</form>

				<div id="customizeSuccess" class="customize-success">
					<h3>Message sent successfully. </h3>

					<p>Thank you for reaching out to us. One of our representative will be in contact with you through your preferred contact method as soon as possible.</p>

					<a href="/" class="btn">Done</a>
				</div>
			</section>

		</div>

	</main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
