@extends('layouts.app')
@section('title', 'فواتير المشتريات')
@section('content')
    <div class="container-fluid">
        <!-- العنوان وزر الإضافة -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6">
                        <h4 class="arabic-heading">فواتير المشتريات</h4>
                    </div>
                </div>
            </div>
        </section>

        <!-- تنبيه النجاح -->
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- جدول الفواتير -->
       <div class="card">
            <div class="card-header">
                <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> إضافة فاتورة
                        </a>
            </div>
            <div class="card-body">
                <div class="card-body table-responsive p-0">
                <table id="purchases-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المورد</th>
                            <th>الإجمالي</th>
                            <th>المدفوع</th>
                            <th>المتبقى</th>
                            <th>التاريخ</th>
                            <th>ملاحظات</th>
                            <th style="width: 180px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                            <tr>
                                <td>{{ $purchase->id }}</td>
                                <td>{{ $purchase->supplier->name ?? '---' }}</td>
                                <td>{{ number_format($purchase->total_amount, 2) }}</td>
                                <td>{{ number_format($purchase->paid_amount, 2) }}</td>
                                <td>{{ number_format($purchase->remaining_amount, 2) }}</td>
                                <td>{{ $purchase->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $purchase->notes ?? '---' }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i> اختر إجراء
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="dropdown-item">
                                                <i class="fas fa-eye text-info me-2"></i> عرض
                                            </a>
                                            <a href="{{ route('admin.purchases.edit', $purchase->id) }}" class="dropdown-item">
                                                <i class="fas fa-edit text-warning me-2"></i> تعديل
                                            </a>
                                            <form action="{{ route('admin.purchases.destroy', $purchase->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
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
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">لا توجد فواتير حتى الآن.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(function () {
        $('#purchases-table').DataTable({
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
