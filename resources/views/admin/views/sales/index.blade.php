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
        <div class="card-header d-flex justify-content-between align-items-center">
            <a href="{{ route('admin.sales.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة فاتورة جديدة
            </a>
            {{-- <form id="bulk-delete-form" action="{{ route('admin.sales.bulkDelete') }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف الفواتير المحددة؟');" class="d-inline">
                @csrf
                @method('DELETE')
                <input type="hidden" name="sales_ids" id="bulk-delete-ids">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> حذف المحدد
                </button>
            </form> --}}
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-striped datatable">
                <thead class="bg-dark text-light">
                    <tr>
                        {{-- <th><input type="checkbox" id="select-all"></th> --}}
                        <th>الرقم</th>
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
                                <a href="{{ route('admin.sales.edit', $sale->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                                <a href="{{ route('admin.sales.show', $sale->id) }}" class="btn btn-sm btn-info">عرض</a>
                                <form action="{{ route('admin.sales.destroy', $sale->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // تحديد أو إلغاء تحديد الكل
    document.getElementById('select-all').addEventListener('change', function () {
        let checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    // تجهيز معرفات الفواتير المحددة للحذف
    document.getElementById('bulk-delete-form').addEventListener('submit', function () {
        let selected = Array.from(document.querySelectorAll('input[name="selected_ids[]"]:checked')).map(cb => cb.value);
        document.getElementById('bulk-delete-ids').value = JSON.stringify(selected);
    });
</script>
@endpush
