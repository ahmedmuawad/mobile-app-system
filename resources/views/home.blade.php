@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="container-fluid">
    <div class="row">

        {{-- إحصائيات اليوم --}}
        <div class="col-12 mb-3">
            <h4 class="mb-3">إحصائيات اليوم</h4>
        </div>
        @php
            $cards = [
                ['label' => 'مبيعات اليوم', 'value' => $today_sales, 'color' => 'success'],
                ['label' => 'مصروفات اليوم', 'value' => $today_expenses, 'color' => 'danger'],
                ['label' => 'مبيعات الصيانة', 'value' => $today_repairs, 'color' => 'primary'],
                ['label' => 'مشتريات اليوم', 'value' => $today_purchases, 'color' => 'warning'],
                ['label' => 'أرباح اليوم', 'value' => $today_profit, 'color' => 'info'],
            ];
        @endphp

        @foreach($cards as $card)
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="small-box bg-{{ $card['color'] }}">
                    <div class="inner">
                        <h4>{{ number_format($card['value'], 2) }}</h4>
                        <p>{{ $card['label'] }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
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
            $month_cards = [
                ['label' => 'مبيعات الشهر', 'value' => $month_sales, 'color' => 'success'],
                ['label' => 'مصروفات الشهر', 'value' => $month_expenses, 'color' => 'danger'],
                ['label' => 'مبيعات الصيانة', 'value' => $month_repairs, 'color' => 'primary'],
                ['label' => 'مشتريات الشهر', 'value' => $month_purchases, 'color' => 'warning'],
                ['label' => 'أرباح الشهر', 'value' => $month_profit, 'color' => 'info'],
            ];
        @endphp

        @foreach($month_cards as $card)
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="small-box bg-{{ $card['color'] }}">
                    <div class="inner">
                        <h4>{{ number_format($card['value'], 2) }}</h4>
                        <p>{{ $card['label'] }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
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
