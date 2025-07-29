@extends('layouts.app')

@section('title', 'قائمة المبيعات')

@section('content')
<div class="container-fluid">
    <!-- العنوان -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">قائمة المبيعات</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- تنبيه النجاح -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- جدول المبيعات -->
    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.sales.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة فاتورة جديدة
            </a>
        </div>

        <div class="card-body">
            <div class="card-body table-responsive p-0">
                <table id="sales-table" class="table table-bordered table-striped text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>العميل</th>
                            <th>اسم العميل (يدوي)</th>
                            <th>الإجمالي</th>
                            <th>الربح</th>
                            <th>التاريخ</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td>{{ $sale->id }}</td>
                                <td>{{ $sale->customer?->name ?? '-' }}</td>
                                <td>{{ $sale->customer_name ?? '-' }}</td>
                                <td>{{ number_format($sale->total, 2) }} جنيه</td>
                                <td>{{ number_format($sale->profit, 2) }} جنيه</td>
                                <td>{{ $sale->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i> اختر إجراء
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="{{ route('admin.sales.show', $sale->id) }}" class="dropdown-item">
                                                <i class="fas fa-eye text-info me-2"></i> عرض
                                            </a>
                                            <a href="{{ route('admin.sales.edit', $sale->id) }}" class="dropdown-item">
                                                <i class="fas fa-edit text-warning me-2"></i> تعديل
                                            </a>
                                            <form action="{{ route('admin.sales.destroy', $sale->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟');">
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
                                <td colspan="7" class="text-center">لا توجد مبيعات حتى الآن</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- روابط الترقيم -->
                <div class="m-3">
                    {{ $sales->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(function () {
        $('#sales-table').DataTable({
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
