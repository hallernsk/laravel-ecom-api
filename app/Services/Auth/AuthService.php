<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Регистрация нового пользователя
     *
     * @param string $name
     * @param string $email
     * @param string $password
     * @return User
     */
    public function register(string $name, string $email, string $password): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Аутентификация пользователя
     *
     * @param string $email
     * @param string $password
     * @return string Токен доступа
     * @throws ValidationException
     */
    public function login(string $email, string $password): string
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('email', $email)->firstOrFail();
        return $user->createToken('auth_token')->plainTextToken;
    }

    /**
     * Выход пользователя из системы
     *
     * @param User $user
     * @return bool
     */
    // public function logout(User $user): bool
    // {
    //     return $user->currentAccessToken()->delete();
    // }
}
