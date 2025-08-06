@extends('layouts.app')

@section('title', 'تفاصيل الفاتورة #' . $sale->id)

@section('content')
@php
    $setting = \App\Models\Setting::first();

    // حساب الإجماليات بدقة من بيانات الأصناف
    $totalBeforeTax = 0;
    $totalTax = 0;
    $totalAfterTax = 0;
@endphp

<div class="container-fluid" id="invoice-content">
    <div class="d-print-none mb-4">
    <div class="row">
        <div class="col-md-4 mb-2">
            <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary btn-block w-100">عودة للقائمة</a>
        </div>
        <div class="col-md-4 mb-2">
            <button onclick="printInvoice('a4')" class="btn btn-primary btn-block w-100">🖨️ طباعة A4</button>
        </div>
        <div class="col-md-4 mb-2">
            <button onclick="printInvoice('thermal')" class="btn btn-dark btn-block w-100">🧾 طباعة حرارية</button>
        </div>
    </div>
</div>


    <div id="print-area">
        <!-- رأس الفاتورة -->
        <div class="text-center mb-4">
            @if($setting?->logo)
                <img src="{{ asset('storage/' . $setting->logo) }}" style="height: 60px;" alt="شعار المتجر">
            @endif
            <h2>{{ $setting?->store_name ?? 'اسم المتجر' }}</h2>
            <h5>رقم الفاتورة: #{{ $sale->id }}</h5>
        </div>

        <!-- بيانات الفاتورة -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <tr class="no-print-col">
                    <th>العميل</th>
                    <td>{{ $sale->customer?->name ?? '-' }}</td>
                </tr>
                <tr class="no-print-col">
                    <th>اسم العميل (يدوي)</th>
                    <td>{{ $sale->customer_name ?? '-' }}</td>
                </tr>

                @if($sale->discount > 0)
                    <tr class="no-print-col">
                        <th>إجمالي بدون خصم</th>
                        <td>{{ number_format($sale->total + $sale->discount, 2) }} جنيه</td>
                    </tr>
                    <tr class="no-print-col">
                        <th>الخصم</th>
                        <td>{{ number_format($sale->discount, 2) }} جنيه</td>
                    </tr>
                @endif

                <tr>
                    <th class="no-print-col">الإجمالي النهائي</th>
                    <td class="no-print-col">{{ number_format($sale->total, 2) }} جنيه</td>
                </tr>

                <tr>
                    <th>تاريخ الإنشاء</th>
                    <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            </table>
        </div>
        {{-- تفاصيل الأصناف --}}
        <h4 class="mt-4">تفاصيل الأصناف:</h4>
    <div class="table-responsive">
        <table class="table table-striped table-bordered text-center">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>الكمية</th>
                    <th>السعر قبل الضريبة</th>
                    <th class="no-print-col">نسبة الضريبة</th>
                    <th class="no-print-col">قيمة الضريبة</th>
                    <th>السعر بعد الضريبة</th>
                    <th>إجمالي الصنف</th>
                    <th class="no-print-col">الضريبة مشمولة؟</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalBeforeTax = 0;
                    $totalTax = 0;
                    $totalAfterTax = 0;
                @endphp
                @foreach($sale->saleItems as $item)
                    @php
                        $subtotal = $item->sale_price * $item->quantity;
                        $totalBeforeTax += $item->base_price * $item->quantity;
                        $totalTax += $item->tax_value * $item->quantity;
                        $totalAfterTax += $subtotal;
                    @endphp
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->base_price, 2) }}</td>
                        <td class="no-print-col">{{ rtrim(rtrim(number_format($item->tax_percentage, 2), '0'), '.') }}%</td>
                        <td class="no-print-col">{{ number_format($item->tax_value, 2) }}</td>
                        <td>{{ number_format($item->sale_price, 2) }}</td>
                        <td>{{ number_format($item->sale_price * $item->quantity, 2) }}</td>
                        <td class="no-print-col">
                          {{ $item->tax_value > 0 && $item->base_price < $item->sale_price ? 'شامل' : 'غير شامل' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="no-print-col">
                <tr class="no-print-col">
                    <th class="no-print-col" colspan="5">الإجمالي الكلي</th>
                    <th class="no-print-col">{{ number_format($totalAfterTax, 2) }} جنيه</th>
                </tr>
            </tfoot>
        </table>
    </div>

        {{-- ملخص الضرائب --}}

        {{-- ملخص الفاتورة --}}
        <h4 class="mt-4">ملخص الفاتورة:</h4>
        <table class="table table-bordered text-center">
            <tr>
                <th>الإجمالي قبل الضريبة</th>
                <td>{{ number_format($totalBeforeTax, 2) }} جنيه</td>
            </tr>
            <tr>
                <th>قيمة الضريبة</th>
                <td>{{ number_format($totalTax, 2) }} جنيه</td>
            </tr>
            <tr>
                <th>الإجمالي بعد الضريبة</th>
                <td>{{ number_format($totalAfterTax, 2) }} جنيه</td>
            </tr>
            @if($sale->discount > 0)
            <tr>
                <th>الخصم</th>
                <td>{{ number_format($sale->discount, 2) }} جنيه</td>
            </tr>
            @endif
            <tr>
                <th>الإجمالي بعد الخصم</th>
                <td>{{ number_format($sale->total, 2) }} جنيه</td>
            </tr>
            <tr>
                <th>المدفوع</th>
                <td>{{ number_format($sale->paid, 2) }} جنيه</td>
            </tr>
            <tr>
                <th>المتبقي</th>
                <td>{{ number_format($sale->remaining, 2) }} جنيه</td>
            </tr>
        </table>

        @if($sale->customerPayments && $sale->customerPayments->count())
            <h5 class="mt-3">سجل الدفعات:</h5>
            <table class="table table-sm table-striped table-bordered text-center">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->customerPayments as $payment)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                            <td>{{ number_format($payment->amount, 2) }} جنيه</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- الفوتر -->
        <hr>
        <div class="text-center mt-3">
            @if($setting?->address)
                <div>العنوان: {{ $setting->address }}</div>
            @endif
            @if($setting?->phone)
                <div>رقم الهاتف: {{ $setting->phone }}</div>
            @endif
            @if($setting?->invoice_footer)
                <div class="mt-2"><strong>{{ $setting->invoice_footer }}</strong></div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function printInvoice(mode = 'a4') {
    const printWindow = window.open('', '', 'width=800,height=600');
    const content = document.getElementById('print-area').innerHTML;

    let style = `
        <style>
            body { font-family: 'Arial'; direction: rtl; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #000; padding: 6px; text-align: center; font-size: 14px; }
            h2, h4, h5 { text-align: center; margin: 5px 0; }
            img { display: block; margin: 0 auto; }
            @media print {
                .no-print-col, .no-print-col * { display: none !important; }
            }
        </style>
    `;

    if (mode === 'thermal') {
        style = `
            <style>
                @page { size: 80mm auto; margin: 5mm; }
                body { font-family: 'Tahoma'; direction: rtl; font-size: 12px; width: 80mm; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px dashed #000; padding: 4px; text-align: center; font-size: 12px; }
                h2, h4, h5 { text-align: center; margin: 2px 0; font-size: 14px; }
                img { display: block; margin: 0 auto; max-height: 50px; }
                            @media print {
                .no-print-col, .no-print-col * { display: none !important; }
            }

            </style>
        `;
    }

    printWindow.document.write(`
        <html>
        <head>
            <title>طباعة فاتورة</title>
            ${style}
        </head>
        <body>
            ${content}
        </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 500);
}
</script>
@endpush
