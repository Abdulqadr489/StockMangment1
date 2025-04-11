<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transfer_id',
        'item_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];
    
    public function transfer()
    {
        return $this->belongsTo(Transfer::class, 'transfer_id');
    }
}
