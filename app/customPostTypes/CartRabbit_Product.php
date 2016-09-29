<?php

namespace CartRabbit\customPostTypes;

class CartRabbit_Product
{
    public function register()
    {
        add_action('init', function () {

            $labels = [
                'name' => _x('Products', 'Post Type General Name', 'text_domain'),
                'singular_name' => _x('Product', 'Post Type Singular Name', 'text_domain'),
                'menu_name' => __('Product', 'text_domain'),
                'name_admin_bar' => __('Product', 'text_domain'),
                'parent_item_colon' => __('Parent Product:', 'text_domain'),
                'all_items' => __('All Product', 'text_domain'),
                'add_new_item' => __('Add New Product', 'text_domain'),
                'add_new' => __('Add Product', 'text_domain'),
                'new_item' => __('New Product', 'text_domain'),
                'edit_item' => __('Edit Product', 'text_domain'),
                'update_item' => __('Update Product', 'text_domain'),
                'view_item' => __('View Product', 'text_domain'),
                'search_items' => __('Search Product', 'text_domain'),
                'not_found' => __('Not found', 'text_domain'),
                'not_found_in_trash' => __('Not found in Trash', 'text_domain'),
            ];
            $rewrite = [
                'slug' => 'items',
                'with_front' => true,
                'pages' => true,
                'feeds' => true,
            ];
            $args = [
                'label' => __('products', 'text_domain'),
                'description' => __('List of Products', 'text_domain'),
                'labels' => $labels,
                'hierarchical' => false,
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'menu_position' => 5,
                'show_in_admin_bar' => true,
                'show_in_nav_menus' => true,
                'can_export' => true,
                'has_archive' => true,
                'exclude_from_search' => false,
                'publicly_queryable' => true,
                'query_var' => true,
//				'rewrite'             => $rewrite,
                'rewrite' => false,
                'capability_type' => 'page',
                'supports' => array('title', 'editor', 'thumbnail', 'page-attributes')
            ];
            register_post_type('CartRabbit_Product', $args);

        }, 0);
    }

}


