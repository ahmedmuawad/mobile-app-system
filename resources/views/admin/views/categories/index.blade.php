@extends('layouts.app')
@section('title', 'التصنيفات')
@section('content')
<div class="container-fluid">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>قائمة التصنيفات</h4>
                </div>
                </div>
            </div>
        </section>

          <div class="card">
            <div class="card-header">
              <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة تصنيف جديد</a>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
            <div class="card-body table-responsive p-0">
                <table table id="categories-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>الاسم</th>
                            <th style="width: 150px;">تاريخ الإنشاء</th>
                            <th style="width: 180px;">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i> اختر اجراء
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="{{ route('admin.categories.edit', $category->id) }}" class="dropdown-item">
                                                <i class="fas fa-edit text-warning me-2"></i> تعديل
                                            </a>

                                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد أنك تريد حذف هذا التصنيف؟');">
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
                                <td colspan="4" class="text-center">لا توجد تصنيفات حالياً.</td>
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
        $('#categories-table').DataTable({
            language: {url:"{{ asset('assets/admin/js/ar.json') }}"},
            responsive: true,
            autoWidth: false,
            paging: true,
            searching: true,
            ordering: true
        });
    });
</script>

@endpush