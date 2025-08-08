@extends('layouts.app')
@section('content')
<div class="container">
    <h1 class="mb-4">إدارة الموديولات للباقة: {{ $package->name }}</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ url('admin/packages/' . $package->id . '/modules') }}">
        @csrf
        <div class="row">
            @foreach($modules as $module)
                <div class="col-md-4 mb-2">
                    <label class="form-check-label">
                        <input type="checkbox" name="modules[]" value="{{ $module->id }}"
                               {{ in_array($module->id, $assignedModuleIds) ? 'checked' : '' }}>
                        {{ $module->name }}
                    </label>
                </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary mt-3">حفظ التعديلات</button>
    </form>
</div>
@endsection
