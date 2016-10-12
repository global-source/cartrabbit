<?php

namespace CartRabbit\Models;

use Corcel\Attachment;
use Corcel\Post;
use Corcel\PostMeta;
use CartRabbit\Helper\Util;

/**
 * Class ProductVariable
 * @package CartRabbit\Models
 */
class ProductVariable extends ProductBase
{
    private $variant_list = [];

    private $product_attributes = [];

    /**
     * @return mixed
     */
    public function getProductAttributes()
    {
        return $this->product_attributes;
    }

    /**
     * @param mixed $product_attributes
     */
    public function setProductAttributes($product_attributes)
    {
        $this->product_attributes = $product_attributes;
    }

    /**
     * @return array
     */
    public function getVariantList()
    {
        return $this->variant_list;
    }

    /**
     * @param array $variant_list
     */
    public function setVariantList($variant_list)
    {
        $this->variant_list = $variant_list;
    }

    /**
     * Product Variable Class constructor.
     * @param $attributes array
     *
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
    }

    /**
     * @return mixed
     */
    public function variants()
    {
        return $this->hasMany('CartRabbit\Models\ProductVariant', 'post_parent')->where('post_type', 'cartrabbit_variant');
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
            $post->meta->_product_image_gallery_raw = json_encode(Util::attachmentToImageURL($gallery));
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

        $post->meta->product_id = $product_id;
        foreach ($meta as $data) {
            if (isset($http[$data])) {
                if (is_array($http[$data])) {
                    $post->meta->$data = json_encode($http[$data]);
                } else {
                    $post->meta->$data = $http[$data];
                }
            }
            $post->save();
        }
    }

    /**
     * To Setup or Confirm the Initial Setup for the product
     *
     * @param array $http instance for access Post contents
     */
    public static function initialProductSetup(&$http)
    {
        //TODO: Verify this
        /** To Set Minimum Sale Qty, if Empty */
        if (isset($http['min_sale_qty'])) {
            if (($http['min_sale_qty'] == '' OR $http['min_sale_qty'] == 0)) {
                $http['min_sale_qty'] = 1;
            }
        } else {
            $http['min_sale_qty'] = 1;
        }

        /** To Set Maximum Sale Qty, if Empty */
        if (isset($http['max_sale_qty'])) {
            if ($http['max_sale_qty'] == '' OR $http['max_sale_qty'] == 0) {
                if (isset($http['stock'])) {
                    $http['max_sale_qty'] = $http['stock'];
                } else {
                    $http['max_sale_qty'] = 0;
                }
            }
        } else {
            $http['max_sale_qty'] = 0;
        }

        if (isset($http['add_cart_text'])) {
            if ($http['add_cart_text'] == '') $http['add_cart_text'] = 'Add To Cart';
        } else {
            $http['add_cart_text'] = 'Add';
        }

        if (isset($http['manage_stock'])) {
            if ($http['manage_stock'] == 'yes') {
                if ($http['stock'] == '' OR $http['stock'] == 0) {
                    $http['stockStatus'] = 'outOfStock';
                    $http['stock'] = 0;
                }
            }
        } else {
            $http['stockStatus'] = 'outOfStock';
            $http['stock'] = 0;
        }

        if (isset($http['regular_price'])) {
            if ($http['regular_price'] == '') $http['regular_price'] = 0;
        } else {
            $http['regular_price'] = 0;
        }


        if (isset($http['on_hand'])) {
            $http['on_hand'] = 0;
        } else {
            $http['on_hand'] = 0;
        }
    }

    /**
     * To Save Variant Combinations, by Ajax call.
     * @param $http
     */
    public static function saveVariants($http)
    {
        $post_parent_id = Util::extractDataFromHTTP('post');

        $data = $http->all();

        $result = array();

        foreach ($data['data'] as $set) {
            $result[$set['name']] = $set['value'];
        }
        $ids = array();

        $variants = array();

        /** Here, the Unwanted Special Characters "[,]" are going to Eliminated */
        foreach ($result as $key => $value) {
            if (str_contains($key, '[variant_product][product_id]')) {
                $ids[] = $value;
            } elseif (str_contains($key, 'cartrabbit[variant][attr]')) {
                $index = str_replace('cartrabbit[variant][attr]', '', $key);
                $index = explode('][', $index);
                $index = str_replace('[', '', $index);
                $index[1] = str_replace(']', '', $index[1]);

                /** To Eliminate Extra Text on $Value */
                if (str_contains($value, '_' . $index[0])) {
                    $value = str_replace('_' . $index[0], '', $value);
                }
                $index[0] = 'attribute_pro_' . $index[0];
                $variants[$index[1]][$index[0]] = $value;
            }
        }

        /** This will returns the Group of Variant Product's ID */
        foreach ($ids as $id) {
            foreach ($result as $key => $value) {
                if (str_contains($key, 'cartrabbit[variant_product][' . $id . ']')) {
                    $index = str_replace('cartrabbit[variant_product][' . $id . ']', '', $key);
                    $index = str_replace(array('[', ']'), '', $index);
                    $variants[$id]['product_id'] = $id;
                    $variants[$id][$index] = $value;
                }
            }
        }
        $price_range = [];
        loop:
        $product = array_last($variants);

        $product['raw_image'] = $product['_thumbnail_id'];

        /**
         * To set the Thumbnail ID
         */
        $_thumbnail_id = Attachment::where('guid', $product['_thumbnail_id'])->pluck('ID');
        $product['_thumbnail_id'] = $_thumbnail_id[0];

        /** To Manually Set the status, if not defined */
        if (!isset($product['enabled'])) $product['enabled'] = 'off';

        $post = Post::find($product['product_id']);
        $post->post_type = 'cartrabbit_variant';
        $post->post_parent = $post_parent_id;
        $post->save();

        if (!isset($product['min_qty_notify_default'])) $product['min_qty_notify_default'] = 'off';

        $price_range[] = ($product['regular_price'] !== '') ? $product['regular_price'] : 0;

        /** This will automatically insert the sku, if not set */
        if ($product['sku'] == '') {
            if (str_contains($post->post_name, 'variation')) {
                /** If SKU is not Set, then it will update with unique Product ID */
                $key = Post::find($post->ID)->post_title;
                Util::makeString($key);
                $product['sku'] = $key . '-' . $product['product_id'];
            } else {
                $key = $post->post_title;
                Util::makeString($key);
                $product['sku'] = $key;
            }
        }

        /** To Ensure there is no duplicate SKU */
//        $product['sku'] = self::verifySKU($product['sku']);

        /** Modify the Response */
        if ($product['shipping_enable'] == 'on') {
            $product['shipping_enable'] = 'yes';
        } else {
            $product['shipping_enable'] = 'no';
        }

        foreach ($product as $key => $value) {
            $post->meta->$key = $value;
        }
        $post->save();
        /** Loop Will continue until the end of variant list */
        if (!empty($variants)) {
            if (count($variants) > 1) {
                array_pop($variants);
                goto loop;
            }
        }

        /** To Save the Price Range of the product */
        if (!empty($price_range)) {
            sort($price_range);

            if (count($price_range) == 1) {
                $price_range[1] = $price_range[0];
                $price_range[0] = 0;
            }

            $post_parent = Post::find($post_parent_id);
            $post_parent->meta->min_price = array_first($price_range);
            $post_parent->meta->max_price = array_last($price_range);
            $post_parent->save();
        }

    }

    public function getVariants()
    {
        //Verify and Update the Attribute list.
        if (empty($variant_list)) {
            //Verify and Update the Product attributes also.
            if (empty($this->product_attributes)) {
                $this->product_attributes = json_decode($this->meta->product_attributes, true);
            }
            $this->variant_list = $this->variants()->get();
        }
        return $this->variant_list;
    }

    public function hasChild()
    {
        return $this->getVariants()->count() > 0;
    }

    /**
     * Processing the Product.
     * @param $complete bool to represent, product in completely or abstract process.
     * @return object
     */
    public function processProduct($complete = true)
    {
        parent::processProduct($complete);
        if ($complete) {
            $this->extractVariantOptions();
            $this->getAllChildren();
        }
    }

    public function getAllChildren()
    {
        $list = [];
        if ($this->hasChild()) {
            $variants = $this->variant_list;
            foreach ($variants as $index => $variant) {
                $variant->processProduct();
                $list[$variant->ID] = $variant->meta->pluck('meta_value', 'meta_key');
            }

            $this->meta->has_variant = true;
        }
        if ($this->meta->product_type == 'variableProduct') $this->meta->is_variant = true;
        $this->meta->variants = json_encode($list);
    }

    /**
     * Ex
     * @return array
     */
    public function extractVariantOptions()
    {
        $variants = $this->getVariants();

        $list = array();
        foreach ($variants as $key => $variant) {
            $list[$variant->ID] = $variant->meta()
                ->where('meta_key', 'like', 'attribute_pro_%')
                ->pluck('meta_value', 'meta_key')
                ->toArray();
        }
        foreach ($list as &$items) {
            Util::arrayRemoveString($items, 'index', 'attribute_pro_');
        }
        $this->extractAttributeSets($list);

        $this->meta->list = $list;
        $this->meta->list2 = json_encode($list);
    }

    /**
     * @param $list
     * @return array
     */
    public function extractAttributeSets(&$list)
    {
        /** To Validate Variant to Eliminate Duplicates */
        $this->validateVariant($list);
        $this->getVariantHtml($list);
    }

    /**
     * To Validate the Variants to Eliminate Duplicate Combinations and Return Valid Combination
     * by filter its occurrence
     *
     * @param $list
     * @return bool
     */
    public function validateVariant(&$list)
    {
        if (empty($list)) return false;
        $set = $list;

        /** To Generate Dummy Variant for Wildcard Options */
        $this->generateDummyVariants($set);
        if (!empty($set)) {
            /** Here, Eliminate the Duplicate Array items */
            $eliminate = Util::eliminateDuplicateArray($set);
            foreach ($eliminate as $id) {
                unset($list[$id]);
                unset($set[$id]);
            }
        }
        $list['sets'] = $set;
    }

    /**
     * For Replacing "Any" with it's Available Attributes to Generate Cartesian
     *
     * @param $set
     */
    public function generateDummyVariants(&$set)
    {
        /** "Wildcard" holds the list of option which are assigned as "any" */
        $wildcard = self::validateVariantWithWildcard($set);

        $attributes = $this->product_attributes;

        foreach ($wildcard as $index => $item) {
            $i = 0;
            /** Separate All Option to Individual Array */
            $set_list = array_chunk($set[$index], 1, true);

            /** Re-Assign the Child Index to Root's Index  */
            Util::redefinedArrayIndex($set_list);

            $list = array();
            foreach ($item as $key => $val) {
                if (isset($attributes[$val]['list'])) {
                    foreach ($attributes[$val]['list'] as $a_index => $a_item) {
                        $term = get_term($a_item);
                        /** Re-Indexing the Attribute List with Index of Taxonomy */
                        $list[$val][] = $term->slug;
                    }
                }
            }

            /** To Match the Wildcard with List */
            foreach ($list as $l_key => $l_item) {
                foreach ($set_list as $s_key => $s_item) {
                    if (isset($s_item[$l_key])) {
                        $set_list[$l_key] = $l_item;
                    }
                }
            }

            /** To Generate the Cartesian with Wildcard Options */
            $dummyVariant = Util::cartesian($set_list);

            /** To Re-Assign the Set with Unique ID */
            foreach ($dummyVariant as $d_key => $d_value) {
                $set[$index . '_' . $i++] = $d_value;
            }
            unset($set[$index]);
        }

    }

    /**
     * To Verify the combinations, and extract the wildcard as option named as "Any"
     *
     * @param $set
     * @return array
     */
    public static function validateVariantWithWildcard($set)
    {
        $wildcard = array();
        foreach ($set as $index => $item) {
            foreach ($item as $key => $value) {
                if ($value == 'any') {
                    $wildcard[$index][] = $key;
                }
            }
        }
        return $wildcard;
    }

    /**
     * To get HTML Output of Variant List
     *
     * @return array
     */
    public function getVariantHtml(&$list)
    {
        $attributes = $this->product_attributes;

        $html = [];
        if (empty($attributes)) return array();
        foreach ($attributes as $key => &$attribute) {
            $html['options'][] = $key;
            foreach ($attribute['list'] as $att_key => $item) {
                $index = $item;
                $slug = get_term($item)->slug;
                $html['items'][$key]['list'][$index] = $slug;
                unset($attributes[$key]['list'][$att_key]);
            }
        }
        $list['html'] = $html;
    }

    /**
     * To Verify and Generate Unique SKU.
     * [Not Yes Implemented]
     * Be Careful, it eats huge [TIME + PERFORMANCE]
     *
     * [Under Testing]
     *
     * @param $key
     * @return string
     */
    public static function verifySKU($key)
    {
        /** If "$sku" not exist. */
        $sku = PostMeta::where('meta_key', 'sku')->where('meta_value', $key)->get()->count();
        if (!$sku) return $key;

        /** If "$sku" is already available. */
        $num = 2;
        do {
            $alt_slug = $key . "-$num";
            $num++;
            $sku = PostMeta::where('meta_key', 'sku')->where('meta_value', $key)->get()->count();
            /** Loop Until get Unique "sku". */
        } while ($sku);
        return $alt_slug;
    }
}
