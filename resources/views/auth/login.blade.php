@extends('layouts.app')
@section('title', 'Вход')
@section('content')
<section class="album-form">
    <h1>Вход</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        @include('auth._input', ['field' => 'email', 'label' => 'Электронная почта', 'type' => 'email', 'autocomplete' => 'email'])
        @include('auth._input', ['field' => 'password', 'label' => 'Пароль', 'type' => 'password', 'autocomplete' => 'current-password'])
        <div class="form-check mb-3">
            <input class="form-check-input" id="remember" name="remember" type="checkbox" @checked(old('remember'))>
            <label class="form-check-label" for="remember">Запомнить меня</label>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-3">
            <button class="btn btn-primary" type="submit">Войти</button>
            <a href="{{ route('password.request') }}">Забыли пароль?</a>
        </div>
    </form>
</section>
@endsection
