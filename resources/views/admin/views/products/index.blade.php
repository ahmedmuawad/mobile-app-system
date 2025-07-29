@extends('layouts.app')
@section('title', 'المنتجات')
@section('content')
    <div class="container-fluid">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">قائمةالمنتجات</h4>
                </div>
                </div>
            </div>
        </section>
        <div class="card">
            <div class="card-header">
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> إضافة منتج جديد
                </a>
            </div>

            @if ($products->isEmpty())
                <p class="p-3">لا توجد منتجات حالياً.</p>
            @else
            <div class="card-body">
                <div class="card-body table-responsive p-0">
                    <table id="products-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th style="width: 120px;">سعر الشراء</th>
                                <th style="width: 120px;">سعر البيع</th>
                                <th style="width: 80px;">الكمية</th>
                                <th>التصنيف</th>
                                <th style="width: 70px;">الصورة</th>
                                <th style="width: 180px;">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ number_format($product->purchase_price, 2) }} ج.م</td>
                                <td>{{ number_format($product->sale_price, 2) }} ج.م</td>
                                <td>{{ $product->stock }}</td>
                                <td>{{ $product->category->name ?? 'بدون تصنيف' }}</td>
                                <td class="text-center">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="صورة المنتج"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                    @else
                                        <small class="text-muted">لا توجد صورة</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i> اختر إجراء
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="{{ route('admin.products.edit', $product->id) }}" class="dropdown-item">
                                                <i class="fas fa-edit text-warning me-2"></i> تعديل
                                            </a>
                                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash-alt me-2"></i> حذف
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(function () {
        $('#products-table').DataTable({
            language: {url:"{{ asset('assets/admin/js/ar.json') }}"},
            responsive: true,
            autoWidth: false,
            paging: true,
            searching: true,
            ordering: true
        });
    });
</script>
@endpush
