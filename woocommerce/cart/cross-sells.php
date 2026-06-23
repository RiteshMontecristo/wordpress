<?php
/**
 * Cross-sells — child theme override.
 * Removes the add-to-cart / Read more button from the cross-sells loop.
 */

defined( 'ABSPATH' ) || exit;

if ( $cross_sells ) :

	// Remove button before loop, restore after so we don't affect the rest of the page.
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	?>

	<div class="cross-sells">
		<?php
		$heading = apply_filters( 'woocommerce_product_cross_sells_products_heading', __( 'You may be interested in&hellip;', 'woocommerce' ) );
		if ( $heading ) : ?>
			<h2><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>

		<?php woocommerce_product_loop_start(); ?>

			<?php foreach ( $cross_sells as $cross_sell ) :
				$post_object = get_post( $cross_sell->get_id() );
				setup_postdata( $GLOBALS['post'] = $post_object );
				wc_get_template_part( 'content', 'product' );
			endforeach; ?>

		<?php woocommerce_product_loop_end(); ?>
	</div>

	<?php
	add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

endif;

wp_reset_postdata();
