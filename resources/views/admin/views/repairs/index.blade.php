@extends('layouts.app')

@section('title', 'فواتير الصيانة')

@section('content')
<div class="container-fluid">
    <!-- العنوان -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">فواتير الصيانة</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- تنبيه النجاح -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- جدول فواتير الصيانة -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <a href="{{ route('admin.repairs.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> فاتورة جديدة
            </a>
        </div>

        <div class="card-body">
            <div class="card-body table-responsive p-0">
                @if ($repairs->count())
                    <table id="repairs-table" class="table table-bordered table-striped text-center">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>العميل</th>
                                <th>نوع الجهاز</th>
                                <th>الحالة</th>
                                <th>الإجمالي</th>
                                <th>المدفوع</th>
                                <th>المتبقى</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($repairs as $repair)
                                <tr>
                                    <td>{{ $repair->id }}</td>
                                    <td>
                                        @if($repair->customer)
                                            {{ $repair->customer->name }}
                                        @else
                                            {{ $repair->customer_name ?? '---' }}
                                        @endif
                                    </td>
                                    <td>{{ $repair->device_type }}</td>
                                    <td>
                                        @php
                                            $color = match($repair->status) {
                                                'جاري' => 'warning',
                                                'تم الإصلاح' => 'success',
                                                'لم يتم الإصلاح' => 'danger',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ $repair->status }}</span>
                                    </td>
                                    @php
                                        $paidAmount = $repair->payments->sum('amount');
                                        $remaining = $repair->total - $paidAmount;
                                    @endphp
                                    <td>{{ number_format($repair->total, 2) }} جنيه</td>
                                    <td><strong>{{ number_format($paidAmount, 2) }}</strong> جنيه</td>
                                    <td><strong>{{ number_format($remaining, 2) }}</strong> جنيه</td>
                                    <td>{{ $repair->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i> إجراء
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a href="{{ route('admin.repairs.show', $repair->id) }}" class="dropdown-item">
                                                    <i class="fas fa-eye text-info me-2"></i> عرض
                                                </a>
                                                <a href="{{ route('admin.repairs.edit', $repair->id) }}" class="dropdown-item">
                                                    <i class="fas fa-edit text-warning me-2"></i> تعديل
                                                </a>
                                                <form action="{{ route('admin.repairs.destroy', $repair->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف الفاتورة؟');">
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

                @else
                    <div class="alert alert-info text-center">لا توجد فواتير صيانة بعد.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(function () {
        $('#repairs-table').DataTable({
            language: { url: "{{ asset('assets/admin/js/ar.json') }}" },
            responsive: true,
            autoWidth: false,
            paging: true,
            searching: true,
            ordering: true
        });
    });
</script>
@endpush
