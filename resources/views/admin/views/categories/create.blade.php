@extends('layouts.app')

@section('title', 'إدارة الفروع')

@section('content')
<div class="container-fluid">
    <div class="card card-primary card-outline">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">قائمة الفروع</h3>
            <a href="{{ route('admin.branches.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> إضافة فرع
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="branches-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>الهاتف</th>
                            <th>العنوان</th>
                            <th>رئيسي؟</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($branches as $branch)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $branch->name }}</td>
                                <td>{{ $branch->phone }}</td>
                                <td>{{ $branch->address }}</td>
                                <td>
                                    <span class="badge badge-{{ $branch->is_main ? 'success' : 'secondary' }}">
                                        {{ $branch->is_main ? 'نعم' : 'لا' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $branch->is_active ? 'success' : 'danger' }}">
                                        {{ $branch->is_active ? 'مفعل' : 'غير مفعل' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.branches.edit', $branch) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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

@section('scripts')
<script>
    $(function () {
        $('#branches-table').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Arabic.json'
            },
            responsive: true,
            pageLength: 10
        });
    });
</script>
@endsection
