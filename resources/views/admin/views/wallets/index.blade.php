@extends('layouts.app')
@section('title', 'المحافظ')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">قائمة المحافظ</h4>
                </div>
            </div>
        </div>
    </section>

    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.wallets.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة محفظة جديدة
            </a>
        </div>

        @if ($wallets->isEmpty())
            <p class="p-3">لا توجد محافظ حالياً.</p>
        @else
        <div class="card-body">
            <div class="card-body table-responsive p-0">
                <table id="wallets-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>رقم المحفظة</th>
                            <th>اسم المالك</th>
                            <th>مزود الخدمة</th>
                            <th style="width: 180px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($wallets as $wallet)
                        <tr>
                            <td>{{ $wallet->number }}</td>
                            <td>{{ $wallet->owner_name }}</td>
                            <td>{{ $wallet->provider->name ?? '-' }}</td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i> اختر إجراء
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="{{ route('admin.wallets.edit', $wallet->id) }}" class="dropdown-item">
                                            <i class="fas fa-edit text-warning me-2"></i> تعديل
                                        </a>
                                        <form action="{{ route('admin.wallets.destroy', $wallet->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
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
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(function () {
        $('#wallets-table').DataTable({
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
 