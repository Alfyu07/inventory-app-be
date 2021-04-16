<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use HasFactory, SoftDeletes;
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
        try {
            //proses validasi inputan request
            $request->validate([
                'email' => ['required'],
                'password' => 'required'
            ]);

            //mengecek credentials (login)
            $credentials = request(['email', 'password']);

            //jika login gagal  
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Invalid email or password',
                ], 'Authentication Failed', 400);
            }

            //jika gagal tidak sesuai maka beri error
            $user = User::where('email', $request->email)->first();

            if (!Hash::check($request->password, $user->password)) {
                throw new \Exception('Invalid Credentials');
            }

            //jika berhasil maka loginkan
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Authentication Failed', 500);
        }
    }

    //fungsi untuk Logout
    public function logout(Request $request){
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, 'Token Revoked');
    }

    public function updateProfile(Request $request){
        $data = $request->all();
        $user = Auth::user();

        $user->update($data);

        return ResponseFormatter::success($user, 'User profile updated');
    }
    
    public function fetch(Request $request){
        return ResponseFormatter::success($request->user(), 'Data user berhasil diambil');
    }
}
