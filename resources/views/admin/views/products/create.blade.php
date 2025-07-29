@extends('layouts.app')

@section('title', 'إضافة منتج جديد')

@section('content')
<div class="container">
    <div class="card shadow rounded-3">
        <div class="card-header text-center bg-primary text-white fw-bold fs-5">
            ➕ إضافة منتج جديد
        </div>

        <div class="card-body">
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">📦 اسم المنتج</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="{{ old('name') }}" required placeholder="مثلاً: بطارية سامسونج">
                    </div>

                    <div class="col-md-6">
                        <label for="category_id" class="form-label">📂 التصنيف</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">اختر التصنيف</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="purchase_price" class="form-label">💰 سعر الشراء</label>
                        <input type="number" step="0.01" class="form-control" id="purchase_price"
                               name="purchase_price" value="{{ old('purchase_price') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label for="sale_price" class="form-label">💵 سعر البيع</label>
                        <input type="number" step="0.01" class="form-control" id="sale_price"
                               name="sale_price" value="{{ old('sale_price') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label for="stock" class="form-label">📦 الكمية المتوفرة</label>
                        <input type="number" class="form-control" id="stock" name="stock"
                               value="{{ old('stock', 1) }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">🖼️ صورة المنتج (اختياري)</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success">
                        💾 حفظ المنتج
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                        ↩️ إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
