<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center">

        {{-- Left Section --}}
        <ul class="navbar-nav d-flex align-items-center">
            <li class="nav-item me-2">
                <a class="nav-link" data-widget="pushmenu" href="#">
                    <i class="fas fa-bars text-secondary"></i>
                </a>
            </li>

            {{-- فرع المستخدم --}}
            @php
                $user = auth()->user();
                $branches = $user->branches ?? collect();
                $currentBranchId = session('current_branch_id');
                $currentBranchName = $currentBranchId
                    ? optional($branches->firstWhere('id', $currentBranchId))->name
                    : 'كل الفروع';
            @endphp

            @if($branches->count() > 1)
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-primary fw-bold" data-toggle="dropdown" href="#" role="button">
                        <i class="fas fa-code-branch me-1"></i> الفرع: {{ $currentBranchName }}
                    </a>
                    <div class="dropdown-menu">
                        {{-- 🌐 كل الفروع --}}
                        <a class="dropdown-item {{ !$currentBranchId ? 'active fw-bold' : '' }}"
                           href="{{ route('admin.change.branch', 'all') }}">
                            <i class="fas fa-globe text-secondary me-1"></i> كل الفروع
                        </a>
                        <div class="dropdown-divider"></div>

                        {{-- 🏬 الفروع --}}
                        @foreach($branches as $branch)
                            <a class="dropdown-item {{ $branch->id == $currentBranchId ? 'active fw-bold' : '' }}"
                               href="{{ route('admin.change.branch', $branch->id) }}">
                                <i class="fas fa-store-alt me-1 text-secondary"></i> {{ $branch->name }}
                            </a>
                        @endforeach
                    </div>
                </li>
            @endif
        </ul>

        {{-- Right Section --}}
        <ul class="navbar-nav d-flex align-items-center ms-auto mt-2 mt-md-0">
            <!-- POS -->
            <li class="nav-item me-2">
                <a class="nav-link text-success fw-bold" href="{{ route('admin.pos') }}">
                    <i class="fas fa-th-large me-1"></i> نقطة البيع
                </a>
            </li>

            <!-- Logout -->
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill">
                        <i class="fas fa-sign-out-alt me-1"></i> تسجيل الخروج
                    </button>
                </form>
            </li>
        </ul>

    </div>
</nav>
