<?php

if (!function_exists('pre')) {

    function pre($array) {
        echo "<pre>";
        print_r($array);
        echo "</pre>";
    }

}
if (!function_exists('is_json')) {

    function is_json($string, $return_data = false) {
        $data = json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
    }

}

if (!function_exists('check_inputs')) {

    function check_inputs() {
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



if(!function_exists('return_api')){
    function return_api($status = false, $message="", $status_code=200, $error = array(), $data = array()){
        echo json_encode(array("status" => $status, "message" => $message, "status_code" => $status_code, "error" => $error, "data" => $data));
        die;
    }
}


if (!function_exists('file_curl_contents')) {

    function file_curl_contents($document) {
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

if(!function_exists('object_to_array')){
    function object_to_array($data)
    {
        if (is_array($data) || is_object($data))
        {
            $result = array();
            foreach ($data as $key => $value)
            {
                $result[$key] = object_to_array($value);
            }
            return $result;
        }
        return $data;
    }
}

if(!function_exists('random_str')){
    function random_str($length=8, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'){
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

