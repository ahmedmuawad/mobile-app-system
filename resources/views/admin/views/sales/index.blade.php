@extends('layouts.app')

@section('title', 'قائمة المبيعات')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">قائمة المبيعات</h4>
                </div>
            </div>
        </div>
    </section>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <a href="{{ route('admin.sales.create') }}" class="btn btn-primary mb-2">
                <i class="fas fa-plus"></i> إضافة فاتورة جديدة
            </a>
            {{--
            <form id="bulk-delete-form" action="{{ route('admin.sales.bulkDelete') }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف الفواتير المحددة؟');" class="d-inline">
                @csrf
                @method('DELETE')
                <input type="hidden" name="sales_ids" id="bulk-delete-ids">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> حذف المحدد
                </button>
            </form>
            --}}
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped mb-0" id="sales-table">
                    <thead class="thead-dark">
                        <tr>
                            {{-- <th><input type="checkbox" id="select-all"></th> --}}
                            <th>رقم الفاتورة</th>
                            <th>العميل</th>
                            <th>المبلغ الإجمالي</th>
                            <th>المدفوع</th>
                            <th>المتبقى</th>
                            <th>التاريخ</th>
                            <th>الفرع</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $sale)
                            <tr>
                                <td>{{ $sale->id }}</td>
                                <td>{{ optional($sale->customer)->name ?? '-' }}</td>
                                <td>{{ number_format($sale->total, 2) }}</td>
                                <td>{{ number_format($sale->paid, 2) }}</td>
                                <td>{{ number_format($sale->remaining, 2) }}</td>
                                <td>{{ $sale->created_at->format('Y-m-d') }}</td>
                                <td>{{ optional($sale->branch)->name ?? '-' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.sales.edit', $sale->id) }}" class="btn btn-warning" title="تعديل"><i class="fas fa-edit"></i></a>
                                        <a href="{{ route('admin.sales.show', $sale->id) }}" class="btn btn-info" title="عرض"><i class="fas fa-eye"></i></a>
                                        <form action="{{ route('admin.sales.destroy', $sale->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟');" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="حذف"><i class="fas fa-trash-alt"></i></button>
                                        </form>
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
@endsection



@push('scripts')

<!-- Buttons extensions -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<!-- JSZip required for Excel export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script>
$(document).ready(function () {
    if ($('#sales-table').length) {
        $('#sales-table').DataTable({
            dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> تصدير Excel',
                    className: 'btn btn-success btn-sm me-2',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> طباعة',
                    className: 'btn btn-primary btn-sm',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
            ],
            responsive: true,
            autoWidth: false,
            language: {
                url: "{{ asset('assets/admin/js/ar.json') }}"
            }
        });
    }
});

</script>
@endpush
