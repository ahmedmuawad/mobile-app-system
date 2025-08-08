@extends('layouts.app')

@section('title', 'الموديولز')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h3 class="mb-2 mb-md-0">الموديولز</h3>
        <a href="{{ route('admin.modules.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> إضافة موديول جديد
        </a>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="modulesTable" style="width: 100%;">
                    <thead class="text-center">
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>الوصف</th>
                            <th>الحالة</th>
                            <th>العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modules as $index => $module)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $module->name }}</td>
                                <td>{{ $module->description ?? '-' }}</td>
                                <td>
                                    @if($module->is_active)
                                        <span class="badge badge-success">مفعل</span>
                                    @else
                                        <span class="badge badge-danger">غير مفعل</span>
                                    @endif
                                </td>
                                <td class="text-nowrap text-center">
                                    <a href="{{ route('admin.modules.edit', $module->id) }}" class="btn btn-sm btn-info me-1 mb-1">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.modules.destroy', $module->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger mb-1">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> {{-- table-responsive --}}
        </div> {{-- card-body --}}
    </div> {{-- card --}}
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        $('#modulesTable').DataTable({
            responsive: true,
            autoWidth: false,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json"
            }
        });
    });
</script>
@endsection
