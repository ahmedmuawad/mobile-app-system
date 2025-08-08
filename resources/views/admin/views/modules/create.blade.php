@extends('layouts.app')

@section('title', 'إضافة موديول جديد')

@section('content')
<div class="container-fluid">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">إضافة موديول</h3>
        </div>

        <form action="{{ route('admin.modules.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="name">اسم الموديول <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                </div>

                <div class="form-group">
                    <label for="description">الوصف</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="is_active">الحالة</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active') == 1 ? 'selected' : '' }}>مفعل</option>
                        <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>غير مفعل</option>
                    </select>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('admin.modules.index') }}" class="btn btn-secondary">إلغاء</a>
                <button type="submit" class="btn btn-primary">حفظ</button>
            </div>
        </form>
    </div>
</div>
@endsection
