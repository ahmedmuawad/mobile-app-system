@extends('layouts.app')

@section('title', 'إضافة مورد')

@section('content')
<div class="container-fluid">
    <h3 class="mb-3">إضافة مورد جديد</h3>

    <form action="{{ route('admin.suppliers.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>الاسم *</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
        </div>

        <div class="form-group">
            <label>رقم الهاتف</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
        </div>

        <div class="form-group">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
        </div>

        <div class="form-group">
            <label>العنوان</label>
            <input type="text" name="address" class="form-control" value="{{ old('address') }}">
        </div>

        <button type="submit" class="btn btn-success mt-2">حفظ</button>
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary mt-2">إلغاء</a>
    </form>
</div>
@endsection
