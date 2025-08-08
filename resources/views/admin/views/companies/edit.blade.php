@extends('layouts.app')
@section('content')
<div class="container">
    <h1>تعديل بيانات الشركة</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.companies.update', $company->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>اسم الشركة</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $company->name) }}" required>
        </div>

        <div class="mb-3">
            <label>البريد للفواتير</label>
            <input type="email" name="billing_email" class="form-control" value="{{ old('billing_email', $company->billing_email) }}">
        </div>

        <div class="mb-3">
            <label>المنطقة الزمنية</label>
            <input type="text" name="timezone" class="form-control" value="{{ old('timezone', $company->timezone) }}" required>
        </div>

        <div class="mb-3">
            <label>Subdomain</label>
            <input type="text" name="subdomain" class="form-control" value="{{ old('subdomain', $company->subdomain) }}" required>
        </div>

        <button type="submit" class="btn btn-success">حفظ التعديلات</button>
        <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection
