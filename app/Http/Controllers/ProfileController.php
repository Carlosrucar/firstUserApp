<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('auth.profile');
    }

    public function show()
    {
        return view('auth.profile');
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
            'current_password' => 'nullable',
            'new_password' => 'nullable|min:8|confirmed',
        ]);
    
        $user = Auth::user();
        $user->name = $request->name;
        $user->email = $request->email;
        
        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
        }
    
        $user->save();
        return back()->with('status', 'Profile updated successfully');
    }
}