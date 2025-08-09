@extends('layouts.app')

@section('title', 'تعديل الاشتراك')


@section('content')

<div class="card card-warning">
        <h1>تعديل الاشتراك</h1>
    <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary">عودة لقائمة الاشتراكات</a>

    <div class="card-body">
        <form action="{{ route('admin.subscriptions.update', $subscription) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="company_id">الشركة</label>
                <select name="company_id" id="company_id" class="form-control @error('company_id') is-invalid @enderror" required>
                    <option value="">اختر الشركة</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id', $subscription->company_id) == $company->id ? 'selected' : '' }}>
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
                        <option value="{{ $package->id }}" {{ old('package_id', $subscription->package_id) == $package->id ? 'selected' : '' }}>
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
                    <option value="active" {{ old('status', $subscription->status) == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ old('status', $subscription->status) == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
                @error('status')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-warning">تحديث الاشتراك</button>
        </form>
    </div>
</div>
@endsection
