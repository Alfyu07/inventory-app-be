<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request){

       try {
            //validate
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required'
        ]);

        //insert new user
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        
        //ambil data yang telah di register
        $user = User::where('email', $request->email)->first();

        //create token
        $tokenResult = $user->createToken('authToken')->plainTextToken;

        return ResponseFormatter::success([
            'access_token' => $tokenResult,
            'token_type' => 'Bearer',
            'user' => $user
        ],'User Created');

       } catch (\Exception $error) {
           return ResponseFormatter::error([
               'message' => 'something went wrong', 
               'error' => $error
           ],'Authentication Failed', 500);
       }
    }

    public function login(Request $request){
        try{
            //validate
            $request->validate([
                'email' => 'required|email|string',
                'password'=> 'required'
            ]);

            $credentials = request(['email', 'password']);

            //cek login
            if(!Auth::attempt($credentials)){
                ResponseFormatter::error([
                    'message' => 'UnAuthorized'
                ],'Authentication Failed', 500);
            }

            //ambil user dan cocokkan dengan password
            $user = User::where('email', $request->email)->first();

            if(!Hash::check($request->password, $user->password)){
                throw new \Exception("Invalid Credentials"); 
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');


        }catch (\Exception $error) {
            ResponseFormatter::error([
                'message' => 'something went wrong'
            ],'Authentication Failed', 500);
        }
    }
}
