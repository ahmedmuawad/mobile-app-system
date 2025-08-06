<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $globalSetting?->store_name ?? 'اسم المتجر' }}</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('assets/admin/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- AdminLTE Theme -->
    <link rel="stylesheet" href="{{ asset('assets/admin/dist/css/adminlte.min.css') }}">
    <!-- Custom Fonts -->
    <link rel="stylesheet" href="{{ asset('assets/admin/fonts/SansPro/SansPro.min.css') }}">
    <!-- Bootstrap RTL -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap_rtl-v4.2.1/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap_rtl-v4.2.1/custom_rtl.css') }}">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/mycustomstyle.css') }}">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/plugins/datatables-bs4/css/dataTables.bootstrap4.css') }}">
    <!-- Bootstrap 5 CSS (لو انت محتاجها) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        body {
            font-family: 'Tajawal', sans-serif !important;
        }
        .navbar-expand .navbar-nav {
            margin-left: 0px !important;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">

<div class="wrapper">

    @include('layouts.navbar') <!-- الترويسة -->
    @include('layouts.sidebar') <!-- القائمة الجانبية -->

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <section class="content pt-3 px-3">
            @yield('content')
        </section>
    </div>

    @include('layouts.footer') <!-- الفوتر -->
</div>

<!-- ============================ JS Files ============================ -->

<!-- jQuery (الأول لازم يكون فوق الكل) -->
<script src="{{ asset('assets/admin/plugins/jquery/jquery.min.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap 4 -->
<script src="{{ asset('assets/admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<!-- DataTables -->
<script src="{{ asset('assets/admin/plugins/datatables/jquery.dataTables.js') }}"></script>
<script src="{{ asset('assets/admin/plugins/datatables-bs4/js/dataTables.bootstrap4.js') }}"></script>

<!-- AdminLTE App -->
<script src="{{ asset('assets/admin/dist/js/adminlte.min.js') }}"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- سكربتات إضافية من الصفحات -->
@stack('scripts')

</body>
</html>
