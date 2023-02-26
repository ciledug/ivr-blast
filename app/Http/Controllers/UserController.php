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
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view('user.index', [
            'users' => $this->getUserList(),
        ]);
    }

    public function create(Request $request)
    {
        return view('user.create');
    }

    public function edit(Request $request, $userName=null)
    {
        $data = array();

        $user = User::where('username' ,'=', Str::replaceFirst('_', '', $userName))
            ->whereNull('deleted_at')
            ->first();
        
        if ($user) {
            $data['user'] = $user;
        }

        return view('user.edit', $data);
    }

    public function delete(Request $request, $userName)
    {
        $data = array();

        $user = User::where('username', Str::replaceFirst('_', '', $request->username))
            ->whereNull('deleted_at')
            ->first();

        if ($user) {
            $data['user'] = $user;
        }

        return view('user.delete', $data);
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

        return redirect()->route('user');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'name' => 'required|string|min:5|max:30',
            'username' => 'required|string|min:5|max:20',
            'email' => 'nullable|email|min:10|max:50',
            'user' => 'required|string|min:5|max:20'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $user = User::where('username', '=', Str::replaceFirst('_', '', $request->user))
            ->whereNull('deleted_at')
            ->first();

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
            
            return redirect()->route('user');
        }
        else {
            return back()->withInput();
        }
    }

    public function destroy(Request $request)
    {
        $user = User::where('username', Str::replaceFirst('_', '', $request->user))
            ->whereNull('deleted_at')
            ->first();

        if ($user != null) {
            $user->delete();
            return redirect()->route('user');
        }
        else {
            return back();
        }
    }

    public function getUserListAjax(Request $request)
    {
        $ORDERED_COLUMNS = ['name', 'username', 'last_login', 'last_ip_address', 'added_by'];
        $ORDERED_BY = ['desc', 'asc'];
        $COLUMN_IDX = is_numeric($request->order[0]['column']) ? $request->order[0]['column'] : 0;
        $START = is_numeric($request->start) ? $request->start : 0;
        $LENGTH = is_numeric($request->length) ? $request->length : 10;
        $SEARCH_VALUE = !empty($request->search['value']) ? $request->search['value'] : '';

        $recordsTotalQuery = 0;
        $userList = [];

        $query = User::select(['name', 'username', 'email', 'added_by']);

        if (!empty($SEARCH_VALUE)) {
            $query->where(function($q) use($SEARCH_VALUE) {
                $q->where('name', 'LIKE', '%' . $SEARCH_VALUE . '%')
                    ->orWhere('username', 'LIKE', '%' . $SEARCH_VALUE . '%')
                    ->orwhere('last_login', 'LIKE', '%' . $SEARCH_VALUE . '%')
                    ->orwhere('last_ip_address', 'LIKE', '%' . $SEARCH_VALUE . '%')
                    ->orwhere('added_by', 'LIKE', '%' . $SEARCH_VALUE . '%');
            });
        }

        if (in_array($request->order[0]['dir'], $ORDERED_BY)) {
            $query->orderBy($ORDERED_COLUMNS[$COLUMN_IDX], $request->order[0]['dir']);
        }

        $filteredData = $query->get();
        $users = $query->offset($START)->limit($LENGTH)->get();

        if ($users) {
            if ($users->count() > 0) {
                foreach ($users AS $keyUser => $valueUser) {
                    $userLog = UserLog::select(['last_login', 'last_ip_address'])
                        ->where('user_id', '=', $valueUser->id)
                        ->orderBy('last_login', 'DESC')
                        ->first();
                    // dd($userLog);

                    $userList[] = array(
                        'name' => $valueUser->name,
                        'username' => $valueUser->username,
                        'email' => $valueUser->email,
                        'added_by' => $valueUser->added_by,
                        'last_login' => $userLog ? $userLog->last_login : '',
                        'last_ip_address' => $userLog ? $userLog->last_ip_address : '',
                    );
                }
            }
        }

        $returnedResponse = array(
            'draw' => $request->draw,
            'recordsTotal' => User::all()->count(),
            'recordsFiltered' => $filteredData->count(),
            'data' => $userList
        );

        return response()->json($returnedResponse);
    }

    public function showResetPassword(Request $request, $userName)
    {
        $data = array();
        $user = User::where('username', '=', Str::replaceFirst('_', '', $userName))
            ->whereNull('deleted_at')
            ->first();

        if ($user) {
            $data['user'] = $user;
        }

        return view('user.reset_password', $data);
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'user' => 'required|string|min:5|max:30',
            'password' => 'required|string|min:6|max:15|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('username', '=', Str::replaceFirst('_', '', $request->user))
            ->first();

        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
            return redirect()->route('user');
        }
        else {
            return back()->withInput();
        }
    }

    private function getUserList()
    {
        $users = User::select(['id', 'name', 'username', 'email', 'added_by'])
            ->orderBy('users.name', 'DESC')
            ->paginate(15);

        if ($users->count() > 0) {
            foreach ($users AS $keyUser => $valueUser) {
                $addedBy = User::select('name', 'username')
                    ->where('id', '=', $valueUser->added_by)
                    ->first();

                $userLog = UserLog::select(['last_login', 'last_ip_address'])
                    ->where('user_id', '=', $valueUser->id)
                    ->orderBy('id', 'DESC')
                    ->first();

                $users[$keyUser]['added_by'] = $addedBy;
                $users[$keyUser]['last_login'] = $userLog ? $userLog->last_login : '-';
                $users[$keyUser]['last_ip_address'] = $userLog ? $userLog->last_ip_address : '-';
            }
        }

        return $users;
    }
}