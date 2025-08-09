@extends('layouts.app')

@section('content')

<style>
/* إخفاء عناصر معينة عند الطباعة */
@media print {
    body * {
        visibility: hidden;
    }
    .printable-area, .printable-area * {
        visibility: visible;
    }
    .printable-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<div class="container">
    <h3 class="mb-4">📜 سجل العميل: {{ $customer->name }}</h3>

    <div class="mb-3 no-print">
        <button class="btn btn-primary" onclick="window.print()">
            🖨️ طباعة
        </button>
        <a href="{{ route('admin.customers.history.export', $customer->id) }}" class="btn btn-success">
            📊 تصدير Excel
        </a>
    </div>

    <!-- Tabs للعرض فقط -->
    <ul class="nav nav-tabs no-print" id="customerHistoryTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="sales-tab" data-toggle="tab" href="#sales" role="tab">
                المبيعات
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="repairs-tab" data-toggle="tab" href="#repairs" role="tab">
                الصيانة
            </a>
        </li>
    </ul>

    <div class="tab-content mt-3 no-print">
        <!-- عرض المبيعات -->
        <div class="tab-pane fade show active" id="sales" role="tabpanel">
            @include('admin.views.customers.partials.customer_sales', ['sales' => $customer->sales])
        </div>

        <!-- عرض الصيانة -->
        <div class="tab-pane fade" id="repairs" role="tabpanel">
            @include('admin.views.customers.partials.customer_repairs', ['repairs' => $customer->repairs])
        </div>
    </div>

    <!-- نسخة للطباعة (المبيعات + الصيانة) -->
    <div class="printable-area" style="display:none;">
        <h4>المبيعات</h4>
        @include('admin.views.customers.partials.customer_sales', ['sales' => $customer->sales])

        <h4 class="mt-4">الصيانة</h4>
        @include('admin.views.customers.partials.customer_repairs', ['repairs' => $customer->repairs])
    </div>
</div>

<script>
    // إظهار نسخة الطباعة وقت الطباعة
    window.onbeforeprint = () => {
        document.querySelector('.printable-area').style.display = 'block';
    };
    window.onafterprint = () => {
        document.querySelector('.printable-area').style.display = 'none';
    };
</script>

@endsection
