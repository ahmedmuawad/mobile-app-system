@extends('layouts.app')

@section('title', 'إضافة طريقة دفع جديدة')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">إضافة طريقة دفع</h1>
    <form action="{{ route('admin.payment-methods.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">اسم طريقة الدفع</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">الوصف</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">حفظ</button>
        <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-secondary">رجوع</a>
    </form>
</div>
@endsection
