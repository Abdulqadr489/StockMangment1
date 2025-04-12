<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try{
            DB::beginTransaction();

            $data= $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'password' => 'required',
                'confirm_password' => 'required|same:password',
            ]);

            $user=User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ]);
            DB::commit();
            return response()->json([
                'message' => 'User registered successfully',
                'data' => $data,
            ], 201);

        }catch(ErrorException $e)
        {
            return response()->json([
                'error' => 'An error occurred while processing your request.',
                'message' => $e->getMessage(),
            ], 500);

        }
    }

    public function login(Request $request)
    {
       $data=$request->only('email', 'password');

       if(Auth::attempt($data))
       {
        $user=Auth::user();
        $token=$user->createToken('AceesToken')->accessToken;
        return response()->json([
            'message' => 'User logged in successfully',
            'data' => $user,
            'token' => $token,
        ], 200);
       }else{
        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
       }

    }
}
