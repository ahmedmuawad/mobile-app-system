<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>التاريخ</th>
            <th>نوع الجهاز</th>
            <th>المشكلة</th>
            <th>الحالة</th>
            <th>الإجمالي</th>
            <th>المدفوع</th>
            <th>المتبقي</th>
            <th>طرق الدفع</th>
            <th>قطع الغيار</th>
        </tr>
    </thead>
    <tbody>
        @forelse($repairs as $repair)
            <tr>
                <td>{{ $repair->created_at->format('Y-m-d') }}</td>
                <td>{{ $repair->device_type }}</td>
                <td>{{ $repair->problem_description }}</td>
                <td>{{ $repair->status }}</td>
                <td>{{ number_format($repair->total, 2) }}</td>
                <td>{{ number_format($repair->paid, 2) }}</td>
                <td>{{ number_format($repair->remaining, 2) }}</td>
                <td>
                    @foreach($repair->payments as $payment)
                        {{ $payment->paymentMethod->name ?? '' }} ({{ number_format($payment->amount, 2) }})<br>
                    @endforeach
                </td>
                <td>
                    @foreach($repair->spareParts as $part)
                        {{ $part->name }} ({{ $part->pivot->quantity }})<br>
                    @endforeach
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">لا توجد عمليات صيانة</td>
            </tr>
        @endforelse
    </tbody>
</table>
