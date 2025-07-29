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
                                    <option value="{{ $product->id }}" data-price="{{ $product->sale_price }}"
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

        <!-- الإجماليات -->
        <div class="form-group">
            <label>إجمالي الفاتورة قبل الخصم:</label>
            <input type="number" id="total-before-discount" class="form-control" readonly>
        </div>

        <div class="form-group">
            <label>إجمالي الفاتورة بعد الخصم:</label>
            <input type="number" id="total-after-discount" class="form-control" readonly>
        </div>

        <!-- الدفعة المبدئية -->
        <div class="form-group">
            <label>الدفعة المقدّمة (اختياري):</label>
            <input type="number" name="initial_payment" id="initial-payment" class="form-control" step="0.01" value="{{ old('initial_payment', $initialPayment ?? 0) }}">
        </div>

        <!-- المبلغ المدفوع -->
        <div class="form-group">
            <label>المبلغ المدفوع:</label>
            <input type="number" id="paid-amount" class="form-control" value="{{ number_format($initialPayment ?? 0, 2) }}" readonly>
        </div>

        <!-- المبلغ المتبقي -->
        <div class="form-group">
            <label>المبلغ المتبقي:</label>
            <input type="number" id="remaining-amount" class="form-control" value="{{ number_format($remaining ?? 0, 2) }}" readonly>
        </div>

        <!-- الدفعة الجديدة -->
        <div class="form-group">
            <label>دفعة جديدة (اختياري):</label>
            <input type="number" name="new_payment" class="form-control" step="0.01" value="0">
        </div>

        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
        <a href="{{ route('admin.sales.index') }}" class="btn btn-light">إلغاء</a>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let itemIndex = {{ count($sale->saleItems) }};

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
        let total = 0;
        document.querySelectorAll('.item-total').forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        const discount = parseFloat(document.querySelector('[name="discount"]').value) || 0;
        const afterDiscount = total - discount;

        document.getElementById('total-before-discount').value = total.toFixed(2);
        document.getElementById('total-after-discount').value = (afterDiscount >= 0 ? afterDiscount : 0).toFixed(2);

        // إعادة حساب المدفوع والمتبقي
        const initialPayment = parseFloat(document.getElementById('initial-payment').value) || 0;
        document.getElementById('paid-amount').value = initialPayment.toFixed(2);
        document.getElementById('remaining-amount').value = Math.max(afterDiscount - initialPayment, 0).toFixed(2);
    }

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input')) {
            const index = e.target.dataset.index;
            calculateItemTotal(index);
        }

        if (e.target.name.includes('[sale_price]')) {
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
    });

    document.getElementById('add-item').addEventListener('click', function () {
        const wrapper = document.getElementById('items-wrapper');

        let productOptions = '<option value="">-- اختر --</option>';
        @foreach($products as $product)
            productOptions += `<option value="{{ $product->id }}" data-price="{{ $product->sale_price }}">{{ $product->name }}</option>`;
        @endforeach

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
</script>
@endpush