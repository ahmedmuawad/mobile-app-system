@extends('layouts.app')

@section('title', 'تعديل الباقة')

@section('content')
<div class="container-fluid">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">تعديل الباقة</h3>
        </div>

        <form action="{{ route('admin.packages.update', $package->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">

                <div class="form-group">
                    <label for="name">اسم الباقة <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $package->name) }}">
                </div>

                <div class="form-group">
                    <label for="price">سعر الباقة <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="price" class="form-control" required value="{{ old('price', $package->price) }}">
                </div>

                <div class="form-group">
                    <label for="modules">الموديولز</label>
                    <div class="row">
                        @foreach($modules as $module)
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" name="modules[]" value="{{ $module->id }}" class="form-check-input" id="module_{{ $module->id }}"
                                        {{ in_array($module->id, $package->modules->pluck('id')->toArray()) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="module_{{ $module->id }}">{{ $module->name }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <div class="card-footer text-right">
                <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">إلغاء</a>
                <button type="submit" class="btn btn-primary">تحديث</button>
            </div>
        </form>
    </div>
</div>
@endsection
