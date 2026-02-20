<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель управления</title>
</head>
<body>
    <h1>Панель управления</h1>

    @if(session('success'))
        <div>{{ session('success') }}</div>
    @endif

    <p>Добро пожаловать, {{ auth()->user()->name }}!</p>

    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit">Выйти</button>
    </form>
</body>
</html>
