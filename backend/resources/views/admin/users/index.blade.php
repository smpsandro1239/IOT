@extends('layouts.admin_app') {{-- Assume que tem um layout base admin --}}

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2>User Management</h2>
        </div>
    </div>

    @include('partials.admin.operation_messages')

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    Users List
                </div>
                <div class="col-md-6 text-right">
                    @can('users:create-company-admin') {{-- Ou uma permissão mais genérica de criar user que o SuperAdmin tenha --}}
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New User
                        </a>
                    @elsecan('users:create-site-manager-own-company') {{-- CompanyAdmin pode criar SiteManagers --}}
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Site Manager
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">
            {{-- Filtros --}}
            <form method="GET" action="{{ route('admin.users.index') }}" class="mb-3">
                <div class="form-row">
                    <div class="col-md-3">
                        <input type="text" name="name_filter" class="form-control form-control-sm" placeholder="Filter by Name" value="{{ request('name_filter') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="email_filter" class="form-control form-control-sm" placeholder="Filter by Email" value="{{ request('email_filter') }}">
                    </div>
                    @if(Auth::user()->isSuperAdmin()) {{-- Só SuperAdmin pode filtrar por papel arbitrário por agora --}}
                    <div class="col-md-3">
                        <select name="role_filter" class="form-control form-control-sm">
                            <option value="">All Roles</option>
                            @foreach($roles_for_filter as $role)
                                <option value="{{ $role->id }}" {{ request('role_filter') == $role->id ? 'selected' : '' }}>
                                    {{ $role->display_name ?? $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light btn-sm">Clear</a>
                    </div>
                </div>
            </form>

            <table class="table table-sm table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Company</th>
                        <th>Verified</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $managedUser) {{-- Renomeado para evitar conflito com Auth::user() --}}
                        <tr>
                            <td>{{ $managedUser->id }}</td>
                            <td>{{ $managedUser->name }}</td>
                            <td>{{ $managedUser->email }}</td>
                            <td>
                                @foreach($managedUser->roles as $role)
                                    <span class="badge badge-info">{{ $role->display_name ?? $role->name }}</span>
                                @endforeach
                            </td>
                            <td>{{ $managedUser->company->name ?? 'N/A' }}</td>
                            <td>
                                @if($managedUser->email_verified_at)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-warning">No</span>
                                @endif
                            </td>
                            <td>
                                @php $canUpdate = false; @endphp
                                @if(Auth::user()->isSuperAdmin())
                                    @can('users:update-any') @php $canUpdate = true; @endphp @endcan
                                @elseif(Auth::user()->hasRole('company-admin') && Auth::user()->company_id === $managedUser->company_id)
                                    @can('users:update-own-company-user', $managedUser) @php $canUpdate = true; @endphp @endcan
                                @elseif(Auth::id() === $managedUser->id) {{-- Editar o próprio perfil --}}
                                     @can('users:update-own', $managedUser) @php $canUpdate = true; @endphp @endcan
                                @endif

                                @if($canUpdate)
                                <a href="{{ route('admin.users.edit', $managedUser->id) }}" class="btn btn-xs btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                @endif

                                @if(Auth::id() !== $managedUser->id) {{-- Não pode excluir a si mesmo --}}
                                    @php $canDelete = false; @endphp
                                    @if(Auth::user()->isSuperAdmin())
                                        @can('users:delete-any') @php $canDelete = true; @endphp @endcan
                                    @elseif(Auth::user()->hasRole('company-admin') && Auth::user()->company_id === $managedUser->company_id)
                                        @can('users:delete-own-company-user', $managedUser)
                                            @if($managedUser->hasRole('site-manager')) {{-- CompanyAdmin só pode excluir SiteManagers --}}
                                                @php $canDelete = true; @endphp
                                            @endif
                                        @endcan
                                    @endif

                                    @if($canDelete)
                                    <form action="{{ route('admin.users.destroy', $managedUser->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
