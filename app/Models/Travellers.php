<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Travellers extends Model
{
    use HasFactory;
    protected $table = 'application_details';
    protected $primaryKey = "id";
    const CREATED_AT = 'insertedTimeIST';
    const UPDATED_AT = 'insertedTimeIST';

    protected $hidden = [
        'insertedTimeIST', 'status', 'phoneCode', 'contactNo', 'serviceType', 'id', 'reference_id'
    ];
}
