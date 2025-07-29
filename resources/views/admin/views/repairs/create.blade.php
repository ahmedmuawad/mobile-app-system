@extends('layouts.app')

@section('title', 'إضافة فاتورة صيانة')

@section('content')
<div class="container-fluid" dir="rtl">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">📋 إضافة فاتورة صيانة</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.repairs.store') }}" method="POST">
                @csrf

                <div class="row">
                    <!-- العميل -->
                    <div class="col-md-6 mb-3">
                        <label>اختر عميل مسجل (اختياري)</label>
                        <select name="customer_id" class="form-control select2">
                            <option value="">-- اختر عميل --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>أو أدخل اسم العميل يدويًا</label>
                        <input type="text" name="customer_name" class="form-control" placeholder="مثال: عميل بدون حساب">
                    </div>

                    <!-- نوع الجهاز -->
                    <div class="col-md-6 mb-3">
                        <label>نوع الجهاز <span class="text-danger">*</span></label>
                        <input type="text" name="device_type" class="form-control" required placeholder="مثال: iPhone 12 Pro">
                    </div>

                    <!-- حالة الفاتورة -->
                    <div class="col-md-6 mb-3">
                        <label>حالة الفاتورة</label>
                        <select name="status" class="form-control" required>
                            <option value="جاري">جاري</option>
                            <option value="تم الإصلاح">تم الإصلاح</option>
                            <option value="لم يتم الإصلاح">لم يتم الإصلاح</option>
                        </select>
                    </div>

                    <!-- وصف العطل -->
                    <div class="col-md-12 mb-3">
                        <label>وصف العطل <span class="text-danger">*</span></label>
                        <textarea name="problem_description" class="form-control" rows="2" required placeholder="اكتب وصف المشكلة..."></textarea>
                    </div>

                    <!-- نوع الصيانة -->
                    <div class="col-md-6 mb-3">
                        <label>نوع الصيانة <span class="text-danger">*</span></label>
                        <select name="repair_type" id="repair_type" class="form-control" required>
                            <option value="">-- اختر نوع الصيانة --</option>
                            <option value="software">سوفت وير</option>
                            <option value="hardware">هارد وير</option>
                            <option value="both">كلاهما</option>
                        </select>
                    </div>
                </div>

                <!-- التصنيف وقطع الغيار -->
                <div id="hardware_fields" class="row w-100" style="display: none;">
                    <div class="col-md-4 mb-3">
                        <label>التصنيف</label>
                        <select id="category_select" class="form-control">
                            <option value="">-- اختر تصنيف --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label>قطع الغيار</label>
                        <select name="spare_part_ids[]" id="product_select" class="form-control" multiple></select>
                        <div id="selected_parts_list" class="mt-3"></div>
                    </div>
                </div>

                <!-- المصنعية والخصم والإجمالي -->
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>تكلفة المصنعية <span class="text-danger">*</span></label>
                        <input type="number" name="repair_cost" step="0.01" min="0" class="form-control" required oninput="calculateTotal()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>الخصم</label>
                        <input type="number" name="discount" id="discount" step="0.01" min="0" class="form-control" value="0" oninput="calculateTotal()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>المدفوع الآن</label>
                        <input type="number" step="0.01" name="paid" id="paid" class="form-control" value="0" oninput="calculateTotal()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>الإجمالي</label>
                        <input type="text" id="total" name="total" class="form-control bg-light" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>المتبقي</label>
                        <input type="text" id="remaining" class="form-control bg-light" readonly>
                    </div>
                </div>

                <!-- الأزرار -->
                <div class="mt-4 d-flex justify-content-between flex-wrap gap-2">
                    <button type="submit" class="btn btn-success">💾 حفظ الفاتورة</button>
                    <button type="reset" class="btn btn-warning" onclick="resetForm()">↩️ إعادة تعيين</button>
                    <a href="{{ route('admin.repairs.index') }}" class="btn btn-secondary">🔙 رجوع</a>
                </div>
            </form>

            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const allProducts = @json($products);
    let selectedQuantities = {};

    const repairType = document.getElementById('repair_type');
    const hardwareFields = document.getElementById('hardware_fields');
    const productSelect = document.getElementById('product_select');
    const selectedPartsList = document.getElementById('selected_parts_list');

    function toggleHardwareFields() {
        hardwareFields.style.display = (repairType.value === 'hardware' || repairType.value === 'both') ? 'flex' : 'none';
        if (repairType.value !== 'hardware' && repairType.value !== 'both') {
            productSelect.innerHTML = '';
            selectedPartsList.innerHTML = '';
            document.getElementById('category_select').value = '';
        }
    }

    repairType.addEventListener('change', toggleHardwareFields);

    function populateProducts(categoryId) {
        productSelect.innerHTML = '';
        const filtered = allProducts.filter(p => p.category_id == categoryId);

        filtered.forEach(product => {
            const opt = document.createElement('option');
            opt.value = product.id;
            opt.text = `${product.name} - ${product.sale_price} ج.م`;
            opt.dataset.price = product.sale_price;
            productSelect.appendChild(opt);
        });
    }

    function renderQuantityInputs() {
        selectedPartsList.innerHTML = '';
        const checkedOptions = document.querySelectorAll('#product_select option:checked');

        checkedOptions.forEach(opt => {
            const productId = String(opt.value);
            const productName = opt.text;
            const qtyValue = selectedQuantities[productId] !== undefined ? selectedQuantities[productId] : 1;

            const div = document.createElement('div');
            div.className = 'd-flex align-items-center mb-2 border p-2 rounded bg-light shadow-sm';

            const label = document.createElement('span');
            label.className = 'flex-grow-1 fw-bold';
            label.textContent = productName;

            const input = document.createElement('input');
            input.type = 'number';
            input.name = `quantities[${productId}]`;
            input.value = qtyValue;
            input.min = 1;
            input.className = 'form-control w-25 ms-2';

            input.addEventListener('input', function () {
                selectedQuantities[productId] = parseInt(this.value) || 1;
                calculateTotal();
            });

            div.appendChild(label);
            div.appendChild(input);
            selectedPartsList.appendChild(div);
        });
    }

    function calculateTotal() {
        let totalParts = 0;

        document.querySelectorAll('#product_select option:checked').forEach(opt => {
            const productId = String(opt.value);
            const price = parseFloat(opt.dataset.price) || 0;
            const qty = selectedQuantities[productId] !== undefined ? selectedQuantities[productId] : 1;
            totalParts += price * qty;
        });

        const repairCost = parseFloat(document.querySelector('[name="repair_cost"]').value) || 0;
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const paid = parseFloat(document.getElementById('paid').value) || 0;

        let total = totalParts + repairCost - discount;
        if (total < 0) total = 0;

        let remaining = total - paid;
        if (remaining < 0) remaining = 0;

        document.getElementById('total').value = total.toFixed(2);
        document.getElementById('remaining').value = remaining.toFixed(2);
    }

    document.getElementById('category_select').addEventListener('change', function () {
        populateProducts(this.value);
        selectedQuantities = {};
        renderQuantityInputs();
        calculateTotal();
    });

    productSelect.addEventListener('mousedown', function (e) {
        e.preventDefault();
        const option = e.target;
        option.selected = !option.selected;
        renderQuantityInputs();
        calculateTotal();
    });

    function resetForm() {
        document.getElementById('total').value = '';
        document.getElementById('remaining').value = '';
        selectedQuantities = {};
        renderQuantityInputs();
    }

    window.addEventListener('DOMContentLoaded', () => {
        toggleHardwareFields();
    });
</script>
@endpush
