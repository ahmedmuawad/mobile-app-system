@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">إضافة عميل جديد</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.customers.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">اسم العميل <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">رقم الهاتف</label>
            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">البريد الإلكتروني</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
        </div>

        <button type="submit" class="btn btn-success">حفظ</button>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">رجوع</a>
    </form>
</div>
@endsection
