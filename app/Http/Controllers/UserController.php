<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\User;
use App\UserLog;

class UserController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }
    
    public function index()
    {
        $users = User::select([
                'users.id', 'name', 'username', 'email',
            ])
            ->selectRaw('
                (SELECT u2.name FROM users u2 WHERE u2.id = users.added_by) AS added_by,
                (SELECT user_logs.last_login FROM user_logs WHERE user_logs.user_id = users.id ORDER BY user_logs.id DESC LIMIT 1) AS last_login,
                (SELECT user_logs.last_ip_address FROM user_logs WHERE user_logs.user_id = users.id ORDER BY user_logs.id DESC LIMIT 1) AS last_ip_address
            ')
            ->whereNotIn('users.id', [Auth::user()->id])
            ->orderBy('users.name', 'ASC')
            ->groupBy('users.id');

        if (Auth::user()->username !== 'sadmin') {
            $users->whereNotIn('users.username', ['sadmin']);
        }

        $users = $users->paginate(15);
        $rowNumber = $users->firstItem();

        return view('user.index', [
            'users' => $users,
            'row_number' => $rowNumber,
        ]);
    }

    public function create(Request $request)
    {
        return view('user.create');
    }

    public function edit(Request $request, $id)
    {
        return view('user.edit', [
            'user' => User::find($id)
        ]);
    }

    public function delete(Request $request, $id)
    {
        return view('user.delete', [
            'user' => User::find($id),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'name' => 'required|string|min:5|max:30',
            'username' => 'required|string|min:5|max:20|unique:users,username',
            'email' => 'nullable|email|min:10|max:50|unique:users,email',
            'password' => 'required|string|min:6|max:15|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'added_by' => Auth::user()->id,
        ]);

        return redirect()->route('users');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'name' => 'required|string|min:5|max:30',
            'username' => 'required|string|min:5|max:20',
            'email' => 'nullable|email|min:10|max:50',
            'user' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $user = User::find($request->user);

        if ($user) {
            if (strcmp($user->username, $request->username) != 0) {
                $checkUserName = DB::select(
                    DB::raw("SELECT username FROM users WHERE username = :theUserName"),
                    array('theUserName' => $request->username)
                );

                if (!$checkUserName) $user->username = $request->username;
                else {
                    $validator->errors()->add('username', 'Duplicate username!');
                    return back()->withErrors($validator)->withInput();
                }
            }

            if (strcmp($user->email, $request->email) != 0) {
                if (!empty($request->email)) {
                    $checkEmail = DB::select(
                        DB::raw("SELECT email FROM users WHERE email = :theEmail"),
                        array('theEmail' => $request->email)
                    );

                    if (!$checkEmail) $user->email = $request->email;
                    else {
                        $validator->errors()->add('email', 'Duplicate email!');
                        return back()->withErrors($validator)->withInput();
                    }
                }
                else {
                    $user->email = null;
                }
            }

            $user->name = $request->name;
            $user->save();
            
            return redirect()->route('users');
        }
        else {
            return back()->withInput();
        }
    }

    public function destroy(Request $request)
    {
        $user = User::find($request->user);

        if ($user != null) {
            $user->delete();
            return redirect()->route('users');
        }
        else {
            return back();
        }
    }

    public function showResetPassword($id)
    {
        return view('user.reset_password', [
            'user' => User::find($id),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'user' => 'required|numeric',
            'password' => 'required|string|min:6|max:15|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::find($request->user);

        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
            return redirect()->route('users');
        }
        else {
            return back()->withInput();
        }
    }
}