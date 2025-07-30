@extends('layouts.app')
@section('title', 'إضافة حركة مالية')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">إضافة حركة مالية</h4>
                </div>
            </div>
        </div>
    </section>

    <div class="card card-primary">
        <form method="POST" action="{{ route('admin.wallet_transactions.store') }}">
            @csrf
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="card-body">
                <div class="form-group">
                    <label for="wallet_id">المحفظة</label>
                    <select name="wallet_id" class="form-control" required>
                        <option value="">اختر المحفظة</option>
                        @foreach($wallets as $wallet)
                            <option value="{{ $wallet->id }}">
                                {{ $wallet->number }} - {{ $wallet->provider->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="type">نوع العملية</label>
                    <select name="type" class="form-control" required>
                        <option value="">اختر النوع</option>
                        <option value="send">إرسال</option>
                        <option value="receive">استلام</option>
                        <option value="bill">فاتورة</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><strong>بيانات الحدود:</strong></label>
                    <div id="limit-info" style="background: #f8f9fa; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></div>
                </div>

                <div class="form-group">
                    <label for="amount">المبلغ</label>
                    <input type="number" name="amount" class="form-control" placeholder="أدخل المبلغ" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="commission">العمولة</label>
                    <input type="number" name="commission" class="form-control" placeholder="أدخل العمولة" step="0.01">
                </div>

                <div class="form-group">
                    <label for="target_number">رقم المستلم (في حالة التحويل)</label>
                    <input type="text" name="target_number" class="form-control" placeholder="مثال: 01012345678">
                </div>

                <div class="form-group">
                    <label for="note">ملاحظات</label>
                    <textarea name="note" class="form-control" rows="3" placeholder="ملاحظات إضافية (اختياري)"></textarea>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> حفظ
                </button>
                <a href="{{ route('admin.wallet_transactions.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection

@php
    $limits = $wallets->mapWithKeys(function($wallet) {
        return [$wallet->id => [
            'provider' => $wallet->provider->name,
            'remaining_daily_send' => $wallet->provider->getRemainingDailyByType('send'),
            'remaining_daily_receive' => $wallet->provider->getRemainingDailyByType('receive'),
            'remaining_daily_bill' => $wallet->provider->getRemainingDailyByType('bill'),
            'remaining_monthly' => $wallet->provider->remaining_monthly,
        ]];
    });
@endphp

@section('scripts') 
@php
    $limits = $wallets->mapWithKeys(function($wallet) {
        return [$wallet->id => [
            'provider' => $wallet->provider->name,
            'remaining_daily_send' => $wallet->provider->getRemainingDailyByType('send'),
            'remaining_daily_receive' => $wallet->provider->getRemainingDailyByType('receive'),
            'remaining_daily_bill' => $wallet->provider->getRemainingDailyByType('bill'),
            'remaining_monthly' => $wallet->provider->remaining_monthly,
        ]];
    });
@endphp

<script>
    const limits = @json($limits);

    function updateLimitInfo() {
        const walletId = document.querySelector('[name="wallet_id"]').value;
        const type = document.querySelector('[name="type"]').value;

        let info = '';
        if (walletId && type && limits[walletId]) {
            let limitInfo = limits[walletId];
            let remaining = 0;

            if (type === 'send') remaining = limitInfo.remaining_daily_send;
            if (type === 'receive') remaining = limitInfo.remaining_daily_receive;
            if (type === 'bill') remaining = limitInfo.remaining_daily_bill;

            info = `المتبقي اليومي لعملية (${type}): ${remaining.toLocaleString()} ج.م<br>
                    المتبقي الشهري: ${limitInfo.remaining_monthly.toLocaleString()} ج.م`;

            console.log("Wallet:", walletId, "Type:", type, "Data:", limitInfo);
        }

        document.getElementById('limit-info').innerHTML = info;
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelector('[name="wallet_id"]').addEventListener('change', updateLimitInfo);
        document.querySelector('[name="type"]').addEventListener('change', updateLimitInfo);
        updateLimitInfo(); // لعرض القيم عند تحميل الصفحة
    });
</script>
@endsection

