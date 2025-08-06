@extends('layouts.app')

@section('title', 'إضافة فاتورة شراء')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">إضافة فاتورة شراء جديدة</h4>

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

    <form action="{{ route('admin.purchases.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>المورد</label>
            <select name="supplier_id" class="form-control" required>
                <option value="">-- اختر المورد --</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>ملاحظات</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
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
                <!-- صف مبدئي -->
                @include('admin.views.purchases.partials.purchase_row', ['index' => 0, 'products' => $products])
            </tbody>
        </table>

        <button type="button" id="addItemBtn" class="btn btn-sm btn-secondary">+ إضافة منتج آخر</button>

        <div class="form-group mt-3">
            <label>المبلغ المدفوع (كاش)</label>
            <input type="number" name="paid_amount" class="form-control" min="0" step="0.01" value="0" required>
        </div>

        <div class="form-group">
            <label>المتبقي (يُحسب تلقائيًا)</label>
            <input type="text" id="remainingAmount" class="form-control" disabled>
        </div>

        <div class="form-group mt-3">
            <strong>الإجمالي الكلي: <span id="totalAmount">0.00</span> جنيه</strong>
        </div>

        <button type="submit" class="btn btn-success">💾 حفظ الفاتورة</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let index = 1;

    // إضافة صف جديد
    document.getElementById('addItemBtn').addEventListener('click', function () {
        const existingProducts = Array.from(document.querySelectorAll('select[name^="items"]')).map(s => s.value);
        const newRow = `
        <tr>
            <td>
                <select name="items[${index}][product_id]" class="form-control product-select" required>
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
        document.querySelector('#itemsTable tbody').insertAdjacentHTML('beforeend', newRow);
        index++;
    });

    // حذف صنف
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('tr').remove();
            calculateAll();
        }
    });

    // مراقبة تغييرات الكمية والسعر
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('quantity') || e.target.classList.contains('price')) {
            const row = e.target.closest('tr');
            const qty = parseFloat(row.querySelector('.quantity').value) || 0;
            const price = parseFloat(row.querySelector('.price').value) || 0;
            const subtotal = (qty * price).toFixed(2);
            row.querySelector('.subtotal').textContent = subtotal;
            calculateAll();
        }

        if (e.target.name === 'paid_amount') {
            calculateAll();
        }
    });

    // منع تكرار نفس المنتج
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('product-select')) {
            const selected = e.target.value;
            const allSelected = Array.from(document.querySelectorAll('.product-select')).map(s => s.value);
            const count = allSelected.filter(v => v === selected).length;
            if (count > 1) {
                alert('هذا المنتج تم اختياره بالفعل!');
                e.target.value = '';
            }
        }
    });

    function calculateAll() {
        let total = 0;
        document.querySelectorAll('.subtotal').forEach(el => {
            total += parseFloat(el.textContent) || 0;
        });
        document.getElementById('totalAmount').textContent = total.toFixed(2);

        const paid = parseFloat(document.querySelector('input[name="paid_amount"]').value) || 0;
        const remaining = total - paid;
        document.getElementById('remainingAmount').value = remaining.toFixed(2);
    }
</script>
@endpush
