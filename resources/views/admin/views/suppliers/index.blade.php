@extends('layouts.app')

@section('title', 'قائمة الموردين')

@section('content')
<div class="container-fluid">
    <!-- الهيدر -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">قائمة الموردين</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- إشعار نجاح -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- الكارت -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة مورد
            </a>
        </div>

        <div class="card-body table-responsive">
            <table id="suppliers-table" class="table table-bordered table-striped text-center">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>الهاتف</th>
                        <th>البريد</th>
                        <th>العنوان</th>
                        <th>الرصيد</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->phone ?? '---' }}</td>
                            <td>{{ $supplier->email ?? '---' }}</td>
                            <td>{{ $supplier->address ?? '---' }}</td>
                            <td>
                                @php $balance = $supplier->balance; @endphp
                                <span class="{{ $balance < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($balance, 2) }} جنيه
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i> اختر إجراء
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="dropdown-item">
                                            <i class="fas fa-edit text-warning me-2"></i> تعديل
                                        </a>
                                        <form action="{{ route('admin.suppliers.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
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
                            <td colspan="7" class="text-center">لا يوجد موردون حالياً.</td>
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
        $('#suppliers-table').DataTable({
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
