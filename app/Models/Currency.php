<?php

namespace CartRabbit\Models;

use Corcel\Post as Post;
use CartRabbit\Helper\MetaConverter;

/**
 * Class products
 * @package CartRabbit\Models
 */
class Currency extends Post
{

    /**
     * To Return additional currencies, that are created by the admin
     *
     * @return Additional currencies converted meta's
     */
    public function getSecondaryCurrencyMeta()
    {
        $meta = '';
        $post_ids = Post::where('post_type', 'cartrabbit_currency')->get();
        foreach ($post_ids as $post_id) {
            $posts = Post::find($post_id['ID'])->meta()->select('meta_key', 'meta_value')->get();
            if (isset($posts[0])) {
                $meta[] = (new MetaConverter())->JSONkeyValConverter($posts);
            }
        }
        return (!is_array($meta)) ? array() : $meta[0];
    }
}