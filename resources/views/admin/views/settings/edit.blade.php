@extends('layouts.app')

@section('title', 'إعدادات المتجر')

@section('content')
<div class="container-fluid">
    <!-- الهيدر -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">إعدادات المتجر</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- رسالة نجاح -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- الكارت -->
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group mb-3">
                    <label>اسم المتجر:</label>
                    <input type="text" name="store_name" class="form-control" value="{{ old('store_name', $setting->store_name) }}" required>
                </div>

                <div class="form-group mb-3">
                    <label>عنوان المتجر:</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $setting->address) }}">
                </div>

                <div class="form-group mb-3">
                    <label>رقم الهاتف:</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $setting->phone) }}">
                </div>

                <div class="form-group mb-3">
                    <label>رسالة أسفل الفاتورة:</label>
                    <textarea name="invoice_footer" class="form-control" rows="3">{{ old('invoice_footer', $setting->invoice_footer) }}</textarea>
                </div>

                <div class="form-group mb-4">
                    <label>شعار المتجر:</label><br>
                    @if($setting->logo)
                        <img src="{{ asset('storage/' . $setting->logo) }}" width="120" class="mb-2 rounded shadow-sm border">
                    @endif
                    <input type="file" name="logo" class="form-control-file">
                </div>

                <div class="text-start">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> حفظ الإعدادات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
