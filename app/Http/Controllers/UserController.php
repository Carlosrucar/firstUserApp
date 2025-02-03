<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = collect();

        if (Auth::user()->role === 'superadmin') {
            $users = User::all();
        } else if (Auth::user()->role === 'admin') {
            $users = User::where('role', '!=', 'superadmin')->get();
        } else {
            $users = User::where('id', Auth::id())->get();
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
    
        if ($user->role === 'superadmin') {
            return back()->with('error', 'No se puede modificar el rol de un superadmin');
        }
    
        if (Auth::user()->role === 'superadmin' || Auth::user()->role === 'admin') {
            $user->update($data);
            return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente');
        }
    
        if (Auth::id() === $user->id) {
            if ($data['role'] !== $user->role) {
                return back()->with('error', 'No tienes permisos para cambiar roles');
            }
            $user->update($data);
            return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente');
        }
    
        return back()->with('error', 'No tienes permisos para editar este usuario');
    }

    public function destroy(User $user)
    {
        if($user->id === 1 || $user->role === 'superadmin') {
            return back()->with('error', 'No se puede eliminar al superadministrador del sistema');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente');
    }

    public function create()
    {
        if (Auth::user()->role !== 'superadmin') {
            return back()->with('error', 'Solo el superadmin puede crear usuarios');
        }

        return view('users.create');
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'superadmin') {
            return back()->with('error', 'Solo el superadmin puede crear usuarios');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:user,admin'
        ]);

        $data['password'] = Hash::make($data['password']);
        User::create($data);
        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente');
    }

    public function verifyEmail(User $user)
    {
        if (Auth::user()->role !== 'superadmin') {
            return back()->with('error', 'Solo el superadmin puede verificar usuarios');
        }

        $user->email_verified_at = now();
        $user->save();

        return back()->with('success', 'Email verificado correctamente');
    }
}