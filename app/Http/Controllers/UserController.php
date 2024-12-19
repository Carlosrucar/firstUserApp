<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller {
    public function index()
    {
        if (Auth::user()->role === 'superadmin') {
        $users = User::all();
    } else if (Auth::user()->role === 'admin') {
        $users = User::where('role', '!=', 'superadmin')->get();
    }
        return view('users.index', compact('users'));
    }   

public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,user'
    ]);

    // Proteger al superadmin
        if ($user->role === 'superadmin') {
        return back()->with('error', 'No se puede modificar al superadmin');
    }

    // Solo superadmin puede crear otros admins
        if ($data['role'] === 'admin' && Auth::user()->role !== 'superadmin') {
        return back()->with('error', 'No tienes permisos para asignar rol de admin');
    }

        $user->update($data);
        return redirect()->route('users.index');
    }   

    public function destroy(User $user)
    {
        if($user->id === 1) {
        return back()->with('error', 'No se puede eliminar al superusuario');
    }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado');
    }

    public function create()
    {
        return view('users.create');
    }
}