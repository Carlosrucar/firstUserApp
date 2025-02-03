@extends('layouts.app')

@section('content')
<div class="container">
    @if(Auth::user()->role === 'superadmin')
        <div class="mb-3">
            <a href="{{ route('users.create') }}" class="btn btn-success">Crear Nuevo Usuario</a>
        </div>
    @endif
    
    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role }}</td>
                    <td>
                        @if(Auth::user()->role === 'superadmin' && Auth::id() === $user->id)
                            <button type="button" class="btn btn-sm btn-primary" disabled 
                                    title="No puedes editarte a ti mismo como superadmin">
                                Editar
                            </button>
                        @else
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-primary">
                                Editar
                            </a>
                        @endif
                        @if($user->role === 'superadmin' || $user->id === 1)
                            <button type="button" class="btn btn-sm btn-danger" disabled 
                                    title="No se puede eliminar al superadmin">
                                Eliminar
                            </button>
                        @else
                            <button type="button" class="btn btn-sm btn-danger" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteModal{{ $user->id }}">
                                Eliminar
                            </button>

                            <!-- Modal Confirmación -->
                            <div class="modal fade" id="deleteModal{{ $user->id }}" 
                                 tabindex="-1" aria-labelledby="deleteModalLabel" 
                                 aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Confirmar eliminación</h5>
                                            <button type="button" class="btn-close" 
                                                    data-bs-dismiss="modal" aria-label="Close">
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            ¿Estás seguro de que quieres eliminar al usuario 
                                            <strong>{{ $user->name }}</strong>?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" 
                                                    data-bs-dismiss="modal">Cancelar</button>
                                            <form action="{{ route('users.destroy', $user) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection