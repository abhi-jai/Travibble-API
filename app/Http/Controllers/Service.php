<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\getService;

class Service extends Controller
{
    //
    function meetAndGreet(Request $request)
    {
        $header =  request_header();
        $data = $request->all();
        $validator = Validator::make($data, [
            'airportId' => 'required',
            'service' => 'required',
        ]);
        if ($validator->fails()) {
            return_api(true, 'Validation Error', 200, $validator->errors(), []);
        }
        $service = getService::join('mng_product', 'mng_product.airportId', '=', 'airport.id')
                    ->join('mng_sub_service', 'mng_sub_service.id', '=', 'mng_product.subServiceId')
                    ->join('mng_product_fee', 'mng_product.activeFeeId', '=', 'mng_product_fee.id')
                    ->where('mng_product.categoryName', 'Meet and Greet')
                    ->where("mng_product.airportId", $request->airportId)
                    ->get();
        return_api(true, 'Meet and greet found at this airport', 200,[], $service);
    }
}
