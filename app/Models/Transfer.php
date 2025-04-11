<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transfer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'brunch_id',
        'transfer_date',
        'status',
    ];
    protected $casts = [
        'transfer_date' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(TransferItem::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'brunch_id');
    }

    public function transferItems()
    {
        return $this->hasMany(TransferItem::class, 'transfer_id', 'id');
    }
}
