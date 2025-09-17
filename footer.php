<?php

/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package storefront
 */

?>
<?php

if (is_product()) {
?>
    </div><!-- .col-full -->
<?php } ?>
</div><!-- #content -->

<?php
wp_footer();
?>
<footer id="colophon" class="site-footer" role="contentinfo">
    <div class="col-full">

        <div class="footer-one">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <img loading="lazy" height="47" width="250" src="<?php echo esc_url(wp_get_attachment_url(get_theme_mod('custom_logo'))); ?>" title="Montecristo Jeweller Logo" alt="Montecristo Jeweller Logo" />
            </a>

        </div>

        <?php
        wp_nav_menu(array(
            'theme_location' => 'footer-menu',
            'menu_class' => 'footer-two',
            'container' => 'nav',
            'walker' => new Walker_Nav_Menu_As_H2()
        )); ?>

        <div class="footer-three">
            <a href="/contact" class="btn boot">Contact Us</a>
            <a href="/customize-your-jewellery" class="btn boot">Customize it</a>
        </div>

        <div class="footer-four">
            <div class="social-icons">
                <h2>STAY CONNECTED</h2>
                <nav>
                    <ul>
                        <li><a href="https://www.facebook.com/montecristojewellers" target="_blank" rel="noopener noreferrer">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M2 6C2 3.79086 3.79086 2 6 2H18C20.2091 2 22 3.79086 22 6V18C22 20.2091 20.2091 22 18 22H6C3.79086 22 2 20.2091 2 18V6ZM6 4C4.89543 4 4 4.89543 4 6V18C4 19.1046 4.89543 20 6 20H12V13H11C10.4477 13 10 12.5523 10 12C10 11.4477 10.4477 11 11 11H12V9.5C12 7.567 13.567 6 15.5 6H16.1C16.6523 6 17.1 6.44772 17.1 7C17.1 7.55228 16.6523 8 16.1 8H15.5C14.6716 8 14 8.67157 14 9.5V11H16.1C16.6523 11 17.1 11.4477 17.1 12C17.1 12.5523 16.6523 13 16.1 13H14V20H18C19.1046 20 20 19.1046 20 18V6C20 4.89543 19.1046 4 18 4H6Z" fill="#ffffff"></path>
                                    </g>
                                </svg>
                            </a></li>
                        <li><a href="https://www.instagram.com/montecristojewellers/" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path d="M3 11C3 7.22876 3 5.34315 4.17157 4.17157C5.34315 3 7.22876 3 11 3H13C16.7712 3 18.6569 3 19.8284 4.17157C21 5.34315 21 7.22876 21 11V13C21 16.7712 21 18.6569 19.8284 19.8284C18.6569 21 16.7712 21 13 21H11C7.22876 21 5.34315 21 4.17157 19.8284C3 18.6569 3 16.7712 3 13V11Z" stroke="#ffffff" stroke-width="2"></path>
                                        <circle cx="16.5" cy="7.5" r="1.5" fill="#ffffff"></circle>
                                        <circle cx="12" cy="12" r="3" stroke="#ffffff" stroke-width="2"></circle>
                                    </g>
                                </svg></a></li>
                        <li><a href="https://www.linkedin.com/company/montecristo-jewellers/" target="_blank" rel="noopener noreferrer">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <path d="M6.5 8C7.32843 8 8 7.32843 8 6.5C8 5.67157 7.32843 5 6.5 5C5.67157 5 5 5.67157 5 6.5C5 7.32843 5.67157 8 6.5 8Z" fill="#ffffff"></path>
                                        <path d="M5 10C5 9.44772 5.44772 9 6 9H7C7.55228 9 8 9.44771 8 10V18C8 18.5523 7.55228 19 7 19H6C5.44772 19 5 18.5523 5 18V10Z" fill="#ffffff"></path>
                                        <path d="M11 19H12C12.5523 19 13 18.5523 13 18V13.5C13 12 16 11 16 13V18.0004C16 18.5527 16.4477 19 17 19H18C18.5523 19 19 18.5523 19 18V12C19 10 17.5 9 15.5 9C13.5 9 13 10.5 13 10.5V10C13 9.44771 12.5523 9 12 9H11C10.4477 9 10 9.44772 10 10V18C10 18.5523 10.4477 19 11 19Z" fill="#ffffff"></path>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M20 1C21.6569 1 23 2.34315 23 4V20C23 21.6569 21.6569 23 20 23H4C2.34315 23 1 21.6569 1 20V4C1 2.34315 2.34315 1 4 1H20ZM20 3C20.5523 3 21 3.44772 21 4V20C21 20.5523 20.5523 21 20 21H4C3.44772 21 3 20.5523 3 20V4C3 3.44772 3.44772 3 4 3H20Z" fill="#ffffff"></path>
                                    </g>
                                </svg>
                            </a></li>
                        <li><a href="https://ca.pinterest.com/MontecristoJewellers/" target="_blank" rel="noopener noreferrer">
                                <svg fill="#ffffff" height="200px" width="200px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-143 145 512 512" xml:space="preserve">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <g>
                                            <path d="M329,145h-432c-22.1,0-40,17.9-40,40v432c0,22.1,17.9,40,40,40h432c22.1,0,40-17.9,40-40V185C369,162.9,351.1,145,329,145z M339,617c0,5.5-4.5,10-10,10h-432c-5.5,0-10-4.5-10-10V185c0-5.5,4.5-10,10-10h432c5.5,0,10,4.5,10,10V617z"></path>
                                            <path d="M113,272.3c-70.7,0-128,57.3-128,128c0,52.4,31.5,97.4,76.6,117.2c-0.4-8.9-0.1-19.7,2.2-29.4 c2.5-10.4,16.5-69.7,16.5-69.7s-4.1-8.2-4.1-20.2c0-19,11-33.1,24.7-33.1c11.6,0,17.3,8.7,17.3,19.2c0,11.7-7.5,29.2-11.3,45.4 c-3.2,13.6,6.8,24.6,20.2,24.6c24.3,0,40.6-31.1,40.6-68c0-28-18.9-49-53.3-49c-38.8,0-63,28.9-63,61.3c0,11.2,3.3,19,8.4,25.1 c2.4,2.8,2.7,3.9,1.8,7.1c-0.6,2.3-2,8-2.6,10.3c-0.9,3.2-3.5,4.4-6.4,3.2c-17.9-7.3-26.2-26.9-26.2-48.9c0-36.4,30.7-80,91.5-80 c48.9,0,81,35.4,81,73.3c0,50.2-27.9,87.7-69.1,87.7c-13.8,0-26.8-7.5-31.3-15.9c0,0-7.4,29.5-9,35.2c-2.7,9.9-8,19.7-12.9,27.4 c11.5,3.4,23.7,5.3,36.3,5.3c70.7,0,128-57.3,128-128C241,329.6,183.7,272.3,113,272.3z"></path>
                                        </g>
                                    </g>
                                </svg>
                            </a></li>
                    </ul>
                </nav>
            </div>

            <div class="newsletter">
                <h2>NEWSLETTER</h2>
                <p>Subscribe to our newsletter to receive all our news and offers in advance.</p>

                <form id="newsletter-form" role="form" method="POST">
                    <input type="email" id="newsletter" class="newsletter-field" placeholder="E-mail" name="newsletter_email" aria-required="true" required>
                    </input>
                    <button name="newsletter_subscribe" type="submit" class="newsletter-submit" aria-label="Subscribe">Subscribe
                    </button>
                </form>

                <?php if (isset($_GET['subscribed']) && $_GET['subscribed'] === '1'): ?>
                    <p class="newsletter-success">🎉 Thank you for subscribing!</p>
                <?php elseif (isset($_GET['subscribed']) && $_GET['subscribed'] === '0'): ?>
                    <p class="newsletter-error">⚠️ There was a problem subscribing. Try again.</p>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- .col-full -->

    <p class="copyright">Copyright © <?php echo date("Y"); ?> Montecristo Jewellers Inc. All rights reserved. | <a class="privacy" href="/privacy-policy">Privacy Policy</a> | <button class="cookie" id="cookie">Cookie Policy</button></p>

</footer><!-- #colophon -->

</div><!-- #page -->

</body>

</html>