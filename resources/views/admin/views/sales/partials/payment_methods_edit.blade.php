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

            {{-- إذا ما فيش دفعات، أضف صف افتراضي واحد --}}
            @if($sale->payments->count() === 0)
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
{{--
@push('scripts')
<script>
$(document).ready(function() {
    function updatePaymentIndexes() {
        $('#payments-table tbody tr').each(function(i) {
            $(this).find('select, input').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    const newName = name.replace(/payments\[\d+\]/, `payments[${i}]`);
                    $(this).attr('name', newName);
                }
            });
        });
    }

    // إضافة صف جديد
    $('#add-payment-row').click(function() {
        const index = $('#payments-table tbody tr').length;
        const newRow = $(`
            <tr>
                <td>
                    <select name="payments[${index}][payment_method_id]" class="form-control">
                        <option value="">-- اختر --</option>
                        @foreach(\App\Models\PaymentMethod::all() as $method)
                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" step="0.01" name="payments[${index}][amount]" class="form-control payment-amount" value="0"></td>
                <td><input type="text" name="payments[${index}][reference]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-payment-row">حذف</button></td>
            </tr>
        `);
        $('#payments-table tbody').append(newRow);
    });

    // حذف صف
    $(document).on('click', '.remove-payment-row', function() {
        $(this).closest('tr').remove();
        updatePaymentIndexes();
    });

    // تحديث الإجمالي عند تغير قيمة الدفع (يمكن ربطها مع الحساب في الفاتورة لو حابب)
    $(document).on('input', '.payment-amount', function() {
        // هنا يمكن إضافة كود لتحديث المتبقي في الفاتورة تلقائياً إذا أردت
    });
});
</script>
@endpush --}}
