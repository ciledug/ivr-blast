<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\User;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view('account.index');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'name' => 'required|string|min:5|max:30',
            'username' => 'required|string|min:5|max:20',
            'email' => 'nullable|email|min:10|max:50',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $user = User::where('username', '=', Auth::user()->username)
            ->whereNull('deleted_at')
            ->first();

        if ($user) {
            $saveOk = true;
            $user->name = $request->name;

            if (strcmp($user->username, $request->username) != 0) {
                $checkUsername = User::where('username', '=', $request->username)
                    ->first();
                
                if (!$checkUsername) {
                    $user->username = $request->username;
                }
                else {
                    $validator->errors()->add('username', 'Duplicate username!');
                    $saveOk = false;
                }
            }

            if (strcmp($user->email, $request->email) != 0) {
                if (!empty($request->email)) {
                    $checkEmail = User::where('email', '=', $request->email)
                        ->first();

                    if (!$checkEmail) {
                        $user->email = $request->email;
                    }
                    else {
                        $validator->errors()->add('email', 'Duplicate email!');
                        $saveOk = false;
                    }
                }
                else {
                    $user->email = null;
                }
            }

            if ($saveOk) {
                $user->save();
                return back();
            }
            else {
                return back()->withErrors($validator)->withInput();
            }
        }
        else {
            return back()->withErrors($validator)->withInput();
        }
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'old_password' => 'required|string|min:6|max:15',
            'password' => 'required|string|min:6|max:15|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('username', '=', Auth::user()->username)
            ->whereNull('deleted_at')
            ->first();

        if ($user) {
            if (Hash::check($request->old_password, $user->password)) {
                $user->password = Hash::make($request->password);
                $user->save();
                return back();
            }
            else {
                $validator->errors()->add('old_password', 'Password incorrect!');
                return back()->withErrors($validator)->withInput();
            }
        }
        else {
            return back()->withInput();
        }
    }
}
