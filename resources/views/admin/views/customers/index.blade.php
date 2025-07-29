@extends('layouts.app')

@section('title', 'قائمة العملاء')

@section('content')
<div class="container-fluid">
    <!-- العنوان -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">قائمة العملاء</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- تنبيه النجاح -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- جدول العملاء -->
    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة عميل جديد
            </a>
        </div>

        <div class="card-body">
                <div class="card-body table-responsive p-0">
            @if($customers->count() > 0)
                <table id="customers-table" class="table table-bordered table-striped text-center">
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>الهاتف</th>
                            <th>البريد الإلكتروني</th>
                            <th style="width: 180px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->phone ?? '-' }}</td>
                                <td>{{ $customer->email ?? '-' }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i> اختر إجراء
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="{{ route('admin.customers.show', $customer->id) }}" class="dropdown-item">
                                                <i class="fas fa-eye text-info me-2"></i> عرض
                                            </a>
                                            <a href="{{ route('admin.customers.edit', $customer->id) }}" class="dropdown-item">
                                                <i class="fas fa-edit text-warning me-2"></i> تعديل
                                            </a>
                                            <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
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

                <!-- روابط الترقيم -->
                <div class="m-3">
                    {{ $customers->links('pagination::bootstrap-5') }}
                </div>
            @else
                <p class="p-3 text-center">لا يوجد عملاء حالياً.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(function () {
        $('#customers-table').DataTable({
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
