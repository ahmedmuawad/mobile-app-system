@extends('layouts.app')

@section('title', 'عرض فاتورة الصيانة')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
    <h3 class="card-title mb-2 mb-md-0 text-center text-md-left w-100 w-md-auto">
        🧾 تفاصيل فاتورة صيانة #{{ $repair->id }}
    </h3>

    <div class="d-print-none d-flex flex-wrap justify-content-center justify-content-md-end w-100 w-md-auto">
        <a href="{{ route('admin.repairs.index') }}" class="btn btn-secondary btn-sm m-1">↩️ رجوع</a>
        <a href="{{ route('admin.repairs.edit', $repair->id) }}" class="btn btn-warning btn-sm m-1">✏️ تعديل</a>
        <button onclick="printReceipt('a4')" class="btn btn-primary btn-sm m-1">🖨️ طباعة A4</button>
        <button onclick="printReceipt('thermal')" class="btn btn-dark btn-sm m-1">🧾 طباعة حرارية</button>
    </div>
</div>

        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>العميل:</strong> {{ $repair->customer->name ?? $repair->customer_name ?? '---' }}
                </div>
                <div class="col-md-6">
                    <strong>رقم الهاتف:</strong> {{ $repair->customer->phone ?? '---' }}
                </div>
            </div>

            <div class="mb-3">
                <strong>نوع الجهاز:</strong> {{ $repair->device_type }}
            </div>

            <div class="mb-3">
                <strong>وصف العطل:</strong> @if($repair->spareParts->isNotEmpty())
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>اسم قطعة الغيار</th>
                <th>سعر القطعة</th>
                <th>الكمية</th>
            </tr>
        </thead>
        <tbody>
            @foreach($repair->spareParts as $sparePart)
                <tr>
                    <td>{{ $sparePart->name }}</td>
                    <td>{{ number_format($sparePart->sale_price, 2) }} جنيه</td>
                    <td>{{ $sparePart->pivot->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>لا توجد قطع غيار مرفقة.</p>
@endif

            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>قطعة الغيار:</strong> {{ $repair->sparePart->name ?? '---' }}
                </div>
                <div class="col-md-6">
                    <strong>سعر القطعة:</strong>
                    {{ $repair->sparePart ? number_format($repair->sparePart->sale_price, 2) . ' جنيه' : '---' }}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>تكلفة المصنعية:</strong> {{ number_format($repair->repair_cost, 2) }} جنيه
                </div>
                <div class="col-md-4">
                    <strong>الخصم:</strong> {{ number_format($repair->discount, 2) }} جنيه
                </div>
                <div class="col-md-4">
                    <strong>الإجمالي:</strong> {{ number_format($repair->total, 2) }} جنيه
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>المدفوع:</strong> {{ number_format($repair->payments->sum('amount'), 2) }} جنيه
                </div>
                <div class="col-md-4">
                    <strong>المتبقي:</strong> {{ number_format($repair->total - $repair->payments->sum('amount'), 2) }} جنيه
                </div>
                <div class="col-md-4">
                    <strong>الحالة:</strong>
                    <span class="badge bg-{{ $repair->status === 'تم الإصلاح' ? 'success' : ($repair->status === 'لم يتم الإصلاح' ? 'danger' : 'warning') }}">
                        {{ $repair->status }}
                    </span>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>تاريخ الإنشاء:</strong> {{ $repair->created_at->format('Y-m-d H:i') }}
                </div>
                <div class="col-md-4">
                    <strong>حالة التسليم:</strong>
                    <span class="badge bg-{{ $repair->delivery_status === 'delivered' ? 'success' : ($repair->delivery_status === 'rejected' ? 'danger' : 'secondary') }}">
                        {{ $repair->delivery_status === 'delivered' ? 'تم التسليم' : ($repair->delivery_status === 'rejected' ? 'الجهاز مرفوض - استرجاع المبلغ' : 'لم يتم التسليم') }}
                    </span>
                </div>
            </div>

            @if($repair->total - $repair->payments->sum('amount') > 0)
                <a href="{{ route('admin.repairs.payments.create', $repair->id) }}" class="btn btn-success mb-3">
                    💵 سداد المتبقي
                </a>
            @endif

            @if($repair->payments->count() > 0)
                <h5>💳 الدفعات السابقة:</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>المبلغ</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($repair->payments as $payment)
                                <tr>
                                    <td>{{ number_format($payment->amount, 2) }} جنيه</td>
                                    <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const store = @json($globalSetting);
    const repair = @json($repair);
    const sparePart = @json($repair->sparePart);

    function printReceipt(mode = 'a4') {
        const isThermal = mode === 'thermal';

        const content = `
            <div class="invoice-wrapper">
                <div class="header">
                    ${store.logo_url ? `<img src="${store.logo_url}" class="logo">` : ''}
                    <h2>${store.store_name}</h2>
                    <p>${store.address}</p>
                    <p>${store.phone}</p>
                    <hr>
                </div>

                <div class="section">
                    <p><strong>فاتورة صيانة رقم:</strong> #${repair.id}</p>
                    <p><strong>التاريخ:</strong> ${new Date(repair.created_at).toLocaleString()}</p>
                </div>

                <div class="section">
                    <p><strong>العميل:</strong> ${repair.customer?.name || repair.customer_name || '---'}</p>
                    <p><strong>رقم الهاتف:</strong> ${repair.customer?.phone || '---'}</p>
                </div>

                <hr>
                <style>
                    table th {
                        font-size: 13px; /* أو اختر الحجم الذي يناسبك */
                    }
                        table td {
                        font-size: 12px; /* أو اختر الحجم الذي يناسبك */
                    }
                </style>
                <div class="section">

                    <p><strong>نوع الجهاز:</strong> ${repair.device_type}</p>
                    <p><strong>وصف العطل:</strong> ${repair.problem_description}</p>
                    <p><strong>قطع الغيار:</strong></p>
                            @if($repair->spareParts->isNotEmpty())
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>اسم قطعة الغيار</th>
                                            <th>الكمية</th>
                                            <th>سعر القطعة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($repair->spareParts as $sparePart)
                                            <tr>
                                                <td>{{ $sparePart->name }}</td>
                                                <td>{{ $sparePart->pivot->quantity }}</td>
                                                <td>{{ number_format($sparePart->sale_price, 2) }} جنيه</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p>لا توجد قطع غيار مرفقة.</p>
                            @endif


                </div>

                <hr>

                <div class="section total">
                    <p><strong>تكلفة المصنعية:</strong> ${parseFloat(repair.repair_cost).toFixed(2)} جنيه</p>
                    <p><strong>الخصم:</strong> ${parseFloat(repair.discount || 0).toFixed(2)} جنيه</p>
                    <p><strong>الإجمالي:</strong> ${parseFloat(repair.total).toFixed(2)} جنيه</p>
                    <p><strong>المدفوع:</strong> ${parseFloat(repair.payments.reduce((acc, p) => acc + p.amount, 0)).toFixed(2)} جنيه</p>
                    <p><strong>المتبقي:</strong> ${(parseFloat(repair.total) - parseFloat(repair.payments.reduce((acc, p) => acc + p.amount, 0))).toFixed(2)} جنيه</p>
                    <p><strong>الحالة:</strong> ${repair.status}</p>
                </div>

                <hr>

                ${store.invoice_footer ? `<div class="footer">${store.invoice_footer} </br>تصميم وبرمجة ستوب جروب للبرمجيات 01030889618</div>` : ''}

            </div>
        `;

        const styles = `
            <style>
                body {
                    font-family: Tahoma, Arial, sans-serif;
                    direction: rtl;
                    text-align: right;
                    margin: 0;
                    padding: 20px;
                    font-size: ${isThermal ? '12px' : '14px'};
                }
                .invoice-wrapper {
                    max-width: ${isThermal ? '250px' : '700px'};
                    margin: auto;
                }
                .logo {
                    max-height: 80px;
                    display: block;
                    margin: 0 auto 10px;
                }
                h2 {
                    text-align: center;
                    margin: 5px 0;
                    font-size: ${isThermal ? '16px' : '22px'};
                }
                .section {
                    margin-bottom: 10px;
                }
                .section p {
                    margin: 3px 0;
                }
                .total p {
                    font-weight: bold;
                }
                hr {
                    border: 1px dashed #aaa;
                    margin: 10px 0;
                }
                .footer {
                    text-align: center;
                    margin-top: 10px;
                    font-size: ${isThermal ? '10px' : '13px'};
                }
                @page {
                    size: ${isThermal ? '80mm auto' : 'A4'};
                    margin: 10mm;
                }
            </style>
        `;

        const win = window.open('', '', 'width=800,height=600');
        win.document.write(`<html><head><title>فاتورة الصيانة</title>${styles}</head><body>${content}</body></html>`);
        win.document.close();
        win.focus();
        setTimeout(() => {
            win.print();
            win.close();
        }, 500);
    }
</script>
@endpush

