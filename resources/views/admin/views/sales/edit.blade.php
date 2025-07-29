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
                        <div class="col-md-5">
                            <label>المنتج:</label>
                            <select name="items[{{ $index }}][product_id]" class="form-control" required>
                                <option value="">-- اختر --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                            data-price="{{ $product->sale_price }}"
                                            {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>الكمية:</label>
                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control" required min="1" value="{{ $item->quantity }}">
                        </div>
                        <div class="col-md-3">
                            <label>سعر البيع:</label>
                            <input type="number" name="items[{{ $index }}][sale_price]" class="form-control" required min="0" step="0.01" value="{{ $item->sale_price }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger remove-item">حذف</button>
                        </div>
                        <div class="col-md-2">
                            <label>إجمالي الصنف:</label>
                            <input type="number" class="form-control item-total" readonly>
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
        <div class="form-group">
        <label>إجمالي الفاتورة قبل الخصم:</label>
        <input type="number" id="total-before-discount" class="form-control" readonly>
        </div>

        <div class="form-group">
            <label>إجمالي الفاتورة بعد الخصم:</label>
            <input type="number" id="total-after-discount" class="form-control" readonly>
        </div>


        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
        <a href="{{ route('admin.sales.index') }}" class="btn btn-light">إلغاء</a>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let itemIndex = {{ count($sale->saleItems) }};

    document.getElementById('add-item').addEventListener('click', function () {
        const wrapper = document.getElementById('items-wrapper');
        const newItem = document.createElement('div');
        newItem.classList.add('item-row', 'border', 'p-3', 'mb-2');

        newItem.innerHTML = `
            <div class="form-row">
                <div class="col-md-5">
                    <label>المنتج:</label>
                    <select name="items[${itemIndex}][product_id]" class="form-control" required>
                        <option value="">-- اختر --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->sale_price }}">
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>الكمية:</label>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control" required min="1" value="1">
                </div>
                <div class="col-md-3">
                    <label>سعر البيع:</label>
                    <input type="number" name="items[${itemIndex}][sale_price]" class="form-control" required min="0" step="0.01">
                </div> <div class="col-md-3">
                    <label>اجمالي الصنف:</label>
                    <input type="number" name="items[${itemIndex}][sale_price]" class="form-control" required min="0" step="0.01">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-item">حذف</button>
                </div>
            </div>
        `;
        wrapper.appendChild(newItem);
        itemIndex++;
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.item-row').remove();
        }
    });

    // إدخال السعر تلقائي عند اختيار المنتج
    document.addEventListener('change', function(e) {
        if (e.target.matches('select[name^="items"]')) {
            const select = e.target;
            const selectedOption = select.options[select.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            if (price) {
                const parentRow = select.closest('.item-row');
                const priceInput = parentRow.querySelector('input[name*="[sale_price]"]');
                if (priceInput && priceInput.value == "") {
                    priceInput.value = price;
                }
            }
        }
    });
    function calculateItemTotal(row) {
    const quantityInput = row.querySelector('input[name*="[quantity]"]');
    const priceInput = row.querySelector('input[name*="[sale_price]"]:not(.item-total)');
    const totalInput = row.querySelector('.item-total');

    if (quantityInput && priceInput && totalInput) {
        const quantity = parseFloat(quantityInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        totalInput.value = (quantity * price).toFixed(2);
    }

    calculateInvoiceTotals();
}

function calculateInvoiceTotals() {
    let total = 0;
    document.querySelectorAll('.item-total').forEach(input => {
        total += parseFloat(input.value) || 0;
    });

    const discountInput = document.querySelector('[name="discount"]');
    const discount = parseFloat(discountInput.value) || 0;
    const afterDiscount = total - discount;

    document.getElementById('total-before-discount').value = total.toFixed(2);
    document.getElementById('total-after-discount').value = (afterDiscount >= 0 ? afterDiscount : 0).toFixed(2);
}

// حساب تلقائي عند إدخال كمية أو سعر
document.addEventListener('input', function(e) {
    if (e.target.name.includes('[quantity]') || (e.target.name.includes('[sale_price]') && !e.target.classList.contains('item-total'))) {
        const row = e.target.closest('.item-row');
        calculateItemTotal(row);
    }

    if (e.target.name === "discount") {
        calculateInvoiceTotals();
    }
});

// حساب عند تحميل الصفحة
document.querySelectorAll('.item-row').forEach(row => {
    calculateItemTotal(row);
});

</script>
@endpush
