@extends('layouts.app')
@section('title', 'تعديل حركة مالية')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">تعديل حركة مالية</h4>
                </div>
            </div>
        </div>
    </section>

    <div class="card card-primary">
        <form method="POST" action="{{ route('admin.wallet_transactions.update', $walletTransaction) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label for="wallet_id">المحفظة</label>
                    <select name="wallet_id" class="form-control" required>
                        @foreach($wallets as $wallet)
                            <option value="{{ $wallet->id }}" {{ $walletTransaction->wallet_id == $wallet->id ? 'selected' : '' }}>
                                {{ $wallet->number }} - {{ $wallet->provider->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="type">نوع العملية</label>
                    <select name="type" class="form-control" required>
                        <option value="deposit" {{ $walletTransaction->type == 'deposit' ? 'selected' : '' }}>إيداع</option>
                        <option value="withdraw" {{ $walletTransaction->type == 'withdraw' ? 'selected' : '' }}>سحب</option>
                        <option value="transfer" {{ $walletTransaction->type == 'transfer' ? 'selected' : '' }}>تحويل</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="amount">المبلغ</label>
                    <input type="number" name="amount" class="form-control" step="0.01" value="{{ $walletTransaction->amount }}" required>
                </div>

                <div class="form-group">
                    <label for="commission">العمولة</label>
                    <input type="number" name="commission" class="form-control" step="0.01" value="{{ $walletTransaction->commission }}">
                </div>

                <div class="form-group">
                    <label for="target_number">رقم المستلم</label>
                    <input type="text" name="target_number" class="form-control" value="{{ $walletTransaction->target_number }}">
                </div>

                <div class="form-group">
                    <label for="note">ملاحظات</label>
                    <textarea name="note" class="form-control" rows="3">{{ $walletTransaction->note }}</textarea>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> حفظ التعديل
                </button>
                <a href="{{ route('admin.wallet_transactions.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
