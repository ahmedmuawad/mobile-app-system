@extends('layouts.app')

@push('styles')
<style>
    .product-image {
        max-height: 100px !important;
        max-width: 100px !important;
    }

    .pos-item {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 10px;
        text-align: center;
        transition: all 0.2s ease-in-out;
        height: 180px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .pos-item:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .price-badge {
        font-weight: bold;
        color: #27ae60;
        margin-top: 5px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid pt-3" dir="rtl">
    <div class="row">
        <!-- ✅ شبكة المنتجات -->
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-3">المنتجات</h5>
                <div class="form-inline">
                    <select id="category-filter" class="form-select form-select-sm" style="width: 200px;">
                        <option value="all">كل التصنيفات</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                @foreach ($products as $product)
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 pos-item-wrapper">
                        <div class="pos-item" data-category="{{ $product->category_id }}">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="rounded-circle product-image">
                            @else
                                <img src="{{ asset('placeholder.png') }}" alt="No image" class="rounded-circle product-image">
                            @endif

                            <span class="price-badge d-flex justify-content-center align-items-center">{{ $product->sale_price }} جنيه</span>
                            <div class="d-flex justify-content-center align-items-center">
                                <span data-bs-toggle="tooltip" title="{{ $product->name }}" class="d-inline-block text-truncate w-100" style="max-width: 100%;">
                                    {{ Str::limit($product->name, 12) }}
                                </span>
                            </div>
                            <button type="button" class="btn btn-block btn-success" style="padding: .125rem .25rem !important; font-size: .75rem !important; line-height: 1.5 !important; border-radius: .15rem !important;" onclick="addToCart({{ $product->id }})">إضافة</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ✅ سلة المشتريات -->
        <div class="col-md-4">
            <h5 class="mb-3">سلة المشتريات</h5>
            <form method="POST" action="{{ route('admin.pos.store') }}" id="checkout-form" class="cart-box">
                @csrf
                <input type="hidden" name="items" id="items-input">
                <input type="hidden" name="discount" id="discount-input">
                <input type="hidden" name="customer_id" id="customer-id-input">
                <input type="hidden" name="payment_type" id="payment-type-input">

                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>الكمية</th>
                                <th>السعر</th>
                                <th>الإجمالي</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cart-items">
                            <!-- سيتم تعبئته بالـ JS -->
                        </tbody>
                    </table>
                </div>

                <div class="form-group mt-2">
                    <label>العميل:</label>
                    <select class="form-control" id="customer-select">
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                        <option value="">-- زبون نقدي --</option>
                    </select>
                </div>

                <div class="form-group mt-2">
                    <label>خصم:</label>
                    <input type="number" class="form-control" id="discount" value="0" min="0">
                </div>

                <div class="mt-3 text-center">
                    <h5>الإجمالي: <span id="total-amount">0</span> جنيه</h5>
                </div>

                <!-- ✅ أزرار الدفع -->
                <div class="mt-3 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-info flex-fill" onclick="setPaymentType('card')">💳 Card</button>
                    <button type="submit" class="btn btn-success flex-fill" onclick="setPaymentType('cash')">💵 Cash</button>
                    <button type="button" class="btn btn-warning flex-fill">📝 Draft</button>
                    <button type="button" class="btn btn-danger flex-fill">❌ Cancel</button>
                    <button type="button" class="btn btn-warning flex-fill">🕘 Recent Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let cart = [];
const products = @json($products);

function addToCart(id) {
    const product = products.find(p => p.id === id);
    const item = cart.find(p => p.id === id);
    if (item) {
        item.quantity += 1;
    } else {
        cart.push({ id: id, name: product.name, price: product.sale_price, quantity: 1 });
    }
    renderCart();
}

function removeItem(id) {
    cart = cart.filter(item => item.id !== id);
    renderCart();
}

function updateQuantity(id, qty) {
    const item = cart.find(i => i.id === id);
    if (item) {
        item.quantity = parseInt(qty) || 1;
    }
    renderCart();
}

function renderCart() {
    const tbody = document.getElementById('cart-items');
    tbody.innerHTML = '';
    let total = 0;

    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.name}</td>
            <td><input type="number" class="form-control form-control-sm" value="${item.quantity}" min="1" onchange="updateQuantity(${item.id}, this.value)" /></td>
            <td>${item.price}</td>
            <td>${itemTotal.toFixed(2)}</td>
            <td><button onclick="removeItem(${item.id})" class="btn btn-danger btn-sm">🗑</button></td>
        `;
        tbody.appendChild(row);
    });

    document.getElementById('total-amount').innerText = total.toFixed(2);
}

function setPaymentType(type) {
    document.getElementById('payment-type-input').value = type;
}

document.getElementById('checkout-form').addEventListener('submit', function (e) {
    const items = cart.map(item => ({
        product_id: item.id,
        quantity: item.quantity,
        price: item.price
    }));

    if (items.length === 0) {
        e.preventDefault();
        alert("السلة فارغة");
        return;
    }

    document.getElementById('items-input').value = JSON.stringify(items);
    document.getElementById('discount-input').value = document.getElementById('discount').value;
    document.getElementById('customer-id-input').value = document.getElementById('customer-select').value;
});
</script>

<script>
document.getElementById('category-filter').addEventListener('change', function () {
    const selected = this.value;
    const items = document.querySelectorAll('.pos-item-wrapper');

    items.forEach(wrapper => {
        const card = wrapper.querySelector('.pos-item');
        const catId = card.getAttribute('data-category');

        if (selected === 'all' || selected === catId) {
            wrapper.classList.remove('d-none');
        } else {
            wrapper.classList.add('d-none');
        }
    });
});
</script>
@endpush
