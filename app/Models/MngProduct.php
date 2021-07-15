<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MngProduct extends Model
{
    use HasFactory;
    protected $table = 'mng_product';
    protected $primaryKey = "id";
}
