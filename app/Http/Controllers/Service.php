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
            ->select("mng_product.id as serviceId", "mng_product.product_name", "mng_product.product_name", "mng_product.subServiceId", "mng_product.service_inclusion", "mng_product.product_image", "mng_product.categoryName", "mng_sub_service.name as service", "mng_product_fee.id as feeId", "mng_product_fee.adultServiceFee" . $travellers['adult'], "mng_product_fee.childServiceFee" . $childrenCount, "mng_product_fee.currency", "airport.Name as airportName", "airport.IATA_FAA as airportIATA_FAA")
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
            $new_inc = [];
            $inlcusions = DB::table('service_inclusion')->whereIn('id', explode(',', $ser['service_inclusion']))->select('id', 'name', 'icon', 'alt')->get();
            foreach ($inlcusions as $inc) {
                $inc->icon = "https://travibble.com/uploads/service_inclusion/" . $inc->icon;
                $new_inc[] = $inc;
            }
            $ser['service_inclusion'] = $new_inc;
            $ser['product_image'] = "https://travibble.com/uploads/product_image/" . $ser['product_image'];
            unset($ser->currency);
            unset($ser['adultServiceFee' . $travellers['adult']]);
            unset($ser['childServiceFee' . $travellers['children']]);
            unset($ser->feeId);
            $newArr[] = $ser;
        }
        $searchAirport = getService::select('id', 'Name', 'City', 'IATA_FAA', 'description', 'mtitle', 'mdesc')->find($request->airportId);
        $subService = DB::table('mng_sub_service')->where('id', $request->service)->select('id', 'name')->get()->first();
        $searchAirport->subService = $subService;
        $data = array(
            'serviceAirport' => $searchAirport,
            'result' => $newArr
        );
        if (!empty($newArr)) {
            return_api(true, 'Meet and greet found at this airport', 200, [], $data);
        } else {
            return_api(false, 'No meet and greet service found', 400, [], []);
        }
    }
    function meetAndGreetDetail($serviceId)
    {
        $header =  request_header();
        $ser = getService::join('mng_product', 'mng_product.airportId', '=', 'airport.id')
            ->join('mng_sub_service', 'mng_sub_service.id', '=', 'mng_product.subServiceId')
            ->join('mng_product_fee', 'mng_product.activeFeeId', '=', 'mng_product_fee.id')
            ->where("mng_product_fee.isActive", 'Y')
            ->where("mng_product.id", $serviceId)
            ->select("mng_product.id as serviceId", "mng_product.product_name", "mng_product.product_name", "mng_product.subServiceId", "mng_product.service_inclusion", "mng_product.product_image", "mng_product.categoryName", "mng_sub_service.name as service", "mng_product_fee.id as feeId", "airport.Name as airportName", "airport.IATA_FAA as airportIATA_FAA")
            ->get()->first();
        $new_inc = [];
        $inlcusions = DB::table('service_inclusion')->whereIn('id', explode(',', $ser['service_inclusion']))->select('id', 'name', 'icon', 'alt')->get();
        foreach ($inlcusions as $inc) {
            $inc->icon = "https://travibble.com/uploads/service_inclusion/" . $inc->icon;
            $new_inc[] = $inc;
        }
        $ser['service_inclusion'] = $new_inc;
        $ser['product_image'] = "https://travibble.com/uploads/product_image/" . $ser['product_image'];
        $newArr = $ser;
        if (!empty($newArr)) {
            return_api(true, 'Meet and greet found at this service id', 200, [], $newArr);
        } else {
            return_api(false, 'No meet and greet service found', 400, [], []);
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
            $airportList = getService::join('country', 'country.id', '=', 'airport.Country')
                ->where("airport.Name", 'like', '%' . $request->keyword . '%')
                ->orWhere("airport.City", 'like', '%' . $request->keyword . '%')
                ->orWhere("airport.IATA_FAA", 'like', '%' . $request->keyword . '%')
                ->select("airport.id", "airport.Name", "airport.City", "country.name as Country", "airport.IATA_FAA")
                ->get();
        } else {
            $airportList = getService::join('country', 'country.id', '=', 'airport.Country')->select("airport.id", "airport.Name", "airport.City", "country.name as Country", "airport.IATA_FAA")->limit(50)->get();
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
    function getCountryList(Request $request)
    {
        request_header();
        $countryList = DB::table('country')->get();
        if (!$countryList->isEmpty()) {
            return_api(true, 'Country List', 200, [], $countryList);
        } else {
            return_api(false, 'Country List not found', 400, [], $countryList);
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
            ->select("mng_product.id as serviceId", "mng_product.product_name", "mng_product.product_name", "mng_product.subServiceId", "mng_product.service_inclusion", "mng_product.product_image", "mng_product.categoryName", "mng_product_fee.id as feeId", "mng_product_fee.adultServiceFee" . $adult, "mng_product_fee.childServiceFee1", "mng_product_fee.currency", "mng_product.service_hours", "mng_product_fee.childAge", "mng_product.terminal")
            ->get();
        $newArr = [];
        foreach ($service as $ser) {
            $amount = $ser['adultServiceFee' . $adult];
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
            unset($ser['childServiceFee1']);
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
    function rtpcr(Request $request)
    {
        $header =  request_header();
        $user = User::where('id', $header['id'])->select('id', 'defaultCurrency as currency')->get()->first();
        // $user->currency = "USD";
        $data = $request->all();
        $validator = Validator::make($data, [
            'airportId1' => 'integer',
            'airportId2' => 'integer',
            'travellers' => 'string'
        ]);
        if ($validator->fails()) {
            return_api(true, 'Validation Error', 200, $validator->errors(), []);
        }
        if (!isset($request->airportId1) && empty($request->airportId1)) {
            $request->airportId1 = 78;
        }
        $travellers = 0;
        if (!isset($request->travellers)) {
            $travellers = 1;
        }
        $airportId = array($request->airportId1, $request->airportId2);
        $service = DB::table('mng_product')
            ->join('country', 'mng_product.airportId', '=', 'country.id')
            ->join('mng_sub_service', 'mng_sub_service.id', '=', 'mng_product.subServiceId')
            ->join('mng_product_fee', 'mng_product.activeFeeId', '=', 'mng_product_fee.id')
            ->where('mng_product.categoryName', 'Rtpcr')
            ->whereIn("mng_product.airportId", $airportId)
            ->where("mng_product_fee.isActive", 'Y')
            ->where("mng_product.isActive", 'Y')
            ->select("mng_product.id as serviceId", "mng_product.product_name", "mng_product.product_name", "mng_product.subServiceId", "mng_product.service_inclusion", "mng_product.categoryName", "mng_product_fee.id as feeId", "mng_product_fee.adultServiceFee1", "mng_product_fee.childServiceFee1", "mng_product_fee.currency", "mng_product.service_hours", "mng_product_fee.childAge", "mng_product.terminal")
            ->get();
        $newArr = [];
        foreach ($service as $ser) {
            $serviceFee = (int)$ser->adultServiceFee1 * (int)$travellers;
            $ser->price = array(
                'amount' => convert_currency($user->currency, $ser->currency, $serviceFee),
                'currency' => $user->currency
            );
            unset($ser->currency);
            unset($ser->adultServiceFee1);
            unset($ser->childServiceFee1);
            unset($ser->feeId);
            unset($ser->childAge);
            $newArr[] = $ser;
        }
        if (!empty($newArr)) {
            return_api(true, 'Rtpcr at this airport', 200, [], $newArr);
        } else {
            return_api(false, 'No Rtpcr service found', 400, [], $newArr);
        }
    }
}
