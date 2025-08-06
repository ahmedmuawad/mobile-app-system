@extends('layouts.app')

@section('title', 'قائمة الماركات')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6"><h4 class="arabic-heading">قائمة الماركات</h4></div>
        </div>
    </section>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">الماركات</h5>
            <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة ماركة جديدة
            </a>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center m-0">
                    <thead class="bg-light">
                        <tr>
                            <th>الاسم</th>
                            <th>الوصف</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($brands as $brand)
                            <tr>
                                <td>{{ $brand->name }}</td>
                                <td>{{ $brand->description ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $brand->is_active ? 'success' : 'danger' }}">
                                        {{ $brand->is_active ? 'مفعلة' : 'غير مفعلة' }}
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-warning btn-sm me-1 mb-1">
                                        <i class="fas fa-edit"></i> تعديل
                                    </a>
                                    <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm mb-1">
                                            <i class="fas fa-trash"></i> حذف
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        @if($brands->isEmpty())
                            <tr>
                                <td colspan="4">لا توجد ماركات مسجلة حالياً</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div> {{-- table-responsive --}}
        </div>
    </div>
</div>
@endsection
