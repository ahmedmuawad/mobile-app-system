<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>التاريخ</th>
            <th>الفرع</th>
            <th>الإجمالي</th>
            <th>المدفوع</th>
            <th>المتبقي</th>
            <th>طرق الدفع</th>
            <th>المنتجات</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sales as $sale)
            <tr>
                <td>{{ $sale->created_at->format('Y-m-d') }}</td>
                <td>{{ $sale->branch->name ?? '-' }}</td>
                <td>{{ number_format($sale->total, 2) }}</td>
                <td>{{ number_format($sale->paid, 2) }}</td>
                <td>{{ number_format($sale->remaining, 2) }}</td>
                <td>
                    @foreach($sale->payments as $payment)
                        {{ $payment->paymentMethod->name ?? '' }} ({{ number_format($payment->amount, 2) }})<br>
                    @endforeach
                </td>
                <td>
                    @foreach($sale->products as $product)
                        {{ $product->name }} ({{ $product->pivot->quantity }})<br>
                    @endforeach
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">لا توجد مبيعات</td>
            </tr>
        @endforelse
    </tbody>
</table>
