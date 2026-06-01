<?php

function mc_register_repeatable_group_field_metabox()
{

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

