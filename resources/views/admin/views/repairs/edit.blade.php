@extends('layouts.app')

@section('title', 'تعديل فاتورة صيانة')

@section('content')
<div class="container-fluid" dir="rtl">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">✏️ تعديل فاتورة صيانة</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.repairs.update', $repair->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    {{-- اختيار العميل -- }}
                    <div class="col-md-6 mb-3">
                        <label>اختر عميل مسجل (اختياري)</label>
                        <select name="customer_id" class="form-control select2">
                            <option value="">-- اختر عميل --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ $repair->customer_id == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} - {{ $customer->phone }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- إدخال اسم العميل --}}
                    <div class="col-md-6 mb-3">
                        <label>أو أدخل اسم العميل يدويًا</label>
                        <input type="text" name="customer_name" class="form-control" value="{{ $repair->customer_name }}">
                    </div>

                    {{-- نوع الجهاز --}}
                    <div class="col-md-6 mb-3">
                        <label>نوع الجهاز <span class="text-danger">*</span></label>
                        <input type="text" name="device_type" class="form-control" value="{{ $repair->device_type }}" required>
                    </div>

                    {{-- حالة الجهاز --}}
                    <div class="col-md-6 mb-3">
                        <label>حالة الجهاز</label>
                        <select name="status" class="form-control">
                            <option value="جاري" {{ $repair->status == 'جاري' ? 'selected' : '' }}>جاري</option>
                            <option value="تم الإصلاح" {{ $repair->status == 'تم الإصلاح' ? 'selected' : '' }}>تم الإصلاح</option>
                            <option value="لم يتم الإصلاح" {{ $repair->status == 'لم يتم الإصلاح' ? 'selected' : '' }}>لم يتم الإصلاح</option>
                        </select>
                    </div>

                    {{-- وصف العطل --}}
                    <div class="col-md-12 mb-3">
                        <label>وصف العطل <span class="text-danger">*</span></label>
                        <textarea name="problem_description" class="form-control" rows="2" required>{{ $repair->problem_description }}</textarea>
                    </div>

                    {{-- نوع الصيانة --}}
                    <div class="col-md-6 mb-3">
                        <label>نوع الصيانة <span class="text-danger">*</span></label>
                        <select name="repair_type" id="repair_type" class="form-control" required>
                            <option value="">-- اختر نوع الصيانة --</option>
                            <option value="software" {{ $repair->repair_type == 'software' ? 'selected' : '' }}>سوفت وير</option>
                            <option value="hardware" {{ $repair->repair_type == 'hardware' ? 'selected' : '' }}>هارد وير</option>
                            <option value="both" {{ $repair->repair_type == 'both' ? 'selected' : '' }}>كلاهما</option>
                        </select>
                    </div>
                </div>

                {{-- Spare Parts --}}
                @php
                    $selectedParts = $repair->spareParts ? $repair->spareParts->pluck('id')->toArray() : [];
                    $selectedQuantities = $repair->spareParts
                        ? $repair->spareParts->pluck('pivot.quantity','id')
                            ->map(function($qty){ return $qty ?? 1; })  // ✅ لو null نخليها 1
                            ->toArray()
                        : [];
                    $selectedCategoryId = ($repair->spareParts && $repair->spareParts->count() > 0)
                        ? $repair->spareParts->first()->category_id
                        : null;
                @endphp

                <div id="hardware_fields" class="row w-100" style="display: {{ ($repair->repair_type == 'hardware' || $repair->repair_type == 'both') ? 'flex' : 'none' }};">
                    <div class="col-md-4 mb-3">
                        <label>التصنيف</label>
                        <select id="category_select" class="form-control">
                            <option value="">-- اختر تصنيف --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $selectedCategoryId == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label>قطع الغيار</label>
                        <select name="spare_part_ids[]" id="product_select" class="form-control" multiple></select>
                        <div id="selected_parts_list" class="mt-3"></div>
                    </div>
                </div>

                {{-- الحسابات --}}
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>تكلفة المصنعية <span class="text-danger">*</span></label>
                        <input type="number" name="repair_cost" step="0.01" min="0" class="form-control" value="{{ $repair->repair_cost }}" required oninput="calculateTotal()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>الخصم</label>
                        <input type="number" name="discount" id="discount" step="0.01" min="0" class="form-control" value="{{ $repair->discount ?? 0 }}" oninput="calculateTotal()">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>المدفوع</label>
                        <input type="number" name="paid" id="paid" step="0.01" min="0" class="form-control" value="{{ $repair->payments->sum('amount') ?? 0 }}" oninput="calculateTotal()">
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

                {{-- الأزرار --}}
                <div class="mt-4 d-flex justify-content-between flex-wrap gap-2">
                    <button type="submit" class="btn btn-success">💾 تحديث الفاتورة</button>
                    @php $paidAmount = $repair->payments ? $repair->payments->sum('amount') : 0; @endphp
                    @if($repair->total - $paidAmount > 0)
                        <a href="{{ route('admin.repairs.payments.create', $repair->id) }}" class="btn btn-success">💵 سداد متبقي</a>
                    @endif
                    <a href="{{ route('admin.repairs.index') }}" class="btn btn-secondary">🔙 رجوع</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const allProducts = @json($spareParts);
    let selectedParts = @json($selectedParts);
let selectedQuantities = Object.fromEntries(
    Object.entries(@json($selectedQuantities)).map(([k, v]) => [String(k), v ?? 1])
);
    console.log('🔍 selectedQuantities:', selectedQuantities);

    const selectedCategoryId = "{{ $selectedCategoryId ?? '' }}";

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
            if (selectedParts.includes(product.id)) opt.selected = true;
            productSelect.appendChild(opt);
        });
    }

    function renderQuantityInputs() {
        selectedPartsList.innerHTML = '';
        const checkedOptions = document.querySelectorAll('#product_select option:checked');
        selectedParts = Array.from(checkedOptions).map(opt => parseInt(opt.value));

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

            input.addEventListener('input', function() {
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

    document.getElementById('category_select').addEventListener('change', function() {
        populateProducts(this.value);
        renderQuantityInputs();
        calculateTotal();
    });

    productSelect.addEventListener('mousedown', function(e) {
        e.preventDefault();
        const option = e.target;
        option.selected = !option.selected;
        renderQuantityInputs();
        calculateTotal();
    });

    window.addEventListener('DOMContentLoaded', () => {
        toggleHardwareFields();
        if (selectedCategoryId) {
            document.getElementById('category_select').value = selectedCategoryId;
            populateProducts(selectedCategoryId);
        }
        renderQuantityInputs();
        calculateTotal();
    });
</script>
@endpush
