@extends('layouts.app')
@section('title', 'المنتجات')

@section('content')
<div class="container-fluid">
    <!-- عنوان الصفحة -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">قائمة المنتجات</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- بطاقة عرض المنتجات -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة منتج جديد
            </a>
        </div>

        @php
            try {
                $bulkRoute = route('admin.products.bulk');
            } catch (\Exception $e) {
                $bulkRoute = null;
                echo '<div class="alert alert-danger">Route [admin.products.bulk] غير معرف في ملف الراوتس!<br>السبب: ' . $e->getMessage() . '</div>';
            }
        @endphp

        <!-- 🔍 فلاتر البحث -->
        <form method="GET" class="row g-2 px-3 mb-2">
            <div class="col-md-3">
                <select name="brand_id" class="form-select" onchange="this.form.submit()">
                    <option value="">🔍 كل الماركات</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select" onchange="this.form.submit()">
                    <option value="">📂 كل التصنيفات</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        @if ($products->isEmpty())
            <p class="p-3">لا توجد منتجات حالياً.</p>
        @else
        <form id="bulk-action-form" method="POST" action="{{ $bulkRoute ?? '#' }}">
            @csrf
            <input type="hidden" name="action" id="bulk-action-type">

            <div class="px-3 mb-2">
                <button type="button" class="btn btn-danger" onclick="submitBulkAction('delete')">
                    🗑️ حذف المحدد
                </button>

                <button type="button" class="btn btn-secondary" onclick="submitBulkAction('generate_barcode')">
                    🔁 توليد باركود تلقائي
                </button>
            </div>

            <div class="card-body table-responsive p-0">
                <table id="products-table" class="table table-bordered table-striped text-center">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>الصورة</th>
                            <th>الباركود</th>
                            <th>التصنيف</th>
                            <th>الاسم</th>
                            <th>الماركة</th>
                            <th>سعر الشراء</th>
                            <th>سعر البيع</th>
                            <th>الكمية</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            @php
                                $currentBranchId = session('current_branch_id');
                                $branchData = $product->branches->firstWhere('id', $currentBranchId);
                                $stock = $branchData && $branchData->pivot ? $branchData->pivot->stock : $product->stock;
                                $salePrice = $product->getFinalPriceForBranch($currentBranchId);
                            @endphp
                            <tr>
                                <td><input type="checkbox" class="product-checkbox" name="selected_products[]" value="{{ $product->id }}"></td>
                                <td>
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}"
                                            alt="صورة المنتج"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                    @else
                                        <small class="text-muted">لا توجد صورة</small>
                                    @endif
                                </td>
                                <td>{{ $product->barcode ?? '-' }}</td>
                                <td>{{ $product->category->name ?? '-' }}</td>
                                <td>{{ \Illuminate\Support\Str::words($product->name, 5, '...') }}</td>
                                <td>{{ $product->brand->name ?? '-' }}</td>
                                <td>{{ number_format($product->purchase_price, 2) }} ج.م</td>
                                <td>{{ number_format($salePrice, 2) }} ج.م</td>
                                <td>{{ $stock }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i> إجراء
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="{{ route('admin.products.edit', $product->id) }}"
                                                class="dropdown-item">
                                                <i class="fas fa-edit text-warning me-2"></i> تعديل
                                            </a>
                                            <button type="button" class="dropdown-item text-danger"
                                                    onclick="submitDelete({{ $product->id }})">
                                                <i class="fas fa-trash-alt me-2"></i> حذف
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
        @endif

        <!-- زر تحميل التمبلت وزر الاستيراد بجانب بعض -->
        <div class="d-flex gap-2 mb-3">
            <a href="{{ asset('templates/products_template.xlsx') }}" class="btn btn-info">
                <i class="fas fa-download"></i> تحميل تمبلت المنتجات (Excel)
            </a>
            <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center">
                @csrf
                <input type="file" name="products_file" accept=".xlsx,.xls" class="form-control form-control-sm me-2" required style="max-width: 220px;">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-upload"></i> استيراد منتجات (Excel)
                </button>
            </form>
        </div>
    </div>
</div>

<!-- فورم الحذف الفردي (مخفي) -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(function () {
        $('#products-table').DataTable({
            language: { url: "{{ asset('assets/admin/js/ar.json') }}" },
            responsive: true,
            autoWidth: false,
            paging: true,
            searching: true,
            ordering: true
        });

        $('#select-all').on('change', function () {
            $('.product-checkbox').prop('checked', $(this).is(':checked'));
        });
    });

    function submitBulkAction(action) {
        const selected = $('.product-checkbox:checked');
        if (selected.length === 0) {
            alert('برجاء اختيار منتج واحد على الأقل.');
            return;
        }

        const confirmed = confirm(
            action === 'delete'
                ? 'هل أنت متأكد من حذف المنتجات المحددة؟'
                : 'هل تريد توليد باركود للمنتجات المحددة؟'
        );

        if (confirmed) {
            $('#bulk-action-type').val(action);
            $('#bulk-action-form').attr('action', '{{ route("admin.products.bulk") }}');
            $('#bulk-action-form').submit();
        }
    }

    function submitDelete(productId) {
        if (confirm('هل أنت متأكد من حذف المنتج؟')) {
            const form = document.getElementById('delete-form');
            form.action = `/admin/products/${productId}`;
            form.submit();
        }
    }
</script>
@endpush
