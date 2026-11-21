<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/\A[a-z][a-z0-9_-]*\z/', 'unique:users'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'username.regex' => 'Используйте строчные латинские буквы, цифры, дефис или подчёркивание. Начните с буквы.',
            'username.unique' => 'Это имя пользователя уже занято.',
            'username.min' => 'Имя пользователя должно содержать не меньше 3 символов.',
            'username.max' => 'Имя пользователя должно содержать не больше 30 символов.',
            'email.unique' => 'Этот адрес уже зарегистрирован.',
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('albums.index', absolute: false));
    }
}
