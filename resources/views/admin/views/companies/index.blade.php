@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>قائمة الشركات</h1>
        <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">إضافة شركة جديدة</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>الاسم</th>
                <th>البريد للفواتير</th>
                <th>Subdomain</th>
                <th>التوقيت</th>
                <th>تاريخ الإنشاء</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($companies as $company)
                <tr>
                    <td>{{ $company->name }}</td>
                    <td>{{ $company->billing_email ?? '-' }}</td>
                    <td>{{ $company->subdomain ?? '-' }}</td>
                    <td>{{ $company->timezone }}</td>
                    <td>{{ $company->created_at ? $company->created_at->format('Y-m-d') : '-' }}</td>
                    <td>
                        <a href="{{ route('admin.companies.edit', $company->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                        <form action="{{ route('admin.companies.destroy', $company->id) }}" method="POST" style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('هل أنت متأكد من الحذف؟')" class="btn btn-sm btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">لا توجد شركات مسجلة</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
