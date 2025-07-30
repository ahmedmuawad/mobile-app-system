@extends('layouts.app')
@section('title', 'عرض مزود محفظة')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">عرض مزود المحفظة</h4>
                </div>
            </div>
        </div>
    </section>

    <div class="card">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">الاسم:</dt>
                <dd class="col-sm-9">{{ $walletProvider->name }}</dd>

                <dt class="col-sm-3">الوصف:</dt>
                <dd class="col-sm-9">{{ $walletProvider->description ?? 'لا يوجد' }}</dd>

                <dt class="col-sm-3">تاريخ الإنشاء:</dt>
                <dd class="col-sm-9">{{ $walletProvider->created_at->format('Y-m-d H:i') }}</dd>

                <dt class="col-sm-3">آخر تحديث:</dt>
                <dd class="col-sm-9">{{ $walletProvider->updated_at->format('Y-m-d H:i') }}</dd>
            </dl>
        </div>

        <div class="card-footer text-right">
            <a href="{{ route('admin.wallet_providers.edit', $walletProvider->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> تعديل
            </a>
            <a href="{{ route('admin.wallet_providers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> رجوع
            </a>
        </div>
    </div>
</div>
@endsection
