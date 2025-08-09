@extends('layouts.app')
@section('title', 'تنبيهات المخزون')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3">📢 تنبيهات المخزون</h4>

    {{-- فلاتر البحث --}}
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <select name="branch_id" class="form-select" onchange="this.form.submit()">
                <option value="">كل الفروع</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <select name="category_id" class="form-select" onchange="this.form.submit()">
                <option value="">كل التصنيفات</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">كل الحالات</option>
                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>نشط</option>
                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>معطل</option>
            </select>
        </div>
    </form>

    @if($alerts->isEmpty())
        <div class="alert alert-info">لا توجد تنبيهات حالياً.</div>
    @else
        <div class="table-responsive">
                <table id="stock-table" class="table table-bordered table-striped text-center">
<thead>
    <tr>
        <th>باركود المنتج</th>
        <th>المنتج</th>
        <th>الماركة</th>
        <th>التصنيف</th>
        <th>الفرع</th>
        <th>الكمية الحالية</th>
        <th>حد التنبيه</th>
    </tr>
</thead>
<tbody>
    @foreach($alerts as $alert)
        <tr>
            <td>{{ $alert->product->barcode ?? '-' }}</td>
            <td>{{ $alert->product->name ?? '-' }}</td>
            <td>{{ $alert->product->brand->name ?? '-' }}</td>
            <td>{{ $alert->product->category->name ?? '-' }}</td>
            <td>{{ $alert->branch->name ?? '-' }}</td>
            <td>{{ $alert->product->branches->firstWhere('id', $alert->branch_id)?->pivot->stock ?? 0 }}</td>
            <td>{{ $alert->threshold }}</td>
        </tr>
    @endforeach
</tbody>

            </table>
        </div>
    @endif
</div>
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script>
    $(document).ready(function() {
        $('#stock-table').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'تصدير Excel',
                    className: 'btn btn-success mb-3'
                },
                {
                    extend: 'print',
                    text: 'طباعة',
                    className: 'btn btn-primary mb-3'
                }
            ],
            language: {
                url: "{{ asset('assets/admin/js/ar.json') }}"
            },
            responsive: true,
            autoWidth: false,
            paging: true,
            searching: true,
            ordering: true
        });

        // ترتيب الفلاتر والصفحات (اختياري)
        $('#stock-table_filter').addClass('float-start');
        $('#stock-table_paginate').addClass('float-end');
    });
</script>
@endpush
