<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'item_name',
        'item_Barcode',
        'category_id',
        'item_description',
        'item_price',
        'item_expiry_date',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function branch()
    {
        return $this->hasMany(Branch::class);
    }

    public function transfer()
    {
        return $this->hasMany(Transfer::class);
    }

    public function branchStock()
    {
        return $this->hasMany(BranchStock::class);
    }

    




}
