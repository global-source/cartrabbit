<?php

namespace CartRabbit\customPostTypes;

class CartRabbit_Order
{
    public function register()
    {

        \add_action('init', function () {

            $labels = [
                'name' => _x('Orders', 'Post Type General Name', 'text_domain'),
                'singular_name' => _x('Order', 'Post Type Singular Name', 'text_domain'),
                'menu_name' => __('Order', 'text_domain'),
                'name_admin_bar' => __('Order', 'text_domain'),
                'parent_item_colon' => __('Parent Order:', 'text_domain'),
                'all_items' => __('All Orders', 'text_domain'),
                'add_new_item' => __('Add New Order', 'text_domain'),
                'add_new' => __('Add New', 'text_domain'),
                'new_item' => __('New Order', 'text_domain'),
                'edit_item' => __('Edit Order', 'text_domain'),
                'update_item' => __('Update Order', 'text_domain'),
                'view_item' => __('View Order', 'text_domain'),
                'search_items' => __('Search Order', 'text_domain'),
                'not_found' => __('Not found', 'text_domain'),
                'not_found_in_trash' => __('Not found in Trash', 'text_domain'),
            ];
            $rewrite = [
                'slug' => 'orders',
                'with_front' => true,
                'pages' => true,
                'feeds' => true,
            ];
            $args = [
                'label' => __('Orders', 'text_domain'),
                'description' => __('List of Order', 'text_domain'),
                'labels' => $labels,
                'supports' => array(''),
                'hierarchical' => false,
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => false,
                'menu_position' => 5,
                'show_in_admin_bar' => true,
                'show_in_nav_menus' => true,
                'can_export' => true,
                'has_archive' => true,
                'exclude_from_search' => false,
                'publicly_queryable' => true,
                'rewrite' => $rewrite,
                'capability_type' => 'page',
            ];
            \register_post_type('CartRabbit_Order', $args);


        }, 0);
    }

}


