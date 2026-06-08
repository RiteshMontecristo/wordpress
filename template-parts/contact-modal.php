<?php
/**
 * Contact Us modal overlay — displayed on the products/shop pages.
 * Triggered by any element with class "open-contact-modal".
 */
?>

<div id="contact-modal-overlay" class="contact-modal-overlay" role="dialog" aria-modal="true" aria-label="Contact Us" hidden>
	<div class="contact-modal-inner">

		<button class="contact-modal-close" aria-label="Close contact form">&times;</button>

		<div class="contact-form-container">

			<form id="contactUsForm" action="" class="contact-us">
				<div>
					<h2>Send us a message</h2>
					<span>Kindly fill out all the listed fields.</span>
				</div>

				<input type="hidden" name="contact_us_nonce" id="contact_us_nonce" value="<?php echo wp_create_nonce( 'contact_us_nonce' ); ?>" />
				<!-- HONEYPOT (invisible to humans) -->
				<input
					type="text"
					name="website"
					class="honeypot-field"
					autocomplete="off"
					tabindex="-1" />

				<div>
					<label for="modal-title">Title</label>
					<select name="title" id="title">
						<option selected disabled value=""></option>
						<option value="Mr">Mr</option>
						<option value="Mrs">Mrs</option>
						<option value="Miss">Miss</option>
						<option value="Mx">Mx</option>
					</select>
				</div>

				<div class="first-name">
					<label for="first-name">First Name *</label>
					<input type="text" id="first-name" name="firstName" required placeholder="Your First Name">
					<span class="error hidden" id="firstNameError">First name is required</span>
				</div>

				<div class="last-name">
					<label for="last-name">Last Name *</label>
					<input type="text" id="last-name" name="lastName" required placeholder="Your Last Name">
					<span class="error hidden" id="lastNameError">Last name is required</span>
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

				<div class="email-address">
					<label for="email">Email Address *</label>
					<input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="on">
					<span class="error hidden" id="emailError">Email is required</span>
				</div>

				<div class="phone-number">
					<label for="phone">Phone Number *</label>
					<input maxlength="10" type="tel" id="phone" name="phone" placeholder="Your Phone Number" autocomplete="on">
					<span class="error hidden" id="phoneError">Phone number is required and must be 10 characters</span>
				</div>

				<div class="street">
					<label for="street">Street Address</label>
					<input type="text" id="street" name="street" placeholder="Your street address">
					<span class="error hidden" id="streetError">Street address is required</span>
				</div>

				<div class="city">
					<label for="city">City *</label>
					<input type="text" id="city" name="city" placeholder="Your city">
					<span class="error hidden" id="cityError">City is required</span>
				</div>

				<div class="province">
					<label for="province">Province *</label>
					<input type="text" id="province" name="province" placeholder="Your province">
					<span class="error hidden" id="provinceError">Province is required</span>
				</div>

				<div class="postalCode">
					<label for="postalCode">Postal Code</label>
					<input type="text" id="postalCode" name="postalCode" placeholder="Your postal code">
					<span class="error hidden" id="postalCodeError">Please enter a valid postal code</span>
				</div>

				<div class="country">
					<label for="country">Country *</label>
					<?php countrySelector('Canada') ?>
					<span class="error hidden" id="countryError">Country is required</span>
				</div>

				<div class="message">
					<label for="message">Message *</label>
					<textarea id="message" name="message" required placeholder="Your Message" rows="4"></textarea>
					<span class="error hidden" id="messageError">Message is required</span>
				</div>

				<div class="contact-footer">
					<div>
						<div class="terms">
							<input type="checkbox" id="terms" name="terms">
							<label for="terms">I have read and accepted the terms and conditions and <a href="/privacy-policy" target="_blank" rel="noopener noreferrer">privacy policy</a>.</label>
						</div>
						<span class="error hidden" id="termsError">Terms and Condition is required</span>
					</div>
					<button id="send-message" type="submit" class="btn submit-button">Send Message</button>
				</div>
				<p class="recaptcha-disclosure">This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank" rel="noopener noreferrer">Terms of Service</a> apply.</p>

				<ul id="serverError" class="server-error"></ul>
			</form>

			<div id="contactSuccess" class="contact-success">
				<h3>Message Received</h3>
				<p>Thank you for reaching out to Montecristo Jewellers. We've received your message and one of our team members will be in touch with you shortly through your preferred contact method.</p>
			</div>

		</div><!-- .contact-form-container -->
	</div><!-- .contact-modal-inner -->
</div><!-- #contact-modal-overlay -->
