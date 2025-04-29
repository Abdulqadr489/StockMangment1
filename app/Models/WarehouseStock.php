<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseStock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_id',
        'quantity',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

}
