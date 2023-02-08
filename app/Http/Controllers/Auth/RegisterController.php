<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\User;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    // protected $redirectTo = '/home';
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('guest');
        $this->middleware('auth');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // return Validator::make($data, [
        //     'name' => 'required|string|max:50',
        //     'email' => 'required|string|email|max:50|unique:users',
        //     'password' => 'required|string|min:6|confirmed',
        // ]);

        return Validator::make($data, [
            'name' => 'required|string|min:5|max:50',
            'username' => 'required|string|min:5|max:15|unique:users,username',
            'email' => 'nullable|email|min:10|max:50|unique:users,email',
            'password' => 'required|string|min:6|max:15|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $returnedResponse = array(
            'code' => 500,
            'message' => 'Server Error',
            'count' => 0,
            'data' => [],
        );

        $user = User::create([
            'name' => $data['name'],
            'email' => !empty($data['email']) ? $data['email'] : null,
            'username' => $data['username'],
            'password' => bcrypt($data['password']),
        ]);
        
        return response()->json($returnedResponse);
    }
}
