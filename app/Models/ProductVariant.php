<?php

namespace CartRabbit\Models;

class ProductVariant extends ProductBase
{
    /**
     * @var array
     */
    protected static $postTypes = ['cartrabbit_variant'];

    protected $with = ['meta'];


}
