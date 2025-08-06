@extends('layouts.app')

@section('title', 'تعديل الفاتورة')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">تعديل الفاتورة رقم #{{ $sale->id }}</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.sales.update', $sale->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- اختيار العميل -->
        <div class="form-group">
            <label>اختر العميل (اختياري):</label>
            <select name="customer_id" class="form-control">
                <option value="">-- عميل يدوي --</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ $sale->customer_id == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- اسم العميل اليدوي -->
        <div class="form-group">
            <label>أو اسم العميل يدويًا:</label>
            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $sale->customer_name) }}">
        </div>
        <!-- الأصناف -->
        <h4 class="mt-4">الأصناف المباعة</h4>
        <div class="table-responsive">
            <table class="table table-bordered" id="items-table">
                <thead>
                    <tr>
                        <th width="30%">المنتج</th>
                        <th>الكمية</th>
                        <th>السعر قبل الضريبة</th>
                        <th>الضريبة المضافة</th>
                        <th>السعر بعد الضريبة</th>
                        <th>نوع السعر</th>
                        <th>إجمالي الصنف</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->saleItems as $index => $item)
                        @php
                            $product = collect($branchProducts)->firstWhere('id', $item->product_id);
                            $taxRate = $product['tax_percentage'] ?? 0;
                            $taxIncluded = $product['tax_included'] ?? 0;
                            $base = $item->sale_price;
                            $tax = 0;
                            $final = $item->sale_price;
                            $taxType = 'بدون ضريبة';
                            if ($taxRate > 0) {
                                if ($taxIncluded) {
                                    $base = $item->sale_price / (1 + $taxRate / 100);
                                    $tax = $item->sale_price - $base;
                                    $taxType = "شامل ($taxRate%)";
                                } else {
                                    $tax = $base * ($taxRate / 100);
                                    $final = $base + $tax;
                                    $taxType = "غير شامل ($taxRate%)";
                                }
                            }
                        @endphp
                        <tr>
                            <td class="product-cell">
                                <select name="items[{{ $index }}][product_id]" class="form-control product-select" data-index="{{ $index }}">
                                    <option value="">-- اختر منتج --</option>
                                    @foreach($branchProducts as $prod)
                                        <option value="{{ $prod['id'] }}"
                                            data-price="{{ $prod['price'] }}"
                                            data-barcode="{{ $prod['barcode'] }}"
                                            data-category="{{ $prod['category_id'] }}"
                                            data-brand="{{ $prod['brand_id'] }}"
                                            data-tax-included="{{ $prod['tax_included'] }}"
                                            data-tax-percentage="{{ $prod['tax_percentage'] }}"
                                            {{ $item->product_id == $prod['id'] ? 'selected' : '' }}>
                                            {{ $prod['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity-input" value="{{ $item->quantity }}" min="1"></td>
                            <td><input type="number" class="form-control base-price" value="{{ round($base,2) }}" readonly><input type="hidden" name="items[{{ $index }}][sale_price]" class="sale-price-hidden" value="{{ round($final,2) }}"></td>
                            <td><input type="number" class="form-control tax-value" value="{{ round($tax,2) }}" readonly></td>
                            <td><input type="number" class="form-control final-price" value="{{ round($final,2) }}" readonly></td>
                            <td><input type="text" class="form-control tax-type" value="{{ $taxType }}" readonly></td>
                            <td><input type="number" class="form-control item-total" readonly></td>
                            <td><button type="button" class="btn btn-danger btn-sm btn-remove-row">حذف</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-success mb-3" id="add-row">إضافة صنف</button>

        <!-- الخصم -->
        <div class="form-group">
            <label>الخصم (جنيه):</label>
        <input type="number" step="0.01" name="discount" id="discount" class="form-control" value="{{ old('discount', $sale->discount ?? 0) }}">        </div>

        <!-- الإجماليات بعد الأصناف مباشرة -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="form-group">
                    <label>الإجمالي قبل الضريبة:</label>
                    <input type="number" id="total-before-tax" class="form-control" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>قيمة الضريبة:</label>
                    <input type="number" id="total-tax" class="form-control" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>الإجمالي بعد الضريبة:</label>
                    <input type="number" id="total-after-tax" class="form-control" readonly>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>المبلغ المدفوع:</label>
                    <input type="number" name="initial_payment" id="initial-payment" class="form-control" step="0.01" value="{{ old('initial_payment', $initialPayment ?? 0) }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>المبلغ المتبقي:</label>
                    <div class="input-group">
                        <input type="number" id="remaining-amount" class="form-control" value="{{ number_format($remaining ?? 0, 2) }}" readonly>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>دفعة جديدة (اختياري):</label>
                    <input type="number" name="new_payment" class="form-control" step="0.01" min="0" value="0">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
        <a href="{{ route('admin.sales.index') }}" class="btn btn-light">إلغاء</a>
    </form>

    {{-- جدول ملخص الأصناف --}}
    <div class="table-responsive">
        <table class="table table-bordered text-center mt-4">
        <thead>
            <tr>
                <th>المنتج</th>
                <th>الكمية</th>
                <th>السعر قبل الضريبة</th>
                <th>نسبة الضريبة</th>
                <th>قيمة الضريبة</th>
                <th>السعر بعد الضريبة</th>
                <th>إجمالي الصنف</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $item)
                @php
                    $product = $products->where('id', $item->product_id)->first();
                    $taxRate = $product?->tax_percentage ?? 0;

                    if ($product && $product->is_tax_included) {
                        $base = $item->sale_price / (1 + $taxRate / 100);
                        $taxValue = $item->sale_price - $base;
                        $priceWithTax = $item->sale_price;
                    } else {
                        $base = $item->sale_price;
                        $taxValue = $base * ($taxRate / 100);
                        $priceWithTax = $base + $taxValue;
                    }
                    $subtotal = $priceWithTax * $item->quantity;
                @endphp
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($base, 2) }}</td>
                    <td>{{ $taxRate }}%</td>
                    <td>{{ number_format($taxValue, 2) }}</td>
                    <td>{{ number_format($priceWithTax, 2) }}</td>
                    <td>{{ number_format($subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    {{-- ملخص الفاتورة --}}
    <h5 class="mt-4">ملخص الفاتورة:</h5>
    <table class="table table-bordered text-center">
        <tr>
            <th>الإجمالي قبل الضريبة</th>
            <td>
                {{ number_format($sale->saleItems->sum(fn($item) => $item->base_price * $item->quantity), 2) }} جنيه
            </td>
        </tr>
        <tr>
            <th>قيمة الضريبة</th>
            <td>
                {{ number_format($sale->saleItems->sum(fn($item) => $item->tax_value * $item->quantity), 2) }} جنيه
            </td>
        </tr>
        <tr>
            <th>الإجمالي بعد الضريبة</th>
            <td>
                {{ number_format($sale->saleItems->sum(fn($item) => $item->sale_price * $item->quantity), 2) }} جنيه
            </td>
        </tr>
        @if($sale->discount > 0)
        <tr>
            <th>الخصم</th>
            <td>{{ number_format($sale->discount, 2) }} جنيه</td>
        </tr>
        @endif
        <tr>
            <th>الإجمالي بعد الخصم</th>
            <td>{{ number_format($sale->total, 2) }} جنيه</td>
        </tr>
    </table>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    const products = @json($branchProducts);

    function round(val) {
        return Math.round(val * 100) / 100;
    }

    function truncateName(name, wordLimit = 5) {
        const words = name.trim().split(/\s+/);
        if (words.length > wordLimit) {
            return words.slice(0, wordLimit).join(' ') + ' ...';
        }
        return name;
    }

    function updateIndexes() {
        $('#items-table tbody tr').each(function(i) {
            $(this).find('[name]').each(function() {
                $(this).attr('name', $(this).attr('name').replace(/items\[\d+\]/, `items[${i}]`));
            });
        });
    }

    function updateTotals() {
        let totalBefore = 0, totalTax = 0, totalAfter = 0;

        $('#items-table tbody tr').each(function() {
            const qty = parseFloat($(this).find('.quantity-input').val()) || 0;
            const base = parseFloat($(this).find('.base-price').val()) || 0;
            const tax = parseFloat($(this).find('.tax-value').val()) || 0;
            const final = parseFloat($(this).find('.final-price').val()) || 0;

            totalBefore += base * qty;
            totalTax += tax * qty;
            totalAfter += final * qty;

            $(this).find('.item-total').val(round(final * qty));
        });

        $('#total-before-tax').val(round(totalBefore));
        $('#total-tax').val(round(totalTax));
        $('#total-after-tax').val(round(totalAfter));

        const discount = parseFloat($('#discount').val()) || 0;
        const paid = parseFloat($('#initial-payment').val()) || 0;
        const remaining = Math.max(totalAfter - discount - paid, 0);
        $('#remaining-amount').val(round(remaining));
    }

    function applyFilters() {
        const cat = $('#category-filter').val();
        const brand = $('#brand-filter').val();

        $('.product-select').each(function() {
            const select = $(this);
            const currentVal = select.val();
            const currentProduct = products.find(p => p.id == currentVal);

            select.empty().append(`<option value="">-- اختر منتج --</option>`);

            if (currentProduct) {
                select.append(`<option value="${currentProduct.id}"
                    data-price="${currentProduct.price}"
                    data-barcode="${currentProduct.barcode}"
                    data-category="${currentProduct.category_id}"
                    data-brand="${currentProduct.brand_id}"
                    data-tax-included="${currentProduct.tax_included}"
                    data-tax-percentage="${currentProduct.tax_percentage}">
                    ${truncateName(currentProduct.name)} (خارج الفلتر)
                </option>`);
            }

            products.forEach(product => {
                const matchCat = !cat || product.category_id == cat;
                const matchBrand = !brand || product.brand_id == brand;

                if (matchCat && matchBrand && product.id != currentVal) {
                    select.append(`<option value="${product.id}"
                        data-price="${product.price}"
                        data-barcode="${product.barcode}"
                        data-category="${product.category_id}"
                        data-brand="${product.brand_id}"
                        data-tax-included="${product.tax_included}"
                        data-tax-percentage="${product.tax_percentage}">
                        ${truncateName(product.name)}
                    </option>`);
                }
            });

            select.val(currentVal);
            select.select2({ dir: "rtl", width: '100%' });
        });
    }

    function fillProductData(row, selectedOption) {
        const base = parseFloat(selectedOption.data('price')) || 0;
        const taxIncluded = selectedOption.data('tax-included') == 1;
        const taxRate = parseFloat(selectedOption.data('tax-percentage')) || 0;

        let basePrice = base, tax = 0, finalPrice = base, taxType = 'شامل';

        if (taxRate > 0) {
            if (taxIncluded) {
                basePrice = base / (1 + taxRate / 100);
                tax = base - basePrice;
                taxType = `شامل (${taxRate}%)`;
            } else {
                tax = base * (taxRate / 100);
                finalPrice = base + tax;
                taxType = `غير شامل (${taxRate}%)`;
            }
        } else {
            taxType = 'بدون ضريبة';
        }

        row.find('.base-price').val(round(basePrice));
        row.find('.sale-price-hidden').val(round(finalPrice));
        row.find('.tax-value').val(round(tax));
        row.find('.final-price').val(round(finalPrice));
        row.find('.tax-type').val(taxType);
        updateTotals();
    }

    function createRow(productId = '', quantity = 1) {
        const index = $('#items-table tbody tr').length;
        const row = $(`
            <tr>
                <td class="product-cell">
                    <select name="items[${index}][product_id]" class="form-control product-select" data-index="${index}">
                        <option value="">-- اختر منتج --</option>
                    </select>
                </td>
                <td><input type="number" name="items[${index}][quantity]" class="form-control quantity-input" value="${quantity}" min="1"></td>
                <td><input type="number" class="form-control base-price" readonly><input type="hidden" name="items[${index}][sale_price]" class="sale-price-hidden"></td>
                <td><input type="number" class="form-control tax-value" readonly></td>
                <td><input type="number" class="form-control final-price" readonly></td>
                <td><input type="text" class="form-control tax-type" readonly></td>
                <td><input type="number" class="form-control item-total" readonly></td>
                <td><button type="button" class="btn btn-danger btn-sm btn-remove-row">حذف</button></td>
            </tr>
        `);

        $('#items-table tbody').append(row);
        applyFilters();

        if (productId) {
            row.find('.product-select').val(productId).trigger('change');
        }
        row.find('.product-select').select2({ dir: "rtl", width: '100%' });
    }

    $('#add-row').click(function() {
        createRow();
    });

    $(document).on('change', '.product-select', function() {
        const selected = $(this).find('option:selected');
        fillProductData($(this).closest('tr'), selected);
    });

    $(document).on('input', '.quantity-input', function() {
        updateTotals();
    });

    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
        updateIndexes();
        updateTotals();
    });

    $('#barcode-search').on('input', function () {
        const val = this.value.trim();
        if (val.length >= 8) {
            const option = $(`.product-select option[data-barcode="${val}"]`).first();
            if (option.length) {
                let select = $('.product-select').filter(function() { return !this.value; }).first();
                if (!select.length) {
                    createRow();
                    select = $('#items-table tbody tr:last-child .product-select');
                }
                select.val(option.val()).trigger('change');
                this.value = '';
            }
        }
    });

    $('#category-filter, #brand-filter').change(applyFilters);
    $('#discount, #initial-payment').on('input', updateTotals);

    $('#customer_name').on('input', function() {
        if ($(this).val().trim()) {
            $('#customer_id').val('').trigger('change');
            $('#customer_id').parent().hide();
        } else {
            $('#customer_id').parent().show();
        }
    });

    // عند تحميل الصفحة: املى بيانات الأصناف القديمة
    $('#items-table tbody tr').each(function() {
        const select = $(this).find('.product-select');
        const selected = select.find('option:selected');
        fillProductData($(this), selected);
    });

    // تفعيل select2 للفلاتر والعملاء
    $('#customer_id, #category-filter, #brand-filter').select2({ dir: "rtl", width: '100%' });
});
</script>
@endpush

