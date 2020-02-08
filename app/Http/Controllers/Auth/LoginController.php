<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;
use Illuminate\Http\Request;
use Auth;
use App\User;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

     /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return Response
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return Response
     */
    public function handleProviderCallback($provider)
    {
        $user = Socialite::driver($provider)->stateless()->user();
        $authUser = $this->findOrCreateUser($user, $provider);
        // print_r($authUser);

        // Auth::login($authUser, true);
        // return $user->token;
        // return redirect($this->redirectTo);
    }

    public function findOrCreateUser($user, $provider) {
       $authUser = User::where('provider_id', $user->id)->first();
       $token = JWTAuth::fromUser($authUser);
       $generatedToken = [
        'auth_token' => 'Bearer '. $token
       ];

       $usernames = $user->name;
       $separateString = explode(" ", $usernames);
       $firstname = $separateString[0];
       $lastname = $separateString[1];
       if($authUser) {
        //    return $authUser;
        // return response()->json(compact('userToken'));
        return response()->json($generatedToken, 200);
       }
       return User::create([
        'firstname'=> $firstname,
        'lastname'=>$lastname,
        'mobilenoorEmail'=>$user->email,
        'provider'=>strtoupper($provider),
        'provider_id'=>$user->id
         ]
       );

    }
}
