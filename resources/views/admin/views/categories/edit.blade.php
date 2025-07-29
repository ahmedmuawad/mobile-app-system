@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">تعديل التصنيف</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.categories.update', $category->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">اسم التصنيف</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                </div>

                <button type="submit" class="btn btn-primary mt-3">حفظ التعديلات</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary mt-3">إلغاء</a>
            </form>
        </div>
    </div>
</div>
@endsection
