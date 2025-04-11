<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'branch_name',
        'location',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

   
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
