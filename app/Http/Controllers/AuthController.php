<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
     $request->validate([
         'name' => 'required',
         'email' => 'required|email|unique:users',
         'password' => 'required|confirmed',
     ]);

     $data['password'] = Hash::make(request->input(key: 'password'));
     User::create($data);

        return response()->json([

            'statut' => 201,
            'data' => $user
        ]);
    }
    public function login(Request $request)
    {
        $data =  $request->validate([
            "email" => "required|email|",
            "password" => "required"
        ]);

        $token = JWTAuth::attempt($data);

        if(!empty($token))
        {
            return response()->json([
                'statut' => 200,
                "token" =>  $token
            ]);

        }else{
            return response()->json([
                "statut" => false,
                "token" =>  null
            ]);
        }
    }

    public function logout()
    {
        auth()->logout();
        return response()->json([
            'statut' => true,
            "message" =>  "user logout !"
        ]);
    }
}
