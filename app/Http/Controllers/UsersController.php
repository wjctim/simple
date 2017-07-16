<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\User;

class UsersController extends Controller
{
    //

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
            'password' => 'required'
        ]);

        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => bcrypt($request['password'])
        ]);
        session()->flush('success', '欢迎，您将在这里开启一段新的旅程~');

        return redirect()->route('users.show', [$user->id]);
    }
    
}
