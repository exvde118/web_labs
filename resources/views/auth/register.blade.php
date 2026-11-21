@extends('layouts.app')
@section('title', 'Регистрация')
@section('content')
<section class="album-form">
    <h1>Регистрация</h1>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        @include('auth._input', ['field' => 'name', 'label' => 'Имя', 'type' => 'text', 'autocomplete' => 'name'])
        @include('auth._input', ['field' => 'username', 'label' => 'Имя пользователя', 'type' => 'text', 'autocomplete' => 'username', 'hint' => 'От 3 до 30 символов. Начните со строчной латинской буквы. Допустимы строчные латинские буквы, цифры, дефис и подчёркивание.'])
        @include('auth._input', ['field' => 'email', 'label' => 'Электронная почта', 'type' => 'email', 'autocomplete' => 'email'])
        @include('auth._input', ['field' => 'password', 'label' => 'Пароль', 'type' => 'password', 'autocomplete' => 'new-password', 'hint' => 'Минимум 8 символов.'])
        @include('auth._input', ['field' => 'password_confirmation', 'label' => 'Повторите пароль', 'type' => 'password', 'autocomplete' => 'new-password'])
        <button class="btn btn-primary" type="submit">Зарегистрироваться</button>
    </form>
</section>
@endsection
