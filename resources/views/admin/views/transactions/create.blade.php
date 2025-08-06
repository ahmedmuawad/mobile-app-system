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
            <div class="card-body">
                @if(session()->has('current_branch_name'))
                    <div class="alert alert-info">
                        الفرع الحالي: {{ session('current_branch_name') }}
                    </div>
                @endif
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
                        <option value="deposit">إيداع</option>
                        <option value="withdraw">سحب</option>
                        <option value="transfer">تحويل</option>
                    </select>
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
