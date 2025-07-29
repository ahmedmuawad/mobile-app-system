@extends('layouts.app')

@section('title', 'إضافة مصروف')

@section('content')
<div class="container">
    <h4>إضافة مصروف جديد</h4>
    <form action="{{ route('admin.expenses.update', $expense->id) }}" method="POST">
    @csrf
    @method('PUT')
        
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
            <input type="date" name="date" class="form-control" required value="{{ old('date', date('Y-m-d')) }}">
        </div>

        <button class="btn btn-success">💾 حفظ</button>
    </form>
</div>
@endsection
