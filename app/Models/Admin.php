<?php

namespace CartRabbit\Models;

use CommerceGuys\Addressing\Repository\CountryRepository;
use Corcel\Post as Post;
use CartRabbit\Helper\MetaConverter;
use Illuminate\Database\Eloquent\Model as Eloquent;
use CartRabbit\Models\Price as SpecialPrice;
use CommerceGuys\Addressing\Repository\SubdivisionRepository;

/**
 * Class products
 * @package CartRabbit\Models
 */
class Admin extends Eloquent
{

    /**
     * Product Id
     * @var
     */
    protected $product_id;

    /**
     * Cart constructor.
     */
    public function __construct()
    {

    }

    /**
     * To Set Product Id
     * @param $id
     */
    public function setProductId($id)
    {
        $this->product_id = $id;
    }

    /**
     * To get Product Id
     * @return mixed Product Id
     */
    public function getProductId()
    {
        return $this->product_id;
    }


    /**
     * To Get Cart Configuration Datas
     * @return bool|\CartRabbit\Helper\Converted
     */

    public function getCartConfiguration()
    {
        $post_id = Post::where('post_type', 'cartrabbit_config')->pluck('ID')->first();
        if (is_null($post_id)) return false;
        $posts = Post::find($post_id)->meta()->select('meta_key', 'meta_value')->get();

        return (new MetaConverter())->keyValConverter($posts);
    }

    /**
     * To Remove Special Price by Id
     *
     * @param $id Special Price Id
     */
    public function removeSpecialPriceByID($id)
    {
        if (!is_null($id)) {
            SpecialPrice::where('id', $id)->delete();
        }
    }

}