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
                        <label for="sale_price" class="form-label">💵 سعر البيع (افتراضي)</label>
                        <input type="number" step="0.01" class="form-control" id="sale_price"
                               name="sale_price" value="{{ old('sale_price') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label for="stock" class="form-label">📦 الكمية المتوفرة (إجمالية)</label>
                        <input type="number" class="form-control" id="stock" name="stock"
                               value="{{ old('stock', 1) }}" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="is_tax_included" class="form-label">💼 السعر شامل الضريبة؟</label>
                        <select class="form-select" name="is_tax_included" id="is_tax_included" required>
                            <option value="0" {{ old('is_tax_included') == '0' ? 'selected' : '' }}>❌ لا</option>
                            <option value="1" {{ old('is_tax_included') == '1' ? 'selected' : '' }}>✅ نعم</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="tax_percentage" class="form-label">٪ نسبة الضريبة</label>
                        <input type="number" step="0.01" class="form-control" id="tax_percentage" name="tax_percentage"
                               value="{{ old('tax_percentage') }}" placeholder="مثلاً: 14">
                    </div>

                    <div class="col-md-4">
                        <label for="barcode" class="form-label">🔢 الباركود (اختياري)</label>
                        <input type="text" class="form-control" name="barcode" id="barcode"
                               value="{{ old('barcode') }}" maxlength="20" placeholder="اتركه فارغ للتوليد التلقائي">
                        <svg id="barcode-preview" class="d-block my-2"></svg>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="brand_id" class="form-label">🏷️ الماركة (Brand)</label>
                    <select class="form-select" name="brand_id" id="brand_id">
                        <option value="">لا يوجد</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}"
                                {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">🖼️ صورة المنتج (اختياري)</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                </div>

                <div class="alert alert-info mt-3" id="tax-result"></div>

                <!-- ✅ إدخال السعر والمخزون حسب كل فرع -->
                <div class="mt-4">
                    <h5 class="fw-bold text-primary">📍 إعدادات الفروع:</h5>
                    <div class="row">
                        @foreach($branches as $branch)
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-2">
                                    <h6 class="text-dark mb-2">{{ $branch->name }}</h6>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <label class="form-label">💰 سعر الشراء</label>
                                            <input type="number" step="0.01" class="form-control"
                                                name="branch_purchase_price[{{ $branch->id }}]"
                                                value="{{ old("branch_purchase_price.{$branch->id}") }}">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">💵 سعر البيع</label>
                                            <input type="number" step="0.01" class="form-control"
                                                name="branch_price[{{ $branch->id }}]"
                                                value="{{ old("branch_price.{$branch->id}") }}">
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <label class="form-label">📦 الكمية</label>
                                            <input type="number" class="form-control"
                                                name="branch_stock[{{ $branch->id }}]"
                                                value="{{ old("branch_stock.{$branch->id}", 0) }}">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">💼 السعر شامل الضريبة؟</label>
                                            <select class="form-select" name="branch_tax_included[{{ $branch->id }}]">
                                                <option value="0">❌ لا</option>
                                                <option value="1">✅ نعم</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label">٪ نسبة الضريبة</label>
                                        <input type="number" step="0.01" class="form-control"
                                            name="branch_tax_percentage[{{ $branch->id }}]"
                                            value="{{ old("branch_tax_percentage.{$branch->id}") }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>


                <div class="d-flex justify-content-between mt-4">
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
$(function () {
    function calculateFinalPrice() {
        let basePrice = parseFloat($('#sale_price').val()) || 0;
        let taxPercentage = parseFloat($('#tax_percentage').val()) || 0;
        let isTaxIncluded = $('#is_tax_included').val();

        if (!taxPercentage) {
            $('#tax-result').html('ℹ️ لم تقم بإدخال نسبة ضريبة بعد.');
            return;
        }

        let resultText = '';
        if (isTaxIncluded == '1') {
            let taxValue = basePrice * (taxPercentage / (100 + taxPercentage));
            let priceWithoutTax = basePrice - taxValue;
            resultText = `✅ السعر يتضمن ضريبة (${taxPercentage}%) = ${taxValue.toFixed(2)} ج.م ➜ السعر بدون الضريبة: ${priceWithoutTax.toFixed(2)} ج.م`;
        } else {
            let taxValue = basePrice * (taxPercentage / 100);
            let finalPrice = basePrice + taxValue;
            resultText = `✅ السعر بعد إضافة (${taxPercentage}%) ضريبة = ${finalPrice.toFixed(2)} ج.م`;
        }

        $('#tax-result').html(resultText);
    }

    $('#sale_price, #tax_percentage, #is_tax_included').on('input change', calculateFinalPrice);
    calculateFinalPrice();

    function generateBarcodePreview(code) {
        if (code.length > 3) {
            JsBarcode("#barcode-preview", code, {
                format: "CODE128",
                width: 2,
                height: 40,
                displayValue: true
            });
        } else {
            $('#barcode-preview').html('');
        }
    }

    $('#barcode').on('input', function () {
        generateBarcodePreview($(this).val());
    });

    $(document).ready(function () {
        const initialCode = $('#barcode').val();
        if (initialCode) {
            generateBarcodePreview(initialCode);
        }
    });
});
</script>
<script>
    $('#sale_price, #purchase_price, #tax_percentage, #is_tax_included').on('change', function () {
    let salePrice = $('#sale_price').val();
    let purchasePrice = $('#purchase_price').val();
    let taxPercentage = $('#tax_percentage').val();
    let isTaxIncluded = $('#is_tax_included').val();

    @foreach($branches as $branch)
        $(`input[name="branch_price[{{ $branch->id }}]"]`).val(salePrice);
        $(`input[name="branch_purchase_price[{{ $branch->id }}]"]`).val(purchasePrice);
        $(`input[name="branch_tax_percentage[{{ $branch->id }}]"]`).val(taxPercentage);
        $(`select[name="branch_tax_included[{{ $branch->id }}]"]`).val(isTaxIncluded);
    @endforeach
});

</script>
@endpush
