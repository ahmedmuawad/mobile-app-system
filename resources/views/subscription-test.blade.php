<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>اختبار الاشتراك</title>
    <style>
        body { font-family: Arial, sans-serif; direction: rtl; text-align: right; }
        h1 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #f4f4f4; }
    </style>
</head>
<body>

    <h1>بيانات الشركة</h1>
    <p><strong>الاسم:</strong> {{ $company->name }}</p>
    <p><strong>الباقة:</strong> {{ $company->package ? $company->package->name : 'لا يوجد' }}</p>
    <p><strong>نهاية الاشتراك:</strong> {{ $company->subscription_ends_at ?? 'غير محدد' }}</p>

    <h2>الموديولز المفعلة</h2>
    @if($company->package && $company->package->modules->count())
        <table>
            <tr>
                <th>الاسم</th>
                <th>الوصف</th>
                <th>Slug</th>
            </tr>
            @foreach($company->package->modules as $module)
                <tr>
                    <td>{{ $module->name }}</td>
                    <td>{{ $module->description }}</td>
                    <td>{{ $module->slug }}</td>
                </tr>
            @endforeach
        </table>
    @else
        <p>لا يوجد موديولز مفعلة.</p>
    @endif

</body>
</html>
