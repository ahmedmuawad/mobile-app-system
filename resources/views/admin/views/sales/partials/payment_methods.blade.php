<div class="mt-4">
    <h4>طرق الدفع</h4>
    <table class="table table-bordered" id="payments-table">
        <thead>
            <tr>
                <th>طريقة الدفع</th>
                <th>المبلغ</th>
                <th>المرجع (اختياري)</th>
                <th>إجراء</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($sale) && $sale->payments->count() > 0)
                @foreach($sale->payments as $index => $payment)
                <tr>
                    <td>
                        <select name="payments[{{ $index }}][payment_method_id]" class="form-control">
                            <option value="">-- اختر --</option>
                            @foreach(\App\Models\PaymentMethod::all() as $method)
                                <option value="{{ $method->id }}" {{ $payment->payment_method_id == $method->id ? 'selected' : '' }}>
                                    {{ $method->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" step="0.01" name="payments[{{ $index }}][amount]" class="form-control payment-amount" value="{{ $payment->amount }}"></td>
                    <td><input type="text" name="payments[{{ $index }}][reference]" class="form-control" value="{{ $payment->reference }}"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-payment-row">حذف</button></td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td>
                        <select name="payments[0][payment_method_id]" class="form-control">
                            <option value="">-- اختر --</option>
                            @foreach(\App\Models\PaymentMethod::all() as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" step="0.01" name="payments[0][amount]" class="form-control payment-amount" value="0"></td>
                    <td><input type="text" name="payments[0][reference]" class="form-control"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-payment-row">حذف</button></td>
                </tr>
            @endif
        </tbody>
    </table>
    <button type="button" class="btn btn-success btn-sm" id="add-payment-row">إضافة طريقة دفع</button>
</div>


{{-- @push('scripts')
<script>
$(document).ready(function(){
    let paymentIndex = 1;

    $('#add-payment-row').on('click', function(){
        let row = `<tr>
            <td>
                <select name="payments[${paymentIndex}][payment_method_id]" class="form-control">
                    <option value="">-- اختر --</option>
                    @foreach(\App\Models\PaymentMethod::all() as $method)
                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" step="0.01" name="payments[${paymentIndex}][amount]" class="form-control" value="0"></td>
            <td><input type="text" name="payments[${paymentIndex}][reference]" class="form-control"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-payment-row">حذف</button></td>
        </tr>`;
        $('#payments-table tbody').append(row);
        paymentIndex++;
    });

    $(document).on('click', '.remove-payment-row', function(){
        $(this).closest('tr').remove();
    });
});
</script>
@endpush --}}
