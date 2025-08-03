@extends('layouts.app')

@section('title', 'إضافة فرع جديد')

@section('content')
<div class="container-fluid">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">إضافة فرع</h3>
        </div>

        <form action="{{ route('admin.branches.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="name">اسم الفرع <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                </div>

                <div class="form-group">
                    <label for="phone">رقم الهاتف</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>

                <div class="form-group">
                    <label for="address">العنوان</label>
                    <textarea name="address" class="form-control" rows="3">{{ old('address') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="is_main">هل هو الفرع الرئيسي؟</label>
                    <select name="is_main" class="form-control">
                        <option value="0" {{ old('is_main') == 0 ? 'selected' : '' }}>لا</option>
                        <option value="1" {{ old('is_main') == 1 ? 'selected' : '' }}>نعم</option>
                    </select>
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
                <a href="{{ route('admin.branches.index') }}" class="btn btn-secondary">إلغاء</a>
                <button type="submit" class="btn btn-primary">حفظ</button>
            </div>
        </form>
    </div>
</div>
@endsection
