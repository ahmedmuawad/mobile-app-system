@extends('layouts.app')
@section('title', 'المحافظ')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 class="arabic-heading">قائمة المحافظ</h4>
                </div>
            </div>
        </div>
    </section>

    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.wallets.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> إضافة محفظة جديدة
            </a>
        </div>

        @if ($wallets->isEmpty())
            <p class="p-3">لا توجد محافظ حالياً.</p>
        @else
        <div class="card-body">
            <div class="card-body table-responsive p-0">
                <table id="wallets-table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>رقم المحفظة</th>
                            <th>اسم المالك</th>
                            <th>مزود الخدمة</th>
                            <th>الفرع</th>
                            <th>الرصيد الحالي</th> <!-- الجديد -->
                            <th style="width: 220px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($wallets as $wallet)
                        <tr>
                            <td>{{ $wallet->number }}</td>
                            <td>{{ $wallet->owner_name }}</td>
                            <td>{{ $wallet->provider->name ?? '-' }}</td>
                            <td>{{ $wallet->branch->name ?? '-' }}</td>
                            <td>{{ number_format($wallet->balance, 2) }} جنيه</td>

                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i> اختر إجراء
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="{{ route('admin.wallets.edit', $wallet->id) }}" class="dropdown-item">
                                            <i class="fas fa-edit text-warning me-2"></i> تعديل
                                        </a>

                                        <!-- زر فتح مودال الإيداع -->
                                        <button
                                            type="button"
                                            class="dropdown-item btn-deposit"
                                            data-wallet-id="{{ $wallet->id }}"
                                            data-wallet-number="{{ $wallet->number }}"
                                            data-provider-name="{{ $wallet->provider->name ?? '' }}"
                                            data-toggle="modal"
                                            data-target="#depositModal"
                                        >

                                            <i class="fas fa-plus-circle text-success me-2"></i> إيداع رصيد
                                        </button>

                                        <form action="{{ route('admin.wallets.destroy', $wallet->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
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

<!-- مودال إيداع الرصيد -->
<div class="modal fade" id="depositModal" tabindex="-1" role="dialog" aria-labelledby="depositModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form method="POST" action="{{ route('admin.wallets.deposit') }}">
      @csrf
      <input type="hidden" name="wallet_id" id="depositWalletId" value="">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="depositModalLabel">إيداع رصيد في المحفظة</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>المحفظة: <strong id="depositWalletNumber"></strong></p>
          <p>مزود الخدمة: <strong id="depositProviderName"></strong></p>

          <div class="form-group">
            <label for="amount">المبلغ</label>
            <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control" required>
          </div>

          <div class="form-group">
            <label for="expense_date">تاريخ الإيداع</label>
            <input type="date" name="expense_date" id="expense_date" class="form-control" required value="{{ date('Y-m-d') }}">
          </div>

          <div class="form-group">
            <label for="description">الوصف (اختياري)</label>
            <textarea name="description" id="description" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
          <button type="submit" class="btn btn-primary">تنفيذ الإيداع</button>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
    $(function () {
        $('#wallets-table').DataTable({
            language: { url: "{{ asset('assets/admin/js/ar.json') }}" },
            responsive: true,
            autoWidth: false,
            paging: true,
            searching: true,
            ordering: true
        });

        // عند الضغط على زر الإيداع مباشرةً (أكثر موثوقية من show.bs.modal)
        $('.btn-deposit').on('click', function () {
            var button = $(this);
            var walletId = button.data('wallet-id');
            var walletNumber = button.data('wallet-number');
            var providerName = button.data('provider-name');

            console.log('Selected wallet ID:', walletId); // لتأكيد أن القيمة صحيحة

            var modal = $('#depositModal');
            modal.find('#depositWalletId').val(walletId);
            modal.find('#depositWalletNumber').text(walletNumber);
            modal.find('#depositProviderName').text(providerName);
            modal.find('#amount').val('');
            modal.find('#description').val('');
            modal.find('#expense_date').val(new Date().toISOString().slice(0,10));
        });
    });
</script>
@endpush

