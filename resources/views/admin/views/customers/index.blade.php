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
        <th>إجمالي المتبقي</th>
        <th>سجل العميل</th>

    </tr>
</thead>
<tbody>
    @foreach($customers as $customer)
        <tr>
            <td>{{ $customer->name }}</td>
            <td>{{ $customer->phone ?? '-' }}</td>
            <td>{{ $customer->email ?? '-' }}</td>
            <td>{{ number_format($customer->total_due, 2) }} ج.م</td>
            <td>
                <a href="{{ route('admin.customers.history', $customer->id) }}" class="btn btn-info btn-sm">
                    <i class="fas fa-history"></i> عرض السجل
                </a>
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
