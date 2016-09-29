<?php

namespace CartRabbit\Helper;


/**
 * Class Rules for Manage Rules for Different Forms
 * @package CartRabbit\Helper
 */
class Rules
{
    public static function checkout_customer_billing_address()
    {
        return [
            /** For Validating Billing Address Form */
            'fname' => 'Required',
            'lname' => 'Required',
            'mobile' => 'Required',
            'address1' => 'Required',
            'city' => 'Required',
            'postalCode' => 'Required',
            'country' => 'Required',
        ];
    }

    public static function checkout_customer_shipping_address()
    {
        return [
            /** For Validating Delivery Address Form */
            'fname' => 'Required',
            'lname' => 'Required',
            'mobile' => 'Required',
            'address1' => 'Required',
            'city' => 'Required',
            'postalCode' => 'Required',
            'country' => 'Required'
        ];
    }

}