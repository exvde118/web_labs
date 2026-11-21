<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Королевский XVII')</title>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <script src="{{ mix('js/app.js') }}" defer></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('albums.index') }}">
            <i class="fas fa-crown site-icon" aria-hidden="true"></i>
            <span>Трек-лист исполнителя «Королевский XVII»</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Открыть меню">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="{{ route('albums.index') }}">Главная</a></li>
                @auth
                    <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}">Пользователи</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('users.albums', auth()->user()->username) }}">Мои треки</a></li>
                    @can('create-album')
                        <li class="nav-item"><a class="nav-link" href="{{ route('albums.create') }}">Добавить трек</a></li>
                    @endcan
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link">{{ 'Выйти ('.auth()->user()->username.')' }}</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Вход</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Регистрация</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
<main id="main">
    <div class="container album-container">
        @if (session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
        @yield('content')
    </div>
</main>
<footer>
    <p>Автор: Даниил Шляхтин</p>
    <div class="social-links">
      <a href="https://ru.wikipedia.org/wiki/%D0%9C%D1%83%D0%B7%D1%8B%D0%BA%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9_%D0%B0%D0%BB%D1%8C%D0%B1%D0%BE%D0%BC" class="social-link" aria-label="О музыкальных альбомах: ссылка 1">in</a>
      <a href="https://ru.wikipedia.org/wiki/%D0%9C%D1%83%D0%B7%D1%8B%D0%BA%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9_%D0%B0%D0%BB%D1%8C%D0%B1%D0%BE%D0%BC" class="social-link" aria-label="О музыкальных альбомах: ссылка 2">f</a>
      <a href="https://ru.wikipedia.org/wiki/%D0%9C%D1%83%D0%B7%D1%8B%D0%BA%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9_%D0%B0%D0%BB%D1%8C%D0%B1%D0%BE%D0%BC" class="social-link" aria-label="О музыкальных альбомах: ссылка 3">t</a>
    </div>
  </footer>
  
</body>
</html>
