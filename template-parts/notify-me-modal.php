<?php
/**
 * Notify Me modal — shown on out-of-stock product pages.
 * Triggered by any element with class "mji-open-notify-modal".
 */
?>
<div id="mji-notify-modal-overlay" hidden role="dialog" aria-modal="true" aria-label="Notify Me When Back in Stock">
    <div class="mji-notify-modal">
        <div class="mji-notify-modal-header">
            <span>Notify Me</span>
            <button class="mji-notify-modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="mji-notify-modal-body">
            <p>Leave your email below and we'll reach out as soon as this item is back in stock.</p>
            <div class="mji-notify-form" data-product="">
                <div class="mji-notify-field">
                    <input type="email" class="mji-notify-email" placeholder="<?php esc_attr_e('Your email address', 'woocommerce'); ?>">
                    <button type="button" class="mji-notify-submit"><?php esc_html_e('Notify Me', 'woocommerce'); ?></button>
                </div>
                <p class="mji-notify-message"></p>
            </div>
        </div>
    </div>
</div>
