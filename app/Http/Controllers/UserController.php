<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'role' => 'required|in:admin,user,superadmin'  // Validamos los roles permitidos
        ]);
    
        // Proteger al superadmin original (ID=1)
        if ($user->id === 1) {
            return back()->with('error', 'No se puede modificar al superadmin original');
        }
    
        // Solo superadmin puede modificar roles
        if (Auth::user()->role !== 'superadmin' && $data['role'] !== $user->role) {
            return back()->with('error', 'Solo el superadmin puede cambiar roles');
        }
    
        // Evitar que un admin se asigne rol de superadmin
        if ($data['role'] === 'superadmin' && Auth::user()->role !== 'superadmin') {
            return back()->with('error', 'No tienes permisos para asignar rol de superadmin');
        }
    
        $user->update($data);
        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente');
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
    if(($user->id === 1 || $user->role === 'superadmin') && Auth::user()->id !== $user->id) {
        return back()->with('error', 'No se puede editar al superadministrador del sistema');
    }

    // Solo superadmin puede editar otros admins
    if ($user->role === 'admin' && Auth::user()->role !== 'superadmin') {
        return back()->with('error', 'No tienes permisos para editar administradores');
    }

    return view('users.edit', compact('user'));
}
}