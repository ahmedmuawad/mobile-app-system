@extends('layouts.app')

@section('title', 'تعديل الفاتورة')

@push('style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .product-cell {
        max-width: 250px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .product-cell .select2-container {
        width: 100% !important;
    }

    .product-cell .select2-selection {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .select2-results__option {
        white-space: normal !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">تعديل الفاتورة رقم #{{ $sale->id }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="sale-form" method="POST" action="{{ route('admin.sales.update', $sale->id) }}">
        @csrf
        @method('PUT')

        <input type="hidden" name="branch_id" value="{{ session('current_branch_id') }}">

        <div class="mb-3">
            <label for="customer_id" class="form-label">اختيار عميل مسجل</label>
            <select name="customer_id" id="customer_id" class="form-control">
                <option value="">-- اختر عميل --</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ $sale->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="customer_name" class="form-label">أو أدخل اسم عميل يدوي</label>
            <input type="text" name="customer_name" id="customer_name" class="form-control" value="{{ old('customer_name', $sale->customer_name) }}">
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
                    @php $categories = collect($branchProducts)->pluck('category_id')->unique()->filter(); @endphp
                    @foreach($categories as $catId)
                        @php $cat = \App\Models\Category::find($catId); @endphp
                        @if($cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label>فلترة حسب البراند</label>
                <select id="brand-filter" class="form-control">
                    <option value="">-- كل البراندات --</option>
                    @php $brands = collect($branchProducts)->pluck('brand_id')->unique()->filter(); @endphp
                    @foreach($brands as $brandId)
                        @php $brand = \App\Models\Brand::find($brandId); @endphp
                        @if($brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>

        <h4>الأصناف المباعة</h4>

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
                {{-- سيتم تعبئة الأصناف عبر جافاسكربت على أساس بيانات saleItems --}}
            </tbody>
        </table>

        <button type="button" class="btn btn-success mb-3" id="add-row">إضافة صنف</button>

        <div class="form-group">
            <label>الخصم (جنيه)</label>
            <input type="number" step="0.01" name="discount" id="discount" class="form-control" value="{{ old('discount', $sale->discount ?? 0) }}">
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
                <input type="number" name="initial_payment" id="initial-payment" class="form-control" value="{{ old('initial_payment', $initialPayment ?? 0) }}" step="0.01">
            </div>
            <div class="col-md-6">
                <label>المتبقي:</label>
                <input type="number" id="remaining-amount" class="form-control" value="{{ number_format($remaining ?? 0, 2) }}" readonly>
            </div>
        </div>

        {{-- استدعاء الـ partial الخاص بطرق الدفع --}}
        @include('admin.views.sales.partials.payment_methods_edit')

        <br>
        <button type="submit" class="btn btn-primary" id="save-btn">حفظ التعديلات</button>
        <a href="{{ route('admin.sales.index') }}" class="btn btn-light">إلغاء</a>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    const products = @json($branchProducts);
    const existingItems = @json($sale->saleItems->map(fn($item) => [
        'product_id' => $item->product_id,
        'quantity' => $item->quantity
    ]));

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
                const name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/items\[\d+\]/, `items[${i}]`));
                }
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

        let totalPaid = 0;
        $('#payments-table tbody tr').each(function(){
            let amount = parseFloat($(this).find('input[name*="[amount]"]').val()) || 0;
            totalPaid += amount;
        });

        const discount = parseFloat($('#discount').val()) || 0;
        const remaining = Math.max(totalAfter - discount - totalPaid, 0);

        $('#initial-payment').val(round(totalPaid));
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
        const taxIncluded = selectedOption.data('tax-included') == 1 || selectedOption.data('tax-included') === true;
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

        const qty = parseFloat(row.find('.quantity-input').val()) || 1;
        row.find('.item-total').val(round(finalPrice * qty));

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

    // إزالة صف
    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
        updateIndexes();
        updateTotals();
    });

    // تغيير اختيار المنتج
    $(document).on('change', '.product-select', function() {
        const selected = $(this).find('option:selected');
        fillProductData($(this).closest('tr'), selected);
    });

    // تحديث الكمية
    $(document).on('input', '.quantity-input', function() {
        updateTotals();
    });

    // إضافة صف جديد
    $('#add-row').click(function() {
        createRow();
    });

    // بحث بالباركود
    $('#barcode-search').on('input', function() {
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

    // الفلاتر تغيير
    $('#category-filter, #brand-filter').change(applyFilters);

    // تحديث الإجماليات عند تغير الخصم أو الدفعة المقدمة
    $('#discount').on('input', updateTotals);
    $('#initial-payment').on('input', updateTotals);

    // التحكم في اختيار العميل يدوي/مسجل
    $('#customer_name').on('input', function() {
        if ($(this).val().trim()) {
            $('#customer_id').val('').trigger('change');
            $('#customer_id').parent().hide();
        } else {
            $('#customer_id').parent().show();
        }
    });

    // تهيئة select2 للفلاتر والعملاء
    $('#customer_id, #category-filter, #brand-filter').select2({ dir: "rtl", width: '100%' });

    // عند تحميل الصفحة - ننظف tbody ونملأ الأصناف المخزنة
    $('#items-table tbody').empty();
    existingItems.forEach(item => {
        createRow(item.product_id, item.quantity);
    });

    // احسب الإجماليات بعد التحميل
    updateTotals();
});
</script>
@endpush
