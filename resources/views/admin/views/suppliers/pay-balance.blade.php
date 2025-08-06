@extends('layouts.app')

@section('title', 'سداد المبلغ المستحق')

@section('content')
<div class="container">
    <h4>سداد المبلغ المستحق للمورد: {{ $supplier->name }}</h4>

    <p>الرصيد المستحق: <span class="text-danger">{{ number_format(abs($balance), 2) }} جنيه</span></p>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.payBalance', $supplier->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label>المبلغ المراد سداده</label>
            <input type="number" name="amount" max="{{ abs($balance) }}" min="0.01" step="0.01" required class="form-control" value="{{ old('amount') }}">
            @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            <label>تاريخ الدفع</label>
            <input type="date" name="payment_date" required class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}">
            @error('payment_date') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <button type="submit" class="btn btn-success mt-2">سداد</button>
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary mt-2">رجوع</a>
    </form>
</div>
<hr>
<h5 class="mt-4">سجل الدفعات السابقة</h5>

@if(isset($payments) && $payments->isNotEmpty())
        <div class="table-responsive">
        <table class="table table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>المبلغ</th>
                    <th>تاريخ الدفع</th>
                    <th>رقم الفاتورة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $index => $payment)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ number_format($payment->amount, 2) }} جنيه</td>
                        <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ route('admin.purchases.show', $payment->purchase_id) }}" target="_blank">
                                #{{ $payment->purchase_id }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@elseif(isset($payments))
    <p class="text-muted">لا توجد دفعات مسجلة لهذا المورد.</p>
@endif

@endsection
