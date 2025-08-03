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

    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة ماركة جديدة
            </a>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-striped text-center">
                <thead>
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
                            <td>
                                <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn btn-warning btn-sm">تعديل</a>
                                <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">حذف</button>
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
