@extends('layouts.app')

@section('title', 'إضافة فاتورة جديدة')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">إضافة فاتورة مبيعات جديدة</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.sales.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="customer_id" class="form-label">اختيار عميل مسجل</label>
            <select name="customer_id" id="customer_id" class="form-control">
                <option value="">-- اختر عميل --</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="customer_name" class="form-label">أو أدخل اسم عميل يدوي</label>
            <input type="text" name="customer_name" id="customer_name" class="form-control">
        </div>

        <hr>

        <h4>الأصناف المباعة</h4>

        <table class="table table-bordered" id="items-table">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>الكمية</th>
                    <th>سعر البيع</th>
                    <th>إجمالي الصنف</th>
                    <th>الإجراء</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="items[0][product_id]" class="form-control product-select" data-index="0">
                            <option value="">اختر منتج</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->sale_price }}">
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[0][quantity]" class="form-control quantity-input" value="1" min="1" data-index="0">
                    </td>
                    <td>
                        <input type="number" name="items[0][sale_price]" class="form-control sale-price" value="0" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control item-total" value="0" readonly>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm btn-remove-row">حذف</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-success mb-3" id="add-row">إضافة صنف</button>

        <div class="form-group">
            <label>الخصم (جنيه)</label>
            <input type="number" step="0.01" name="discount" class="form-control" value="0">
        </div>

        <div class="form-group">
            <label>إجمالي الفاتورة قبل الخصم</label>
            <input type="number" id="total-before-discount" class="form-control" readonly>
        </div>

        <div class="form-group">
            <label>إجمالي الفاتورة بعد الخصم</label>
            <input type="number" id="total-after-discount" class="form-control" readonly>
        </div>

        <div class="form-group">
            <label>الدفعة المقدّمة (اختياري)</label>
            <input type="number" name="initial_payment" class="form-control" step="0.01" value="0">
        </div>

        <br>
        <button type="submit" class="btn btn-primary">حفظ الفاتورة</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function calculateInvoiceTotals() {
            const itemTotals = document.querySelectorAll('.item-total');
            let totalBeforeDiscount = 0;

            itemTotals.forEach(input => {
                totalBeforeDiscount += parseFloat(input.value) || 0;
            });

            const discount = parseFloat(document.querySelector('[name="discount"]').value) || 0;
            const totalAfterDiscount = totalBeforeDiscount - discount;

            document.getElementById('total-before-discount').value = totalBeforeDiscount.toFixed(2);
            document.getElementById('total-after-discount').value = (totalAfterDiscount >= 0 ? totalAfterDiscount : 0).toFixed(2);
        }

        function calculateItemTotal(index) {
            const quantityInput = document.querySelector(`[name="items[${index}][quantity]"]`);
            const priceInput = document.querySelector(`[name="items[${index}][sale_price]"]`);
            const totalInputs = document.querySelectorAll('.item-total');
            const totalInput = totalInputs[index];

            if (quantityInput && priceInput && totalInput) {
                const quantity = parseFloat(quantityInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const total = quantity * price;
                totalInput.value = total.toFixed(2);
                calculateInvoiceTotals();
            }
        }

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

        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('quantity-input')) {
                const index = e.target.dataset.index;
                calculateItemTotal(index);
            }

            if (e.target.name === 'discount') {
                calculateInvoiceTotals();
            }
        });

        document.getElementById('add-row').addEventListener('click', function () {
            const tableBody = document.querySelector('#items-table tbody');
            const rowCount = tableBody.querySelectorAll('tr').length;

            let productOptions = '<option value="">اختر منتج</option>';
            @foreach($products as $product)
                productOptions += `<option value="{{ $product->id }}" data-price="{{ $product->sale_price }}">{{ $product->name }}</option>`;
            @endforeach

            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>
                    <select name="items[${rowCount}][product_id]" class="form-control product-select" data-index="${rowCount}">
                        ${productOptions}
                    </select>
                </td>
                <td>
                    <input type="number" name="items[${rowCount}][quantity]" class="form-control quantity-input" value="1" min="1" data-index="${rowCount}">
                </td>
                <td>
                    <input type="number" name="items[${rowCount}][sale_price]" class="form-control sale-price" value="0" readonly>
                </td>
                <td>
                    <input type="number" class="form-control item-total" value="0" readonly>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm btn-remove-row">حذف</button>
                </td>
            `;
            tableBody.appendChild(newRow);
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('btn-remove-row')) {
                e.target.closest('tr').remove();
                calculateInvoiceTotals();
            }
        });
    });
</script>
@endpush
