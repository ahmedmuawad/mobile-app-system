@extends('layouts.app')
@section('title', 'إضافة محفظة جديدة')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">إضافة محفظة جديدة</h4>
                </div>
            </div>
        </div>
    </section>

    @if(session('current_branch_id'))
        <div class="card card-primary">
            <form method="POST" action="{{ route('admin.wallets.store') }}">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="number">رقم المحفظة</label>
                        <input type="text" name="number" class="form-control" placeholder="أدخل رقم المحفظة" value="{{ old('number') }}" required>
                        @error('number')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="wallet_provider_id">مزود المحفظة</label>
                        <select name="wallet_provider_id" class="form-control" required>
                            <option value="">اختر المزود</option>
                            @foreach($providers as $provider)
                                <option value="{{ $provider->id }}" {{ old('wallet_provider_id') == $provider->id ? 'selected' : '' }}>
                                    {{ $provider->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('wallet_provider_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="owner_name">اسم المالك</label>
                        <input type="text" name="owner_name" class="form-control" placeholder="أدخل اسم المالك" value="{{ old('owner_name') }}" required>
                        @error('owner_name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> حفظ
                    </button>
                    <a href="{{ route('admin.wallets.index') }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    @else
        <div class="alert alert-warning">
            <strong>تنبيه:</strong> لم يتم تحديد الفرع الحالي. الرجاء اختيار فرع أولاً من أعلى الصفحة.
        </div>
    @endif
</div>
@endsection
