@extends('layouts.app')

@section('title', 'الباقات')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h3 class="mb-2 mb-md-0">الباقات</h3>
        <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">
            <i class="fa fa-plus"></i> إضافة باقة جديدة
        </a>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="packagesTable" style="width: 100%;">
                    <thead class="text-center">
                        <tr>
                            <th>#</th>
                            <th>اسم الباقة</th>
                            <th>السعر</th>
                            <th>الموديولز</th>
                            <th>إدارة الموديولات</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packages as $index => $package)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $package->name }}</td>
                                <td>{{ $package->price }}</td>
                                <td>
                                    @foreach($package->modules as $module)
                                        <span class="badge badge-info">{{ $module->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <a href="{{ url('admin/packages/' . $package->id . '/edit') }}" class="btn btn-sm btn-warning">تعديل</a>
                                    <a href="{{ url('admin/packages/' . $package->id . '/modules') }}" class="btn btn-sm btn-info">إدارة الموديولات</a>
                                    <form action="{{ route('admin.packages.destroy', $package->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
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
        $('#packagesTable').DataTable({
            responsive: true,
            autoWidth: false,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json"
            }
        });
    });
</script>
@endsection

