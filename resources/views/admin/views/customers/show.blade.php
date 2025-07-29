@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">تفاصيل العميل</h2>

    <div class="card">
        <div class="card-header">
            {{ $customer->name }}
        </div>
        <div class="card-body">
            <p><strong>الهاتف:</strong> {{ $customer->phone ?? '-' }}</p>
            <p><strong>البريد الإلكتروني:</strong> {{ $customer->email ?? '-' }}</p>
            <p><strong>تاريخ الإضافة:</strong> {{ $customer->created_at->format('Y-m-d') }}</p>
        </div>
    </div>

    <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary mt-3">رجوع</a>
</div>
@endsection
