<?php namespace CartRabbit;

    /** @var \Herbert\Framework\Shortcode $shortcode */


/**
 * For Admin Panel Metabox
 */

$shortcode->add(
    'CartRabbitProductNewMetabox',
    __NAMESPACE__ . '\Controllers\Admin\AdminController@getProductMetabox'
);

$shortcode->add(
    'CartRabbitProductOrderInfo',
    __NAMESPACE__ . '\Controllers\Order\OrderController@getOrderInfo'
);

$shortcode->add(
    'CartRabbitProductOrderItems',
    __NAMESPACE__ . '\Controllers\Order\OrderController@getOrderedItems'
);

/** END ADMIN PANEL  */

/**
 * For Site
 */

$shortcode->add(
    'CartRabbitSingleProduct',
    __NAMESPACE__ . '\Controllers\Products\ProductsController@viewProduct',
    [
        'post_id' => 'postId'
    ]
);

$shortcode->add(
    'CartRabbitSingleProductTitle',
    __NAMESPACE__ . '\Controllers\Products\ProductsController@viewProductTitle',
    [
        'post_id' => 'postId'
    ]
);

$shortcode->add(
    'CartRabbitSingleProductImage',
    __NAMESPACE__ . '\Controllers\Products\ProductsController@viewProductImage',
    [
        'post_id' => 'postId'
    ]
);

$shortcode->add(
    'CartRabbitSingleProductPrice',
    __NAMESPACE__ . '\Controllers\Products\ProductsController@viewProductPrice',
    [
        'post_id' => 'postId'
    ]
);

$shortcode->add(
    'CartRabbitSingleProductDescription',
    __NAMESPACE__ . '\Controllers\Products\ProductsController@viewProductDescription',
    [
        'post_id' => 'postId'
    ]
);

$shortcode->add(
    'CartRabbitSingleProductGallery',
    __NAMESPACE__ . '\Controllers\Products\ProductsController@viewProductGallery',
    [
        'post_id' => 'postId'
    ]
);

$shortcode->add(
        'CartRabbitSingleProductCart',
    __NAMESPACE__ . '\Controllers\Products\ProductsController@viewProductCart',
    [
        'post_id' => 'postId'
    ]
);

/** PRODUCT SECONDARY CURRENCY */

$shortcode->add(
    'CartRabbitSecondaryCurrency',
    __NAMESPACE__ . '\Controllers\Products\ProductsController@secondaryCurrency'
);

/** PRODUCT CART SUMMERY */

$shortcode->add(
    'CartRabbitProductCartSummery',
    __NAMESPACE__ . '\Controllers\Cart\CartController@cartSummery'
);

/** END SITE  */

/** Page To Display | Short Codes */
$shortcode->add(
        'CartRabbitCart',
    __NAMESPACE__ . '\Controllers\Cart\CartController@showCart'
);

$shortcode->add(
    'CartRabbitProducts',
    __NAMESPACE__ . '\Controllers\Products\ProductsController@getProducts'
);

$shortcode->add(
    'CartRabbitCheckout',
    __NAMESPACE__ . '\Controllers\CheckOut\CheckoutController@init_CheckOut'
);

$shortcode->add(
    'CartRabbitAccount',
    __NAMESPACE__ . '\Controllers\Account\AccountController@myAccount'
);

$shortcode->add(
    'CartRabbitPrePayment',
    __NAMESPACE__ . '\Controllers\Account\OrderController@prePayment'
);

$shortcode->add(
    'CartRabbitThankYou',
    __NAMESPACE__ . '\Controllers\Account\AccountController@myAccount'
);

$shortcode->add(
    'CartRabbitBilling',
    __NAMESPACE__ . '\Controllers\Cart\CartController@billing'
);

$shortcode->add(
    'CartRabbitPermalink',
    __NAMESPACE__ . '\Controllers\Admin\SettingsController@configPermalink'
);

/**
 * TEST Purpose
 */
$shortcode->add(
    'CartRabbitTest',
    __NAMESPACE__ . '\Controllers\Admin\AdminController@test2'
);