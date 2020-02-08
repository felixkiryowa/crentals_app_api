<?php

namespace App\Http\Controllers;
use  App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;



class UserController extends Controller
{

    /*
     * Function to authenticate Users
     */
    public function  authenticate(Request $request) {
        $credentials = $request->only('mobilenoorEmail', 'password');
        try {
          if(!$token = JWTAuth::attempt($credentials))   {
             return response()->json(['error' => 'invalid_credentials'], 400);
          }
        }catch(JWTException $e) {
          return response()->json(['error' => 'could not create a token'], 500);
        }
        $generatedToken = [
            'auth_token' => 'Bearer '. $token
        ];

        return response()->json($generatedToken, 200);

    }


    /*
     * Function to register a user
     */
    public  function  register(Request $request) {
        $validator  = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'mobilenoorEmail'=>'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);
        $email = User::where('mobilenoorEmail', $request->get('mobilenoorEmail'))->first();
        if($email){
            return response()->json([
                 'success' => false,
                 'message' => 'Email already exists.'
            ], 400);
        }

        if($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'firstname' =>  ucfirst($request->get('firstname')),
            'lastname' => ucfirst($request->get('lastname')),
            'mobilenoorEmail'=> $request->get('mobilenoorEmail'),
            'password' => Hash::make($request->get('password')),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Sucessfully created an account please Log in'
        ], 201);
    }

    /*
     * Function to logout a user
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message'=>'Successfully logged out']);
    }

    /*
     * Function to get authenticated user
     */
    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        return response()->json(compact('user'));
    }

    /*
     * Function to login using facebook
     */

    public  function login_with_fb(Request $request) {
        $checkUser = User::where('provider_id', $request->get('provider_id'))->first();
        $name = $request->get('name');
        $separateString = explode(" ", $name);
        $firstname = $separateString[0];
        $lastname = $separateString[1];

        if($checkUser) {
            return response()->json(['success' => true, 'user' => $checkUser], 200);
        }else {
            $user = User::create([
                    'firstname'=> $firstname,
                    'lastname'=>$lastname,
                    'email'=>$request->get('email'),
                    'provider'=>strtoupper($request->get('provider')),
                    'provider_id'=> $request->get('provider_id')
                ]
            );
            return response()->json([
                'success' => true,
                'user' => $user
            ], 201);
        }
        // return response()->json(['res' => $request->get('email')]);
    }

}
