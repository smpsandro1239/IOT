@csrf
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $managedUser->name ?? '') }}" required>
            @error('name')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="email">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $managedUser->email ?? '') }}" required>
            @error('email')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="password">Password @if(!isset($managedUser))<span class="text-danger">*</span>@endif</label>
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                   @if(!isset($managedUser)) required @endif>
            @if(isset($managedUser))<small class="form-text text-muted">Leave blank to keep current password.</small>@endif
            @error('password')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="password_confirmation">Confirm Password @if(!isset($managedUser))<span class="text-danger">*</span>@endif</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                   @if(!isset($managedUser)) required @endif>
        </div>
    </div>
</div>

@php
    $currentUser = Auth::user();
    $isEditingSelf = isset($managedUser) && $currentUser->id === $managedUser->id;
    $canManageRoles = false; // Se pode mudar os papéis do $managedUser
    $canManageCompany = false; // Se pode mudar a empresa do $managedUser

    if ($currentUser->isSuperAdmin()) {
        $canManageRoles = true;
        $canManageCompany = true;
    } elseif ($currentUser->hasRole('company-admin')) {
        // CompanyAdmin pode gerir papéis de SiteManagers da sua empresa.
        // Não pode mudar a empresa de ninguém.
        if (isset($managedUser) && $currentUser->company_id === $managedUser->company_id) {
            $canManageRoles = true; // Limitado a 'site-manager' no controlador.
        } elseif (!isset($managedUser)) { // Ao criar novo user
            $canManageRoles = true; // Limitado a 'site-manager' no controlador.
        }
        $canManageCompany = false; // CompanyAdmin não muda a empresa de outros, é fixada à sua.
                                 // E ao criar, a empresa é a sua.
    }
    // Utilizador a editar o seu próprio perfil não pode mudar papel ou empresa através deste form.
    if ($isEditingSelf) {
        $canManageRoles = false;
        $canManageCompany = false;
    }

@endphp


@if($canManageRoles && $editable_roles->count() > 0)
<div class="form-group">
    <label for="role_ids">Roles <span class="text-danger">*</span></label>
    <select name="role_ids[]" id="role_ids" class="form-control select2 @error('role_ids') is-invalid @enderror" multiple required>
        @foreach($editable_roles as $role)
            <option value="{{ $role->id }}"
                @if(isset($managedUser) && $managedUser->roles->contains($role->id)) selected @endif
                @if(is_array(old('role_ids')) && in_array($role->id, old('role_ids'))) selected @endif>
                {{ $role->display_name ?? $role->name }}
            </option>
        @endforeach
    </select>
    @error('role_ids')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
    @if($currentUser->hasRole('company-admin') && !$currentUser->isSuperAdmin())
        <small class="form-text text-muted">Company Admins can only assign the 'Site Manager' role.</small>
    @endif
</div>
@elseif(isset($managedUser) && !$canManageRoles && $managedUser->roles->count() > 0)
    <div class="form-group">
        <label>Roles</label>
        <p>
            @foreach($managedUser->roles as $role)
                <span class="badge badge-info">{{ $role->display_name ?? $role->name }}</span>
            @endforeach
        </p>
        <small class="form-text text-muted">Your roles cannot be changed through this form.</small>
    </div>
@endif


@if($canManageCompany && $companies->count() > 0)
    <div class="form-group">
        <label for="company_id">Company</label>
        <select name="company_id" id="company_id" class="form-control @error('company_id') is-invalid @enderror">
            <option value="">Assign to Company (Optional)</option>
            @foreach($companies as $company)
                <option value="{{ $company->id }}"
                    @if( (isset($managedUser) && $managedUser->company_id == $company->id) || old('company_id') == $company->id) selected @endif >
                    {{ $company->name }}
                </option>
            @endforeach
        </select>
        @error('company_id')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
        <small class="form-text text-muted">Required if assigning 'Company Admin' role.</small>
    </div>
@elseif(isset($managedUser) && $managedUser->company_id && !$canManageCompany)
    <div class="form-group">
        <label>Company</label>
        <p>{{ $managedUser->company->name ?? 'N/A' }}</p>
        @if($currentUser->hasRole('company-admin') && $currentUser->company_id === $managedUser->company_id)
             <small class="form-text text-muted">Users created by Company Admins are automatically assigned to your company.</small>
        @else
            <small class="form-text text-muted">Your company assignment cannot be changed through this form.</small>
        @endif
    </div>
@elseif(!isset($managedUser) && $currentUser->hasRole('company-admin') && $currentUser->company_id)
    {{-- Ao criar novo user, se o criador é CompanyAdmin, a empresa é fixada e mostrada --}}
    <div class="form-group">
        <label>Company</label>
        @php $creatorCompany = $companies->first(); @endphp
        <p>{{ $creatorCompany->name ?? 'Error: Company not found for creator' }}</p>
        <input type="hidden" name="company_id" value="{{ $creatorCompany->id ?? '' }}">
        <small class="form-text text-muted">This user will be assigned to your company: {{ $creatorCompany->name ?? '' }}.</small>
    </div>
@endif


<div class="form-group mt-4">
    <button type="submit" class="btn btn-primary">
        {{ isset($managedUser) ? 'Update User' : 'Create User' }}
    </button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
</div>
