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

        {{-- 🔍 فلاتر المنتجات --}}
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
                    <th>السعر النهائي (شامل الضريبة)</th>
                    <th>نوع السعر</th>
                    <th>الضريبة المضافة</th>
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
                                <option
                                    value="{{ $product->id }}"
                                    data-price="{{ $product->sale_price }}"
                                    data-barcode="{{ $product->barcode }}"
                                    data-category="{{ $product->category_id }}"
                                    data-brand="{{ $product->brand_id }}"
                                    data-tax-included="{{ $product->is_tax_included ? 1 : 0 }}"
                                    data-tax-percentage="{{ $product->tax_percentage ?? 0 }}"
                                >
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
                        <input type="text" class="form-control tax-type" value="" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control tax-value" value="0" readonly>
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
        let total = 0;
        document.querySelectorAll('.item-total').forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        const discount = parseFloat(document.querySelector('[name="discount"]').value) || 0;
        document.getElementById('total-before-discount').value = total.toFixed(2);
        document.getElementById('total-after-discount').value = Math.max(total - discount, 0).toFixed(2);
    }

    function calculateItemTotal(index) {
        const quantity = parseFloat(document.querySelector(`[name="items[${index}][quantity]"]`).value) || 0;
        const price = parseFloat(document.querySelector(`[name="items[${index}][sale_price]"]`).value) || 0;
        const total = quantity * price;
        document.querySelectorAll('.item-total')[index].value = total.toFixed(2);
        calculateInvoiceTotals();
    }

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('product-select')) {
            const index = e.target.dataset.index;
            const selected = e.target.selectedOptions[0];

            const basePrice = parseFloat(selected.getAttribute('data-price')) || 0;
            const taxIncluded = selected.getAttribute('data-tax-included') === '1';
            const taxPercent = parseFloat(selected.getAttribute('data-tax-percentage')) || 0;

            let finalPrice = basePrice;
            let taxValue = 0;
            let taxType = 'شامل الضريبة';

            if (!taxIncluded) {
                taxValue = basePrice * (taxPercent / 100);
                finalPrice += taxValue;
                taxType = `غير شامل (${taxPercent}%)`;
            }

            document.querySelector(`[name="items[${index}][sale_price]"]`).value = finalPrice.toFixed(2);
            document.querySelectorAll('.tax-type')[index].value = taxType;
            document.querySelectorAll('.tax-value')[index].value = taxValue.toFixed(2);
            calculateItemTotal(index);
        }

        if (e.target.id === 'category-filter' || e.target.id === 'brand-filter') {
            filterProductOptions();
        }
    });

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('quantity-input')) {
            calculateItemTotal(e.target.dataset.index);
        }

        if (e.target.name === 'discount') {
            calculateInvoiceTotals();
        }

        if (e.target.id === 'barcode-search') {
            const barcode = e.target.value.trim();
            if (barcode) {
                const option = document.querySelector(`.product-select option[data-barcode="${barcode}"]`);
                if (option) {
                    const select = document.querySelector('.product-select');
                    select.value = option.value;
                    select.dispatchEvent(new Event('change'));
                }
            }
        }
    });

    function filterProductOptions() {
        const category = document.getElementById('category-filter').value;
        const brand = document.getElementById('brand-filter').value;
        document.querySelectorAll('.product-select').forEach(select => {
            Array.from(select.options).forEach(opt => {
                if (!opt.value) return;
                const matchCat = !category || opt.getAttribute('data-category') === category;
                const matchBrand = !brand || opt.getAttribute('data-brand') === brand;
                opt.style.display = (matchCat && matchBrand) ? '' : 'none';
            });
        });
    }

    document.getElementById('add-row').addEventListener('click', function () {
        const tableBody = document.querySelector('#items-table tbody');
        const index = tableBody.querySelectorAll('tr').length;

        let options = '<option value="">اختر منتج</option>';
        @foreach($products as $product)
            options += `<option value="{{ $product->id }}"
                            data-price="{{ $product->sale_price }}"
                            data-barcode="{{ $product->barcode }}"
                            data-category="{{ $product->category_id }}"
                            data-brand="{{ $product->brand_id }}"
                            data-tax-included="{{ $product->is_tax_included ? 1 : 0 }}"
                            data-tax-percentage="{{ $product->tax_percentage ?? 0 }}">
                            {{ $product->name }}
                        </option>`;
        @endforeach

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <select name="items[${index}][product_id]" class="form-control product-select" data-index="${index}">
                    ${options}
                </select>
            </td>
            <td>
                <input type="number" name="items[${index}][quantity]" class="form-control quantity-input" value="1" min="1" data-index="${index}">
            </td>
            <td>
                <input type="number" name="items[${index}][sale_price]" class="form-control sale-price" value="0" readonly>
            </td>
            <td>
                <input type="text" class="form-control tax-type" value="" readonly>
            </td>
            <td>
                <input type="number" class="form-control tax-value" value="0" readonly>
            </td>
            <td>
                <input type="number" class="form-control item-total" value="0" readonly>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm btn-remove-row">حذف</button>
            </td>
        `;
        tableBody.appendChild(row);
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
