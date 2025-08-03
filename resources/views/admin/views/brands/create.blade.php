@extends('layouts.app')

@section('title', 'إضافة ماركة')

@section('content')
<div class="container">
    <div class="card shadow rounded">
        <div class="card-header bg-primary text-white fw-bold">➕ إضافة ماركة جديدة</div>

        <div class="card-body">
            <form action="{{ route('admin.brands.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">📛 اسم الماركة <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control"
                           value="{{ old('name') }}" required placeholder="مثلاً: Samsung">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">📝 الوصف</label>
                    <textarea name="description" id="description" class="form-control" rows="3"
                              placeholder="وصف إضافي للماركة (اختياري)">{{ old('description') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="is_active" class="form-label">⚙️ الحالة</label>
                    <select name="is_active" id="is_active" class="form-select">
                        <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>مفعلة</option>
                        <option value="0" {{ old('is_active', 1) == 0 ? 'selected' : '' }}>غير مفعلة</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success">💾 حفظ</button>
                    <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">↩️ رجوع</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
