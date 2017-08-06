<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Mail;

class UsersController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth', [
            'only' => ['edit', 'update', 'destroy']
        ]);
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    public function index()
    {
        $users = User::paginate(20);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function show($userId)
    {
        $user = User::findOrFail($userId);
        return view('users.show', compact('user'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
//        Auth::login($user);
        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');

//        return redirect()->route('users.show', [$user->id]);
        return redirect('/');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    public function update($id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'confirmed|min:6'
        ]);

        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $data = [
            'name' => $request['name']
        ];
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '更新成功');

        return redirect()->route('users.show', $id);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('destroy', $user);
        $user->delete();

        session()->flash('success', '删除成功！');
        return back();
    }

    public function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'wjctim1990@gmail.com';
        $name = 'Tim';
        $to = $user->email;
        $subject = '感谢注册 Simple 应用！请确认你的邮箱。';

        Mail::send($view, $data, function ($message) use($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }

    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();
        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');

        return redirect()->route('users.show', [$user->id]);
    }
    
}
