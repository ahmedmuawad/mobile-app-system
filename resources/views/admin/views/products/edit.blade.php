@extends('layouts.app')

@section('title', 'تعديل المنتج')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@php
    $currentBranchId = session('current_branch_id');
@endphp

<div class="container">
    <div class="card shadow rounded-3">
        <div class="card-header text-center bg-warning text-dark fw-bold fs-5">
            ✏️ تعديل المنتج: {{ $product->name }}
        </div>

        <div class="card-body">
            <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- 🏷️ الاسم والتصنيف --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">📦 اسم المنتج</label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="{{ old('name', $product->name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="category_id" class="form-label">📂 التصنيف</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- 💰 الأسعار والكمية (قيم افتراضية للفروع) --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="purchase_price" class="form-label">💰 سعر الشراء (افتراضي)</label>
                        <input type="number" step="0.01" class="form-control" id="purchase_price" name="purchase_price"
                            value="{{ old('purchase_price', $product->purchase_price) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="sale_price" class="form-label">💵 سعر البيع (افتراضي)</label>
                        <input type="number" step="0.01" class="form-control" id="sale_price" name="sale_price"
                            value="{{ old('sale_price', $product->sale_price) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="stock" class="form-label">📦 الكمية (إجمالية)</label>
                        <input type="number" class="form-control" id="stock" name="stock"
                            value="{{ old('stock', $product->stock) }}">
                    </div>
                </div>

                {{-- 💼 الضريبة والباركود --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="is_tax_included" class="form-label">💼 السعر شامل الضريبة؟</label>
                        <select class="form-select" name="is_tax_included" id="is_tax_included" required>
                            <option value="0" {{ old('is_tax_included', $product->is_tax_included) == 0 ? 'selected' : '' }}>❌ لا</option>
                            <option value="1" {{ old('is_tax_included', $product->is_tax_included) == 1 ? 'selected' : '' }}>✅ نعم</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="tax_percentage" class="form-label">٪ نسبة الضريبة</label>
                        <input type="number" step="0.01" class="form-control" id="tax_percentage" name="tax_percentage"
                            value="{{ old('tax_percentage', $product->tax_percentage) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="barcode" class="form-label">🔢 الباركود</label>
                        <input type="text" class="form-control" name="barcode" id="barcode"
                            value="{{ old('barcode', $product->barcode) }}" maxlength="20">
                        <svg id="barcode-preview" class="d-block my-2"></svg>
                    </div>
                </div>

                {{-- طباعة الباركود --}}
                <div class="row mb-3">
                    <div class="col-md-2">
                        <label for="barcode_copies" class="form-label">📄 عدد النسخ</label>
                        <input type="number" class="form-control" id="barcode_copies" value="1" min="1" max="100">
                    </div>
                    <div class="col-md-10 d-flex align-items-end">
                        <button type="button" id="print-barcode" class="btn btn-dark w-100">
                            🖨️ طباعة الباركود
                        </button>
                    </div>
                </div>

                {{-- 🖼️ الماركة والصورة --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="brand_id" class="form-label">🏷️ الماركة</label>
                        <select class="form-select" name="brand_id" id="brand_id">
                            <option value="">لا يوجد</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}"
                                    {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="image" class="form-label">🖼️ صورة المنتج</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" class="mt-2 rounded" width="100">
                        @endif
                    </div>
                </div>

                {{-- 💡 نتيجة الضريبة --}}
                <div class="alert alert-info mt-3" id="tax-result"></div>

                {{-- 🏢 إعدادات الفروع --}}
<div class="mt-4">
    <h5 class="fw-bold text-primary">📍 إعدادات الفروع:</h5>
    <div class="row">
        @foreach($branches as $branch)
            @php
                $pivot = $product->branches->firstWhere('id', $branch->id)?->pivot;
                $isCurrent = !$currentBranchId || $branch->id == $currentBranchId;
            @endphp
            <div class="col-md-6 mb-3">
                <div class="border rounded p-2 {{ $isCurrent ? 'border-primary' : 'border-light bg-light' }}">
                    <h6 class="text-dark mb-2">{{ $branch->name }}</h6>

                    {{-- 💰 سعر الشراء + 💵 سعر البيع --}}
                    <div class="row mb-2">
                        <div class="col-6">
                            <label class="form-label">💰 سعر الشراء</label>
                            <input type="number" step="0.01" class="form-control"
                                name="branch_purchase_price[{{ $branch->id }}]"
                                value="{{ old("branch_purchase_price.{$branch->id}", $pivot->purchase_price ?? 0) }}"
                                {{ $isCurrent ? '' : 'disabled' }}>
                        </div>
                        <div class="col-6">
                            <label class="form-label">💵 سعر البيع</label>
                            <input type="number" step="0.01" class="form-control"
                                name="branch_price[{{ $branch->id }}]"
                                value="{{ old("branch_price.{$branch->id}", $pivot->price ?? 0) }}"
                                {{ $isCurrent ? '' : 'disabled' }}>
                        </div>
                    </div>

                    {{-- 📦 الكمية + عتبة التنبيه --}}
                    <div class="row mb-2">
                        <div class="col-6">
                            <label class="form-label">📦 الكمية</label>
                            <input type="number" class="form-control"
                                name="branch_stock[{{ $branch->id }}]"
                                value="{{ old("branch_stock.{$branch->id}", $pivot->stock ?? 0) }}"
                                {{ $isCurrent ? '' : 'disabled' }}>
                        </div>
                        <div class="col-6">
                            <label class="form-label">⚠️ تنبيه عند الكمية</label>
                            <input type="number" class="form-control"
                                name="branch_low_stock_threshold[{{ $branch->id }}]"
                                value="{{ old("branch_low_stock_threshold.{$branch->id}", $pivot->low_stock_threshold ?? 0) }}"
                                {{ $isCurrent ? '' : 'disabled' }}>
                        </div>
                    </div>

                    {{-- 💼 السعر شامل الضريبة؟ + نسبة الضريبة --}}
                    <div class="row mb-2">
                        <div class="col-6">
                            <label class="form-label">💼 السعر شامل الضريبة؟</label>
                            <select class="form-select" name="branch_tax_included[{{ $branch->id }}]"
                                {{ $isCurrent ? '' : 'disabled' }}>
                                <option value="0" {{ old("branch_tax_included.{$branch->id}", $pivot->is_tax_included ?? 0) == 0 ? 'selected' : '' }}>❌ لا</option>
                                <option value="1" {{ old("branch_tax_included.{$branch->id}", $pivot->is_tax_included ?? 0) == 1 ? 'selected' : '' }}>✅ نعم</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">٪ نسبة الضريبة</label>
                            <input type="number" step="0.01" class="form-control"
                                name="branch_tax_percentage[{{ $branch->id }}]"
                                value="{{ old("branch_tax_percentage.{$branch->id}", $pivot->tax_percentage ?? 0) }}"
                                {{ $isCurrent ? '' : 'disabled' }}>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>


                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-warning">💾 تحديث المنتج</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">↩️ إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = window.jQuery;

    function calculateFinalPrice() {
        let basePrice = parseFloat($('#sale_price').val()) || 0;
        let taxPercentage = parseFloat($('#tax_percentage').val()) || 0;
        let isTaxIncluded = $('#is_tax_included').val();

        let resultText = '';
        if (taxPercentage === 0) {
            resultText = 'ℹ️ لم تقم بإدخال نسبة ضريبة بعد.';
        } else if (isTaxIncluded == '1') {
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

    $('#sale_price, #tax_percentage, #is_tax_included').on('input change', function () {
        const price = parseFloat($('#sale_price').val()) || 0;
        const tax = parseFloat($('#tax_percentage').val()) || 0;
        const isIncluded = $('#is_tax_included').val() == '1';
        const finalPrice = isIncluded ? price : price + (price * tax / 100);

        const branchBox = document.querySelector('[data-current-branch="1"]');
        if (branchBox) {
            const branchId = branchBox.getAttribute('data-branch-id');
            $(`input[name="branch_price[${branchId}]"]`).val(finalPrice.toFixed(2));
            $(`input[name="branch_tax_percentage[${branchId}]"]`).val(tax.toFixed(2));
            $(`select[name="branch_tax_included[${branchId}]"]`).val(isIncluded ? '1' : '0');
        }

        calculateFinalPrice();
    });

    function generateBarcode(code) {
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

    setTimeout(() => {
        generateBarcode($('#barcode').val());
        calculateFinalPrice();
    }, 300);

    $('#barcode').on('input', function () {
        generateBarcode($(this).val());
    });

    // $('#print_barcode').on('click', function () {
    //     const barcode = $('#barcode').val();
    //     const productName = $('#name').val().trim().split(/\s+/).slice(0, 5).join(' ');
    //     const tax = parseFloat($('#tax_percentage').val()) || 0;
    //     const isIncluded = $('#is_tax_included').val() == '1';
    //     const basePrice = parseFloat($('#sale_price').val()) || 0;
    //     const copies = parseInt($('#print_copies').val()) || 1;

    //     if (barcode.length < 4) {
    //         alert('🚫 الباركود يجب أن يكون 4 أحرف أو أكثر.');
    //         return;
    //     }

    //     let finalPrice = basePrice;
    //     if (!isIncluded && tax > 0) {
    //         finalPrice += basePrice * tax / 100;
    //     }

    //     let printHtml = '';
    //     for (let i = 0; i < copies; i++) {
    //         printHtml += `
    //             <div style="width:60mm;height:30mm;border:1px solid #ccc;margin-bottom:10mm;padding:4mm;text-align:center;font-family:sans-serif;">
    //                 <div style="font-size:12px;font-weight:bold;margin-bottom:2mm;">${productName}</div>
    //                 <svg id="code-${i}"></svg>
    //                 <div style="font-size:14px;margin-top:2mm;">${finalPrice.toFixed(2)} ج.م</div>
    //             </div>
    //         `;
    //     }

    //     const printArea = document.getElementById('barcode-print-area');
    //     printArea.innerHTML = printHtml;
    //     printArea.classList.remove('d-none');

    //     for (let i = 0; i < copies; i++) {
    //         JsBarcode(`#code-${i}`, barcode, { format: "CODE128", width: 2, height: 30, displayValue: true });
    //     }

    //     const printWindow = window.open('', '', 'width=800,height=600');
    //     printWindow.document.write(`<html><head><title>طباعة الباركود</title></head><body>${printArea.innerHTML}</body></html>`);
    //     printWindow.document.close();
    //     printWindow.focus();
    //     printWindow.print();
    //     printWindow.close();
    // });

    $('#name').on('input', function () {
        const maxWords = 10;
        const words = $(this).val().trim().split(/\s+/);
        if (words.length > maxWords) {
            alert(`🚫 الحد الأقصى لعدد كلمات اسم المنتج هو ${maxWords} كلمة.`);
            $(this).val(words.slice(0, maxWords).join(' '));
        }
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = window.jQuery;

    $('#print-barcode').on('click', function () {
        const name = $('#name').val().trim().split(/\s+/).slice(0, 5).join(' ');
        const barcode = $('#barcode').val();
        const basePrice = parseFloat($('#sale_price').val()) || 0;
        const taxPercentage = parseFloat($('#tax_percentage').val()) || 0;
        const isTaxIncluded = $('#is_tax_included').val();
        const copies = parseInt($('#barcode_copies').val()) || 1;

        if (!barcode || barcode.length < 4) {
            alert('🚫 تأكد من إدخال باركود صحيح أولاً.');
            return;
        }

        let finalPrice = basePrice;
        if (isTaxIncluded === '0') {
            finalPrice += basePrice * (taxPercentage / 100);
        }

        // توليد الباركود في SVG مؤقت
        JsBarcode("#print-barcode-preview", barcode, {
            format: "CODE128",
            width: 2,
            height: 40,
            displayValue: false
        });

        const barcodeSVG = $('#print-barcode-preview')[0].outerHTML;

        const labelHTML = `
            <div class="label">
                <div class="title">${name}</div>
                <div class="barcode-svg">${barcodeSVG}</div>
                <div class="code">${barcode}</div>
                <div class="price">السعر: ${finalPrice.toFixed(2)} ج.م</div>
            </div>
        `;

        let fullContent = '';
        for (let i = 0; i < copies; i++) {
            fullContent += labelHTML;
        }

        const printWindow = window.open('', '', 'width=800,height=600');
        printWindow.document.write(`
            <html>
            <head>
                <title>طباعة الباركود</title>
                <style>
                    body {
                        margin: 0;
                        padding: 10mm;
                        display: flex;
                        flex-wrap: wrap;
                        gap: 5mm;
                        font-family: sans-serif;
                    }
                    .label {
                        width: 60mm;
                        height: 30mm;
                        border: 1px dashed #aaa;
                        box-sizing: border-box;
                        padding: 4mm;
                        text-align: center;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        page-break-inside: avoid;
                    }
                    .label .title {
                        font-size: 11px;
                        font-weight: bold;
                        margin-bottom: 4px;
                    }
                    .barcode-svg svg {
                        width: 100%;
                        height: auto;
                    }
                    .code {
                        font-size: 10px;
                        margin-top: 2px;
                    }
                    .price {
                        font-size: 11px;
                        color: green;
                        font-weight: bold;
                        margin-top: 2px;
                    }
                    @media print {
                        body {
                            justify-content: start;
                        }
                        .label {
                            margin: 0 4mm 4mm 0;
                        }
                    }
                </style>
            </head>
            <body>${fullContent}</body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 800);
    });
});
</script>

@endpush
