@extends('layouts.app')

@section('title', 'تعديل فاتورة شراء')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">تعديل فاتورة شراء</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.purchases.update', $purchase->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>المورد</label>
            <select name="supplier_id" class="form-control" required>
                <option value="">-- اختر المورد --</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ $supplier->id == $purchase->supplier_id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>ملاحظات</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $purchase->notes) }}</textarea>
        </div>

        <hr>
        <h5>المنتجات</h5>

        <table class="table table-bordered text-center" id="itemsTable">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>الكمية</th>
                    <th>سعر الشراء</th>
                    <th>الإجمالي</th>
                    <th>حذف</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $index => $item)
                    <tr>
                        <td>
                            <select name="items[{{ $index }}][product_id]" class="form-control" required>
                                <option value="">-- اختر المنتج --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ $product->id == $item->product_id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity"
                                   min="1" value="{{ $item->quantity }}" required>
                        </td>
                        <td>
                            <input type="number" name="items[{{ $index }}][purchase_price]" class="form-control price"
                                   min="0" step="0.01" value="{{ $item->unit_price }}" required>
                        </td>
                        <td class="subtotal">{{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-item">✖</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="button" id="addItemBtn" class="btn btn-sm btn-secondary">+ إضافة منتج آخر</button>

        <div class="form-group mt-3">
            <label>المبلغ المدفوع (كاش)</label>
            <input type="number" name="paid_amount" class="form-control" min="0" step="0.01"
                   value="{{ old('paid_amount', $purchase->paid_amount) }}" required>
        </div>

        <div class="form-group">
            <label>المتبقي (يُحسب تلقائيًا)</label>
            <input type="text" id="remainingAmount" class="form-control" disabled>
        </div>

        <div class="form-group mt-3">
            <strong>الإجمالي الكلي: <span id="totalAmount">0.00</span> جنيه</strong>
        </div>

        <button type="submit" class="btn btn-success">💾 تحديث الفاتورة</button>
    </form>


    <h5>المدفوعات السابقة:</h5>
<table class="table">
    <thead>
        <tr>
            <th>المبلغ</th>
            <th>تاريخ الدفع</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchase->payments as $payment)
            <tr>
                <td>{{ number_format($payment->amount, 2) }}</td>
                <td>{{ $payment->payment_date }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</div>
@endsection

@push('scripts')
<script>
    let index = {{ count($purchase->items) }};

    document.getElementById('addItemBtn').addEventListener('click', function () {
        const row = `
        <tr>
            <td>
                <select name="items[${index}][product_id]" class="form-control" required>
                    <option value="">-- اختر المنتج --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" name="items[${index}][quantity]" class="form-control quantity" min="1" value="1" required></td>
            <td><input type="number" name="items[${index}][purchase_price]" class="form-control price" min="0" step="0.01" value="0.00" required></td>
            <td class="subtotal">0.00</td>
            <td><button type="button" class="btn btn-danger btn-sm remove-item">✖</button></td>
        </tr>
        `;
        document.querySelector('#itemsTable tbody').insertAdjacentHTML('beforeend', row);
        index++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('tr').remove();
            calculateTotal();
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity') || e.target.classList.contains('price') || e.target.name === "paid_amount") {
            const row = e.target.closest('tr');
            if (row) {
                const qty = parseFloat(row.querySelector('.quantity').value) || 0;
                const price = parseFloat(row.querySelector('.price').value) || 0;
                const subtotal = (qty * price).toFixed(2);
                row.querySelector('.subtotal').textContent = formatNumber(subtotal);
            }
            calculateTotal();
        }
    });

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal').forEach(el => {
            let value = el.textContent.replace(/,/g, '');
            total += parseFloat(value) || 0;
        });

        document.getElementById('totalAmount').textContent = formatNumber(total);

        const paid = parseFloat(document.querySelector('input[name="paid_amount"]').value) || 0;
        const remaining = total - paid;
        document.getElementById('remainingAmount').value = formatNumber(remaining);
    }

    function formatNumber(number) {
        return Number(number).toLocaleString('en', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    window.onload = calculateTotal;
</script>
@endpush
