@extends('adminlte::auth.register')
@extends('adminlte::page')

@section('title', 'تسجيل عضوية جديدة')

@section('content_header')
    <h1>تسجيل عضوية</h1>
@endsection

@section('content')
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label for="name">الاسم</label>
            <input id="name" type="text" class="form-control" name="name" required autofocus>
        </div>

        <div class="form-group">
            <label for="email">البريد الإلكتروني</label>
            <input id="email" type="email" class="form-control" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">كلمة المرور</label>
            <input id="password" type="password" class="form-control" name="password" required>
        </div>

        <div class="form-group">
            <label for="password-confirm">تأكيد كلمة المرور</label>
            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
        </div>

        <button type="submit" class="btn btn-primary">تسجيل</button>
    </form>
@endsection
