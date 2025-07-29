@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">تعديل بيانات العميل</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">اسم العميل <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">رقم الهاتف</label>
            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">البريد الإلكتروني</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $customer->email) }}">
        </div>

        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">إلغاء</a>
    </form>
</div>
@endsection

