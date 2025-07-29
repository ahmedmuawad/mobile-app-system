@extends('layouts.app')

@section('title', 'تعديل فاتورة صيانة')

@section('content')
<div class="container">
    <h3 class="mb-4">✏️ تعديل فاتورة صيانة</h3>

    <form action="{{ route('admin.repairs.update', $repair->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- اختيار العميل --}}
        <div class="form-group mb-3">
            <label>اختر عميل مسجل (اختياري)</label>
            <select name="customer_id" class="form-control">
                <option value="">-- اختر عميل --</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ $repair->customer_id == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }} - {{ $customer->phone }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- إدخال اسم العميل يدويًا --}}
        <div class="form-group mb-3">
            <label>أو أدخل اسم العميل يدويًا</label>
            <input type="text" name="customer_name" class="form-control" value="{{ $repair->customer_name }}">
        </div>

        {{-- نوع الجهاز --}}
        <div class="form-group mb-3">
            <label>نوع الجهاز</label>
            <input type="text" name="device_type" class="form-control" value="{{ $repair->device_type }}" required>
        </div>

        {{-- وصف العطل --}}
        <div class="form-group mb-3">
            <label>وصف العطل</label>
            <textarea name="problem_description" class="form-control" rows="3" required>{{ $repair->problem_description }}</textarea>
        </div>

        {{-- اختيار التصنيف --}}
        <div class="form-group mb-3">
            <label>اختر التصنيف</label>
            <select id="category_select" class="form-control">
                <option value="">-- اختر تصنيف --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- اختيار المنتج --}}
        <div class="form-group mb-3">
            <label>اختر المنتج (قطعة الغيار)</label>
            <select name="spare_part_id" id="product_select" class="form-control" onchange="updateSparePartPrice(this)">
                <option value="">-- اختر منتج --</option>
                @foreach($spareParts as $product)
                    <option value="{{ $product->id }}"
                        data-price="{{ $product->sale_price }}"
                        {{ $repair->spare_part_id == $product->id ? 'selected' : '' }}>
                        {{ $product->name }} - {{ number_format($product->sale_price, 2) }} جنيه
                    </option>
                @endforeach
            </select>
        </div>

        {{-- سعر المنتج --}}
        <div class="form-group mb-3">
            <label>سعر المنتج</label>
            <input type="text" id="spare_part_price" class="form-control" readonly>
        </div>

        {{-- المصنعية --}}
        <div class="form-group mb-3">
            <label>تكلفة المصنعية</label>
            <input type="number" name="repair_cost" class="form-control" step="0.01" min="0"
                value="{{ $repair->repair_cost }}" oninput="calculateTotal()">
        </div>

        {{-- الخصم --}}
        <div class="form-group mb-3">
            <label>الخصم</label>
            <input type="number" name="discount" id="discount" class="form-control" step="0.01" min="0"
                value="{{ $repair->discount ?? 0 }}" oninput="calculateTotal()">
        </div>

        {{-- الإجمالي --}}
        <div class="form-group mb-3">
            <label>الإجمالي</label>
            <input type="text" id="total" class="form-control bg-light" readonly>
        </div>

        {{-- حالة الجهاز --}}
        <div class="form-group mb-3">
            <label>حالة الجهاز</label>
            <select name="status" class="form-control">
                <option value="جاري" {{ $repair->status == 'جاري' ? 'selected' : '' }}>جاري</option>
                <option value="تم الإصلاح" {{ $repair->status == 'تم الإصلاح' ? 'selected' : '' }}>تم الإصلاح</option>
                <option value="لم يتم الإصلاح" {{ $repair->status == 'لم يتم الإصلاح' ? 'selected' : '' }}>لم يتم الإصلاح</option>
            </select>
        </div>
    <p>المتبقي: <strong>{{ number_format($repair->total - $repair->payments->sum('amount'), 2) }}</strong> جنيه</p>

        {{-- الأزرار --}}
        <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">💾 تحديث الفاتورة</button>
        @php
            $paidAmount = $repair->payments ? $repair->payments->sum('amount') : 0;
        @endphp

        @if($repair->total - $paidAmount > 0)
        <a href="{{ route('admin.repairs.payments.create', $repair->id) }}" class="btn btn-sm btn-success">
            💵 سداد متبقي
        </a>
        @endif            
        <a href="{{ route('admin.repairs.index') }}" class="btn btn-secondary">رجوع</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function updateSparePartPrice(select) {
        const selectedOption = select.options[select.selectedIndex];
        const price = selectedOption.getAttribute('data-price') || 0;
        document.getElementById('spare_part_price').value = parseFloat(price).toFixed(2);
        calculateTotal();
    }

    function calculateTotal() {
        const partPrice  = parseFloat(document.getElementById('spare_part_price').value) || 0;
        const repairCost = parseFloat(document.querySelector('[name="repair_cost"]').value) || 0;
        const discount   = parseFloat(document.getElementById('discount').value) || 0;

        let total = (partPrice + repairCost - discount);
        if (total < 0) total = 0;
        document.getElementById('total').value = total.toFixed(2);
    }

    window.addEventListener('DOMContentLoaded', function () {
        const selectedProduct = document.querySelector('#product_select option:checked');
        if (selectedProduct) {
            const price = selectedProduct.getAttribute('data-price') || 0;
            document.getElementById('spare_part_price').value = parseFloat(price).toFixed(2);
            calculateTotal();
        }
    });
</script>
@endpush
