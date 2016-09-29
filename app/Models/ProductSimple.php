<?php

namespace CartRabbit\Models;

class ProductSimple extends ProductBase
{
    /**
     * Product Id
     *
     * @var
     */
    protected $product_id;

    /**
     * Product Simple Class constructor.
     * @param $attributes array
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
    }

    /**
     * On Before Save Simple Product
     *
     * @param $http
     */
    public function onBeforeSave($http)
    {
        $product_id = $http['post_ID'];

        $post = $this->find($product_id);

        /** Add Product's Images ID to Product Meta */
        $gallery = $http['product_image_gallery'];

        if (count($gallery) !== 0) {
            $post->meta->_product_image_gallery = $gallery;
            $post->meta->_product_image_gallery_raw = json_encode(\CartRabbit\Helper\Util::attachmentToImageURL($gallery));
            $post->meta->image = get_the_post_thumbnail_url($product_id);
            $post->meta->image_thump = get_the_post_thumbnail_url($product_id, 'thumbnail');
        }
        /** Insert Product's Meta */
        $meta = array(
            'product_id',
            'status',
            'sku',
            'brand',
            'tax_profile',
            'add_cart_text',
            'regular_price',
            'basePrice',
            'manage_stock',
            'stock',
            'back_orders',
            'stockStatus',
            'min_qty_to_notify',
            'min_qty_notify_default',
            'product_type',
            'qty_restriction',
            'max_sale_qty',
            'max_sale_qty_default',
            'min_sale_qty',
            'min_sale_qty_default',
            'enable_tax',
            'shipping_enable',
            'shipping_class',
            'length',
            'width',
            'height',
            'length_class',
            'weight',
            'weight_class',
            'up_sells',
            'cross_sells',
            'sold',
            'on_hand',
            'onHold',
            'visible_on_storefront'

        );

        self::initialProductSetup($http);
        if ($http['sku'] == '') {
            $http['sku'] = str_replace(' ', '_', $http['post_name']);
        }
        foreach ($meta as $data) {
            if (isset($http[$data])) {
                if (is_array($http[$data])) {
                    $post->meta->$data = json_encode($http[$data]);
                } else {
                    $post->meta->$data = $http[$data];
                    $post->meta->product_id = $product_id;
                }
                $post->save();
            }
        }
    }

    /**
     * To Setup or Confirm the Initial Setup for the product
     *
     * @param array $http instance for access Post contents
     */
    public static function initialProductSetup(&$http)
    {
        /** To Set Minimum Sale Qty, if Empty */
        if ($http['min_sale_qty'] == '' OR $http['min_sale_qty'] == 0) {
            $http['min_sale_qty'] = 1;
        }
        /** To Set Maximum Sale Qty, if Empty */
        if ($http['max_sale_qty'] == '' OR $http['max_sale_qty'] == 0) {
            $http['max_sale_qty'] = $http['stock'];
        }
        if ($http['add_cart_text'] == '') $http['add_cart_text'] = 'Add To Cart';

        if ($http['manage_stock'] == 'yes') {
            if ($http['stock'] == '' OR $http['stock'] == 0) {
                $http['stockStatus'] = 'outOfStock';
                $http['stock'] = 0;
            }
        }
        if ($http['regular_price'] == '') $http['regular_price'] = 0;

        $http['on_hand'] = 0;
    }

}
