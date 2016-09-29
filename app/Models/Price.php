<?php

namespace CartRabbit\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class products
 * @package CartRabbit\Models
 */
class Price extends Eloquent
{
    protected $table = 'cartrabbit_price';

    protected $primaryKey = 'id';

    protected $fillable = [
        'post_id',
        'date_from',
        'date_to',
        'qty_from',
        'price'
    ];

    /**
     * Cart constructor.
     */
    public function __construct()
    {

    }

    /**
     * Product relationship
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product($price_ref = false)
    {
        if ($price_ref) {
            return $this->hasOne('Cartrabbit\Models\Product', 'post_id');
        }

        return $this->belongsTo('Cartrabbit\Models\Product');
    }

    /**
     * To Get All Special Prices
     * @return mixed
     */
    public function getAllSpecialPrice($product_id)
    {
        $specialprice = Price::where('post_id', $product_id)
            ->where('date_from', '>=', date('d-m-Y'))->get();
        return $specialprice;
    }

    /**
     * To Check Has Special Price Or Not
     * @return bool
     */
    public function hasSpecialPrice($id)
    {
        $specialprice = Price::where('post_id', $id)->get()->count();
        return ($specialprice == 0) ? false : true;
    }

    /**
     * To Get Special Price By Product Id and Qty
     * @param int $qty
     * @param $format 'True' to Calculate (Price * Qty) then Convert , else Only con Convert the Price
     * @return bool|float
     */
    public function getSpecialPriceByQty($id, $qty = 1, $format = false)
    {
        if (empty($id) or !isset($id)) return array();

        if (!$this->hasSpecialPrice($id)) return false;

        if ($qty == '') $qty = 1;
        $specialprice = Price::where('post_id', $id)
            ->where('qty_from', '<=', (int)$qty)
            ->where('date_from', '<=', Carbon::today()->format('Y-m-d'))
            ->where('date_to', '>=', Carbon::today()->format('Y-m-d'))
            ->orderBy('qty_from', 'desc')
            ->limit(1)
            ->pluck('price');

        return (count($specialprice) == 0) ? false : $specialprice;
    }

    public function scopeQuantity($query, $quantity)
    {
        return $query->where('qty_from', '<=', $quantity);
    }

    public function scopeDateFrom($query, $date_from)
    {
        return $query->where('date_from', '<=', $date_from);
    }

    public function scopeDateTo($query, $date_to)
    {

        return $query->where('date_to', '=>', $date_to)->orWhere('date_to', '=', '0000-00-00');
    }

}