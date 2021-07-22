<?php
if (!function_exists('pre')) {
    function pre($array)
    {
        echo "<pre>";
        print_r($array);
        echo "</pre>";
        die();
    }
}
if (!function_exists('is_json')) {
    function is_json($string, $return_data = false)
    {
        $data = json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
    }
}
if (!function_exists('check_inputs')) {
    function check_inputs()
    {
        //pre($this->input->post());
        $json = file_get_contents('php://input');
        if ($json) {
            $_POST =  ((array) json_decode($json));
            return $_POST;
        } else if ($_POST) {
            //echo'dddd'.pre($_POST);
            return $_POST;
        } else {
            //pre($_POST);
            echo json_encode(array("status" => false, "message" => "Invalid Input", "data" => array()));
        }
    }
}
if (!function_exists('return_api')) {
    function return_api($status = false, $message = "", $status_code = 200, $error = array(), $data = array())
    {
        echo json_encode(array("status" => $status, "message" => $message, "status_code" => $status_code, "error" => $error, "data" => $data));
        die;
    }
}
if (!function_exists('file_curl_contents')) {
    function file_curl_contents($document)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $document['file_url']);
        curl_setopt($ch, CURLOPT_POST, 1);
        unset($document['file_url']);
        //pre($document); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $document);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        //pre($server_output);
        curl_close($ch);
        $data = json_decode($server_output, true);
        return $data;
    }
}
if (!function_exists('object_to_array')) {
    function object_to_array($data)
    {
        if (is_array($data) || is_object($data)) {
            $result = array();
            foreach ($data as $key => $value) {
                $result[$key] = object_to_array($value);
            }
            return $result;
        }
        return $data;
    }
}
if (!function_exists('random_str')) {
    function random_str($length = 8, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        if ($max < 1) {
            throw new Exception('$keyspace must be at least two characters long');
        }
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }
}

use Illuminate\Support\Facades\DB;

if (!function_exists('convert_currency')) {
    function convert_currency($currency, $serviceCurrency, $amount) //$serviceCurrency i.e old Currency //$currency = new currency
    {
        if ($currency != $serviceCurrency) {
            $query = DB::table('currencies')->where('code', $serviceCurrency)->select('conversion')->get()->first();
            $serviceCurrencyData =  $query;
            if (!empty($serviceCurrencyData) && !empty($serviceCurrencyData->conversion)) {
                $serviceFee     = ceil($amount / $serviceCurrencyData->conversion);
            }
            /*------- ^ convert Currency to USD ---------------*/
            /*------- convert Currency to Customer Currency ---------------*/
            $query = DB::table('currencies')->where('code', $currency)->select('conversion')->get()->first();
            $conversionData =  $query;
            if (!empty($conversionData) && !empty($conversionData->conversion)) {
                $serviceFee     = ceil($serviceFee * $conversionData->conversion);
            }
            return $serviceFee;
        } else {
            return $amount;
        }
    }
}
if (!function_exists('getMngFee')) {
    function getMngFee($adult = 1, $child = 0, $serviceId, $currency = NULL)
    {
        $childC = $child;
        if ($child == 0) {
            $childC = 1;
        }
        $service = DB::table('mng_product')->join("mng_product_fee", 'mng_product_fee.id', '=', 'mng_product.activeFeeId')->where('mng_product.id', $serviceId)
            ->select("mng_product_fee.currency", "mng_product_fee.childServiceFee" . $childC, "mng_product_fee.adultServiceFee" . $adult)
            ->get()->first();
        $service = object_to_array($service);
        if ($child > 0) {
            $amount = $service['adultServiceFee' . $adult] + $service['childServiceFee' . $child];
        } else {
            $amount = $service['adultServiceFee' . $adult];
        }
        if (!empty($currency)) {
            $amount = convert_currency($currency, $service['currency'], $amount);
        }
        return $amount;
    }
}
if (!function_exists('getLoungeFee')) {
    function getLoungeFee($adult = 1, $child = 0, $serviceId, $service_hour, $currency = NULL) //$service_hour=requested service hour
    {
        $adult = $adult + $child;
        $service = DB::table('mng_product')->join("mng_product_fee", 'mng_product_fee.id', '=', 'mng_product.activeFeeId')->where('mng_product.id', $serviceId)
            ->select("mng_product_fee.currency", "mng_product_fee.adultServiceFee" . $adult,  "mng_product_fee.childServiceFee1", "mng_product_fee.currency", "mng_product.service_hours", "mng_product_fee.childAge", "mng_product.terminal")
            ->get()->first();
        $ser = object_to_array($service);
        $amount = $ser['adultServiceFee' . $adult];
        $extraAdultServiceFee = 0;
        if ($service_hour > $service->service_hours) {
            $extraHour                  = ($service_hour - $service->service_hours);
            $extra                      = ceil($extraHour / $service->childAge);
            $FServiceHour               = $service->service_hours + ($extra * $service->childAge);
            $extraAdultServiceFee       = ($adult * ($extra * $service->childServiceFee1));
        }
        $serviceFee = $amount + $extraAdultServiceFee;
        if (!empty($currency)) {
            $serviceFee = convert_currency($currency, $ser['currency'], $serviceFee);
        }
        return $serviceFee;
    }
}
if (!function_exists('generate_reference_id')) {
    function generate_reference_id()
    {
        $reference_id = DB::table('form_series')->where('country', 'travibble')->select('series')->get()->first();
        $reference_id = $reference_id->series;
        DB::table('form_series')->where('country', 'travibble')->update(['series' => $reference_id + 1]);
        return $reference_id;
    }
}
if (!function_exists('applicationFeeCreate')) {
    function applicationFeeCreate($reference_id, $serviceId, $feeId)
    {
        $serviceFee = DB::table('mng_product_fee')->where('id', $feeId)->get()->first();
        $feeData = array(
            'reference_id'        =>    $reference_id,
            'serviceId'            =>    $serviceId,
            'feeId'             =>  $serviceFee->id,
            'currency'            =>    $serviceFee->currency,
            'pricingType'       =>  $serviceFee->pricingType,
            'childAge'          =>  $serviceFee->childAge,
            'adultServiceFee1'    =>    $serviceFee->adultServiceFee1,
            'adultServiceFee2'    =>    $serviceFee->adultServiceFee2,
            'adultServiceFee3'    =>    $serviceFee->adultServiceFee3,
            'adultServiceFee4'    =>    $serviceFee->adultServiceFee4,
            'adultServiceFee5'    =>    $serviceFee->adultServiceFee5,
            'adultServiceFee6'    =>    $serviceFee->adultServiceFee6,
            'adultServiceFee7'    =>    $serviceFee->adultServiceFee7,
            'adultServiceFee8'    =>    $serviceFee->adultServiceFee8,
            'adultServiceFee9'    =>    $serviceFee->adultServiceFee9,
            'adultServiceFee10'    =>    $serviceFee->adultServiceFee10,
            'childServiceFee1'    =>    $serviceFee->childServiceFee1,
            'childServiceFee2'    =>    $serviceFee->childServiceFee2,
            'childServiceFee3'    =>    $serviceFee->childServiceFee3,
            'childServiceFee4'    =>    $serviceFee->childServiceFee4,
            'childServiceFee5'    =>    $serviceFee->childServiceFee5,
            'childServiceFee6'    =>    $serviceFee->childServiceFee6,
            'childServiceFee7'    =>    $serviceFee->childServiceFee7,
            'childServiceFee8'    =>    $serviceFee->childServiceFee8,
            'childServiceFee9'    =>    $serviceFee->childServiceFee9,
            'childServiceFee10'    =>    $serviceFee->childServiceFee10,
            'insertedDate'        =>    time(),
        );
        $status = DB::table('applications_mng_fee')->insert($feeData);
    }
}
