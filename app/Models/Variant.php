<?php

namespace CartRabbit\Models;

use Corcel\Post as Post;
use Illuminate\Database\Eloquent\Collection;
use CartRabbit\Helper\Currency;
use CartRabbit\Helper\Util;

/**
 * Class products
 * @package CartRabbit\Models
 */
class Variant extends Product
{

    /**
     * Product Id
     *
     * @var
     */
    protected $product_id;

    /**
     * @var array
     */
    protected static $product_attribute = array();

    /**
     * Cart Class constructor.
     *
     */
    public function __construct()
    {

    }

    public function meta()
    {
        return $this->hasMany('Corcel\PostMeta', 'post_id');
    }

    /**
     * To Set Product Id
     *
     * @param $id
     * @return int
     */
    public function setProductId($id)
    {
        $this->product_id = $id;
    }

    /**
     * To get Product Id
     *
     * @return mixed Product Id
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * @return array
     */
    public function getVariantProduct()
    {
        $product = array();
        if (!isset($this->product_id)) {
            $product = Post::find($this->product_id)->get();
            $this->processVariant($product);
        }
        return $product;
    }

    /**
     * @param $variants
     * @return array
     * [EXT]
     */
    public static function extractVariants($variants)
    {
        $list = array();
        foreach ($variants as $key => $variant) {
            $list[$variant['ID']] = $variant->meta()->where('meta_key', 'like', 'attribute_pro_%')->pluck('meta_value', 'meta_key');
        }
        foreach ($list as &$items) {
            Util::arrayRemoveString($items, 'index', 'attribute_pro_');
        }
        return $list;
    }

    /**
     * public function processVariant(&$parent_product, $isSingleProduct = false)
     *
     * @param $variant
     * @return mixed
     */
    public function processVariant($variant)
    {
//        $variant->meta->stock = 55;
//        dd($variant->meta->stock);
//        $variant->processProduct();
//        $variant->setAttributes($variant->getOriginal());
        //TODO Verify this Alternatives for getting Meta
        $out = $variant->getRelations()['meta']->pluck('meta_value', 'meta_key');
        $variant->setRelation('meta', $out);

        return $variant;
    }

    /**
     * @param $list
     * @return array
     * [EXT]
     */
    public static function extractAttributeSets($list)
    {
        /** To Validate Variant to Eliminate Duplicates */
        self::validateVariant($list);
        return $list['sets'];
    }

    /**
     * To Validate the Variants to Eliminate Duplicate Combinations and Return Valid Combination
     * by filter its occurrence
     *
     * @param $list
     * @return bool
     * [EXT]
     */
    public static function validateVariant(&$list)
    {
        if (empty($list)) return false;
        $set = array();

        foreach ($list as $index => $item) {
            foreach ($item->meta as $key => $value) {
                if (str_contains($key, 'attribute_pro_')) {
                    $key = str_replace('attribute_pro_', '', $key);
                    $set[$index][$key] = $value;
                }
            }
        }

        /** To Generate Dummy Variant for Wildcard Options */
        self::generateDummyVariants($set);

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
     * To Verify the combinations, and extract the wildcard as option named as "Any"
     *
     * @param $set
     * @return array
     * [EXT]
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
     * For Replacing "Any" with it's Available Attributes to Generate Cartesian
     * [EXT]
     * @param $set
     */
    public static function generateDummyVariants(&$set)
    {
        /** "Wildcard" holds the list of option which are assigned as "any" */
        $wildcard = self::validateVariantWithWildcard($set);

        $attributes = self::$product_attribute;
        foreach ($wildcard as $index => $item) {
            $i = 0;
            /** Separate All Option to Individual Array */
            $set_list = array_chunk($set[$index], 1, true);

            /** Re-Assign the Child Index to Root's Index  */
            Util::redefinedArrayIndex($set_list);
            $list = array();
            foreach ($item as $key => $val) {
                foreach ($attributes[$val]['list'] as $a_index => $a_item) {
                    $term = get_term($a_item);
                    /** Re-Indexing the Attribute List with Index of Taxonomy */
                    $list[$val][] = $term->slug;
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
                $set[$index . '_' . $i ++] = $d_value;
            }
            unset($set[$index]);
        }

    }

    /**
     * To get HTML Output of Variant List
     *
     * @param array $meta collection of Product Meta's
     * @return array
     * [EXT]
     */
    public static function getVariantHtml($meta)
    {
        $attributes = json_decode($meta->product_attributes, true);

        $list = array();
        if (empty($attributes)) return array();
        foreach ($attributes as $key => &$attribute) {
            $list['options'][] = $key;
            foreach ($attribute['list'] as $att_key => $item) {
                $index = $item;
                $slug = get_term($item)->slug;
                $list['items'][$key]['list'][$index] = $slug;
                unset($attributes[$key]['list'][$att_key]);
            }
        }
        return $list;
    }

    /**
     * @param $product
     */
    public function getChildren($product)
    {
        $product->meta->variants = new Collection();
        //load children products
        $variants = $product->variants()->get();
        $this->validate($variants);
        foreach ($variants as $variant) {
            $variant->processProduct();
        }
    }

    /**
     * @param $variants
     */
    public function validate(&$variants)
    {

    }
}