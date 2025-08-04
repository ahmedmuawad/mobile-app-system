@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h4 class="mb-4"> الصيانة</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
    <div class="card-header">
        <h4 class="mb-0">فواتير الصيانة</h4>
    </div>
    <div class="card-body">
        <!-- زر إضافة فاتورة جديدة داخل الكارد -->
        <div class="mb-4">
            <a href="{{ route('admin.repairs.create') }}" class="btn btn-success">
                <i class="fas fa-plus-circle me-2"></i> إضافة فاتورة صيانة جديدة
            </a>
        </div>

        <div class="table-responsive p-0">
            <table class="table table-bordered table-striped text-center mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>العميل</th>
                        <th>نوع الجهاز</th>
                        <th>الوصف</th>
                        <th>الإجمالي</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>حالة الصيانة</th>
                        <th>حالة التسليم</th>
                        <th style="width: 160px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($repairs as $repair)
                        <tr>
                            <td>{{ $repair->id }}</td>
                            <td>{{ $repair->customer?->name ?? $repair->customer_name }}</td>
                            <td>{{ $repair->device_type }}</td>
                            <td>{{ $repair->problem_description }}</td>
                            <td>{{ $repair->total }}</td>
                            <td>{{ $repair->paid }}</td>
                            <td>{{ $repair->remaining }}</td>
                            <td>
                                @if($repair->delivery_status === 'not_delivered')
                                    جاري
                                @elseif($repair->delivery_status === 'delivered')
                                    تم الإصلاح
                                @elseif($repair->delivery_status === 'rejected')
                                    لم يتم الإصلاح
                                @endif
                            </td>

                            <td>
                                {{ $repair->delivery_status === 'delivered' ? 'تم التسليم' :
                                   ($repair->delivery_status === 'rejected' ? 'الجهاز مرفوض - استرجاع المبلغ' :
                                   'لم يتم التسليم') }}
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i> اختر إجراء
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="{{ route('admin.repairs.edit', $repair->id) }}" class="dropdown-item">
                                            <i class="fas fa-edit text-warning me-2"></i> تعديل
                                        </a>
                                        <a href="{{ route('admin.repairs.show', $repair->id) }}" class="dropdown-item">
                                            <i class="fas fa-eye text-info me-2"></i> عرض
                                        </a>
                                        @if($repair->delivery_status !== 'rejected')
                                            <button type="button"
                                                    class="dropdown-item text-secondary change-status-btn"
                                                    data-id="{{ $repair->id }}"
                                                    data-delivery="{{ $repair->delivery_status }}"
                                                    data-paid="{{ $repair->paid }}"
                                                    data-remaining="{{ $repair->remaining }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#changeStatusModal">
                                                <i class="fas fa-exchange-alt me-2"></i> تغيير حالة التسليم
                                            </button>
                                        @endif
                                        <form action="{{ route('admin.repairs.destroy', $repair->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash-alt me-2"></i> حذف
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

    </div>

    <!-- Modal -->
    <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.repairs.updateStatus') }}" id="statusForm">
                @csrf
                <input type="hidden" name="repair_id" id="repair_id">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="changeStatusModalLabel">تغيير حالة التسليم</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="delivery_status" class="form-label">حالة التسليم</label>
                            <select class="form-select" name="delivery_status" id="delivery_status" required>
                                <option value="not_delivered">لم يتم التسليم</option>
                                <option value="delivered">تم الاستلام</option>
                                <option value="rejected">الجهاز مرفوض - استرجاع المبلغ</option>
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="refundAmountBox">
                            <label class="form-label">المبلغ المسترد:</label>
                            <input type="text" class="form-control" id="refund_amount" disabled>
                        </div>

                        <div class="mb-3 d-none" id="addPaymentBox">
                            <label class="form-label">إضافة دفعة جديدة</label>
                            <input type="number" name="paid_amount" id="paid_amount" class="form-control" min="1">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">حفظ</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const form = document.getElementById('statusForm');
    const modalElement = document.getElementById('changeStatusModal');
    let originalValues = {};

    modalElement.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) return;

        const id = button.getAttribute('data-id');
        const currentStatus = button.getAttribute('data-delivery');
        const paid = parseFloat(button.getAttribute('data-paid')) || 0;
        const remaining = parseFloat(button.getAttribute('data-remaining')) || 0;

        form.repair_id.value = id;
        form.delivery_status.value = currentStatus;

        // حفظ المبلغ المتبقي في dataset
        form.dataset.remaining = remaining;

        // إعادة القيم الأصلية للمقارنة لاحقًا
        originalValues = {
            delivery_status: currentStatus,
            paid_amount: '',
        };

        // إخفاء الحقول أولاً
        document.getElementById('refundAmountBox').classList.add('d-none');
        document.getElementById('addPaymentBox').classList.add('d-none');

        // تعبئة المبلغ المسترد
        document.getElementById('refund_amount').value = paid;
        document.getElementById('paid_amount').value = '';
    });

    form.delivery_status.addEventListener('change', function () {
        const selected = this.value;
        const refundBox = document.getElementById('refundAmountBox');
        const addPaymentBox = document.getElementById('addPaymentBox');
        const paidInput = document.getElementById('paid_amount');
        const remaining = parseFloat(form.dataset.remaining) || 0;

        // إظهار خانة المرتجع فقط في حالة "مرفوض"
        refundBox.classList.toggle('d-none', selected !== 'rejected');

        // إظهار خانة الدفع فقط إذا في مبلغ متبقي
        const showPayment = selected === 'delivered' && remaining > 0;
        addPaymentBox.classList.toggle('d-none', !showPayment);

        // تعبئة المبلغ المتبقي تلقائيًا
        paidInput.value = showPayment ? remaining : '';
    });

    form.addEventListener('submit', function (e) {
        const selectedStatus = form.delivery_status.value;
        const enteredAmount = form.paid_amount.value;

        if (
            selectedStatus === originalValues.delivery_status &&
            enteredAmount === originalValues.paid_amount
        ) {
            e.preventDefault();
            alert('لم تقم بأي تغييرات!');
        }
    });
</script>
@endpush
