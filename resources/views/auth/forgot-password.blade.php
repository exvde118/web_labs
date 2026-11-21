@extends('layouts.app')
@section('title', 'Восстановление пароля')
@section('content')
<section class="album-form">
    <h1>Восстановление пароля</h1>
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        @include('auth._input', ['field' => 'email', 'label' => 'Электронная почта', 'type' => 'email', 'autocomplete' => 'email'])
        <button class="btn btn-primary" type="submit">Отправить ссылку</button>
    </form>
</section>
@endsection
