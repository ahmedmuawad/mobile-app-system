<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars text-secondary"></i></a>
        </li>
    </ul>

    @php
        $user = auth()->user();
        $branches = $user->branches ?? collect();
        $currentBranchId = session('current_branch_id');
        $currentBranchName = $currentBranchId
            ? optional($branches->firstWhere('id', $currentBranchId))->name
            : 'كل الفروع';
    @endphp

    @if($branches->count() > 1)
        <ul class="navbar-nav ml-3">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-primary fw-bold" data-toggle="dropdown" href="#" role="button">
                    <i class="fas fa-code-branch me-1"></i> الفرع: {{ $currentBranchName }}
                </a>
                <div class="dropdown-menu dropdown-menu-left">

                    {{-- 🌐 كل الفروع --}}
                    <a class="dropdown-item {{ !$currentBranchId ? 'active fw-bold' : '' }}"
                       href="{{ route('admin.change.branch', 'all') }}">
                        <i class="fas fa-globe text-secondary me-1"></i> كل الفروع
                    </a>
                    <div class="dropdown-divider"></div>

                    {{-- 🏬 الفروع المتاحة --}}
                    @foreach($branches as $branch)
                        <a class="dropdown-item {{ $branch->id == $currentBranchId ? 'active fw-bold' : '' }}"
                           href="{{ route('admin.change.branch', $branch->id) }}">
                            <i class="fas fa-store-alt me-1 text-secondary"></i> {{ $branch->name }}
                        </a>
                    @endforeach
                </div>
            </li>
        </ul>
    @endif

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto align-items-center">
        <!-- POS Link -->
        <li class="nav-item me-3">
            <a class="nav-link text-success fw-bold" href="{{ route('admin.pos') }}">
                <i class="fas fa-th-large me-1"></i> نقطة البيع
            </a>
        </li>

        <!-- Logout Button as Icon -->
        <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm rounded-pill">
                    <i class="fas fa-sign-out-alt me-1"></i> تسجيل الخروج
                </button>
            </form>
        </li>
    </ul>
</nav>
