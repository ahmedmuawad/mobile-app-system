@extends('layouts.app')

@section('title', 'سداد مستحقات فاتورة')

@section('content')
<div class="container">
    <h4>سداد مستحق لفاتورة #{{ $repair->id }}</h4>
    @php
    $paidAmount = $repair->payments->sum('amount');
    $remaining = $repair->total - $paidAmount;
@endphp

    <p>الإجمالي: <strong>{{ number_format($repair->total, 2) }}</strong> جنيه</p>
    <p>المدفوع مسبقًا: <strong>{{ number_format($repair->payments->sum('amount'), 2) }}</strong> جنيه</p>
    <p>المتبقي: <strong>{{ number_format($repair->total - $repair->payments->sum('amount'), 2) }}</strong> جنيه</p>

<form action="{{ route('admin.repairs.payments.store', $repair->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label>المبلغ المسدّد الآن:</label>
            <input type="number" name="amount" step="0.01" class="form-control" required max="{{ $repair->total - $repair->payments->sum('amount') }}">
        </div>
        <button class="btn btn-success">سداد</button>
    </form>
</div>
@endsection
