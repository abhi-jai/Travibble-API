<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;
    protected $table = 'applications';
    protected $primaryKey = "id";
    const CREATED_AT = 'insertedDate';
    const UPDATED_AT = 'insertedDate';
    protected $fillable = [
        'reference_id'
    ];
    protected $hidden = [
        'terminal','transport','city','prd_service_hour','cancel_status','applicationType','createdBy','flag',
        'paymentStatus', 'paymentDate', 'currencyConversion1', 'currencyConversion2', 'id'
    ];
}
