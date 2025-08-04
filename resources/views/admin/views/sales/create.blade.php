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

        <div class="row mb-3">
            <div class="col-md-6">
                <label>الفرع <span class="text-danger">*</span></label>
                <select name="branch_id" id="branch_id" class="form-control" required>
                    <option value="">-- اختر فرع --</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <hr>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>بحث بالباركود</label>
                <input type="text" id="barcode-search" class="form-control" placeholder="اكتب الباركود">
            </div>
            <div class="col-md-4">
                <label>فلترة حسب التصنيف</label>
                <select id="category-filter" class="form-control">
                    <option value="">-- كل التصنيفات --</option>
                    @php $categories = $products->pluck('category')->unique()->filter(); @endphp
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label>فلترة حسب البراند</label>
                <select id="brand-filter" class="form-control">
                    <option value="">-- كل البراندات --</option>
                    @php $brands = $products->pluck('brand')->unique()->filter(); @endphp
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <h4>الأصناف المباعة</h4>

        <table class="table table-bordered" id="items-table">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>الكمية</th>
                    <th>السعر قبل الضريبة</th>
                    <th>الضريبة المضافة</th>
                    <th>السعر بعد الضريبة</th>
                    <th>نوع السعر</th>
                    <th>إجمالي الصنف</th>
                    <th>الإجراء</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <button type="button" class="btn btn-success mb-3" id="add-row">إضافة صنف</button>

        <div class="form-group">
            <label>الخصم (جنيه)</label>
            <input type="number" step="0.01" name="discount" class="form-control" value="0">
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <label>الإجمالي قبل الضريبة:</label>
                <input type="number" id="total-before-tax" class="form-control" readonly>
            </div>
            <div class="col-md-4">
                <label>قيمة الضريبة:</label>
                <input type="number" id="total-tax" class="form-control" readonly>
            </div>
            <div class="col-md-4">
                <label>الإجمالي بعد الضريبة:</label>
                <input type="number" id="total-after-tax" class="form-control" readonly>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <label>الدفعة المقدّمة:</label>
                <input type="number" name="initial_payment" id="initial-payment" class="form-control" value="0" step="0.01">
            </div>
            <div class="col-md-6">
                <label>المتبقي:</label>
                <input type="number" id="remaining-amount" class="form-control" readonly>
            </div>
        </div>

        <br>
        <button type="submit" class="btn btn-primary">حفظ الفاتورة</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const branchProducts = @json($branchProducts);
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.querySelector('#items-table tbody');

    function getSelectedBranchId() {
        return document.getElementById('branch_id').value;
    }

    function getProductOptions(branchId, selectedId = null) {
        let options = '<option value="">-- اختر منتج --</option>';
        if (branchProducts[branchId]) {
            branchProducts[branchId].forEach(prod => {
                options += `<option value="${prod.id}"
                    data-price="${prod.price}"
                    data-barcode="${prod.barcode}"
                    data-category="${prod.category_id}"
                    data-brand="${prod.brand_id}"
                    data-tax-included="${prod.tax_included}"
                    data-tax-percentage="${prod.tax_percentage}"
                    data-stock="${prod.stock}"
                    ${selectedId == prod.id ? 'selected' : ''}>
                    ${prod.name} (متوفر: ${prod.stock})
                </option>`;
            });
        }
        return options;
    }

    function createRow(index = null) {
        if (index === null) index = tbody.querySelectorAll('tr').length;
        const branchId = getSelectedBranchId();
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <select name="items[${index}][product_id]" class="form-control product-select" data-index="${index}">
                    ${getProductOptions(branchId)}
                </select>
            </td>
            <td><input type="number" name="items[${index}][quantity]" class="form-control quantity-input" value="1" min="1" data-index="${index}"></td>
            <td>
                <input type="number" class="form-control base-price" readonly>
                <input type="hidden" name="items[${index}][sale_price]" class="sale-price-hidden">
            </td>
            <td><input type="number" class="form-control tax-value" readonly></td>
            <td><input type="number" class="form-control final-price" readonly></td>
            <td><input type="text" class="form-control tax-type" readonly></td>
            <td><input type="number" class="form-control item-total" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm btn-remove-row">حذف</button></td>
        `;
        tbody.appendChild(row);
    }

    function updateProductSelects() {
        const branchId = getSelectedBranchId();
        tbody.querySelectorAll('tr').forEach((tr, i) => {
            const select = tr.querySelector('.product-select');
            const selected = select.value;
            select.innerHTML = getProductOptions(branchId, selected);
            // حدث باقي الحقول إذا تغير المنتج
            const selectedOption = select.querySelector(`option[value="${selected}"]`);
            if (selectedOption) {
                fillProductData(tr, selectedOption);
            }
        });
    }

    function fillProductData(row, selectedOption) {
    const base = parseFloat(selectedOption.getAttribute('data-price')) || 0;
    const taxIncluded = selectedOption.getAttribute('data-tax-included') === '1';
    const taxRate = parseFloat(selectedOption.getAttribute('data-tax-percentage')) || 0;

    let tax = 0;
    let taxType = 'شامل';
    let basePrice = base;
    let finalPrice = base;

    if (!taxIncluded) {
        tax = base * (taxRate / 100);
        taxType = `غير شامل (${taxRate}%)`;
        finalPrice = base + tax;
    } else {
        basePrice = base / (1 + taxRate / 100);
        tax = base - basePrice;
        finalPrice = base;
    }

    row.querySelector('.base-price').value = basePrice.toFixed(2);
    row.querySelector('.sale-price-hidden').value = finalPrice.toFixed(2);
    row.querySelector('.tax-value').value = tax.toFixed(2);
    row.querySelector('.tax-type').value = taxType;

    updateTotals();
}


    function updateTotals() {
        let totalBefore = 0, totalTax = 0, totalAfter = 0;

        tbody.querySelectorAll('tr').forEach((tr, i) => {
            const quantity = parseFloat(tr.querySelector('.quantity-input')?.value) || 0;
            const basePrice = parseFloat(tr.querySelector('.base-price')?.value) || 0;
            const taxValue = parseFloat(tr.querySelector('.tax-value')?.value) || 0;
            const finalPrice = basePrice + taxValue;

            totalBefore += basePrice * quantity;
            totalTax += taxValue * quantity;
            totalAfter += finalPrice * quantity;

            tr.querySelector('.final-price').value = finalPrice.toFixed(2);
            tr.querySelector('.item-total').value = (finalPrice * quantity).toFixed(2);
        });

        document.getElementById('total-before-tax').value = totalBefore.toFixed(2);
        document.getElementById('total-tax').value = totalTax.toFixed(2);
        document.getElementById('total-after-tax').value = totalAfter.toFixed(2);

        const discount = parseFloat(document.querySelector('[name="discount"]').value) || 0;
        const paid = parseFloat(document.getElementById('initial-payment').value) || 0;
        const remaining = Math.max(totalAfter - discount - paid, 0);

        document.getElementById('remaining-amount').value = remaining.toFixed(2);
    }

    tbody.addEventListener('change', function (e) {
        const row = e.target.closest('tr');
        if (e.target.classList.contains('product-select')) {
            const selectedOption = e.target.selectedOptions[0];
            fillProductData(row, selectedOption);
        }
        if (e.target.classList.contains('quantity-input')) {
            updateTotals();
        }
    });

    document.getElementById('add-row').addEventListener('click', function () {
        createRow();
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-remove-row')) {
            e.target.closest('tr').remove();
            updateTotals();
        }
    });

    document.querySelector('[name="discount"]').addEventListener('input', updateTotals);
    document.querySelector('#initial-payment').addEventListener('input', updateTotals);

    document.getElementById('barcode-search').addEventListener('input', function () {
        const val = this.value.trim();
        if (!val) return;
        const branchId = getSelectedBranchId();
        const options = document.querySelectorAll(`.product-select option[data-barcode="${val}"]`);
        let target = Array.from(document.querySelectorAll('.product-select')).find(s => !s.value);
        if (!target) {
            document.getElementById('add-row').click();
            target = document.querySelectorAll('.product-select');
            target = target[target.length - 1];
        }
        options.forEach(opt => {
            if (opt.parentElement === target) {
                target.value = opt.value;
                target.dispatchEvent(new Event('change'));
            }
        });
    });

    function applyFilters() {
        const selectedCategory = document.getElementById('category-filter').value;
        const selectedBrand = document.getElementById('brand-filter').value;
        tbody.querySelectorAll('tr').forEach(tr => {
            const select = tr.querySelector('.product-select');
            Array.from(select.options).forEach(opt => {
                if (!opt.value) return;
                const matchesCategory = !selectedCategory || opt.getAttribute('data-category') === selectedCategory;
                const matchesBrand = !selectedBrand || opt.getAttribute('data-brand') === selectedBrand;
                opt.style.display = (matchesCategory && matchesBrand) ? '' : 'none';
            });
        });
    }

    document.getElementById('category-filter').addEventListener('change', applyFilters);
    document.getElementById('brand-filter').addEventListener('change', applyFilters);

    document.getElementById('branch_id').addEventListener('change', function() {
        updateProductSelects();
        updateTotals();
    });

    // أول صف تلقائي
    document.getElementById('add-row').click();
});
</script>
@endpush
