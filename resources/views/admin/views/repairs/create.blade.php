@extends('layouts.app')

@section('title', 'إضافة فاتورة صيانة')

@section('content')
<div class="container">
    <h3 class="mb-4">📋 إضافة فاتورة صيانة</h3>

    <form action="{{ route('admin.repairs.store') }}" method="POST">
        @csrf

        {{-- اختيار العميل --}}
        <div class="form-group mb-3">
            <label>اختر عميل مسجل (اختياري)</label>
            <select name="customer_id" class="form-control">
                <option value="">-- اختر عميل --</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                @endforeach
            </select>
        </div>

        {{-- إدخال اسم العميل يدويًا --}}
        <div class="form-group mb-3">
            <label>أو أدخل اسم العميل يدويًا</label>
            <input type="text" name="customer_name" class="form-control" placeholder="مثال: عميل بدون حساب">
        </div>

        {{-- نوع الجهاز --}}
        <div class="form-group mb-3">
            <label>نوع الجهاز <span class="text-danger">*</span></label>
            <input type="text" name="device_type" class="form-control" required placeholder="مثال: iPhone 12 Pro">
        </div>

        {{-- وصف العطل --}}
        <div class="form-group mb-3">
            <label>وصف العطل <span class="text-danger">*</span></label>
            <textarea name="problem_description" class="form-control" rows="3" required placeholder="اكتب وصف المشكلة..."></textarea>
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
            </select>
        </div>

        {{-- عرض السعر --}}
        <div class="form-group mb-3">
            <label>سعر المنتج</label>
        <input type="text" id="spare_part_price" name="spare_part_price" class="form-control" readonly>
        </div>

        {{-- تكلفة المصنعية --}}
        <div class="form-group mb-3">
            <label>تكلفة المصنعية <span class="text-danger">*</span></label>
            <input type="number" name="repair_cost" step="0.01" min="0" class="form-control" required oninput="calculateTotal()">
        </div>

        {{-- الخصم --}}
        <div class="form-group mb-3">
            <label>الخصم</label>
            <input type="number" name="discount" id="discount" step="0.01" min="0" class="form-control" value="0" oninput="calculateTotal()">
        </div>

{{-- عرض الإجمالي --}}
<div class="form-group mb-3">
    <label>الإجمالي</label>
<input type="text" id="total" class="form-control bg-light" readonly>
</div>

        {{-- حالة الجهاز --}}
        <div class="form-group mb-3">
            <label>حالة الجهاز</label>
            <select name="status" class="form-control" required>
                <option value="جاري">جاري</option>
                <option value="تم الإصلاح">تم الإصلاح</option>
                <option value="لم يتم الإصلاح">لم يتم الإصلاح</option>
            </select>
        </div>
        {{-- تاريخ الاستلام --}}
        <div class="form-group">
            <label for="paid">المبلغ المدفوع الآن</label>
            <input type="number" step="0.01" name="paid" class="form-control" value="0">
        </div>




        {{-- زر الحفظ وزر إعادة التعيين --}}
        <div class="form-group mt-4">
            <button type="submit" class="btn btn-success">💾 حفظ الفاتورة</button>
            <button type="reset" class="btn btn-warning" onclick="resetForm()">↩️ إعادة تعيين</button>
            <a href="{{ route('admin.repairs.index') }}" class="btn btn-secondary">رجوع</a>
        </div>
    </form>
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
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
        const partPrice   = parseFloat(document.getElementById('spare_part_price').value) || 0;
        const repairCost  = parseFloat(document.querySelector('[name="repair_cost"]').value) || 0;
        const discount    = parseFloat(document.getElementById('discount').value) || 0;

        let total = (partPrice + repairCost - discount);
        if (total < 0) total = 0;
        document.getElementById('total').value = total.toFixed(2);
    }

    // تحميل المنتجات حسب التصنيف
    document.getElementById('category_select').addEventListener('change', function () {
        const categoryId = this.value;
        const productSelect = document.getElementById('product_select');

        // فضي الاختيارات القديمة
        productSelect.innerHTML = '<option value="">-- اختر منتج --</option>';
        document.getElementById('spare_part_price').value = '';

        if (categoryId) {
            fetch(`/admin/repairs/products-by-category/${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = `${product.name} - ${parseFloat(product.sale_price).toFixed(2)} جنيه`;
                        option.setAttribute('data-price', product.sale_price);
                        productSelect.appendChild(option);
                    });
                });
        }
    });

    // إعادة تعيين الإجمالي والسعر عند إعادة تعيين النموذج
    function resetForm() {
        document.getElementById('spare_part_price').value = '';
        document.getElementById('total').value = '';
    }
    document.querySelector('form').addEventListener('submit', function(e) {
    const repairCost = parseFloat(document.querySelector('[name="repair_cost"]').value) || 0;
    const partPrice = parseFloat(document.getElementById('spare_part_price').value) || 0;

    if (repairCost === 0 && partPrice === 0) {
        alert('⚠️ يجب إدخال تكلفة المصنعية أو اختيار قطعة غيار.');
        e.preventDefault();
    }
});

</script>
@endpush
