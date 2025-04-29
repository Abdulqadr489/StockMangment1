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
    ];
    protected $casts = [
        'transfer_date' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(TransferItem::class);
    }

    public function brunch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function transferItems()
    {
        return $this->hasMany(TransferItem::class, 'transfer_id', 'id');
    }

    public function brunchStock()
    {
        return $this->hasMany(BranchStock::class, 'branch_id', 'brunch_id');
    }
}
