@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="container-fluid">
             @php
                $user = auth()->user();

            $modules = $user->role === 'super_admin' ? ['all'] : $user->company->package->modules->pluck('slug')->toArray();
            @endphp




    <div class="row">
@if((is_array($modules) && in_array('sales', $modules)) || $user->role === 'super_admin')
    <div class="col-12 col-md-4 mb-3">
        <div class="position-relative p-3 bg-light rounded shadow-sm border-start border-4 border-success">
            <small class="text-muted d-block mb-1">درج المبيعات</small>
            <h5 class="m-0 text-primary">{{ number_format($sales_drawer, 2) }} جنيه</h5>
            <i class="fas fa-cash-register position-absolute text-success-50" style="top:10px; right:10px; font-size: 1.5rem; opacity: 0.1;"></i>
        </div>
    </div>
@endif
@if((is_array($modules) && in_array('repair', $modules)) || $user->role === 'super_admin')
    <div class="col-12 col-md-4 mb-3">
        <div class="position-relative p-3 bg-light rounded shadow-sm border-start border-4 border-primary">
            <small class="text-muted d-block mb-1">درج الصيانة</small>
            <h5 class="m-0 text-primary">{{ number_format($repair_drawer, 2) }} جنيه</h5>
            <i class="fas fa-tools position-absolute text-primary-50" style="top:10px; right:10px; font-size: 1.5rem; opacity: 0.1;"></i>
        </div>
    </div>
@endif
@if((is_array($modules) && in_array('sales', $modules) && in_array('repairs', $modules)) || $user->role === 'super_admin')

    <div class="col-12 col-md-4 mb-3">
        <div class="position-relative p-3 bg-light rounded shadow-sm border-start border-4 border-warning">
            <small class="text-muted d-block mb-1">إجمالي الدرج</small>
            <h5 class="m-0 text-primary">{{ number_format($total_drawer, 2) }} جنيه</h5>
            <i class="fas fa-layer-group position-absolute text-warning-50" style="top:10px; right:10px; font-size: 1.5rem; opacity: 0.1;"></i>
        </div>

@endif
    </div>
    @if((is_array($modules) && in_array('wallets', $modules)) || $user->role === 'super_admin')

    <div class="row mt-4">
    <div class="col-12 mb-2">
        <h5>أرصدة مزودي المحافظ</h5>
    </div>

    @foreach ($wallet_providers as $provider)
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="small-box {{ $provider->balance >= 0 ? 'bg-success' : 'bg-danger' }}">
                <div class="inner">
                    <h4>{{ number_format($provider->balance, 2) }} جنيه</h4>
                    <p>{{ $provider->name }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
</div>

    <div class="row">



        {{-- إحصائيات اليوم --}}
        <div class="col-12 mb-3">
            <h4 class="mb-3">إحصائيات اليوم</h4>
        </div>

        @php
    $cards = [];

    if ((is_array($modules) && in_array('sales', $modules)) || $user->role === 'super_admin') {
        $cards[] = ['label' => 'مبيعات اليوم', 'value' => $today_sales, 'color' => 'success', 'icon' => 'fas fa-shopping-cart'];
        $cards[] = ['label' => 'أرباح اليوم', 'value' => $today_profit, 'color' => 'secondary', 'icon' => 'fas fa-chart-line'];
        $cards[] = ['label' => 'أرباح مبيعات اليوم', 'value' => $today_sales_product_profit + $today_repair_product_profit, 'color' => 'info', 'icon' => 'fas fa-money-bill'];
    }

    if ((is_array($modules) && in_array('expenses', $modules)) || $user->role === 'super_admin') {
        $cards[] = ['label' => 'إجمالي المصروفات', 'value' => $today_expenses, 'color' => 'danger', 'icon' => 'fas fa-dollar-sign'];
    }

    if ((is_array($modules) && in_array('repairs', $modules)) || $user->role === 'super_admin') {
        $cards[] = ['label' => 'مبيعات الصيانة', 'value' => $today_repairs, 'color' => 'primary', 'icon' => 'fas fa-cogs'];
        $cards[] = ['label' => 'أرباح قطع غيار الصيانة', 'value' => $today_repair_product_profit, 'color' => 'secondary', 'icon' => 'fas fa-box'];
        $cards[] = ['label' => 'مصنعية الصيانة والسوفتوير', 'value' => $today_repair_labor_profit, 'color' => 'warning', 'icon' => 'fas fa-tools'];
    }

    if ((is_array($modules) && in_array('purchases', $modules)) || $user->role === 'super_admin') {
        $cards[] = ['label' => 'مشتريات اليوم', 'value' => $today_purchases, 'color' => 'warning', 'icon' => 'fas fa-truck'];
    }

    if ((is_array($modules) && in_array('wallets', $modules)) || $user->role === 'super_admin') {
        $cards[] = ['label' => 'تحويلات مرسلة اليوم', 'value' => $today_wallet_send_total, 'color' => 'dark', 'icon' => 'fas fa-paper-plane'];
        $cards[] = ['label' => 'تحويلات مستلمة اليوم', 'value' => $today_wallet_receive_total, 'color' => 'info', 'icon' => 'fas fa-inbox'];
        $cards[] = ['label' => 'عمولات التحويلات اليوم', 'value' => $today_wallet_commission, 'color' => 'success', 'icon' => 'fas fa-percentage'];
        $cards[] = ['label' => 'صافي التحويلات اليوم', 'value' => $today_wallet_receive_total - $today_wallet_send_total, 'color' => ($today_wallet_receive_total - $today_wallet_send_total) >= 0 ? 'success' : 'danger', 'icon' => 'fas fa-exchange-alt'];
    }
@endphp
@foreach($cards as $card)
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="small-box bg-{{ $card['color'] }}">
            <div class="inner">
                <h4>{{ number_format($card['value'], 2) }}</h4>
                <p>{{ $card['label'] }}</p>
            </div>
            <div class="icon">
                <i class="{{ $card['icon'] }}"></i>
            </div>
            <a href="#" class="small-box-footer">
                المزيد <i class="fas fa-arrow-circle-left"></i>
            </a>
        </div>
    </div>
@endforeach


        {{-- رسم بياني لآخر 7 أيام --}}
        <div class="col-12 mt-5">
            <h4 class="mb-3">آخر 7 أيام</h4>
            <canvas id="weeklyChart" height="120"></canvas>
        </div>

        {{-- ملخص الشهر --}}
        <div class="col-12 mt-5">
            <h4 class="mb-3">ملخص الشهر الحالي</h4>
        </div>

        @php
    $month_cards = [];

    if ((is_array($modules) && in_array('sales', $modules)) || $user->role === 'super_admin') {
        $month_cards[] = ['label' => 'مبيعات الشهر', 'value' => $month_sales, 'color' => 'success', 'icon' => 'fas fa-shopping-cart'];
        $month_cards[] = ['label' => 'أرباح الشهر', 'value' => $month_profit, 'color' => 'secondary', 'icon' => 'fas fa-chart-line'];
    }

    if ((is_array($modules) && in_array('expenses', $modules)) || $user->role === 'super_admin') {
        $month_cards[] = ['label' => 'مصروفات الشهر', 'value' => $month_expenses, 'color' => 'danger', 'icon' => 'fas fa-dollar-sign'];
    }

    if ((is_array($modules) && in_array('repairs', $modules)) || $user->role === 'super_admin') {
        $month_cards[] = ['label' => 'مبيعات الصيانة', 'value' => $month_repairs, 'color' => 'primary', 'icon' => 'fas fa-cogs'];
        $month_cards[] = ['label' => 'أرباح قطع غيار الصيانة', 'value' => $month_repair_product_profit, 'color' => 'secondary', 'icon' => 'fas fa-box'];
        $month_cards[] = ['label' => 'مصنعية الصيانة والسوفتوير', 'value' => $month_repair_labor_profit, 'color' => 'warning', 'icon' => 'fas fa-tools'];
    }

    if ((is_array($modules) && in_array('purchases', $modules)) || $user->role === 'super_admin') {
        $month_cards[] = ['label' => 'مشتريات الشهر', 'value' => $month_purchases, 'color' => 'warning', 'icon' => 'fas fa-truck'];
    }
@endphp

@foreach($month_cards as $card)
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="small-box bg-{{ $card['color'] }}">
            <div class="inner">
                <h4>{{ number_format($card['value'], 2) }}</h4>
                <p>{{ $card['label'] }}</p>
            </div>
            <div class="icon">
                <i class="{{ $card['icon'] }}"></i>
            </div>
            <a href="#" class="small-box-footer">
                المزيد <i class="fas fa-arrow-circle-left"></i>
            </a>
        </div>
    </div>
@endforeach


    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('weeklyChart').getContext('2d');
    const weeklyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($last_7_days->pluck('date')),
            datasets: [{
                label: 'إجمالي المبيعات',
                data: @json($last_7_days->pluck('total')),
                backgroundColor: 'rgba(0, 123, 255, 0.3)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 10
                    }
                }
            }
        }
    });
</script>
@endpush
