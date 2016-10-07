<?php

namespace CartRabbit\Controllers\cart;

use CommerceGuys\Addressing\Repository\CountryRepository;
use CommerceGuys\Addressing\Repository\SubdivisionRepository;
use Corcel\Post;
use Flycartinc\Order\Model\Order;
use Herbert\Framework\Models\PostMeta;
use CartRabbit\Helper;
use CartRabbit\Controllers\BaseController;
use Herbert\Framework\Http;
use CartRabbit\Models\Cart;
use CartRabbit\Models\Checkout;
use CartRabbit\Models\Products;

/**
 * Class Cart
 * @package CartRabbit\Controllers
 */
class CartController extends BaseController
{

    /**
     * CartController constructor.
     * @param Http $http
     */
    public function __construct(Http $http)
    {
        parent::__construct();
    }

    /**
     * To Show the Cart products
     * @return view of Cart Items
     */
    public function showCart(Http $http)
    {
        $order = new Order();
        $order->initOrder();
        $countries = (new CountryRepository())->getList();
        $currency = new Helper\Currency();
        $session = Checkout::checkoutSession();
        return parent::view('Site.Cart.showCart', compact('countries', 'currency', 'order', 'session'));
    }


    /**
     * To Billing and Shipping for the client products
     */
    public function billing()
    {
        return parent::view('Site.Checkout.billing');
    }

    /**
     * To Add item to Cart
     *
     * @param $http Http instance for access post data
     * @return bool
     */
    public function addCart(Http $http)
    {
        $item['pro_id'] = $http->get('pro_id', false);
        $item['var_id'] = $http->get('id', false);
        $item['quantity'] = $http->get('txt_product_qty', false);
        $item['variation'] = $http->get('variant_combination', false);
        $item['is_variant'] = false;

        if (!$item['pro_id'] OR !$item['quantity']) return array();

        /** To Set Product Id to Process */
        $item['product_id'] = $item['pro_id'];
        if ($item['pro_id'] !== $item['var_id']) {
            $item['product_id'] = $item['var_id'];
            $item['is_variant'] = true;
        }
        $cart = new Cart();
        return $cart->addCart($item);
    }

    /**
     * To Update Caty Item by Item ID's
     *
     * @param $http Http instance for access post data
     * @return string Redirect to Previous URL
     */
    public function updateCart(Http $http)
    {
        $items = $http->get('cartrabbit', false);
        if ($items) {
            Session()->set('updateCartStart', 1);
            (new Cart())->updateCartItems($items);
        }
        return parent::redirect($_SERVER['HTTP_REFERER'], true);
    }

    /**
     * To Remove Cart Item by Item ID
     *
     * @param $http Http instance for access post data
     */
    public function removeCart(Http $http)
    {
        $id = ($http->has('id')) ? $http->get('id') : null;
        if (!is_null($id)) {
            $cart = new \Flycartinc\Cart\Cart();
            $row_id = $cart->getRowID($id);
            $cart->removeItem($row_id);
        }
    }

    public function getCartSummery()
    {
        return Cart::getCartSummery();
    }

    public function cartSummery()
    {
        $summery = Cart::getCartSummery(true);
        return parent::view('Site.Product.showCartSummery', compact('summery'));
    }

}