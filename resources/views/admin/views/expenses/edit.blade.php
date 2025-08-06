@extends('layouts.app')

@section('title', 'تعديل مصروف')

@section('content')
<div class="container">
    <h4>تعديل المصروف</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.expenses.update', $expense->id) }}" method="POST">
        @csrf
        @method('PUT')

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
                        <option value="{{ $branch->id }}" {{ old('branch_id', $expense->branch_id) == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                @error('branch_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
        @endif

        <div class="form-group">
            <label>اسم المصروف:</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name', $expense->name) }}">
        </div>

        <div class="form-group">
            <label>الوصف (اختياري):</label>
            <textarea name="description" class="form-control">{{ old('description', $expense->description) }}</textarea>
        </div>

        <div class="form-group">
            <label>القيمة:</label>
            <input type="number" step="0.01" name="amount" class="form-control" required value="{{ old('amount', $expense->amount) }}">
        </div>

        <div class="form-group">
            <label>التاريخ:</label>
            <input type="date" name="date" class="form-control" required value="{{ old('date', date('Y-m-d', strtotime($expense->expense_date))) }}">

        </div>

        <button class="btn btn-success">💾 حفظ</button>
    </form>
</div>
@endsection
