@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">إضافة تصنيف جديد</div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">اسم التصنيف</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success mt-3">حفظ</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary mt-3">رجوع</a>
            </form>
        </div>
    </div>
</div>
@endsection
