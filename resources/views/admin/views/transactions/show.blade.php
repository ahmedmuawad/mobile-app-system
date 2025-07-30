@extends('layouts.app')
@section('title', 'عرض تفاصيل الحركة المالية')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">عرض تفاصيل الحركة المالية</h4>
                </div>
            </div>
        </div>
    </section>

    <div class="card card-info">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">المحفظة:</dt>
                <dd class="col-sm-9">
                    {{ $walletTransaction->wallet->number }} ({{ $walletTransaction->wallet->provider->name }})
                </dd>

                <dt class="col-sm-3">نوع الحركة:</dt>
                <dd class="col-sm-9">
                    @php
                        $types = ['deposit' => 'إيداع', 'withdraw' => 'سحب', 'transfer' => 'تحويل'];
                    @endphp
                    {{ $types[$walletTransaction->type] ?? $walletTransaction->type }}
                </dd>

                <dt class="col-sm-3">المبلغ:</dt>
                <dd class="col-sm-9">{{ number_format($walletTransaction->amount, 2) }}</dd>

                <dt class="col-sm-3">العمولة:</dt>
                <dd class="col-sm-9">{{ number_format($walletTransaction->commission, 2) }}</dd>

                <dt class="col-sm-3">رقم المستلم:</dt>
                <dd class="col-sm-9">{{ $walletTransaction->target_number ?? '-' }}</dd>

                <dt class="col-sm-3">ملاحظات:</dt>
                <dd class="col-sm-9">
                    {!! $walletTransaction->note ? e($walletTransaction->note) : '<span class="text-muted">لا يوجد</span>' !!}
                </dd>

                <dt class="col-sm-3">تاريخ الإنشاء:</dt>
                <dd class="col-sm-9">{{ $walletTransaction->created_at->format('Y-m-d H:i') }}</dd>

                <dt class="col-sm-3">آخر تحديث:</dt>
                <dd class="col-sm-9">{{ $walletTransaction->updated_at->format('Y-m-d H:i') }}</dd>
            </dl>
        </div>

        <div class="card-footer text-right">
            <a href="{{ route('admin.wallet_transactions.edit', $walletTransaction->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> تعديل
            </a>
            <a href="{{ route('admin.wallet_transactions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> رجوع
            </a>
        </div>
    </div>
</div>
@endsection
