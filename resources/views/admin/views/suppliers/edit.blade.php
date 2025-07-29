@extends('layouts.app')

@section('title', 'تعديل مورد')

@section('content')
<div class="container-fluid">
    <h3 class="mb-3">تعديل بيانات المورد</h3>

    <form action="{{ route('admin.suppliers.update', $supplier->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>الاسم *</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name', $supplier->name) }}">
        </div>

        <div class="form-group">
            <label>رقم الهاتف</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
        </div>

        <div class="form-group">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email) }}">
        </div>

        <div class="form-group">
            <label>العنوان</label>
            <input type="text" name="address" class="form-control" value="{{ old('address', $supplier->address) }}">
        </div>

        <button type="submit" class="btn btn-primary mt-2">تحديث</button>
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary mt-2">إلغاء</a>
    </form>
</div>
@endsection
