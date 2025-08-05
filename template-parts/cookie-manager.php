<div class="cookie-overlay hidden" tabindex="0">

    <div class="cookie-container">
        <div class="cookie-header"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/montecristo-symbol.png" title="Montecristo logo" alt="a montecristo symbol" width="101" height="66">
            <h2 class="font-bold text-2xl">We value your privacy</h2>
        </div>
        <p id="cookieInfo"> On our website, we use services (including from third-party providers) that help us to improve our online presence.
        </p>

        <div class="preference-container hidden">
            <div class="preference">
                <input type="checkbox" name="necessary" checked disabled />
                <label for="necessary">Necessary</label>
                <p>Used for cookie control. Can't be turned off.</p>
            </div>
            <div class="preference">
                <input type="checkbox" name="statistics" id="statistics" />
                <label for="statistics">Statistics</label>
                <p>Enables storage (such as cookies) related to analytics e.g. visit duration.</p>
            </div>
            <div class="preference">
                <input type="checkbox" name="marketing" id="marketing" />
                <label for="marketing">Marketing</label>
                <p>Enables storage (such as cookies) related to advertising.</p>
            </div>
            <div class="preference">
                <input type="checkbox" name="rolex" id="rolex" />
                <label for="rolex">Rolex (Adobe Analytics and Content Square)</label>

                <div class="rolex-text">
                    <p>Please visit our <a href="/privacy">Privacy Policy</a> page to learn more.</p>
                    <p>Adobe Analytics <a href="https://www.adobe.com/privacy/policy.html" target="_blank">https://www.adobe.com/privacy/policy.html</a></p>
                    <p>Content Square <a href="https://contentsquare.com/" target="_blank">https://contentsquare.com/</a></p>
                </div>
            </div>
        </div>

        <div class="cookie-cta-container">
            <button id="preference">PREFERENCES</button>
            <button id="declineAll">DECLINE ALL</button>
            <button id="acceptAll">ACCEPT ALL</button>
            <button id="accept" class="hidden">ACCEPT</button>
        </div>
    </div>

</div>