@extends('layouts.app')

@section('title', 'قائمة الاشتراكات')


@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">قائمة الاشتراكات</h3>
        <div class="card-tools">
            <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة اشتراك جديد
            </a>
        </div>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الشركة</th>
                    <th>الباقة</th>
                    <th>تاريخ البداية</th>
                    <th>تاريخ النهاية</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subscriptions as $subscription)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $subscription->company->name }}</td>
                    <td>{{ $subscription->package->name }}</td>
                    <td>{{ optional($subscription->starts_at)->format('Y-m-d') }}</td>
                    <td>{{ optional($subscription->ends_at)->format('Y-m-d') }}</td>
                    <td>
                        @if($subscription->status == 'active')
                            <span class="badge badge-success">نشط</span>
                        @else
                            <span class="badge badge-secondary">غير نشط</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="btn btn-sm btn-warning">تعديل</a>
                        <form action="{{ route('admin.subscriptions.destroy', $subscription) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('هل أنت متأكد من حذف الاشتراك؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
                @endforeach

                @if($subscriptions->isEmpty())
                <tr>
                    <td colspan="7">لا توجد اشتراكات لعرضها.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
