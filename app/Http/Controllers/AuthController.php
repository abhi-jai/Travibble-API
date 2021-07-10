<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use JWT;

class AuthController extends Controller
{
    //
    public function Login(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'username' => 'required|email',
            'password' => 'required|alphaNum',
        ]);
        if ($validator->fails()) {
            return response(['error' => $validator->errors(), 'Validation Error']);
        }
        $user = User::where('username', $request->username)->where('password', $request->password)->first();
        if($user){
            $payload_arr = array(
                'id' => $user->id,
                'username' => $user->username,
                'time' => time()
            );
            $payload = json_encode($payload_arr);
            $jwt = new JWT();
            $jd = $jwt->encode($payload);
            $ret = array('token' => $jd);
            User::where('id',$user->id)->update(array('token' => $jd));
            return_api(true, 'Login Success!', 200, [], $ret);
        }else{
            return_api(false, 'Login Failure! Invalid credential.', 200);
        }
    }
}
