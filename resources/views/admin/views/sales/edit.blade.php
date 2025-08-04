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

        <!-- اختيار الفرع -->
        <div class="form-group">
            <label>اختر الفرع:</label>
            <select name="branch_id" class="form-control" required>
                <option value="">-- اختر فرع --</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ (old('branch_id') ?? $sale->branch_id ?? null) == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <hr>

        <!-- الأصناف -->
        <h5>الأصناف</h5>
        <div id="items-wrapper">
            @foreach($sale->saleItems as $index => $item)
                <div class="item-row border p-3 mb-2">
                    <div class="form-row">
                        <div class="col-md-4">
                            <label>المنتج:</label>
                            <select name="items[{{ $index }}][product_id]" class="form-control product-select" data-index="{{ $index }}" required>
                                <option value="">-- اختر --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                        data-price="{{ $product->sale_price }}"
                                        data-tax-percentage="{{ $product->tax_percentage }}"
                                        data-tax-included="{{ $product->is_tax_included ? 1 : 0 }}"
                                        {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>الكمية:</label>
                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity-input" required min="1" value="{{ $item->quantity }}" data-index="{{ $index }}">
                        </div>
                        <div class="col-md-2">
                            <label>سعر البيع:</label>
                            <input type="number" name="items[{{ $index }}][sale_price]" class="form-control sale-price" required min="0" step="0.01" value="{{ $item->sale_price }}">
                        </div>
                        <div class="col-md-2">
                            <label>إجمالي الصنف:</label>
                            <input type="number" class="form-control item-total" readonly>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger remove-item">حذف</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button" class="btn btn-secondary mb-3" id="add-item">+ إضافة صنف</button>

        <!-- الخصم -->
        <div class="form-group">
            <label>الخصم (جنيه):</label>
            <input type="number" step="0.01" name="discount" class="form-control" value="{{ old('discount', $sale->discount ?? 0) }}">
        </div>

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
                    <input type="number" id="remaining-amount" class="form-control" value="{{ number_format($remaining ?? 0, 2) }}" readonly>
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

    {{-- ملخص الفاتورة --}}
    <h5 class="mt-4">ملخص الفاتورة:</h5>
    <table class="table table-bordered text-center">
        <tr>
            <th>الإجمالي قبل الضريبة</th>
            <td>
                {{ number_format(
                    $sale->saleItems->sum(function($item) use ($products) {
                        $product = $products->where('id', $item->product_id)->first();
                        $taxRate = $product?->tax_percentage ?? 0;
                        if ($product && $product->is_tax_included) {
                            $base = $item->sale_price / (1 + $taxRate / 100);
                        } else {
                            $base = $item->sale_price;
                        }
                        return $base * $item->quantity;
                    }), 2) }} جنيه
            </td>
        </tr>
        <tr>
            <th>قيمة الضريبة</th>
            <td>
                {{ number_format(
                    $sale->saleItems->sum(function($item) use ($products) {
                        $product = $products->where('id', $item->product_id)->first();
                        $taxRate = $product?->tax_percentage ?? 0;
                        if ($product && $product->is_tax_included) {
                            $base = $item->sale_price / (1 + $taxRate / 100);
                            $taxValue = $item->sale_price - $base;
                        } else {
                            $base = $item->sale_price;
                            $taxValue = $base * ($taxRate / 100);
                        }
                        return $taxValue * $item->quantity;
                    }), 2) }} جنيه
            </td>
        </tr>
        <tr>
            <th>الإجمالي بعد الضريبة</th>
            <td>
                {{ number_format(
                    $sale->saleItems->sum(function($item) use ($products) {
                        $product = $products->where('id', $item->product_id)->first();
                        $taxRate = $product?->tax_percentage ?? 0;
                        if ($product && $product->is_tax_included) {
                            $priceWithTax = $item->sale_price;
                        } else {
                            $base = $item->sale_price;
                            $taxValue = $base * ($taxRate / 100);
                            $priceWithTax = $base + $taxValue;
                        }
                        return $priceWithTax * $item->quantity;
                    }), 2) }} جنيه
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
<script>
    const branchProducts = @json($branchProducts);
</script>
<script>

    function getSelectedBranchId() {
        return document.querySelector('[name="branch_id"]').value;
    }

    function getProductOptions(branchId, selectedId = null) {
        let options = '<option value="">-- اختر --</option>';
        if (branchProducts[branchId]) {
            branchProducts[branchId].forEach(prod => {
                options += `<option value="${prod.id}" data-price="${prod.price}" data-tax-percentage="${prod.tax_percentage}" data-tax-included="${prod.tax_included}" data-stock="${prod.stock}" ${selectedId == prod.id ? 'selected' : ''}>${prod.name} (متوفر: ${prod.stock})</option>`;
            });
        }
        return options;
    }

    function updateProductSelects() {
        const branchId = getSelectedBranchId();
        document.querySelectorAll('.product-select').forEach(select => {
            const selected = select.value;
            select.innerHTML = getProductOptions(branchId, selected);
            // حدث السعر والضريبة إذا تغيرت بيانات المنتج
            const selectedOption = select.querySelector(`option[value="${selected}"]`);
            if (selectedOption) {
                const priceInput = select.closest('.item-row').querySelector('.sale-price');
                priceInput.value = selectedOption.getAttribute('data-price') || 0;
                calculateInvoiceTotals();
            }
        });
    }

    function calculateItemTotal(index) {
        const quantityInput = document.querySelector(`[name="items[${index}][quantity]"]`);
        const priceInput = document.querySelector(`[name="items[${index}][sale_price]"]`);
        const totalInputs = document.querySelectorAll('.item-total');
        const totalInput = totalInputs[index];

        if (quantityInput && priceInput && totalInput) {
            const quantity = parseFloat(quantityInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            totalInput.value = (quantity * price).toFixed(2);
            calculateInvoiceTotals();
        }
    }

    function calculateInvoiceTotals() {
        let totalBeforeTax = 0;
        let totalTax = 0;
        let totalAfterTax = 0;

        document.querySelectorAll('.item-row').forEach((row, idx) => {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            const priceInput = row.querySelector('.sale-price');
            const totalInput = row.querySelector('.item-total');

            const quantity = parseFloat(quantityInput?.value) || 0;
            const salePrice = parseFloat(priceInput?.value) || 0;

            let taxRate = 0, isTaxIncluded = false;
            if (productSelect && productSelect.selectedOptions.length) {
                const selected = productSelect.selectedOptions[0];
                taxRate = parseFloat(selected.getAttribute('data-tax-percentage')) || 0;
                isTaxIncluded = selected.getAttribute('data-tax-included') === '1';
            }

            let base = salePrice, taxValue = 0, priceWithTax = salePrice;

            if (isTaxIncluded) {
                base = salePrice / (1 + taxRate / 100);
                taxValue = salePrice - base;
                priceWithTax = salePrice;
            } else {
                base = salePrice;
                taxValue = base * (taxRate / 100);
                priceWithTax = base + taxValue;
            }

            totalBeforeTax += base * quantity;
            totalTax += taxValue * quantity;
            totalAfterTax += priceWithTax * quantity;

            if (totalInput) totalInput.value = (priceWithTax * quantity).toFixed(2);
        });

        document.getElementById('total-before-tax').value = totalBeforeTax.toFixed(2);
        document.getElementById('total-tax').value = totalTax.toFixed(2);
        document.getElementById('total-after-tax').value = totalAfterTax.toFixed(2);

        const discount = parseFloat(document.querySelector('[name="discount"]').value) || 0;
        const afterDiscount = Math.max(totalAfterTax - discount, 0);

        const initialPayment = parseFloat(document.getElementById('initial-payment').value) || 0;
        document.getElementById('remaining-amount').value = Math.max(afterDiscount - initialPayment, 0).toFixed(2);

        const newPaymentInput = document.querySelector('[name="new_payment"]');
        if (newPaymentInput) {
            newPaymentInput.max = document.getElementById('remaining-amount').value.replace(/[^\d.]/g, '');
            if (parseFloat(newPaymentInput.value) > parseFloat(newPaymentInput.max)) {
                newPaymentInput.value = newPaymentInput.max;
            }
        }
    }

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input')) {
            const index = e.target.dataset.index;
            calculateItemTotal(index);
        }
        if (e.target.name && e.target.name.includes('[sale_price]')) {
            const index = e.target.closest('.item-row').querySelector('.quantity-input').dataset.index;
            calculateItemTotal(index);
        }
        if (e.target.name === "discount" || e.target.name === "initial_payment") {
            calculateInvoiceTotals();
        }
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('product-select')) {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            const index = e.target.dataset.index;
            const priceInput = document.querySelector(`[name="items[${index}][sale_price]"]`);
            if (priceInput && price) {
                priceInput.value = price;
                calculateItemTotal(index);
            }
        }
        if (e.target.name === "branch_id") {
            updateProductSelects();
            calculateInvoiceTotals();
        }
    });

    document.getElementById('add-item').addEventListener('click', function () {
        const wrapper = document.getElementById('items-wrapper');
        const branchId = getSelectedBranchId();
        let productOptions = getProductOptions(branchId);

        const newRow = document.createElement('div');
        newRow.classList.add('item-row', 'border', 'p-3', 'mb-2');
        newRow.innerHTML = `
            <div class="form-row">
                <div class="col-md-4">
                    <label>المنتج:</label>
                    <select name="items[${itemIndex}][product_id]" class="form-control product-select" data-index="${itemIndex}" required>
                        ${productOptions}
                    </select>
                </div>
                <div class="col-md-2">
                    <label>الكمية:</label>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity-input" value="1" min="1" data-index="${itemIndex}">
                </div>
                <div class="col-md-2">
                    <label>سعر البيع:</label>
                    <input type="number" name="items[${itemIndex}][sale_price]" class="form-control sale-price" value="0" step="0.01">
                </div>
                <div class="col-md-2">
                    <label>إجمالي الصنف:</label>
                    <input type="number" class="form-control item-total" value="0" readonly>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-item">حذف</button>
                </div>
            </div>
        `;
        wrapper.appendChild(newRow);
        itemIndex++;
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.item-row').remove();
            calculateInvoiceTotals();
        }
    });

    // عند تحميل الصفحة لأول مرة
    document.querySelectorAll('.item-row').forEach((row, idx) => {
        calculateItemTotal(idx);
    });

    document.querySelector('[name="new_payment"]').addEventListener('input', function(e) {
        let remaining = document.getElementById('remaining-amount').value.replace(/[^\d.]/g, '');
        remaining = parseFloat(remaining) || 0;
        let val = e.target.value.replace(/[^\d.]/g, '');
        val = parseFloat(val) || 0;
        if (val > remaining) {
            e.target.value = remaining.toFixed(2);
        }
    });

    // تحديث المنتجات عند تغيير الفرع أول مرة
    document.querySelector('[name="branch_id"]').addEventListener('change', updateProductSelects);
    // تحديث المنتجات عند تحميل الصفحة
    updateProductSelects();
</script>
@endpush
