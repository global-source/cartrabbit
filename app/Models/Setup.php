<?php

namespace CartRabbit\Models;

use Corcel\Post as Post;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Class products
 * @package CartRabbit\Models
 */
class Setup extends Post
{

    /**
     * Cart constructor.
     */
    public function __construct()
    {

    }

    /**
     *To Check Cart's Initial Configuration
     *
     */
    public function isNewBoot()
    {
        $isNew = false;
        $ID = Settings::getStoreConfigID();
        if (!$ID) {
            $isNew = true;
        }
        return $isNew;
    }

    /**
     * To Initialize and Configure the Default CartRabbit pages
     */
    public function initBasicPages()
    {
        /** Create Default Product category */
        wp_insert_term('uncategorized', 'genre');

        /** Create Default Pages */
        $pages = array(
            'products' => array(
                'title' => 'Products',
                'name' => 'products',
                'type' => 'page',
                'content' => '[CartRabbitProducts]'
            ),
            'cart' => array(
                'title' => 'Cart',
                'name' => 'cart',
                'type' => 'page',
                'content' => '[CartRabbitCart]'
            ),
            'checkout' => array(
                'title' => 'Checkout',
                'name' => 'checkout',
                'type' => 'page',
                'content' => '[CartRabbitCheckout]'
            ),
            'account' => array(
                'title' => 'Account',
                'name' => 'account',
                'type' => 'page',
                'content' => '[CartRabbitAccount]'
            ),
            'prePayment' => array(
                'title' => 'Pre Payment',
                'name' => 'prePayment',
                'type' => 'page',
                'content' => '[CartRabbitPrePayment]'
            ),
            'thankYou' => array(
                'title' => 'ThankYou',
                'name' => 'thankYou',
                'type' => 'page',
                'content' => '[CartRabbitThankYou]'
            )
        );

        $page_index = array();
        foreach ($pages as $index => $item) {
            $post = new Post();
            foreach ($item as $key => $value) {
                $key = 'post_' . $key;
                $post->$key = $value;
            }
            $post->save();
            $page_index[$index] = $post->ID;
        }
        /** Create Prodcuts Page for List Products */
        $product_id = $page_index['products'];

        /** Create Cart Page for List Cart Products */
        $cart_id = $page_index['cart'];

        /** Create Checkout Page for Shop Checkout */
        $checkout_id = $page_index['checkout'];

        /** Create Account Page for Manage Orders */
        $account_id = $page_index['account'];

        /** Create Thank You Page for Display on Payment Completion */
        $thankYou_id = $page_index['thankYou'];

        /** Create Default Page for Display  */
        $display = array(
            'page_to_list_product' => $product_id,
            'page_to_cart_product' => $cart_id,
            'page_to_account' => $account_id,
            'page_to_checkout' => $checkout_id,
            'page_to_thank' => $thankYou_id
        );
        (new Settings())->setPageToDisplay($display);

        Settings::set('d_config_catalog_model', 'no');
    }
}