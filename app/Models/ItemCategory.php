<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemCategory extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'category_name',
        'created_by',
        'updated_by',
    ];
}
