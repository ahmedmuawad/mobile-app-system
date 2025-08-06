<tr>
    <td>
        <select name="items[{{ $index }}][product_id]" class="form-control product-select" required>
            <option value="">-- اختر المنتج --</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
            @endforeach
        </select>
    </td>
    <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity" min="1" value="1" required></td>
    <td><input type="number" name="items[{{ $index }}][purchase_price]" class="form-control price" min="0" step="0.01" value="0.00" required></td>
    <td class="subtotal">0.00</td>
    <td><button type="button" class="btn btn-danger btn-sm remove-item">✖</button></td>
</tr>
