<?php

function mc_register_repeatable_group_field_metabox()
{

	/**
	 * Repeatable Field Groups
	 */
	// $cmb_group = new_cmb2_box(array(
	// 	'id'           => 'sku_metabox',
	// 	'title'        => esc_html__('Enter SKU values', 'cmb2'),
	// 	'object_types' => array('product'), // can be page or posts or product or CPT
	// 	'context'      => 'normal',  // 'side' for side metabox, or 'advanced'
	// 	'priority'     => 'high',    // Metabox display priority, needs to be high or will be replaced by woocommerce
	// ));

	// /**
	//  * Group fields works the same, except ids only need
	//  * to be unique to the group. Prefix is not needed.
	//  *
	//  * The parent field's id needs to be passed as the first argument.
	//  */
	// $group_field_id = $cmb_group->add_field(array(
	// 	'name'       => esc_html__('SKU Entries', 'cmb2'),
	// 	'id'         => 'new_repeatable_sku_field',
	// 	'type'       => 'group',
	// 	'repeatable' => true, // This makes the text field repeatable
	// 	'options'     => array(
	// 		'group_title'   => __('SKU Set {#}', 'textdomain'),
	// 		'add_button'    => __('Add Another SKU Set', 'textdomain'),
	// 		'remove_button' => __('Remove SKU Set', 'textdomain'),
	// 	),
	// ));


	// // Add a text field for the SKU
	// $cmb_group->add_group_field($group_field_id, array(
	// 	'id'   => 'sku_text',
	// 	'type' => 'text',
	// 	'name' => __('SKU Text', 'textdomain'),
	// 	'sanitize_cb' => 'validate_sku_text',
	// ));


	// // Add a select field for variations
	// $cmb_group->add_group_field($group_field_id, array(
	// 	'id'      => 'sku_variation',
	// 	'type'    => 'select',
	// 	'name'    => __('Variation', 'textdomain'),
	// 	'options_cb' => 'get_product_variation_options', // Callback for dynamic options
	// 	'show_on_cb' => 'show_sku_field_if_variations_exist', // Custom display callback
	// 	'validate' => array('required' => true)
	// ));


	// HOME PAGE
	$cmb_group_home = new_cmb2_box(array(
		'id'           => 'home_banner',
		'title'        => esc_html__('Enter slider information', 'cmb2'),
		'object_types' => array('page'),
		// only display on particular page
		'show_on'      => array(
			'key'   => 'id',
			'value' => get_option('page_on_front') // This needs to be id of the page, currently it uses the front-page id
		),
	));


	// Main group to make it repeateable
	$group_field_id = $cmb_group_home->add_field(array(
		'id'          => 'mc_repeatable_slider',
		'type'        => 'group', // this is what makes it repeatable
		'description' => esc_html__('Add images, and URLs', 'cmb2'),
		'options'     => array(
			'group_title'   => esc_html__('Slider {#}', 'cmb2'),
			'add_button'    => esc_html__('Add Another Slider', 'cmb2'),
			'remove_button' => esc_html__('Remove Slider', 'cmb2'),
		),
	));

	// adding it to the group to make it repeatable
	$cmb_group_home->add_group_field($group_field_id, array(
		'name' => esc_html__('URL', 'cmb2'),
		'id'   => 'mc_repeatable_url',
		'type' => 'text_url',
	));

	$cmb_group_home->add_group_field($group_field_id, array(
		'name' => esc_html__('Image', 'cmb2'),
		'id'   => 'mc_repeatable_image',
		'type' => 'file',
	));

	$cmb_group_home->add_group_field($group_field_id, array(
		'name' => esc_html__('Mobile Image', 'cmb2'),
		'id'   => 'mc_repeatable_mobile_image',
		'type' => 'file',
	));
}

add_action('cmb2_admin_init', 'mc_register_repeatable_group_field_metabox');

function get_product_variation_options($field)
{
	// Get the current post ID
	$post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;

	// Ensure it's a product and has variations
	if ($post_id) {
		$product = wc_get_product($post_id);

		if ($product && $product->is_type('variable')) {
			$variation_ids = $product->get_children();
			$options = array();

			foreach ($variation_ids as $variation_id) {
				$variation = wc_get_product($variation_id);
				$attributes = $variation->get_attributes();
				$options[$variation_id] = implode(", ", $attributes);
			}

			return $options;
		}
	}

	return array(__('No variations available', 'your-textdomain')); // Default if no variations
}

function show_sku_field_if_variations_exist()
{
	$post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
	if ($post_id) {
		$product = wc_get_product($post_id);

		if ($product && $product->is_type('variable')) {
			return true;
		}
	}

	return false;
}

function validate_sku_text($value, $field_args, $field) {
    if (empty($value)) {
        // Prevent saving empty values and show an admin error.
        add_settings_error('cmb2', 'sku_text_empty', __('SKU Text cannot be empty!', 'textdomain'));
        return false; // Return false to prevent saving the field.
    }
    return $value; // If valid, return the value to save it.
}