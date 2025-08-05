<?php

/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined('ABSPATH') || exit;

get_header('shop');

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action('woocommerce_before_main_content');

/**
 * Hook: woocommerce_shop_loop_header.
 *
 * @since 8.6.0
 *
 * @hooked woocommerce_product_taxonomy_archive_header - 10
 */
do_action('woocommerce_shop_loop_header');

if (woocommerce_product_loop()) {

	/**
	 * Hook: woocommerce_before_shop_loop.
	 *
	 * @hooked woocommerce_output_all_notices - 10
	 * @hooked woocommerce_result_count - 20
	 * @hooked woocommerce_catalog_ordering - 30
	 */
	do_action('woocommerce_before_shop_loop');

	$current_category = get_queried_object();

	// Only for Brand Blancpain
	if (isset($current_category->slug) && $current_category->slug === 'blancpain') {

		// Get ordered subcategories
		$children = get_terms([
			'taxonomy' => 'product_cat',
			'parent' => $current_category->term_id,
			'orderby' => 'menu_order',
			'order' => 'ASC',
			'hide_empty' => false,
		]);

		if (!empty($children) && !is_wp_error($children)) {
			$products_needed = 12;
			$found_products = 0;


			echo '<ul class="products columns-3">';

			foreach ($children as $child) {
				$remaining = $products_needed - $found_products;

				if ($remaining <= 0) break;

				$args = [
					'post_type' => 'product',
					'posts_per_page' => $remaining,
					'tax_query' => [
						[
							'taxonomy' => 'product_cat',
							'field' => 'term_id',
							'terms' => $child->term_id
						]
					],
					'no_found_rows' => true,
					'post_status' => 'publish'
				];

				$loop = new WP_Query($args);

				if ($loop->have_posts()) {
					while ($loop->have_posts()) : $loop->the_post();
						if ($found_products == 12) {
							return;
						}
						wc_get_template_part('content', 'product');
						$found_products++;
					endwhile;
				}

				wp_reset_postdata();
			}

			echo '</ul>';
		}
	} else {
		// For all other categories – default WooCommerce loop
		woocommerce_product_loop_start();

		if (wc_get_loop_prop('total')) {
			while (have_posts()) : the_post();

				/**
				 * Hook: woocommerce_shop_loop.
				 */
				do_action('woocommerce_shop_loop');

				wc_get_template_part('content', 'product');
			endwhile;
		}

		woocommerce_product_loop_end();
	}

	/**
	 * Hook: woocommerce_after_shop_loop.
	 *
	 * @hooked woocommerce_pagination - 10
	 */
	do_action('woocommerce_after_shop_loop');
} else {
	/**
	 * Hook: woocommerce_no_products_found.
	 *
	 * @hooked wc_no_products_found - 10
	 */
	do_action('woocommerce_no_products_found');
}

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action('woocommerce_after_main_content');

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action('woocommerce_sidebar');

get_footer('shop');
