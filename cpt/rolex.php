<?php
// Register Custom Post Type: Portfolio
function create_rolex_pages()
{

    $labels = array(
        'name'                  => _x('Rolex', 'Post Type General Name', 'text_domain'),
        'singular_name'         => _x('Rolex', 'Post Type Singular Name', 'text_domain'),
        'menu_name'             => __('Rolex', 'text_domain'),
        'name_admin_bar'        => __('Rolex', 'text_domain'),
        'archives'              => __('Rolex Archives', 'text_domain'),
        'attributes'            => __('Rolex Attributes', 'text_domain'),
        'parent_item_colon'     => __('Parent Rolex:', 'text_domain'),
        'all_items'             => __('All Rolex', 'text_domain'),
        'add_new_item'          => __('Add New Rolex', 'text_domain'),
        'add_new'               => __('Add New', 'text_domain'),
        'new_item'              => __('New Rolex', 'text_domain'),
        'edit_item'             => __('Edit Rolex', 'text_domain'),
        'update_item'           => __('Update Rolex', 'text_domain'),
        'view_item'             => __('View Rolex', 'text_domain'),
        'view_items'            => __('View Rolex', 'text_domain'),
        'search_items'          => __('Search Rolex', 'text_domain'),
        'not_found'             => __('Not found', 'text_domain'),
        'not_found_in_trash'    => __('Not found in Trash', 'text_domain'),
        'featured_image'        => __('Featured Image', 'text_domain'),
        'set_featured_image'    => __('Set featured image', 'text_domain'),
        'remove_featured_image' => __('Remove featured image', 'text_domain'),
        'use_featured_image'    => __('Use as featured image', 'text_domain'),
        'insert_into_item'      => __('Insert into Rolex', 'text_domain'),
        'uploaded_to_this_item' => __('Uploaded to this Rolex', 'text_domain'),
        'items_list'            => __('Rolex list', 'text_domain'),
        'items_list_navigation' => __('Rolex list navigation', 'text_domain'),
        'filter_items_list'     => __('Filter Rolex list', 'text_domain'),
    );
    $args = array(
        'label'                 => __('Rolex', 'text_domain'),
        'description'           => __('A custom post type for rolex', 'text_domain'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail'),
        'hierarchical'          => true,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-admin-page',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rewrite' => array(
            'slug' => '/rolex',
            'with_front' => false,
        ),
    );

    register_post_type('mc-rolex', $args);

    $labels = array(
        'name'                  => _x('Rolex Product', 'Post Type General Name', 'text_domain'),
        'singular_name'         => _x('Rolex Product', 'Post Type Singular Name', 'text_domain'),
        'menu_name'             => __('Rolex Product', 'text_domain'),
        'name_admin_bar'        => __('Rolex Product', 'text_domain'),
        'archives'              => __('Rolex Product Archives', 'text_domain'),
        'attributes'            => __('Rolex Product Attributes', 'text_domain'),
        'parent_item_colon'     => __('Parent Rolex Product:', 'text_domain'),
        'all_items'             => __('All Rolex Product', 'text_domain'),
        'add_new_item'          => __('Add New Rolex Product', 'text_domain'),
        'add_new'               => __('Add New', 'text_domain'),
        'new_item'              => __('New Rolex Product', 'text_domain'),
        'edit_item'             => __('Edit Rolex Product', 'text_domain'),
        'update_item'           => __('Update Rolex Product', 'text_domain'),
        'view_item'             => __('View Rolex Product', 'text_domain'),
        'view_items'            => __('View Rolex Product', 'text_domain'),
        'search_items'          => __('Search Rolex Product', 'text_domain'),
        'not_found'             => __('Not found', 'text_domain'),
        'not_found_in_trash'    => __('Not found in Trash', 'text_domain'),
        'featured_image'        => __('Featured Image', 'text_domain'),
        'set_featured_image'    => __('Set featured image', 'text_domain'),
        'remove_featured_image' => __('Remove featured image', 'text_domain'),
        'use_featured_image'    => __('Use as featured image', 'text_domain'),
        'insert_into_item'      => __('Insert into Rolex Product', 'text_domain'),
        'uploaded_to_this_item' => __('Uploaded to this Rolex Product', 'text_domain'),
        'items_list'            => __('Rolex Product list', 'text_domain'),
        'items_list_navigation' => __('Rolex Product list navigation', 'text_domain'),
        'filter_items_list'     => __('Filter Rolex Product list', 'text_domain'),
    );

    $args = array(
        'label'                 => __('Rolex Product', 'text_domain'),
        'description'           => __('A custom post type for rolex product', 'text_domain'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail'),
        'hierarchical'          => true,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-smiley',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rewrite' => array(
            'slug' => 'rolex/product',
            'with_front' => false,
        ),
    );

    register_post_type('mc-rolex-product', $args);
}
add_action('init', 'create_rolex_pages');

function create_rolex_category_taxonomy() {
    $labels = array(
        'name' => _x('Rolex Categories', 'taxonomy general name', 'textdomain'),
        'singular_name' => _x('Rolex Category', 'taxonomy singular name', 'textdomain'),
    );
    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'rolex-category'),
    );
    register_taxonomy('rolex_category', array('mc-rolex'), $args);

    $labels = array(
        'name' => _x('Rolex Product Categories', 'taxonomy general name', 'textdomain'),
        'singular_name' => _x('Rolex Product Category', 'taxonomy singular name', 'textdomain'),
    );
    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'rolex-product-category'),
    );
    register_taxonomy('rolex_product_category', array('mc-rolex-product'), $args);
}

add_action('init', 'create_rolex_category_taxonomy');
