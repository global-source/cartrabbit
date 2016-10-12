<?php

/** @var  \Herbert\Framework\Application $container */
/** @var  \Herbert\Framework\Http $http */
/** @var  \Herbert\Framework\Router $router */
/** @var  \Herbert\Framework\Enqueue $enqueue */
/** @var  \Herbert\Framework\Panel $panel */
/** @var  \Herbert\Framework\Shortcode $shortcode */
/** @var  \Herbert\Framework\Widget $widget */

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * For Manage Customer's Orders
 */
if (!Capsule::Schema()->hasTable('cartrabbit_orders')) {
    Capsule::schema()->create('cartrabbit_orders', function ($table) {
        $table->increments('id');
        $table->string('order_user_id');
        $table->string('unique_order_id');
        $table->string('invoice_prefix');
        $table->integer('invoice_no');
        $table->string('order_status');
        $table->string('order_mail');
        $table->timestamps();
    });
}

if (!Capsule::Schema()->hasTable('cartrabbit_ordermeta')) {
    Capsule::schema()->create('cartrabbit_ordermeta', function ($table) {
        $table->increments('id');
        $table->integer('order_id');
        $table->string('meta_key');
        $table->longText('meta_value');
        $table->timestamps();
    });
}

/**
 * For Manage Customer's Order Meta's
 */
if (!Capsule::Schema()->hasTable('cartrabbit_order_items')) {
    Capsule::schema()->create('cartrabbit_order_items', function ($table) {
        $table->increments('id');
        $table->integer('order_id');
        $table->string('order_item_type');
        $table->timestamps();
    });
}

/**
 * For Manage Customer's Order Meta's
 */
if (!Capsule::Schema()->hasTable('cartrabbit_order_itemmeta')) {
    Capsule::schema()->create('cartrabbit_order_itemmeta', function ($table) {
        $table->increments('id');
        $table->integer('order_item_id');
        $table->string('meta_key');
        $table->longText('meta_value');
        $table->timestamps();
    });
}

/**
 * For Managing Products Special Prices
 */
if (!Capsule::Schema()->hasTable('cartrabbit_price')) {
    Capsule::schema()->create('cartrabbit_price', function ($table) {
        $table->increments('id');
        $table->string('post_id');
        $table->string('date_from');
        $table->string('date_to');
        $table->integer('qty_from');
        $table->integer('price');
        $table->timestamps();
    });
}