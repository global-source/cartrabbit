<?php

namespace CartRabbit\Controllers\Test;

use CommerceGuys\Addressing\Formatter\DefaultFormatter;
use CommerceGuys\Addressing\Model\Address;
use CommerceGuys\Addressing\Repository\AddressFormatRepository;
use CommerceGuys\Addressing\Repository\SubdivisionRepository;
use CommerceGuys\Addressing\Repository\CountryRepository;

use Flycartinc\Cart\Cart;
use Flycartinc\Order\Model\Order;
use Flycartinc\Order\Model\OrderItemMeta;
use Herbert\Framework\Http;
use CartRabbit\Controllers\BaseController;
use CartRabbit\Helper;
use Illuminate\Contracts\Encryption;
use CartRabbit\Models\Customer;
use CartRabbit\Models\Product;
use CartRabbit\Models\ProductBase;


/**
 * Class TestController [COMPLETELY TESTING PURPOSE '0%' Dependencies]
 * @package CartRabbit\Controllers
 */
class TestController extends BaseController
{
    public static $items = array();

    public function test(Http $http)
    {
        dd(is_object(get_user_by('login', 'admin1')));
        dd(Session()->all());
        dd(Session()->remove('uaccount'));
        $product = Product::init(592);
        $product->processProduct();
        dd($product);
    }

    public function snippets()
    {
        /** For Getting Prodcut by Its Category */
        dd(Product::taxonomy('genre', 'digital')->get());

        /** Paginations */
        $page = new \CartRabbit\library\Pagination();

        $page->setTotal(100);
        $page->setLimit(10);

        $paginate = $page->generatePagination();
        //        dd(DB::table('posts')->paginate(5));
    }

    /** JUST Rough Work */
    public function rough()
    {
        $post = Product::join('postmeta', 'posts.ID', '=', 'postmeta.post_id')
            ->where('postmeta.meta_key', 'visible_on_storefront')
            ->where('postmeta.meta_value', 'yes')
            ->where('post_title', '!=', 'Auto Draft')
            ->pluck('posts.id');

        $posts = ProductBase::join('postmeta', 'posts.ID', '=', 'postmeta.post_id')
            ->where('postmeta.meta_key', 'visible_on_storefront')
            ->where('postmeta.meta_value', 'yes')
            ->orWhere('posts.post_parent', '!=', 0)
            ->where('posts.post_title', '!=', 'Auto Draft')->get();

    }

}
