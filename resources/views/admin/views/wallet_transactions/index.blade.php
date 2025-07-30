@extends('layouts.app')
@section('title', 'الحركات المالية')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">قائمة الحركات المالية</h4>
                </div>
            </div>
        </div>
    </section>

    {{-- الكروت الخاصة بالحدود --}}
    <div class="row">
        @foreach($wallets as $wallet)
            @php
                $provider = $wallet->provider;
            @endphp
            <div class="col-md-4">
                <div class="card shadow-sm border border-primary">
                    <div class="card-header bg-primary text-white text-center font-weight-bold">
                        {{ $provider->name }}<br><small>{{ $wallet->number }}</small>
                    </div>
                    <div class="card-body p-2 text-right">
                        <p class="mb-1">📤 إرسال (يومي): 
                            <strong class="{{ $provider->getRemainingDailyByType('send') < 0 ? 'text-danger' : '' }}">
                                {{ number_format(max($provider->getRemainingDailyByType('send'), 0), 2) }} ج.م
                            </strong>
                        </p>
                        <p class="mb-1">📥 استلام (يومي): 
                            <strong class="{{ $provider->getRemainingDailyByType('receive') < 0 ? 'text-danger' : '' }}">
                                {{ number_format(max($provider->getRemainingDailyByType('receive'), 0), 2) }} ج.م
                            </strong>
                        </p>
                        <p class="mb-1">🧾 فواتير (يومي): 
                            <strong class="{{ $provider->getRemainingDailyByType('bill') < 0 ? 'text-danger' : '' }}">
                                {{ number_format(max($provider->getRemainingDailyByType('bill'), 0), 2) }} ج.م
                            </strong>
                        </p>
                        <hr class="my-2">
                        <p class="mb-0">📅 المتبقي الشهري: 
                            <strong class="{{ $provider->remaining_monthly < 0 ? 'text-danger' : '' }}">
                                {{ number_format(max($provider->remaining_monthly, 0), 2) }} ج.م
                            </strong>
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- الجدول --}}
    <div class="card mt-3">
        <div class="card-header">
            <a href="{{ route('admin.wallet_transactions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة حركة جديدة
            </a>
        </div>

        @if ($transactions->isEmpty())
            <p class="p-3">لا توجد حركات مالية حالياً.</p>
        @else
        <div class="card-body">
            <div class="card-body table-responsive p-0">
                <table id="transactions-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>المحفظة</th>
                            <th>النوع</th>
                            <th>المبلغ</th>
                            <th>العمولة</th>
                            <th>رقم التحويل إليه</th>
                            <th>ملاحظة</th>
                            <th style="width: 180px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $t)
                        <tr>
                            <td>{{ $t->wallet->number }} ({{ $t->wallet->provider->name ?? '-' }})</td>
                            <td>
                                @if ($t->type == 'send')
                                    إرسال
                                @elseif ($t->type == 'receive')
                                    استلام
                                @elseif ($t->type == 'bill')
                                    فاتورة
                                @else
                                    {{ $t->type }}
                                @endif
                            </td>
                            <td>{{ number_format($t->amount, 2) }} ج.م</td>
                            <td>{{ number_format($t->commission, 2) }} ج.م</td>
                            <td>{{ $t->target_number }}</td>
                            <td>{{ $t->note }}</td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i> اختر إجراء
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="{{ route('admin.wallet_transactions.edit', $t->id) }}" class="dropdown-item">
                                            <i class="fas fa-edit text-warning me-2"></i> تعديل
                                        </a>
                                        <form action="{{ route('admin.wallet_transactions.destroy', $t->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash-alt me-2"></i> حذف
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(function () {
        $('#transactions-table').DataTable({
            language: { url: "{{ asset('assets/admin/js/ar.json') }}" },
            responsive: true,
            autoWidth: false,
            paging: true,
            searching: true,
            ordering: true
        });
    });
</script>
@endpush
