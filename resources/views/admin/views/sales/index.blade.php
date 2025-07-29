@extends('layouts.app')

@section('title', 'قائمة المبيعات')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">قائمة المبيعات</h4>
                </div>
            </div>
        </div>
    </section>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.sales.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة فاتورة جديدة
            </a>
        </div>

        <div class="card-body">
            <div class="card-body table-responsive p-0">
                <table id="sales-table" class="table table-bordered table-striped text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>العميل</th>
                            <th>اسم العميل (يدوي)</th>
                            <th>الإجمالي</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>التاريخ</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td>{{ $sale->id }}</td>
                                <td>{{ $sale->customer?->name ?? '-' }}</td>
                                <td>{{ $sale->customer_name ?? '-' }}</td>
                                <td>{{ number_format($sale->total, 2) }} جنيه</td>
                                <td>{{ number_format($sale->paid, 2) }} جنيه</td>
                                <td>{{ number_format($sale->remaining, 2) }} جنيه</td>
                                <td>{{ $sale->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i> اختر إجراء
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="{{ route('admin.sales.show', $sale->id) }}" class="dropdown-item">
                                                <i class="fas fa-eye text-info me-2"></i> عرض
                                            </a>
                                            <a href="{{ route('admin.sales.edit', $sale->id) }}" class="dropdown-item">
                                                <i class="fas fa-edit text-warning me-2"></i> تعديل
                                            </a>
                                            <form action="{{ route('admin.sales.destroy', $sale->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash-alt me-2"></i> حذف
                                                </button>
                                            </form>

                                            @if($sale->remaining > 0)
                                            <button type="button" class="dropdown-item text-success" data-toggle="modal" data-target="#paymentModal{{ $sale->id }}">
                                                <i class="fas fa-money-bill-wave me-2"></i> سداد
                                            </button>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- مودال سداد -->
                                    <div class="modal fade" id="paymentModal{{ $sale->id }}" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel{{ $sale->id }}" aria-hidden="true">
                                      <div class="modal-dialog" role="document">
                                        <form action="{{ route('admin.sales.update', $sale->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-content">
                                              <div class="modal-header">
                                                <h5 class="modal-title" id="paymentModalLabel{{ $sale->id }}">سداد دفعة</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق">
                                                  <span aria-hidden="true">&times;</span>
                                                </button>
                                              </div>
                                              <div class="modal-body">
                                                  <div class="form-group">
                                                      <label>المتبقي: {{ number_format($sale->remaining, 2) }} جنيه</label>
                                                      <input type="number" name="new_payment" step="0.01" min="0" max="{{ $sale->remaining }}" class="form-control" required>
                                                  </div>
                                              </div>
                                              <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                                                <button type="submit" class="btn btn-success">تأكيد السداد</button>
                                              </div>
                                            </div>
                                        </form>
                                      </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">لا توجد مبيعات حتى الآن</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="m-3">
                    {{ $sales->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(function () {
        $('#sales-table').DataTable({
            language: { url: "{{ asset('assets/admin/js/ar.json') }}" },
            responsive: true,
            autoWidth: false,
            paging: true,
            searching: true,
            ordering: false;
        });
    });
</script>
@endpush
