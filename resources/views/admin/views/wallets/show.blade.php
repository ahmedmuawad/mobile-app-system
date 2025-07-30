@extends('layouts.app')
@section('title', 'عرض تفاصيل المحفظة')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">عرض تفاصيل المحفظة</h4>
                </div>
            </div>
        </div>
    </section>

    <div class="card card-info">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">رقم المحفظة:</dt>
                <dd class="col-sm-9">{{ $wallet->number }}</dd>

                <dt class="col-sm-3">اسم المالك:</dt>
                <dd class="col-sm-9">{{ $wallet->owner_name }}</dd>

                <dt class="col-sm-3">مزود المحفظة:</dt>
                <dd class="col-sm-9">{{ $wallet->provider->name }}</dd>

                <dt class="col-sm-3">تاريخ الإنشاء:</dt>
                <dd class="col-sm-9">{{ $wallet->created_at->format('Y-m-d H:i') }}</dd>
            </dl>
        </div>

        <div class="card-footer">
            <a href="{{ route('admin.wallets.edit', $wallet) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> تعديل
            </a>
            <a href="{{ route('admin.wallets.index') }}" class="btn btn-secondary">رجوع</a>
        </div>
    </div>
</div>
@endsection
