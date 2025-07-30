@extends('layouts.app')
@section('title', 'مزودي المحافظ')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">قائمة مزودي المحافظ</h4>
                </div>
            </div>
        </div>
    </section>

    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.wallet_providers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة مزود جديد
            </a>
        </div>

        @if ($providers->isEmpty())
            <p class="p-3">لا توجد مزودات حالياً.</p>
        @else
        <div class="card-body table-responsive p-0">
            <table id="providers-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>الحد اليومي</th>
                        <th>الحد الشهري</th>
                        <th style="width: 180px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($providers as $provider)
                    <tr>
                        <td>{{ $provider->name }}</td>
                        <td>{{ $provider->daily_limit }}</td>
                        <td>{{ $provider->monthly_limit }}</td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i> اختر إجراء
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a href="{{ route('admin.wallet_providers.edit', $provider->id) }}" class="dropdown-item">
                                        <i class="fas fa-edit text-warning me-2"></i> تعديل
                                    </a>
                                    <form action="{{ route('admin.wallet_providers.destroy', $provider->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
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
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(function () {
        $('#providers-table').DataTable({
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
