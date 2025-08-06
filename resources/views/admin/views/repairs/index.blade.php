@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">الصيانة</h4>

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
                                <td>{{ number_format($repair->total, 2) }}</td>
                                <td>{{ number_format($repair->paid, 2) }}</td>
                                <td>{{ number_format($repair->remaining, 2) }}</td>
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
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a href="{{ route('admin.repairs.edit', $repair->id) }}" class="dropdown-item">
                                                    <i class="fas fa-edit text-warning me-2"></i> تعديل
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.repairs.show', $repair->id) }}" class="dropdown-item">
                                                    <i class="fas fa-eye text-info me-2"></i> عرض
                                                </a>
                                            </li>
                                            @if($repair->delivery_status !== 'rejected')
                                            <li>
                                            <button type="button"
                                                    class="dropdown-item text-secondary change-status-btn"
                                                    data-id="{{ $repair->id }}"
                                                    data-delivery="{{ $repair->delivery_status }}"
                                                    data-paid="{{ $repair->paid }}"
                                                    data-remaining="{{ $repair->remaining }}"
                                                    data-toggle="modal"
                                                    data-target="#changeStatusModal">
                                                <i class="fas fa-exchange-alt me-2"></i> تغيير حالة التسليم
                                            </button>
                                            </li>
                                            @endif
                                            <li>
                                                <form action="{{ route('admin.repairs.destroy', $repair->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟');" class="m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash-alt me-2"></i> حذف
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
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

<!-- Modal تغيير حالة التسليم -->
<div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.repairs.updateStatus') }}" id="statusForm">
            @csrf
            <input type="hidden" name="repair_id" id="repair_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeStatusModalLabel">تغيير حالة التسليم</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق">
                        <span aria-hidden="true">&times;</span>
                    </button>
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
                        <label for="paid_amount" class="form-label">إضافة دفعة جديدة</label>
                        <input type="number" name="paid_amount" id="paid_amount" class="form-control" min="1" step="0.01" placeholder="أدخل مبلغ الدفعة">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    const form = $('#statusForm');
    let originalValues = {};

    $('#changeStatusModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        if (!button.length) return;

        const id = button.data('id');
        const currentStatus = button.data('delivery');
        const paid = parseFloat(button.data('paid')) || 0;
        const remaining = parseFloat(button.data('remaining')) || 0;

        form.find('input[name="repair_id"]').val(id);
        form.find('select[name="delivery_status"]').val(currentStatus);

        form.data('remaining', remaining);

        originalValues = {
            delivery_status: currentStatus,
            paid_amount: '',
        };

        // إخفاء الحقول
        $('#refundAmountBox').addClass('d-none');
        $('#addPaymentBox').addClass('d-none');

        // تعبئة الحقول
        $('#refund_amount').val(paid.toFixed(2));
        $('#paid_amount').val('');
    });

    form.find('select[name="delivery_status"]').on('change', function () {
        const selected = $(this).val();
        const refundBox = $('#refundAmountBox');
        const addPaymentBox = $('#addPaymentBox');
        const paidInput = $('#paid_amount');
        const remaining = parseFloat(form.data('remaining')) || 0;

        // إظهار خانة المرتجع فقط في حالة "مرفوض"
        refundBox.toggleClass('d-none', selected !== 'rejected');

        // إظهار خانة الدفع فقط إذا في مبلغ متبقي وحالة التسليم "تم الاستلام"
        const showPayment = selected === 'delivered' && remaining > 0;
        addPaymentBox.toggleClass('d-none', !showPayment);

        paidInput.val(showPayment ? remaining.toFixed(2) : '');
    });

    form.on('submit', function (e) {
        const selectedStatus = form.find('select[name="delivery_status"]').val();
        const enteredAmount = form.find('input[name="paid_amount"]').val();

        if (
            selectedStatus === originalValues.delivery_status &&
            enteredAmount === originalValues.paid_amount
        ) {
            e.preventDefault();
            alert('لم تقم بأي تغييرات!');
        }
    });
});
</script>
@endpush
