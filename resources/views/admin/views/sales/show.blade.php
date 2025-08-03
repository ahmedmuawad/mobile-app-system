@extends('layouts.app')

@section('title', 'تفاصيل الفاتورة #' . $sale->id)

@section('content')
@php
    $setting = \App\Models\Setting::first();
    $totalBeforeTax = $sale->saleItems->sum(fn($i) => $i->quantity * $i->purchase_price);
    $totalTax = $sale->saleItems->sum(fn($i) => $i->quantity * ($i->sale_price - $i->purchase_price));
    $totalAfterTax = $sale->total;
@endphp

<div class="container-fluid" id="invoice-content">
    <div class="d-print-none mb-4">
        <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">عودة للقائمة</a>
        <button onclick="printInvoice('a4')" class="btn btn-primary">🖨️ طباعة A4</button>
        <button onclick="printInvoice('thermal')" class="btn btn-dark">🧾 طباعة حرارية</button>
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
        <table class="table table-bordered">
            <tr>
                <th>العميل</th>
                <td>{{ $sale->customer?->name ?? '-' }}</td>
            </tr>
            <tr>
                <th>اسم العميل (يدوي)</th>
                <td>{{ $sale->customer_name ?? '-' }}</td>
            </tr>

            @if($sale->discount > 0)
                <tr>
                    <th>إجمالي بدون خصم</th>
                    <td>{{ number_format($sale->total + $sale->discount, 2) }} جنيه</td>
                </tr>
                <tr>
                    <th>الخصم</th>
                    <td>{{ number_format($sale->discount, 2) }} جنيه</td>
                </tr>
            @endif

            <tr>
                <th>الإجمالي النهائي</th>
                <td>{{ number_format($sale->total, 2) }} جنيه</td>
            </tr>

            <tr>
                <th>تاريخ الإنشاء</th>
                <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
            </tr>
        </table>

        <!-- تفاصيل الأصناف -->
        <h4 class="mt-4">تفاصيل الأصناف:</h4>
        <table class="table table-striped table-bordered text-center">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>الكمية</th>
                    <th>السعر قبل الضريبة</th>
                    <th>نسبة الضريبة</th>
                    <th>قيمة الضريبة</th>
                    <th>السعر بعد الضريبة</th>
                    <th>إجمالي الصنف</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $item)
                    @php
                        $product = \App\Models\Product::find($item->product_id);
                        $taxRate = $product?->tax_percentage ?? 0;

                        if ($product && $product->is_tax_included) {
                            // السعر شامل الضريبة
                            $base = $item->sale_price / (1 + $taxRate / 100);
                            $taxValue = $item->sale_price - $base;
                        } else {
                            // السعر غير شامل الضريبة
                            $base = $item->sale_price;
                            $taxValue = $base * ($taxRate / 100);
                        }
                        $subtotal = $item->sale_price * $item->quantity;
                    @endphp
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($base, 2) }}</td>
                        <td>{{ $taxRate }}%</td>
                        <td>{{ number_format($taxValue, 2) }}</td>
                        <td>
                            @if($product && !$product->is_tax_included)
                                {{ number_format($base + $taxValue, 2) }}
                            @else
                                {{ number_format($item->sale_price, 2) }}
                            @endif
                        </td>
                        <td>
                            @if($product && !$product->is_tax_included)
                                {{ number_format(($base + $taxValue) * $item->quantity, 2) }}
                            @else
                                {{ number_format($item->sale_price * $item->quantity, 2) }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6">الإجمالي الكلي</th>
                    <th>
                        {{ number_format($sale->saleItems->sum(fn($i) => $i->quantity * $i->sale_price), 2) }} جنيه
                    </th>
                </tr>
            </tfoot>
        </table>

        <!-- تفاصيل المدفوعات -->
        <h4 class="mt-4">المدفوعات:</h4>
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
                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d H:i') }}</td>
                            <td>{{ number_format($payment->amount, 2) }} جنيه</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- إجمالي قبل الضريبة -->
        <div>
            <strong>الإجمالي قبل الضريبة:</strong>
            {{ number_format($totalBeforeTax, 2) }}
        </div>

        <!-- قيمة الضريبة -->
        <div>
            <strong>قيمة الضريبة:</strong>
            {{ number_format($totalTax, 2) }}
        </div>

        <!-- الإجمالي بعد الضريبة -->
        <div>
            <strong>الإجمالي بعد الضريبة:</strong>
            {{ number_format($totalAfterTax, 2) }}
        </div>

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
