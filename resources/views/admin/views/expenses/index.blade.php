@extends('layouts.app')

@section('title', 'قائمة المصروفات')

@section('content')
<div class="container-fluid">
    <!-- الهيدر -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">قائمة المصروفات</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- تنبيه نجاح -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- الكارت -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة مصروف
            </a>
        </div>

        <div class="card-body table-responsive">
            <table id="expenses-table" class="table table-bordered table-striped text-center">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>الوصف</th>
                        <th>القيمة</th>
                        <th>التاريخ</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $expense)
                        <tr>
                            <td>{{ $expense->name }}</td>
                            <td>{{ $expense->description ?? '-' }}</td>
                            <td>{{ number_format($expense->amount, 2) }} جنيه</td>
                            <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d') }}</td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i> اختر إجراء
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="dropdown-item" title="تعديل">
                                            <i class="fas fa-edit text-warning me-2"></i> تعديل
                                        </a>
                                        <form action="{{ route('admin.expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger" title="حذف">
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
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(function () {
        $('#expenses-table').DataTable({
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
