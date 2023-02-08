<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\UserLog;
use Carbon\Carbon;

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
    // protected $redirectTo = '/home';
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($request->input(), [
            'username' => 'required|string|min:5|max:20',
            'password' => 'required|string|min:6|max:15'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $validator->errors()->add('login_invalid', 'Username or Password is incorrect!');
        $fieldType = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (auth()->attempt(array(
            $fieldType => $input['username'],
            'password' => $input['password']
        ))) {
            UserLog::create([
                'user_id' => Auth::user()->id,
                'last_login' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                'last_ip_address' => $request->ip(),
            ]);
            return redirect()->route('dashboard');
        }
        else {
            return back()->withErrors($validator)->withInput();
        }
    }
}
