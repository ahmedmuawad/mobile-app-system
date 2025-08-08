@extends('layouts.app')

@section('title', 'إضافة اشتراك جديد')



@section('content')
<div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">إضافة اشتراك جديد</h3>
            <div class="card-tools">
                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> العودة لقائمة الاشتراكات
                </a>
            </div>
        </div>
    <div class="card-body">
        <form action="{{ route('admin.subscriptions.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="company_id">الشركة</label>
                <select name="company_id" id="company_id" class="form-control @error('company_id') is-invalid @enderror" required>
                    <option value="">اختر الشركة</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
                @error('company_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="package_id">الباقة</label>
                <select name="package_id" id="package_id" class="form-control @error('package_id') is-invalid @enderror" required>
                    <option value="">اختر الباقة</option>
                    @foreach($packages as $package)
                        <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
                            {{ $package->name }}
                        </option>
                    @endforeach
                </select>
                @error('package_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="starts_at">تاريخ البداية</label>
                <input type="date" name="starts_at" id="starts_at" class="form-control @error('starts_at') is-invalid @enderror" value="{{ old('starts_at') }}" required>
                @error('starts_at')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="ends_at">تاريخ النهاية</label>
                <input type="date" name="ends_at" id="ends_at" class="form-control @error('ends_at') is-invalid @enderror" value="{{ old('ends_at') }}" required>
                @error('ends_at')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="status">الحالة</label>
                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
                @error('status')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-success">حفظ الاشتراك</button>
        </form>
    </div>
</div>
@endsection
