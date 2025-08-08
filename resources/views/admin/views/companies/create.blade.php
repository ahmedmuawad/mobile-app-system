@extends('layouts.app')

@section('content')
<div class="container">
    <h1>إنشاء شركة جديدة</h1>

    <form method="POST" action="{{ route('admin.companies.store') }}">
        @csrf
        <div class="mb-3">
            <label>اسم الشركة</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>البريد للفواتير</label>
            <input type="email" name="billing_email" class="form-control">
        </div>
        <div class="mb-3">
            <label>المنطقة الزمنية</label>
            <input type="text" name="timezone" value="Africa/Cairo" class="form-control" required>
        </div>

        <hr>
        <h4>بيانات الأدمن</h4>
        <div class="mb-3">
            <label>اسم الأدمن</label>
            <input type="text" name="admin_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>البريد الإلكتروني للأدمن</label>
            <input type="email" name="admin_email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>كلمة مرور الأدمن</label>
            <input type="password" name="admin_password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">حفظ</button>
    </form>
</div>
@endsection
