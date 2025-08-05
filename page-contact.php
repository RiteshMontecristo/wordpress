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

			<h1><?php the_title() ?></h1>

			<p class="contact-content">
				Whether you have questions about our pieces, corporate affairs, or career opportunities, we’re here to assist you every step of the way.
				<br />
				Feel free to connect with us directly via email at corporate@montecristo1978.com.
			</p>

			<section id="contact-container" class="contact-us-container">

				<!-- <div class="form-navigation">
					<button id="contactNav">Contact</button>
					<button id="appointmentNav">Appointment</button>
				</div> -->

				<div class="form-container">

					<!--Contact Form -->
					<div class="contact-form-container">

						<form id="contactUsForm" action="" class="contact-us">
							<div>
								<h2>Send us a message</h2>
								<span>Kindly fill out all the listed fields.</span>
							</div>

							<input type="hidden" name="contact_us_nonce" id="contact_us_nonce" value="<?php echo wp_create_nonce('contact_us_nonce') ?>" />

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

							<div class="first-name">
								<label for="first-name">First Name:</label>
								<input type="text" id="first-name" name="first-name" required placeholder="Your First Name">
								<span class="error hidden" id="firstNameError">First name is required</span>
							</div>

							<div class="last-name">
								<label for="last-name">Last Name:</label>
								<input type="text" id="last-name" name="last-name" required placeholder="Your Last Name">
								<span class="error hidden" id="lastNameError">Last name is required</span>
							</div>

							<div class="preferred-contact">
								<label for="preferred-contact">Preferred Contact Method:</label>
								<select id="preferred-contact" name="preferred-contact" required>
									<option value="" selected disabled>Select One</option>
									<option value="email">Email</option>
									<option value="phone">Phone</option>
								</select>
							</div>

							<div class="email-address hidden">
								<label for="email">Email Address:</label>
								<input type="email" id="email" name="email" placeholder="you@example.com">
								<span class="error hidden" id="emailError">Email is required</span>
							</div>

							<div class="phone-number hidden">
								<label for="phone">Phone Number:</label>
								<input maxlength="10" type="tel" id="phone" name="phone" placeholder="Your Phone Number">
								<span class="error hidden" id="phoneError">Phone number is required and must be 10 characters</span>
							</div>

							<div class="message">
								<label for="message">Message:</label>
								<textarea id="message" name="message" required placeholder="Your Message" rows="4"></textarea>
								<span class="error hidden" id="messageError">Message is required</span>
							</div>

							<div class="contact-footer">
								<div>
									<div class="terms">
										<input type="checkbox" id="terms" name="terms">
										<label for="terms">I have read and accepted the terms and conditions and privacy policy.</label>
									</div>
									<span class="error hidden" id="termsError">Terms and Condition is required</span>
								</div>
								<button id="send-message" type="submit" class="btn submit-button">Send Message</button>
							</div>

							<ul id="serverError" class="server-error">
							</ul>

							<!-- Your code -->
						</form>

						<div id="contactSuccess" class="contact-success">
							<h3>Message sent successfully. </h3>

							<p>Thank you for reaching out to us. One of our representative will be in contact with you through your preferred contact method as soon as possible.</p>

							<a href="/" class="btn">Done</a>
						</div>
					</div>

					<!-- Appointment Form -->
					<!-- <div class="appointment-container">
						<div class="appointment-description">
							<h2>Schedule an Appointment</h2>
							<p>
								We invite you to experience the elegance of Montecristo firsthand. Whether you're interested in exploring our stunning jewellery collections or discussing bespoke designs, our team is ready to assist you.
							</p>
							<p>
								Please fill out the appointment form, and we will get back to you promptly to confirm your visit. We look forward to welcoming you and providing you with personalized service every step of the way.
							</p>
						</div>

						<form id="appointmentForm" action="" class="appointment-form">
							<input type="hidden" name="appointment_nonce" id="appointment_nonce" value="<?php echo wp_create_nonce('appointment_nonce') ?>" />

							<div class="first-name">
								<label for="first-name">First Name:</label>
								<input type="text" id="first-name" name="first-name" required placeholder="Your First Name">
								<span class="error hidden" id="firstNameError">First name is required</span>
							</div>

							<div class="last-name">
								<label for="last-name">Last Name:</label>
								<input type="text" id="last-name" name="last-name" required placeholder="Your Last Name">
								<span class="error hidden" id="lastNameError">Last name is required</span>
							</div>

							<div class="store">
								<label for="store">Appointment Store:</label>
								<select id="store" name="store" required>
									<option value="" selected disabled>Select store</option>
									<option value="vancouver">Downtown Vancouver</option>
									<option value="richmond">Richmond Centre</option>
									<option value="metrowtown">Metropolis at Metrotown</option>
								</select>
								<span class="error hidden" id="storeError">Store is required</span>
							</div>

							<div class="date">
								<label for="date">Appointment Date:</label>
								<input min="<?php echo date("Y-m-d", strtotime('+1 day')); ?>" type="date" id="date" name="date" required>
								<span class="error hidden" id="dateError">Date is required</span>
							</div>

							<div class="time">
								<label for="time">Appointment Time:</label>
								<select id="time" name="time" required>
									<option value="" selected disabled>Select One</option>
									<option value="" disabled>Select store and date first</option>
								</select>
								<span class="error hidden" id="timeError">Time is required</span>
							</div>

							<div class="preferred-contact">
								<label for="preferred-contact">Preferred Contact Method:</label>
								<select id="preferred-contact" name="preferred-contact" required>
									<option value="" selected disabled>Select One</option>
									<option value="email">Email</option>
									<option value="phone">Phone</option>
								</select>
							</div>

							<div class="email-address">
								<label for="email">Email Address:</label>
								<input type="email" id="email" name="email" placeholder="you@example.com">
								<span class="error hidden" id="emailError">Email is required</span>
							</div>

							<div class="phone-number">
								<label for="phone">Phone Number:</label>
								<input maxlength="10" type="tel" id="phone" name="phone" placeholder="Your Phone Number">
								<span class="error hidden" id="phoneError">Phone number is required and must be 10 characters</span>
							</div>

							<div class="file">
								<label for="imageFile">Upload Image</label>
								<div class="drop-area">
									<span>Drag & drop a file here, or click to select a file</span>
								</div>
								<input multiple style="display: none;" type="file" id="imageFile" name="imageFile" accept=".webp, .png, .jpeg, .jpg">
								<ul class="img-list">
								</ul>
							</div>

							<ul id="serverError" class="server-error">
							</ul>
							<button id="send-message" type="submit" class="submit-button">Book Appointment</button>
						</form>

						<div id="appointmentSuccess" class="appointment-success">
							<h3>Appointment request sent successfully. </h3>

							<p>Thank you for reaching out to us. One of our representative will be in contact with you through your preferred contact method as soon as possible regarding the appointment.</p>

							<a href="/" class="btn">Done</a>
						</div>

						</form>
					</div> -->

				</div>
			</section>
		</div>

		<?php
		while (have_posts()) :
			the_post();

			the_content();

		endwhile; // End of the loop.
		?>

	</main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
