<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchStock extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'item_id',
        'quantity',
        'branch_id',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function transfer()
    {
        return $this->hasMany(Transfer::class, 'brunch_id');
    }
}
