@extends('layouts.app')

@section('title', 'إدارة طرق الدفع')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">طرق الدفع</h1>
    <a href="{{ route('admin.payment-methods.create') }}" class="btn btn-primary mb-3">+ إضافة طريقة دفع</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>الاسم</th>
                <th>الوصف</th>
                <th width="150">إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($paymentMethods as $method)
            <tr>
                <td>{{ $method->name }}</td>
                <td>{{ $method->description }}</td>
                <td>
                    <a href="{{ route('admin.payment-methods.edit', $method->id) }}" class="btn btn-sm btn-warning">تعديل</a>
                    <form action="{{ route('admin.payment-methods.destroy', $method->id) }}" method="POST" style="display:inline-block;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('تأكيد الحذف؟')">حذف</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
