@extends('layouts.app')
@section('title', 'Новый пароль')
@section('content')
<section class="album-form">
    <h1>Новый пароль</h1>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        @include('auth._input', ['field' => 'email', 'label' => 'Электронная почта', 'type' => 'email', 'autocomplete' => 'email', 'value' => $request->email])
        @include('auth._input', ['field' => 'password', 'label' => 'Новый пароль', 'type' => 'password', 'autocomplete' => 'new-password'])
        @include('auth._input', ['field' => 'password_confirmation', 'label' => 'Повторите пароль', 'type' => 'password', 'autocomplete' => 'new-password'])
        <button class="btn btn-primary" type="submit">Сохранить пароль</button>
    </form>
</section>
@endsection
