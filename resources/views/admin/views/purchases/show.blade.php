@extends('layouts.app')
@section('title', 'تفاصيل الفاتورة #' . $purchase->id)

@section('content')
@php
    $setting = \App\Models\Setting::first();
@endphp
<div class="container">

   
    <div class="d-print-none mb-4">
        <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary">عودة للقائمة</a>
        <button onclick="printInvoice('a4')" class="btn btn-primary">🖨️ طباعة A4</button>
        <button onclick="printInvoice('thermal')" class="btn btn-dark">🧾 طباعة حرارية</button>
    </div>
<div id="print-area">
     <h2> فاتورة شراء #{{ $purchase->id }}</h2>
    <div class="card mb-4">
        <div class="card-body">
            <p><strong>المورد:</strong> {{ $purchase->supplier->name }}</p>
            <p><strong>تاريخ الإنشاء:</strong> {{ $purchase->created_at->format('Y-m-d') }}</p>
            <p><strong>إجمالي الفاتورة:</strong> {{ number_format($purchase->total_amount, 2) }}</p>
            <p><strong>المدفوع:</strong> {{ number_format($purchase->paid_amount, 2) }}</p>
            <p><strong>المتبقي:</strong> {{ number_format($purchase->remaining_amount, 2) }}</p>
            <p><strong>ملاحظات:</strong> {{ $purchase->notes }}</p>
        </div>
    </div>

    <h4>العناصر</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>المنتج</th>
                <th>الكمية</th>
                <th>سعر الوحدة</th>
                <th>الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $item)
                <tr>
                    <td>{{ $item->product->name ?? '-' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h4 class="mt-4">المدفوعات</h4>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>المبلغ</th>
                <th>تاريخ الدفع</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchase->payments as $payment)
                <tr>
                    <td>{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="text-center">لا يوجد مدفوعات</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
    <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary mt-3">رجوع</a>

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
