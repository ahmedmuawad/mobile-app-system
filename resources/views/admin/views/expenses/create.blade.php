@extends('layouts.app')

@section('title', 'إضافة مصروف')

@section('content')
<div class="container">
    <h4 class="mb-4">إضافة مصروف جديد</h4>

    <!-- رسائل الخطأ -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- الفورم -->
    <form action="{{ route('admin.expenses.store') }}" method="POST">
        @csrf

        @php
            $user = auth()->user();
            $branches = $user->branches ?? collect();
            $currentBranchId = session('current_branch_id');
        @endphp

        @if (!$currentBranchId && $branches->count() > 1)
            <div class="form-group mb-3">
                <label>اختر الفرع:</label>
                <select name="branch_id" class="form-control" required>
                    <option value="">-- اختر الفرع --</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                @error('branch_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
        @endif

        <div class="form-group mb-3">
            <label>اسم المصروف:</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="form-group mb-3">
            <label>الوصف (اختياري):</label>
            <textarea name="description" class="form-control">{{ old('description') }}</textarea>
            @error('description') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="form-group mb-3">
            <label>القيمة:</label>
            <input type="number" step="0.01" name="amount" class="form-control" required value="{{ old('amount') }}">
            @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="form-group mb-4">
            <label>التاريخ:</label>
            <input type="date" name="date" class="form-control" required value="{{ old('date', date('Y-m-d')) }}">
            @error('date') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <button class="btn btn-success">
            💾 حفظ
        </button>
    </form>
</div>
@endsection
