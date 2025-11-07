<?php

/**
 * Related Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/related.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     10.3.0
 */

if (! defined('ABSPATH')) {
	exit;
}

global $product;

// Get the categories for the product
$terms = get_the_terms($product->get_id(), 'product_cat');
const JEWELLERY_CATEGORY = 569;
const MONTECRISTO_CATEGORY = 408;

// Loop through the product categories
if ($terms && ! is_wp_error($terms)) {

	$lastChild;

	// If the category has no children, that means its the last category and we need to get products related to this item
	foreach ($terms as $term) {
		$children = get_term_children($term->term_id, 'product_cat');
		$parentTerm = $term->parent;
		// since we have two categories for some products, want to remove child category of jewellery
		if (empty($children) && $parentTerm != JEWELLERY_CATEGORY) {
			$lastChild = $term;
			break;
		}
	}

	$termQuery = empty($lastChild) ? MONTECRISTO_CATEGORY : $lastChild->term_id;
	$args = array(
		'post_type' => 'product',
		'posts_per_page' => 3, // Limit to 3 products
		'post__not_in' => array($product->get_id()), // Exclude current product
		'tax_query' => array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $termQuery,
			),
		),
		'orderby' => 'rand', // Random order
	);

	$random_products = new WP_Query($args);

	if ($random_products->have_posts()) {
?>
		<div class="col-full">
			<section class="related_products">
				<h2>You might also like</h2>

		<?php
		woocommerce_product_loop_start();

		while ($random_products->have_posts()) {
			$random_products->the_post();
			wc_get_template_part('content', 'product'); // Display product
		}

		woocommerce_product_loop_end();
		echo "</section>";
		echo "</div>";
	}
}

wp_reset_postdata();
