<?php

namespace CartRabbit\Helper;

/**
 * For Managing Post Meta Operaions
 * Class MetaConverter
 * @package CartRabbit\Helper
 */
/**
 * Class MetaConverter
 * @package CartRabbit\Helper
 */
class MetaConverter{
	/**
     * To Convert the meta in default format to simplified array format
     *
     * @param $posts Bulk post meta's
     * @return $meta in array format
     */
    public function keyValConverter($posts){
        foreach($posts as $post){
            $meta[$post['meta_key']] = $post['meta_value'];
        }
        return $meta;
    }

    /**
     * To Convert the meta in JSON format to simplified array format
     *
     * @param $posts Bulk post meta's
     * @return $meta in JSON format
     */
    public function JSONkeyValConverter($posts){
        foreach($posts as $post){
            $metas[] = json_decode($post['meta_value'],true);
        }
        foreach($metas as $key=>$val){
            $meta[$key] = $val;
        }
        return $meta;
    }

    /**
     * To Converting Bulk Objects to Array
     *
     * @param $objects Bulk Objects
     * @return array Converted
     */
    public function ObjToArrayConverter($objects){

        foreach($objects as $object){
            $toArray[] = (array)$object;
        }
        return $toArray;
    }
}