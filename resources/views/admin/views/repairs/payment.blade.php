@extends('layouts.app')

@section('title', 'سداد مستحقات فاتورة')

@section('content')
<div class="container-fluid">
    <div class="card card-success">
        <div class="card-header">
            <h4 class="card-title">💵 سداد مستحق لفاتورة #{{ $repair->id }}</h4>
        </div>

        @php
            $paidAmount = $repair->payments->sum('amount');
            $remaining = $repair->total - $paidAmount;
        @endphp

        <div class="card-body">
            <div class="mb-3">
                <p><strong>الإجمالي:</strong> {{ number_format($repair->total, 2) }} جنيه</p>
                <p><strong>المدفوع مسبقًا:</strong> {{ number_format($paidAmount, 2) }} جنيه</p>
                <p><strong>المتبقي:</strong> {{ number_format($remaining, 2) }} جنيه</p>
            </div>

            @if($remaining <= 0)
                <div class="alert alert-success">تم سداد كامل المبلغ لهذه الفاتورة.</div>
            @else
                <form action="{{ route('admin.repairs.payments.store', $repair->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="amount">المبلغ المسدّد الآن:</label>
                        <input type="number" name="amount" id="amount" step="0.01" class="form-control"
                            required max="{{ $remaining }}" value="{{ $remaining > 0 ? $remaining : '' }}" placeholder="أدخل المبلغ المطلوب سداده">
                    </div>

                    <div class="form-group text-end mt-3">
                        <button type="submit" class="btn btn-success">
                            💰 سداد المبلغ
                        </button>
                        <a href="{{ route('admin.repairs.index') }}" class="btn btn-secondary">رجوع</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
