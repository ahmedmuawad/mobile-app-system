@extends('layouts.app')
@section('title', 'تعديل مزود محفظة')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">تعديل مزود محفظة</h4>
                </div>
            </div>
        </div>
    </section>

    <div class="card card-primary">
        <form method="POST" action="{{ route('admin.wallet_providers.update', $walletProvider) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label for="name">الاسم</label>
                    <input type="text" name="name" class="form-control" value="{{ $walletProvider->name }}" required>
                </div>

                <div class="form-group">
                    <label for="daily_limit">الحد اليومي</label>
                    <input type="number" name="daily_limit" class="form-control" value="{{ $walletProvider->daily_limit }}" required>
                </div>

                <div class="form-group">
                    <label for="monthly_limit">الحد الشهري</label>
                    <input type="number" name="monthly_limit" class="form-control" value="{{ $walletProvider->monthly_limit }}" required>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> حفظ التعديلات
                </button>
                <a href="{{ route('admin.wallet_providers.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
