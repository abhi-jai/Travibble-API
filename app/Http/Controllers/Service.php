<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\getService;
use DB;

class Service extends Controller
{
    //
    function meetAndGreet(Request $request)
    {
        $header =  request_header();
        $user = User::where('id', $header['id'])->select('id', 'defaultCurrency as currency')->get()->first();
        $data = $request->all();
        $validator = Validator::make($data, [
            'airportId' => 'required',
            'service' => 'required',
            'travellers' => 'string'
        ]);
        if ($validator->fails()) {
            return_api(true, 'Validation Error', 200, $validator->errors(), []);
        }
        $travellers = array(
            'adult' => 1,
            'children' => 1,
            'infant' => 0
        );
        $childrenCount = 1;
        if (isset($request->travellers)) {
            $ex = explode(',', $request->travellers);
            if (count($ex) != 3) {
                return_api(false, 'Bad format travellers', 400);
            } else {
                $travellers = array(
                    'adult' => $ex[0],
                    'children' => $ex[1],
                    'infant' => $ex[2]
                );
                if ($travellers['children'] > 1) {
                    $childrenCount = $travellers['children'];
                }
            }
        }
        $service = getService::join('mng_product', 'mng_product.airportId', '=', 'airport.id')
            ->join('mng_sub_service', 'mng_sub_service.id', '=', 'mng_product.subServiceId')
            ->join('mng_product_fee', 'mng_product.activeFeeId', '=', 'mng_product_fee.id')
            ->where('mng_product.categoryName', 'Meet and Greet')
            ->where("mng_product.airportId", $request->airportId)
            ->where("mng_product_fee.isActive", 'Y')
            ->where("mng_product.subServiceId", $request->service)
            ->select("mng_product.id as serviceId", "mng_product.product_name", "mng_product.product_name", "mng_product.subServiceId", "mng_product.service_inclusion", "mng_product.product_image", "mng_product.categoryName", "mng_sub_service.name as service", "mng_product_fee.id as feeId", "mng_product_fee.adultServiceFee" . $travellers['adult'], "mng_product_fee.childServiceFee" . $childrenCount, "mng_product_fee.currency")
            ->get();

        $newArr = [];
        foreach ($service as $ser) {
            if ($travellers['children'] > 0) {
                $amount = $ser['adultServiceFee' . $travellers['adult']] + $ser['childServiceFee' . $travellers['children']];
            } else {
                $amount = $ser['adultServiceFee' . $travellers['adult']];
            }
            $ser['price'] = array(
                'amount' => convert_currency($user->currency, $ser->currency, $amount),
                'currency' => $user->currency
            );
            unset($ser->currency);
            unset($ser['adultServiceFee' . $travellers['adult']]);
            unset($ser['childServiceFee' . $travellers['children']]);
            unset($ser->feeId);
            $newArr[] = $ser;
        }

        if (!empty($newArr)) {
            return_api(true, 'Meet and greet found at this airport', 200, [], $newArr);
        } else {
            return_api(false, 'No meet and greet service found', 400, [], $newArr);
        }
    }

    function getServiceAirport(Request $request)
    {
        request_header();
        $data = $request->all();
        $validator = Validator::make($data, [
            'keyword' => 'required|string|min:3'
        ]);
        if ($request->keyword) {
            if ($validator->fails()) {
                return_api(true, 'Validation Error', 200, $validator->errors(), []);
            }
            $airportList = getService::where("Name", 'like', '%' . $request->keyword . '%')
                ->orWhere("City", 'like', '%' . $request->keyword . '%')
                ->orWhere("IATA_FAA", 'like', '%' . $request->keyword . '%')
                ->get();
        } else {
            $airportList = getService::limit(50)->get();
        }
        if (!$airportList->isEmpty()) {
            return_api(true, 'Service Airport List', 200, [], $airportList);
        } else {
            return_api(false, 'Service Airport List not found', 400, [], $airportList);
        }
    }
    function getAirportList(Request $request)
    {
        request_header();
        $data = $request->all();
        $validator = Validator::make($data, [
            'keyword' => 'required|string|min:3'
        ]);
        if ($request->keyword) {
            if ($validator->fails()) {
                return_api(true, 'Validation Error', 200, $validator->errors(), []);
            }
            $airportList = DB::table('airport_list')->where("Name", 'like', '%' . $request->keyword . '%')
                ->orWhere("City", 'like', '%' . $request->keyword . '%')
                ->orWhere("IATA_FAA", 'like', '%' . $request->keyword . '%')
                ->orWhere("Country", 'like', '%' . $request->keyword . '%')
                ->get();
        } else {
            $airportList = DB::table('airport_list')->limit(10)->get();
        }
        if (!$airportList->isEmpty()) {
            return_api(true, 'Airport List', 200, [], $airportList);
        } else {
            return_api(false, 'Airport List not found', 400, [], $airportList);
        }
    }

    function lounge(Request $request)
    {
        $header =  request_header();
        $user = User::where('id', $header['id'])->select('id', 'defaultCurrency as currency')->get()->first();
        // $user->currency = "USD";
        $data = $request->all();
        $validator = Validator::make($data, [
            'airportId' => 'required',
            'travellers' => 'string',
            'service_hour' => 'required'
        ]);
        if ($validator->fails()) {
            return_api(true, 'Validation Error', 200, $validator->errors(), []);
        }
        $travellers = array(
            'adult' => 1,
            'children' => 0,
            'infant' => 0
        );
        if (isset($request->travellers)) {
            $ex = explode(',', $request->travellers);
            if (count($ex) != 3) {
                return_api(false, 'Bad format travellers', 400);
            } else {
                $travellers = array(
                    'adult' => $ex[0],
                    'children' => $ex[1],
                    'infant' => $ex[2]
                );
            }
        }
        $adult = $travellers['adult'] + $travellers['children'];
        $service = getService::join('mng_product', 'mng_product.airportId', '=', 'airport.id')
            ->join('mng_sub_service', 'mng_sub_service.id', '=', 'mng_product.subServiceId')
            ->join('mng_product_fee', 'mng_product.activeFeeId', '=', 'mng_product_fee.id')
            ->where('mng_product.categoryName', 'Airport Lounge')
            ->where("mng_product.airportId", $request->airportId)
            ->where("mng_product_fee.isActive", 'Y')
            ->where("mng_product.isActive", 'Y')
            ->select("mng_product.id as serviceId", "mng_product.product_name", "mng_product.product_name", "mng_product.subServiceId", "mng_product.service_inclusion", "mng_product.product_image", "mng_product.categoryName", "mng_sub_service.name as service", "mng_product_fee.id as feeId", "mng_product_fee.adultServiceFee" . $travellers['adult'], "mng_product_fee.childServiceFee1", "mng_product_fee.currency", "mng_product.service_hours", "mng_product_fee.childAge", "mng_product.terminal")
            ->get();
        $newArr = [];
        foreach ($service as $ser) {

            $amount = $ser['adultServiceFee' . $travellers['adult']];
            $extraAdultServiceFee = 0;
            if ($request->service_hour > $ser->service_hours) {
                $extraHour                  = ($request->service_hour - $ser->service_hours);
                $extra                      = ceil($extraHour / $ser->childAge);
                $FServiceHour               = $ser->service_hours + ($extra * $ser->childAge);
                $extraAdultServiceFee       = ($adult * ($extra * $ser->childServiceFee1));
            }
            $serviceFee = $amount + $extraAdultServiceFee;

            $ser['price'] = array(
                'amount' => convert_currency($user->currency, $ser->currency, $serviceFee),
                'currency' => $user->currency
            );
            unset($ser->currency);
            unset($ser['adultServiceFee' . $travellers['adult']]);
            unset($ser['childServiceFee' . $travellers['children']]);
            unset($ser->feeId);
            unset($ser->childAge);
            $ser->service_hours = $request->service_hour;
            $newArr[] = $ser;
        }

        if (!empty($newArr)) {
            return_api(true, 'Airport lounge at this airport', 200, [], $newArr);
        } else {
            return_api(false, 'No Airport lounge service found', 400, [], $newArr);
        }
    }
}
