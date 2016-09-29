<?php

namespace CartRabbit\Models;

use Flycartinc\Order\Model\Order;
use Illuminate\Database\Eloquent\Model as Eloquent;
use CartRabbit\Helper\Currency;
use CartRabbit\Helper\Util;

/**
 * Class products
 * @package CartRabbit\Models
 */
class Cart extends Eloquent
{
    /**
     * Cart constructor.
     */
    public function __construct()
    {
        //
    }

    /**
     * For Adding and Updating Cart | Automatically
     * @param $item
     * @return string
     */
    public function addCart($item)
    {
        if (!\Flycartinc\Cart\Cart::add($item)) {
            $log['error'] = 'Invalid Entry.';
            return $log;
        }
    }

    public function updateCartItems($items)
    {
        $log = [];
        foreach ($items['cart'] as $id => $item) {
            if (\Flycartinc\Cart\Cart::verifyStock($item['product_id'], $item['qty'])) {
                $product = \Flycartinc\Cart\Cart::search('product_id', $item['product_id']);
                $data = array(
                    'row_id' => $product['row_id'],
                    'field' => 'quantity',
                    'value' => $item['qty']
                );
                \Flycartinc\Cart\Cart::update_cart($data);
            } else {
                $log['error'] = 'Invalid Entry.';
            }
        }
        return $log;
    }

    public static function getCartSummery($html = false)
    {
        $currency = new Currency();
        $order = new Order();
        $order->initOrder();
        $summery = $order;
        if ($html) {
            $print = 'You have ' . $summery->cart_item_quantity . ' items | ' . $currency->format($summery->total) . '<br> <a href="' . Util::getURLof('cart') . '">View Cart</a>';
            return $print;
        } else {
            return [
                'quantity' => $summery->cart_item_quantity,
                'total' => $currency->format($summery->total)
            ];
        }
    }

}