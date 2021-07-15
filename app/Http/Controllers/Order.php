<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\MngProduct;
use Illuminate\Support\Facades\DB;
use App\Models\OrderDetail;
use App\Models\Travellers;

class Order extends Controller
{
    function meetAndGreet(Request $request)
    {
        $status = false;
        $uPstatus = false;
        $header = request_header();
        $user = User::where('id', $header['id'])->select('id', 'createdBy', 'defaultCurrency as currency')->get()->first();
        $createdBy = $user->createdBy;
        $data = $request->all();
        $validator = Validator::make($data, [
            "serviceId" => 'required',
            "booker_detail" => 'required|array',
            "booker_detail.name" => 'required',
            "booker_detail.email" => 'required',
            "booker_detail.country_code" => 'required',
            "booker_detail.mobile" => 'required',
            'passenger_detail' => 'required|array',
            "adult_count" => "required",
            "infant_count" => "required",
            "child_count" => "required"
        ]);
        if ($validator->fails()) {
            return_api(true, 'Validation Error', 200, $validator->errors(), []);
        }
        $serviceDetail = MngProduct::where('id', $request->serviceId)->select('id', 'airportId', 'product_name', 'subServiceId')->get()->first();
        if (empty($request->reference_id)) {
            $reference_id = generate_reference_id();
        } else {
            $reference_id = $request->reference_id;
        }
        $applications['reference_id'] = $reference_id;
        $applications['createdBy']             = $createdBy;
        $applications['applName']             = trim($data['booker_detail']['name']);
        $applications['emailId']             = trim($data['booker_detail']['email']);
        $applications['locationId']         = trim($data['booker_detail']['country_code']);
        $applications['contactNo']             = trim($data['booker_detail']['mobile']);
        $applications['product_name']         = $serviceDetail->product_name;
        $applications['serviceAirportId']     = $serviceDetail->airportId;
        $applications['serviceId']             = $serviceDetail->id;
        $applications['subServiceId']          = $serviceDetail->subServiceId;
        $applications['adults']             = trim($data['adult_count']);
        $applications['children']             = trim($data['child_count']);
        $applications['infants']             = trim($data['infant_count']);
        if ($serviceDetail->subServiceId == 1 || $serviceDetail->subServiceId == 3) {
            if (!empty($data['arrival_detail'])) {
                $applications['airportId1']         = trim($data['arrival_detail']['airportId']);
                $applications['serviceDate1']         = date('Y-m-d', strtotime($data['arrival_detail']['date']));
                $applications['flightNo1']             = $data['arrival_detail']['flight_no'];
                $applications['flightTime1']         = $data['arrival_detail']['flight_time'];
            } else {
                return_api(false, 'Arrival flight detail is not found!', 400);
            }
        } elseif ($serviceDetail->subServiceId == 2) {
            if (!empty($data['departure_detail'])) {
                $applications['airportId1']         = trim($data['departure_detail']['airportId']);
                $applications['serviceDate1']         = date('Y-m-d', strtotime($data['departure_detail']['date']));
                $applications['flightNo1']             = $data['departure_detail']['flight_no'];
                $applications['flightTime1']         = $data['departure_detail']['flight_time'];
            } else {
                return_api(false, 'Departure flight detail is not found!', 400);
            }
        } elseif ($serviceDetail->subServiceId == 3) {
            if (!empty($data['departure_detail'])) {
                $applications['airportId2']         = trim($data['departure_detail']['airportId']);
                $applications['serviceDate2']         = date('Y-m-d', strtotime($data['departure_detail']['date']));
                $applications['flightNo2']             = $data['departure_detail']['flight_no'];
                $applications['flightTime2']         = $data['departure_detail']['flight_time'];
            } else {
                return_api(false, 'Departure flight detail is not found!', 400);
            }
        }
        $applications['status']                = 'INCOMPLETE';
        $applications['insertedDate']          = time();
        $applications['applicationType']       = 'TRAVIBBLE-API';
        $applications['cancel_status'] = 0;
        $applications['pay_amount'] = getMngFee($applications['adults'], $applications['children'], $serviceDetail->id, $user->currency);
        $applications['currency'] = $user->currency;
        if (empty($request->reference_id)) {
            $status = OrderDetail::insert($applications);
        } else {
            DB::table('application_details')->where('reference_id', $reference_id)->delete();
            $uPstatus = OrderDetail::where('reference_id', $reference_id)->update($applications);
        }
        if (!empty($data['passenger_detail']) && !empty($data['passenger_detail']['email'])) {
            $passInsData = array(
                'locationId'     => trim($data['passenger_detail']['country_code']),
                'contact'         => trim($data['passenger_detail']['mobile']),
                'email_id'         => trim($data['passenger_detail']['email']),
            );
            $passInsData['reference_id'] = $applications['reference_id'];
            if (empty($request->reference_id)) {
                DB::table('application_additional_contact')->insert($passInsData);
            } else {
                DB::table('application_additional_contact')->where('reference_id', $reference_id)->update($passInsData);
            }
        }
        if (count($data['adult_detail']) == $data['adult_count']) {
            $adults = [];
            $applicationsDetail = [];
            foreach ($data['adult_detail'] as $ad) {
                $applicationsDetail['reference_id']     = $reference_id;
                $applicationsDetail['appl_title']         = $ad['title'];
                $applicationsDetail['appl_name']         = $ad['name'];
                $applicationsDetail['nationality_id']     = $ad['nationality'];
                $applicationsDetail['flight_class']     = $ad['class'];
                $applicationsDetail['appl_type']         = 'adult';
                array_push($adults, $applicationsDetail);
            }
            DB::table('application_details')->insert($adults);
        } else {
            return_api(false, 'Number of adult passenger not match to no. of detail shared', 401);
        }
        if (count($data['child_detail']) == $data['child_count']) {
            $child = [];
            $applicationsDetail = [];
            foreach ($data['child_detail'] as $ad) {
                $applicationsDetail['reference_id']     = $reference_id;
                $applicationsDetail['appl_title']         = $ad['title'];
                $applicationsDetail['appl_name']         = $ad['name'];
                $applicationsDetail['nationality_id']     = $ad['nationality'];
                $applicationsDetail['flight_class']     = $ad['class'];
                $applicationsDetail['appl_type']         = 'child';
                array_push($child, $applicationsDetail);
            }
            DB::table('application_details')->insert($child);
        } else {
            return_api(false, 'Number of Children passenger not match to no. of children detail shared', 401);
        }
        if (count($data['infant_detail']) == $data['infant_count']) {
            $infant = [];
            $applicationsDetail = [];
            foreach ($data['infant_detail'] as $ad) {
                $applicationsDetail['reference_id']     = $reference_id;
                $applicationsDetail['appl_title']         = $ad['title'];
                $applicationsDetail['appl_name']         = $ad['name'];
                $applicationsDetail['nationality_id']     = $ad['nationality'];
                $applicationsDetail['flight_class']     = $ad['class'];
                $applicationsDetail['appl_type']         = 'infant';
                array_push($infant, $applicationsDetail);
            }
            DB::table('application_details')->insert($infant);
        } else {
            return_api(false, 'Number of infant passenger not match to no. of infant detail shared', 401);
        }
        if ($status) {
            $response = array(
                'reference_id' => $reference_id,
                'pay_amount' => $applications['pay_amount'],
                'currency' => $applications['currency']
            );
            return_api(true, 'Meet and greet order is successfully created!', 200, [], $response);
        } elseif ($uPstatus) {
            $response = array(
                'reference_id' => $reference_id,
                'pay_amount' => $applications['pay_amount'],
                'currency' => $applications['currency']
            );
            return_api(true, 'Order is updated successfully created!', 200, [], $response);
        } else {
            return_api(false, 'Error while creating order please try again', 401);
        }
    }

    function getMeetGreetOrder($reference_id)
    {
        request_header();
        if (!isset($reference_id) && empty($reference_id)) {
            return_api(false, '`reference_id` is not found!', 400);
        }
        $orderDetail = OrderDetail::where('reference_id', $reference_id)->first();
        $bookerDetail = DB::table('application_additional_contact')->where('reference_id', $reference_id)->select('locationId', 'contact', 'email_id')->first();
        $travellers = Travellers::where('reference_id', $reference_id)->get();
        $meetOrder = [];
        $meetOrder['reference_id'] = $orderDetail->reference_id;
        $meetOrder['serviceId'] = $orderDetail->serviceId;
        /*prepare booker detail*/
        $meetOrder['booker_detail'] = array(
            'name' => $orderDetail->applName,
            'country_code' => $orderDetail->locationId,
            'mobile' => $orderDetail->contactNo,
            'email' => $orderDetail->emailId
        );
        /*end prepare booker detail*/
        /* prepare passenger detail*/
        $meetOrder['passenger_detail'] = array(
            'country_code' => $bookerDetail->locationId,
            'mobile' => $bookerDetail->contact,
            'email' => $bookerDetail->email_id
        );
        if ($orderDetail->subServiceId == 1 || $orderDetail->subServiceId == 3) {
            $arrival['airportId'] =  $orderDetail->airportId1;
            $arrival['date'] = $orderDetail->serviceDate1;
            $arrival['flight_no'] = $orderDetail->flightNo1;
            $arrival['flight_time'] = $orderDetail->flightTime1;
            $meetOrder['arrival_detail'] = $arrival;
        } elseif ($orderDetail->subServiceId == 2) {
            $departure['airportId'] = $orderDetail->airportId1;
            $departure['date'] = $orderDetail->serviceDate1;
            $departure['flight_no'] = $orderDetail->flightNo1;
            $departure['flight_time'] = $orderDetail->flightTime1;
            $meetOrder['departure_detail'] = $departure;
        } elseif ($orderDetail->subServiceId == 3) {
            $arrival['airportId'] =  $orderDetail->airportId2;
            $arrival['date'] =  $orderDetail->serviceDate2;
            $arrival['flight_no'] =  $orderDetail->flightNo2;
            $arrival['flight_time'] =  $orderDetail->flightTime2;
            $meetOrder['departure_detail'] = $arrival;
        }
        /* end prepare passenger detail*/
        $meetOrder['adult_count'] = $orderDetail->adults;
        $meetOrder['child_count'] = $orderDetail->children;
        $meetOrder['infant_count'] = $orderDetail->infants;
        /*prepare travellers*/
        foreach ($travellers as $trv) {
            if ($trv->appl_type == "adult") {
                $meetOrder['adult_detail'][] = array(
                    'title' => $trv->appl_title,
                    'name' => $trv->appl_name,
                    'nationality' => $trv->nationality_id,
                    'flight_class' => $trv->flight_class
                );
            } elseif ($trv->appl_type == "child") {
                $meetOrder['child_detail'][] = array(
                    'title' => $trv->appl_title,
                    'name' => $trv->appl_name,
                    'nationality' => $trv->nationality_id,
                    'flight_class' => $trv->flight_class
                );
            } elseif ($trv->appl_type == "infant") {
                $meetOrder['infant_detail'][] = array(
                    'title' => $trv->appl_title,
                    'name' => $trv->appl_name,
                    'nationality' => $trv->nationality_id,
                    'flight_class' => $trv->flight_class
                );
            }
        }
        if(!empty($meetOrder)){
            return_api(true, 'Order detail found!', 200, [], $meetOrder);
        }else{
            return_api(false, 'Order detail not found!', 401);
        }        
    }
}
