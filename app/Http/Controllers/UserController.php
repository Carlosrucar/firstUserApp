<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller {
    public function index()
    {
        $users = User::where('id', '!=', 1)->get(); // Excluimos el superadmin
        return view('users.index', compact('users'));
    }

    public function edit(User $user) 
    {
        if (Auth::user()->role != 'admin' && Auth::id() != $user->id) {
            return redirect('/home');
        }
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,user'
        ]);

        // Evitar que el superusuario cambie su propio rol
        if ($user->id === 1) {
        unset($data['role']);
    }

        if ($user->email != $data['email']) {
        $user->email_verified_at = null;
    }

    $user->update($data);
    return redirect()->route('users.index')->with('success', 'Usuario actualizado');
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