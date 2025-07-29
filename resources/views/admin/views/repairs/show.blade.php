@extends('layouts.app')

@section('title', 'عرض فاتورة الصيانة')

@section('content')
<div class="container">
    <h3 class="mb-4">🧾 تفاصيل فاتورة صيانة #{{ $repair->id }}</h3>

    {{-- أزرار --}}
    <div class="d-print-none mb-4">
        <a href="{{ route('admin.repairs.index') }}" class="btn btn-secondary">↩️ رجوع</a>
        <a href="{{ route('admin.repairs.edit', $repair->id) }}" class="btn btn-warning">✏️ تعديل</a>
        <button onclick="printReceipt('a4')" class="btn btn-primary">🖨️ طباعة A4</button>
        <button onclick="printReceipt('thermal')" class="btn btn-dark">🧾 طباعة حرارية</button>
    </div>

    {{-- تفاصيل الفاتورة --}}
    <div class="card">
        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>العميل:</strong>
                    {{ $repair->customer->name ?? $repair->customer_name ?? '---' }}
                </div>
                <div class="col-md-6">
                    <strong>رقم الهاتف:</strong>
                    {{ $repair->customer->phone ?? '---' }}
                </div>
            </div>

            <div class="mb-3">
                <strong>نوع الجهاز:</strong> {{ $repair->device_type }}
            </div>

            <div class="mb-3">
                <strong>وصف العطل:</strong> {{ $repair->problem_description }}
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
            <p><strong>الإجمالي:</strong> {{ number_format($repair->total, 2) }} جنيه</p>
            <p><strong>المدفوع:</strong> {{ number_format($repair->payments->sum('amount'), 2) }} جنيه</p>
            <p><strong>المتبقي:</strong> {{ number_format($repair->total - $repair->payments->sum('amount'), 2) }} جنيه</p>

            <div class="mb-3">
                <strong>الحالة:</strong>
                <span class="badge bg-{{ $repair->status === 'تم الإصلاح' ? 'success' : ($repair->status === 'لم يتم الإصلاح' ? 'danger' : 'warning') }}">
                    {{ $repair->status }}
                </span>
            </div>
           

            <div class="mb-3">
                <strong>تاريخ الإنشاء:</strong> {{ $repair->created_at->format('Y-m-d H:i') }}
            </div>

            @if($repair->total - $repair->payments->sum('amount') > 0)
    <a href="{{ route('admin.repairs.payments.create', $repair->id) }}" class="btn btn-success">
        💵 سداد المتبقي
    </a>

    <h4>الدفعات السابقة:</h4>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>المبلغ</th>
            <th>التاريخ</th>
        </tr>
    </thead>
    <tbody>
        @forelse($repair->payments as $payment)
            <tr>
                <td>{{ number_format($payment->amount, 2) }} جنيه</td>
                <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2">لا يوجد دفعات مسجلة.</td>
            </tr>
        @endforelse
    </tbody>
</table>

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
        const content = `
            <div style="text-align:center;">
                ${store.logo_url ? `<img src='${store.logo_url}' style='max-height:80px;'><br>` : ''}
                <strong>${store.store_name}</strong><br>
                <hr>
            </div>

            <p><strong>فاتورة صيانة رقم:</strong> ${repair.id}</p>
            <p><strong>العميل:</strong> ${repair.customer?.name || repair.customer_name || '---'}</p>
            <p><strong>رقم الهاتف:</strong> ${repair.customer?.phone || '---'}</p>
            <p><strong>نوع الجهاز:</strong> ${repair.device_type}</p>
            <p><strong>وصف العطل:</strong> ${repair.problem_description}</p>
            <p><strong>قطعة الغيار:</strong> ${sparePart?.name || '---'}</p>
            <p><strong>سعر القطعة:</strong> ${sparePart?.sale_price ? parseFloat(sparePart.sale_price).toFixed(2) : '0.00'} جنيه</p>
            <p><strong>تكلفة المصنعية:</strong> ${parseFloat(repair.repair_cost).toFixed(2)} جنيه</p>
            <p><strong>الخصم:</strong> ${parseFloat(repair.discount || 0).toFixed(2)} جنيه</p>
            <p><strong>الإجمالي:</strong> ${parseFloat(repair.total || 0).toFixed(2)} جنيه</p>
            <p><strong>الحالة:</strong> ${repair.status}</p>
            <p><strong>التاريخ:</strong> ${new Date(repair.created_at).toLocaleString()}</p>
            <hr>
            ${store.address}<br>
            ${store.phone}<br>
            ${store.invoice_footer || ''}
        `;

        const style = `
            <style>
                body { direction: rtl; font-family: Tahoma, Arial; padding: 10px; }
                h3 { margin: 0 0 10px 0; }
                p { margin: 3px 0; }
                @page { size: ${mode === 'thermal' ? '80mm auto' : 'A4'}; margin: 10px; }
            </style>
        `;

        const printWindow = window.open('', '', 'width=800,height=600');
        printWindow.document.write(`<html><head><title>فاتورة الصيانة</title>${style}</head><body>${content}</body></html>`);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    }
</script>
@endpush
