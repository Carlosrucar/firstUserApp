<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller {
    public function index()
    {
    // Inicializar $users
    $users = collect();  // Colección vacía por defecto

    if (Auth::user()->role === 'superadmin') {
        $users = User::all();
    } else if (Auth::user()->role === 'admin') {
        $users = User::where('role', '!=', 'superadmin')->get();
    } else {
        // Para usuarios normales, mostrar solo su propio usuario
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
    
        // Prevent changing role if target user is superadmin
        if ($user->role === 'superadmin') {
            return back()->with('error', 'No se puede modificar el rol de un superadmin');
        }
    
        // Modificar esta condición para permitir que superadmin cambie roles
        if (Auth::user()->role === 'superadmin' || Auth::user()->role === 'admin') {
            $user->update($data);
            return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente');
        }
    
        // Si no es admin ni superadmin, solo puede actualizar sus propios datos sin cambiar rol
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
    // Verificar si es superadmin por rol o por ID
    if($user->id === 1 || $user->role === 'superadmin') {
        return back()->with('error', 'No se puede eliminar al superadministrador del sistema');
    }

    $user->delete();
    return redirect()->route('users.index')->with('success', 'Usuario eliminado');
}

public function edit(User $user)
{
    // Verificar si es superadmin por rol o por ID
    if (Auth::user()->role === 'superadmin' && Auth::id() === $user->id) {
        return back()->with('error', 'No puedes editarte a ti mismo como superadmin');
    }

    // Rest of the existing checks...
    if(($user->id === 1 || $user->role === 'superadmin') && Auth::user()->id !== $user->id) {
        return back()->with('error', 'No se puede editar al superadministrador del sistema');
    }

    if ($user->role === 'admin' && Auth::user()->role !== 'superadmin') {
        return back()->with('error', 'No tienes permisos para editar administradores');
    }

    return view('users.edit', compact('user'));
    }

    public function create()
    {
        // Only superadmin can create users
        if (Auth::user()->role !== 'superadmin') {
            return back()->with('error', 'Solo el superadmin puede crear usuarios');
        }
    
        return view('users.create');
    }
    
    public function store(Request $request)
    {
        // Only superadmin can create users
        if (Auth::user()->role !== 'superadmin') {
            return back()->with('error', 'Solo el superadmin puede crear usuarios');
        }
    
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:user,admin'
        ]);
    
        // Hash the password
        $data['password'] = Hash::make($data['password']);
    
        User::create($data);
        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente');
    }
}