<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <div class="app">
        <header class="header">
            <a href="{{ url('/') }}">
                <img class="header-logo" src="{{ asset('images/logo.svg') }}" alt="サイトロゴ">
            </a>

            @if(!in_array(Route::currentRouteName(),['login','register']))
                <div class="header-search-form">
                    <form class="header-search-form__form" action="{{ route('search') }}" method="get">
                        @csrf
                        <input class="header-search-form__input" type="text" name="keyword" value="{{ request('keyword') }}" placeholder="なにをお探しですか？">
                    </form>
                </div>

                <nav class="header-nav">
                    <ul class="header-nav__list">
                        <li class="header-nav__item">
                            @auth
                            <form action="/logout" method="post">
                                @csrf
                                <input class="header-nav__link" type="submit" value="ログアウト">
                            </form>
                            @else
                            <a class="header-nav__link" href="/login">ログイン</a>
                            @endauth
                        </li>
                        <li class="header-nav__item">
                            <a class="header-nav__link" href="/mypage">マイページ</a>
                        </li>
                        <li class="header-nav__item">
                            <a class="header-nav__link header-nav__link-sell" href="/sell">出品</a>
                        </li>
                    </ul>
                </nav>
            @endif
        </header>
        
        <div class="content">
            @yield('content')
        </div>
    </div>
</body>

</html>