@extends('layouts.admin_app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2>Edit User: {{ $managedUser->name }}</h2>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            User Details
        </div>
        <div class="card-body">
            @include('partials.admin.error_messages') {{-- Para mostrar erros de validação --}}
            @include('partials.admin.success_message') {{-- Para mostrar mensagens de sucesso --}}

            <form method="POST" action="{{ route('admin.users.update', $managedUser->id) }}">
                @method('PUT')
                @include('admin.users._form', ['managedUser' => $managedUser, 'editable_roles' => $editable_roles, 'companies' => $companies])
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- Inicializar select2 se estiver a ser usado --}}
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Select roles",
                allowClear: true
            });
        });
    </script>
@endpush
