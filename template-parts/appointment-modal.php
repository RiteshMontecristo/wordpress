<?php
/**
 * Book an Appointment modal.
 * Triggered by any element with class "open-appointment-modal".
 */
?>
<div id="appointment-modal-overlay" class="contact-modal-overlay" role="dialog" aria-modal="true" aria-label="Book an Appointment" hidden>
	<div class="contact-modal-inner appt-modal-inner">

		<div class="appt-modal-header">
			<span>Book an Appointment</span>
			<button class="appt-modal-close" aria-label="Close">&times;</button>
		</div>

		<div class="appt-form-container">

			<form id="appointmentModalForm" action="" class="appt-modal-form">

				<input type="hidden" name="nonce" id="appt-modal-nonce" value="<?php echo wp_create_nonce('mji_appointment_modal_nonce'); ?>">

				<div>
					<label for="appt-title">Title</label>
					<select name="title" id="appt-title">
						<option selected disabled value=""></option>
						<option value="Mr">Mr</option>
						<option value="Mrs">Mrs</option>
						<option value="Miss">Miss</option>
						<option value="Mx">Mx</option>
					</select>
				</div>

				<div class="appt-first-name">
					<label for="appt-first-name">First Name *</label>
					<input type="text" id="appt-first-name" name="firstName" required placeholder="Your First Name">
					<span class="error hidden" id="apptFirstNameError">First name is required</span>
				</div>

				<div class="appt-last-name">
					<label for="appt-last-name">Last Name *</label>
					<input type="text" id="appt-last-name" name="lastName" required placeholder="Your Last Name">
					<span class="error hidden" id="apptLastNameError">Last name is required</span>
				</div>

				<div class="appt-type">
					<label>Appointment Type *</label>
					<div class="appt-type-options">
						<label class="store-option">
							<input type="radio" name="appointmentType" value="in-store">
							<span class="store-info">
								<strong>In-Store</strong>
								<small>Visit us at one of our locations</small>
							</span>
						</label>
						<label class="store-option">
							<input type="radio" name="appointmentType" value="virtual">
							<span class="store-info">
								<strong>Virtual</strong>
								<small>Video call with our team</small>
							</span>
						</label>
					</div>
					<span class="error hidden" id="apptTypeError">Please select an appointment type</span>
				</div>

				<div class="appt-store-selection hidden" id="appt-store-selection">
					<span class="store-selection-label">Select a Store *</span>
					<div class="store-options">
						<label class="store-option">
							<input type="radio" name="store" value="downtown">
							<span class="store-info">
								<strong>Downtown Vancouver</strong>
								<small>406 Hornby St, Vancouver, BC V6C 0A6</small>
							</span>
						</label>
						<label class="store-option">
							<input type="radio" name="store" value="richmond">
							<span class="store-info">
								<strong>Richmond Centre</strong>
								<small>6551 Number 3 Rd Unit 1564, Richmond, BC V6Y 2B6</small>
							</span>
						</label>
					</div>
					<span class="error hidden" id="apptStoreError">Please select a store</span>
				</div>

				<div class="appt-date">
					<label for="appt-date">Preferred Date *</label>
					<input type="date" id="appt-date" name="date" min="<?php echo esc_attr(date('Y-m-d', strtotime('+1 day'))); ?>" required>
					<span class="error hidden" id="apptDateError">Date is required</span>
				</div>

				<div class="appt-time">
					<label for="appt-time">Preferred Time *</label>
					<select id="appt-time" name="time" required>
						<option value="" selected disabled>Select a time</option>
						<option value="10:00 AM">10:00 AM</option>
						<option value="11:00 AM">11:00 AM</option>
						<option value="12:00 PM">12:00 PM</option>
						<option value="1:00 PM">1:00 PM</option>
						<option value="2:00 PM">2:00 PM</option>
						<option value="3:00 PM">3:00 PM</option>
						<option value="4:00 PM">4:00 PM</option>
						<option value="5:00 PM">5:00 PM</option>
					</select>
					<span class="error hidden" id="apptTimeError">Time is required</span>
				</div>

				<div class="appt-email">
					<label for="appt-email">Email Address *</label>
					<input type="email" id="appt-email" name="email" placeholder="you@example.com" autocomplete="on">
					<span class="error hidden" id="apptEmailError">A valid email address is required</span>
				</div>

				<div class="appt-phone">
					<label for="appt-phone">Phone Number *</label>
					<input maxlength="10" type="tel" id="appt-phone" name="phone" placeholder="Your Phone Number" autocomplete="on">
					<span class="error hidden" id="apptPhoneError">Phone number is required</span>
				</div>

				<div class="appt-message">
					<label for="appt-message">Message</label>
					<textarea id="appt-message" name="message" placeholder="Anything you'd like us to know?" rows="3"></textarea>
				</div>

				<div class="appt-footer">
					<div>
						<div class="terms">
							<input type="checkbox" id="appt-terms" name="terms">
							<label for="appt-terms">I have read and accepted the <a href="/terms-and-conditions" target="_blank" rel="noopener noreferrer">terms and conditions</a> and <a href="/privacy-policy" target="_blank" rel="noopener noreferrer">privacy policy</a>.</label>
						</div>
						<span class="error hidden" id="apptTermsError">Please accept the privacy policy</span>
					</div>
					<button id="appt-submit" type="submit" class="btn submit-button">Book Appointment</button>
				</div>

				<p class="recaptcha-disclosure">This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank" rel="noopener noreferrer">Terms of Service</a> apply.</p>

				<ul id="apptServerError" class="server-error"></ul>

			</form>

			<div id="apptSuccess" class="appt-success" hidden>
				<h3>Appointment Request Received</h3>
				<p>Thank you for reaching out to Montecristo Jewellers. We've received your appointment request and one of our team members will be in touch with you shortly to confirm your appointment.</p>
			</div>

		</div><!-- .appt-form-container -->
	</div><!-- .appt-modal-inner -->
</div><!-- #appointment-modal-overlay -->
