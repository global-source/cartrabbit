<?php

namespace CartRabbit\Models;

interface ProductInterface
{


    /**
     * To Return the Product's actual price.
     *
     * @param int $qty
     * @return bool|float Price of a Product
     */

    public function getPrice($qty = 1, $date_from = null, $date_to = null);

    /**
     * To Return the product's price with "Including Tax".
     *
     * @return array
     */

    public function get_price_including_tax($qty = 1, $price = '');

    /**
     * Returns the price (excluding tax) - ignores tax_class filters since the price may *include* tax and thus needs subtracting.
     * Uses store base tax rates. Can work for a specific $qty for more accurate taxes.
     *
     * @param  int $qty
     * @param  string $price to calculate, left blank to just use get_price()
     * @param  bool $single_product represents, that product need quantity reset [ex. quantity = 1 or quantity > 1]
     * @return string
     */

    public function get_price_excluding_tax($qty = 1, $price = '', $single_product);


    public function requiresShipping();


}
