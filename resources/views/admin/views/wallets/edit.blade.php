@extends('layouts.app')
@section('title', 'تعديل المحفظة')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">تعديل المحفظة</h4>
                </div>
            </div>
        </div>
    </section>

    <div class="card card-primary">
        <form method="POST" action="{{ route('admin.wallets.update', $wallet) }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="form-group">
                    <label>الفرع</label>
                    <input type="text" class="form-control" value="{{ $wallet->branch->name ?? '-' }}" readonly>
                </div>
                <div class="form-group">
                    <label for="number">رقم المحفظة</label>
                    <input type="text" name="number" class="form-control" value="{{ $wallet->number }}" required>
                </div>

                <div class="form-group">
                    <label for="wallet_provider_id">مزود المحفظة</label>
                    <select name="wallet_provider_id" class="form-control" required>
                        @foreach($providers as $provider)
                            <option value="{{ $provider->id }}" {{ $wallet->wallet_provider_id == $provider->id ? 'selected' : '' }}>
                                {{ $provider->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="owner_name">اسم المالك</label>
                    <input type="text" name="owner_name" class="form-control" value="{{ $wallet->owner_name }}" required>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> حفظ التعديلات
                </button>
                <a href="{{ route('admin.wallets.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
