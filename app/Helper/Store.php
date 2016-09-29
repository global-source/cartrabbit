<?php

namespace CartRabbit\Helper;

use CartRabbit\Models\Customer;

/**
 * Class Store
 * @package CartRabbit\Helper
 */
class Store
{
    /**
     * Store constructor.
     */
    public function __construct()
    {
        //
    }

    /**
     *
     */
    public static function init()
    {
        Customer::initAddress();
    }
}