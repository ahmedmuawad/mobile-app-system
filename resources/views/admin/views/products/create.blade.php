@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">إضافة منتج جديد</div>
        <div class="card-body">
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">اسم المنتج</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label for="purchase_price" class="form-label">سعر الشراء</label>
                    <input type="number" step="0.01" class="form-control" id="purchase_price" name="purchase_price" value="{{ old('purchase_price') }}" required>
                </div>

                <div class="mb-3">
                    <label for="sale_price" class="form-label">سعر البيع</label>
                    <input type="number" step="0.01" class="form-control" id="sale_price" name="sale_price" value="{{ old('sale_price') }}" required>
                </div>

                <div class="mb-3">
                    <label for="stock" class="form-label">الكمية</label>
                    <input type="number" class="form-control" id="stock" name="stock" value="{{ old('stock', 1) }}" required>
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">التصنيف</label>
                    <select class="form-select" id="category_id" name="category_id" required>
                        <option value="">اختر التصنيف</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">صورة المنتج (اختياري)</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                </div>

                <button type="submit" class="btn btn-success">إضافة المنتج</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">إلغاء</a>
            </form>
        </div>
    </div>
</div>
@endsection
