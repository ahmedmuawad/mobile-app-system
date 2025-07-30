@extends('layouts.app')

@section('title', 'تقرير الصيانة')

@section('content')
<div class="container-fluid">

    <!-- الهيدر -->
    <section class="content-header mb-4">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">تقرير الصيانة</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- نموذج الفلترة -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.repairs') }}" class="row g-3">
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
            <div class="col-md-2">
                <div class="card border-info mb-3 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">عدد الفواتير</h6>
                        <p class="card-text fs-5">{{ $report->invoices_count }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-success mb-3 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">إجمالي الفواتير</h6>
                        <p class="card-text fs-5">{{ number_format($report->total_repairs, 2) }} ج</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-primary mb-3 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">مبيعات قطع الغيار</h6>
                        <p class="card-text fs-5">{{ number_format($report->spare_parts_sales, 2) }} ج</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-warning mb-3 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">أرباح قطع الغيار</h6>
                        <p class="card-text fs-5">{{ number_format($report->product_profit, 2) }} ج</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-dark mb-3 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">مصنعية الصيانة</h6>
                        <p class="card-text fs-5">{{ number_format($report->labor_profit, 2) }} ج</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- جدول التفاصيل -->
        @if(count($repairs))
            <div class="card mt-5">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">تفاصيل الفواتير</h5>
                </div>
                <div class="card-body table-responsive">
                    <table id="datatable1" class="table table-bordered table-striped text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>رقم الفاتورة</th>
                                <th>العميل</th>
                                <th>إجمالي الفاتورة</th>
                                <th>مبيعات قطع الغيار</th>
                                <th>أرباح القطع</th>
                                <th>المصنعية</th>
                                <th>التاريخ</th>
                                <th>العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($repairs as $index => $repair)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $repair->id }}</td>
                                    <td>{{ $repair->customer->name ?? 'غير معروف' }}</td>
                                    <td>{{ number_format($repair->total, 2) }} ج</td>
                                    <td>{{ number_format($repair->parts_sale, 2) }} ج</td>
                                    <td>{{ number_format($repair->product_profit, 2) }} ج</td>
                                    <td>{{ number_format($repair->labor_profit, 2) }} ج</td>
                                    <td>{{ $repair->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.repairs.show', $repair->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
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
            <div class="alert alert-info mt-4">لا توجد فواتير في الفترة المحددة.</div>
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
                language: {
                    url: "{{ asset('assets/admin/js/ar.json') }}"
                },
                responsive: true,
                autoWidth: false,
                paging: true,
                searching: true,
                ordering: true
            });
        });
    </script>
@endpush
