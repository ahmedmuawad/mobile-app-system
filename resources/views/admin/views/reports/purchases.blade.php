@extends('layouts.app')

@section('title', 'تقرير المشتريات')

@section('content')
<div class="container-fluid">
    <!-- الهيدر -->
    <section class="content-header mb-4">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">تقرير المشتريات</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- نموذج الفلترة -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.purchases') }}" class="row g-3">
                <div class="col-md-4">
                    <label>من تاريخ</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>إلى تاريخ</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter ms-1"></i> عرض التقرير
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- عرض التقرير -->
    @if($report)
        <div class="row text-center">
            <div class="col-md-4">
                <div class="card border-info mb-3 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">عدد الفواتير</h5>
                        <p class="card-text fs-4">{{ $report->invoices_count }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-primary mb-3 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي المشتريات</h5>
                        <p class="card-text fs-4">{{ number_format($report->total_purchases, 2) }} ج</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success mb-3 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">إجمالي المدفوع</h5>
                        <p class="card-text fs-4">{{ number_format($report->total_paid, 2) }} ج</p>
                    </div>
                </div>
            </div>
        </div>

        @if(count($purchases))
            <div class="card mt-5">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">تفاصيل فواتير الشراء</h5>
                </div>
                <div class="card-body table-responsive">
                    <table id="datatable1" class="table table-bordered table-striped text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>رقم الفاتورة</th>
                                <th>المورد</th>
                                <th>المجموع</th>
                                <th>التاريخ</th>
                                <th>العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchases as $index => $purchase)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $purchase->id }}</td>
                                    <td>{{ $purchase->supplier->name ?? 'غير معروف' }}</td>
                                    <td>{{ number_format($purchase->total_amount, 2) }} ج</td>
                                    <td>{{ $purchase->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="fas fa-eye me-1"></i> عرض
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="alert alert-info mt-4">لا توجد فواتير شراء في الفترة المحددة.</div>
        @endif
    @elseif(request('from') && request('to'))
        <div class="alert alert-warning">لا توجد بيانات في هذه الفترة.</div>
    @endif
</div>
@endsection

@push('scripts')
    <!-- DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
       $(function () {
        $('#datatable1').DataTable({
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
