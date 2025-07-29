<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;
use App\Models\Category;

class POSController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $customers = Customer::all();
        $categories = Category::all();
        return view('admin.views.pos.index', compact('products', 'categories', 'customers'));

    }

public function store(Request $request)
{
    $validated = $request->validate([
        'items' => 'required|json',
        'discount' => 'nullable|numeric',
        'customer_id' => 'nullable|exists:customers,id'
    ]);

    $items = json_decode($validated['items'], true);

    $total = collect($items)->sum(function ($item) {
        return $item['price'] * $item['quantity'];
    });

    $sale = Sale::create([
        'customer_id' => $validated['customer_id'],
        'customer_name' => optional(Customer::find($validated['customer_id']))->name,
        'discount' => $validated['discount'] ?? 0,
        'total' => $total,
        'profit' => 0,
    ]);

    $profit = 0;

    foreach ($items as $item) {
        $product = Product::find($item['product_id']);

        $sale->saleItems()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $item['quantity'],
            'sale_price' => $item['price'],
            'purchase_price' => $product->purchase_price,
        ]);

        $profit += ($item['price'] - $product->purchase_price) * $item['quantity'];
    }

    $sale->update(['profit' => $profit]);

    return redirect()->route('admin.pos')->with('success', 'تمت عملية البيع بنجاح.');
}

}
