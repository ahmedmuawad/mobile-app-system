@extends('layouts.app')
@section('title', 'إضافة مزود محفظة')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">إضافة مزود محفظة</h4>
                </div>
            </div>
        </div>
    </section>

    <div class="card card-primary">
        <form method="POST" action="{{ route('admin.wallet_providers.store') }}">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="name">اسم المزود</label>
                    <input type="text" name="name" class="form-control" placeholder="اسم المزود" required>
                </div>

                <div class="form-group">
                    <label for="daily_send_limit">الحد اليومي للإرسال</label>
                    <input type="number" name="daily_send_limit" class="form-control" placeholder="مثال: 50000" required>
                </div>

                <div class="form-group">
                    <label for="daily_receive_limit">الحد اليومي للاستلام</label>
                    <input type="number" name="daily_receive_limit" class="form-control" placeholder="مثال: 50000" required>
                </div>

                <div class="form-group">
                    <label for="daily_bill_limit">الحد اليومي للفواتير</label>
                    <input type="number" name="daily_bill_limit" class="form-control" placeholder="مثال: 20000" required>
                </div>

                <div class="form-group">
                    <label for="monthly_limit">الحد الشهري الكلي</label>
                    <input type="number" name="monthly_limit" class="form-control" placeholder="مثال: 200000" required>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> حفظ</button>
                <a href="{{ route('admin.wallet_providers.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
