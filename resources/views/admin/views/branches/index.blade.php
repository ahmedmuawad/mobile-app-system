@extends('layouts.app')

@section('title', 'الفروع')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between mb-3">
        <h3>الفروع</h3>
        <a href="{{ route('admin.branches.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> إضافة فرع جديد
        </a>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped" id="branchesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>رقم الهاتف</th>
                        <th>العنوان</th>
                        <th>رئيسي؟</th>
                        <th>الحالة</th>
                        <th>العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branches as $index => $branch)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $branch->name }}</td>
                            <td>{{ $branch->phone ?? '-' }}</td>
                            <td>{{ $branch->address ?? '-' }}</td>
                            <td>
                                @if($branch->is_main)
                                    <span class="badge badge-success">نعم</span>
                                @else
                                    <span class="badge badge-secondary">لا</span>
                                @endif
                            </td>
                            <td>
                                @if($branch->is_active)
                                    <span class="badge badge-success">مفعل</span>
                                @else
                                    <span class="badge badge-danger">غير مفعل</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.branches.edit', $branch->id) }}" class="btn btn-sm btn-info">
                                    <i class="fa fa-edit"></i>
                                </a>

                                <form action="{{ route('admin.branches.destroy', $branch->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="fa fa-trash"></i>
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
@endsection

@section('scripts')
<script>
    $(function () {
        $('#branchesTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json"
            }
        });
    });
</script>
@endsection
