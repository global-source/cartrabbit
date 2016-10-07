<?php

//103 Temp For Alternative
$upload = wp_upload_dir();

return [

    /**
     * The Herbert version constraint.
     */
    'constraint' => '~0.9.9',

    /**
     * Auto-load all required files.
     */
    'requires' => [
        __DIR__ . '/app/customPostTypes.php'
    ],

    /**
     * The tables to manage.
     */
    'tables' => [
    ],


    /**
     * Activate
     */
    'activators' => [
        __DIR__ . '/app/activate.php'
    ],

    /**
     * Deactivate
     */
    'deactivators' => [
        __DIR__ . '/app/deactivate.php'
    ],

    /**
     * The shortcodes to auto-load.
     */
    'shortcodes' => [
        __DIR__ . '/app/shortcodes.php'
    ],

    /**
     * The widgets to auto-load.
     */
    'widgets' => [
        __DIR__ . '/app/widgets.php'
    ],

    /**
     * The styles and scripts to auto-load.
     */
    'enqueue' => [
        __DIR__ . '/app/enqueue.php'
    ],

    /**
     * The routes to auto-load.
     */
    'routes' => [
        'CartRabbit' => __DIR__ . '/app/routes.php'
    ],

    /**
     * The panels to auto-load.
     */
    'panels' => [
        'CartRabbit' => __DIR__ . '/app/panels.php'
    ],

    /**
     * The APIs to auto-load.
     */
    'apis' => [
        'CartRabbit' => __DIR__ . '/app/api.php'
    ],

    /**
     * The view paths to register.
     *
     * E.G: 'CartRabbit' => __DIR__ . '/views'
     * can be referenced via @CartRabbit/
     * when rendering a view in twig.
     */
    'views' => [
        'CartRabbit' => __DIR__ . '/resources/views'
    ],

    /**
     * The view globals.
     */
    'viewGlobals' => [

    ],

    /**
     * The asset path.
     */
    'assets' => '/resources/assets/',

    'site_url' => plugin_dir_url(__FILE__),

    'site_path' => plugin_dir_path(__FILE__),

    'site_addr' => get_site_url(),

    'library' => __DIR__ . '/app/library',

    'enc_key' => '!@#$%^OLKINU1234',

    'upload_dir' => $upload['basedir']
];
