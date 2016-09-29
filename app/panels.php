<?php namespace CartRabbit;

/** @var \Herbert\Framework\Panel $panel */

$panel->add( [
	'type'   => 'panel',
	'as'     => 'mainPanel',
	'title'  => 'CartRabbit',
	'rename' => 'Dashboard',
	'slug'   => 'dashboard',
	'icon'   => 'dashicons-carrot',
	'order'  => 5,
	'uses'   => __NAMESPACE__ . '\Controllers\Admin\DashboardController@getDashboard'
] );

$panel->add( [
	'type'   => 'sub-panel',
	'as'     => 'products',
	'parent' => 'mainPanel',
	'title'  => 'Products',
	'slug'   => 'edit.php?post_type=cartrabbit_product',
	'icon'   => 'dashicons-smiley',
	'rename' =>1,
	'order'  => 5,
	'uses'   => __NAMESPACE__ . '\Controllers\Products\ProductsController@productPanel'
] );


$panel->add( [
	'type'   => 'sub-panel',
	'parent' => 'mainPanel',
	'as'     => 'order',
	'title'  => 'Orders',
	'slug'   => 'cartrabbit_order',
	'uses'   => __NAMESPACE__ . '\Controllers\Order\OrderController@getOrderList',
	'post'    => __NAMESPACE__ . '\Controllers\Order\OrderController@updateOrder'
] );


$panel->add( [
	'type'   => 'sub-panel',
	'parent' => 'mainPanel',
	'as'     => 'cartConfig',
	'title'  => 'Settings',
	'slug'   => 'cartrabbit-config',
	'uses'   => __NAMESPACE__ . '\Controllers\Admin\SettingsController@cartConfiguration',
	'post'   => __NAMESPACE__ . '\Controllers\Admin\SettingsController@cartGeneralConfig',
] );

