<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars text-secondary"></i></a>
        </li>
    </ul>

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
