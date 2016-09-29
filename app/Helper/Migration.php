<?php

namespace CartRabbit\Helper;


/**
 * Class Rules for Manage Rules for Different Forms
 * @package CartRabbit\Helper
 */
class Migration
{
    public static function cartrabbit_tables()
    {
        $tables = array(
            /** --------------------------- */
            /** For "CartRabbit Orders" Table */
            /** --------------------------- */
            'cartrabbit_orders' => [
                'order_user_id' => 'string',
                'unique_order_id' => 'string',
                'invoice_prefix' => 'string',
                'invoice_no' => 'integer',
                'order_status' => 'string',
                'order_mail' => 'string'
            ],

            /** ------------------------------- */
            /** For "CartRabbit Order Meta" Table */
            /** ------------------------------- */
            'cartrabbit_ordermeta' => [
                'order_id' => 'string',
                'meta_key' => 'string',
                'meta_value' => 'longtext'
            ],

            /** -------------------------------- */
            /** For "CartRabbit Order Items" Table */
            /** -------------------------------- */
            'cartrabbit_order_items' => [
                'order_id' => 'string',
                'order_item_type' => 'string'
            ],

            /** ------------------------------------ */
            /** For "CartRabbit Order Item Meta" Table */
            /** ------------------------------------ */
            'cartrabbit_order_itemmeta' => [
                'order_item_id' => 'integer',
                'meta_key' => 'string',
                'meta_value' => 'longtext'
            ],

            /** --------------------------- */
            /** For "CartRabbit Price" Table */
            /** --------------------------- */
            'cartrabbit_price' => [
                'post_id' => 'string',
                'date_from' => 'date',
                'date_to' => 'date',
                'qty_from' => 'integer',
                'price' => 'integer'
            ]
        );
        return $tables;
    }

}