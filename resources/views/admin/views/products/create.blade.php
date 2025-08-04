{{-- ⚙️ كامل كود صفحة إضافة المنتج --}}
@extends('layouts.app')

@section('title', 'إضافة منتج جديد')

@section('content')
@php
    $currentBranchId = session('current_branch_id');
@endphp

<div class="container">
    <div class="card shadow rounded-3">
        <div class="card-header text-center bg-primary text-white fw-bold fs-5">
            ➕ إضافة منتج جديد
        </div>

        <div class="card-body">
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- 🏷️ الاسم والتصنيف --}}
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

                {{-- 💰 الأسعار والكمية --}}
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

                {{-- 💼 الضريبة والباركود --}}
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
                    <div class="col-md-2">
                        <label for="barcode_copies" class="form-label">📄 عدد النسخ</label>
                        <input type="number" class="form-control" id="barcode_copies" value="1" min="1" max="100">
                    </div>
                    <div class="col-md-10 d-flex align-items-end">
                        <button type="button" id="print-barcode" class="btn btn-dark w-100">
                            🖨️ طباعة الباركود
                        </button>
                    </div>

                    <div id="print-area" class="d-none text-center p-3">
                        <h6 id="print-product-name"></h6>
                        <svg id="print-barcode-preview"></svg>
                        <p id="print-barcode-number" class="mb-1 fw-bold"></p>
                        <p id="print-final-price" class="mb-0 text-success"></p>
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
                                    {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="image" class="form-label">🖼️ صورة المنتج</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
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
                                $isCurrent = !$currentBranchId || $branch->id == $currentBranchId;
                            @endphp
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-2 {{ $isCurrent ? 'border-primary' : 'border-light bg-light' }}">
                                    <h6 class="text-dark mb-2">
                                        {{ $branch->name }}
                                        @if($isCurrent)
                                            <span class="badge bg-success">الفرع الحالي</span>
                                        @else
                                            <span class="badge bg-secondary">غير مفعل</span>
                                        @endif
                                    </h6>

                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <label class="form-label">💰 سعر الشراء</label>
                                            <input type="number" step="0.01" class="form-control"
                                                name="branch_purchase_price[{{ $branch->id }}]"
                                                value="{{ old("branch_purchase_price.{$branch->id}") }}"
                                                {{ $isCurrent ? '' : 'disabled' }}>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">💵 سعر البيع</label>
                                            <input type="number" step="0.01" class="form-control"
                                                name="branch_price[{{ $branch->id }}]"
                                                value="{{ old("branch_price.{$branch->id}") }}"
                                                {{ $isCurrent ? '' : 'disabled' }}>
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <label class="form-label">📦 الكمية</label>
                                            <input type="number" class="form-control"
                                                name="branch_stock[{{ $branch->id }}]"
                                                value="{{ old("branch_stock.{$branch->id}", 0) }}"
                                                {{ $isCurrent ? '' : 'disabled' }}>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">💼 السعر شامل الضريبة؟</label>
                                            <select class="form-select" name="branch_tax_included[{{ $branch->id }}]"
                                                {{ $isCurrent ? '' : 'disabled' }}>
                                                <option value="0">❌ لا</option>
                                                <option value="1">✅ نعم</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label">٪ نسبة الضريبة</label>
                                        <input type="number" step="0.01" class="form-control"
                                            name="branch_tax_percentage[{{ $branch->id }}]"
                                            value="{{ old("branch_tax_percentage.{$branch->id}") }}"
                                            {{ $isCurrent ? '' : 'disabled' }}>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-success">💾 حفظ المنتج</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">↩️ إلغاء</a>
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
    // 💡 حساب السعر النهائي بناءً على الضريبة
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

    // ✅ نسخ البيانات لجميع الفروع + تعطيل الحقول حسب اختيار الضريبة
    function copyProductDetailsToBranches() {
        let salePrice = $('#sale_price').val();
        let purchasePrice = $('#purchase_price').val();
        let stock = $('#stock').val();
        let taxIncluded = $('#is_tax_included').val();
        let taxPercentage = $('#tax_percentage').val();

        $('[name^="branch_price"]').val(salePrice);
        $('[name^="branch_purchase_price"]').val(purchasePrice);
        $('[name^="branch_stock"]').val(stock);
        $('[name^="branch_tax_included"]').val(taxIncluded).prop('disabled', taxIncluded === '1');
        $('[name^="branch_tax_percentage"]').val(taxPercentage).prop('readonly', taxIncluded === '1');
    }

    // 🔒 قفل/فتح حقل نسبة الضريبة فوق حسب اختيار السعر شامل الضريبة
    function toggleTaxPercentageField() {
        let isIncluded = $('#is_tax_included').val();
        if (isIncluded === '1') {
            $('#tax_percentage').prop('readonly', true).addClass('bg-light');
        } else {
            $('#tax_percentage').prop('readonly', false).removeClass('bg-light');
        }
    }

    // 🎯 الأحداث
    $('#sale_price, #purchase_price, #stock, #tax_percentage, #is_tax_included').on('input change', function () {
        calculateFinalPrice();
        toggleTaxPercentageField();
        copyProductDetailsToBranches();
    });

    $('#is_tax_included').on('change', function () {
        toggleTaxPercentageField();
        copyProductDetailsToBranches();
    });

    // ✅ توليد باركود تلقائي إذا لم يُدخل المستخدم باركود
    function generateRandomBarcode(length = 9) {
        const digits = '0123456789';
        let barcode = '';
        for (let i = 0; i < length; i++) {
            barcode += digits.charAt(Math.floor(Math.random() * digits.length));
        }
        return barcode;
    }

    function renderBarcode(code) {
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
        const code = $(this).val();
        renderBarcode(code);
    });
    // 🖨️ طباعة الباركود مع عدد النسخ
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


    // ✅ عند تحميل الصفحة
    $(document).ready(function () {
        let currentCode = $('#barcode').val().trim();

        // توليد تلقائي إذا الحقل فارغ
        if (!currentCode) {
            currentCode = generateRandomBarcode();
            $('#barcode').val(currentCode);
        }

        renderBarcode(currentCode);

        calculateFinalPrice();
        toggleTaxPercentageField();
        copyProductDetailsToBranches();
    });

    // 🚫 تحديد حد أقصى لعدد كلمات اسم المنتج
    $('#name').on('input', function () {
        const maxWords = 10;
        const words = $(this).val().trim().split(/\s+/);
        if (words.length > maxWords) {
            alert(`🚫 الحد الأقصى لعدد كلمات اسم المنتج هو ${maxWords} كلمة.`);
            $(this).val(words.slice(0, maxWords).join(' '));
        }
    });

    // ✅ تشغيل كل شيء عند تحميل الصفحة
    $(document).ready(function () {
        const initialCode = $('#barcode').val();
        if (initialCode) {
            JsBarcode("#barcode-preview", initialCode, {
                format: "CODE128",
                width: 2,
                height: 40,
                displayValue: true
            });
        }

        calculateFinalPrice();
        toggleTaxPercentageField();
        copyProductDetailsToBranches();
    });
});
</script>
@endpush
