@extends('layouts.app')

@section('title', 'تعديل طريقة دفع')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">تعديل طريقة دفع</h1>
    <form action="{{ route('admin.payment-methods.update', $paymentMethod->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label">اسم طريقة الدفع</label>
            <input type="text" name="name" class="form-control" value="{{ $paymentMethod->name }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">الوصف</label>
            <textarea name="description" class="form-control">{{ $paymentMethod->description }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">تحديث</button>
        <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-secondary">رجوع</a>
    </form>
</div>
@endsection
