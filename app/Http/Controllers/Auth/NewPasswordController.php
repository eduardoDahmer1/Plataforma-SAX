<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class NewPasswordController extends Controller
{
    public function create(Request $request): RedirectResponse
    {
        return redirect()->route('home', [
            'open'  => 'reset',
            'token' => $request->route('token'),
            'email' => $request->query('email'),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
                Auth::login($user);
            }
        );

        if ($request->expectsJson()) {
            return $status === Password::PASSWORD_RESET
                ? response()->json(['success' => true,  'message' => __($status)])
                : response()->json(['success' => false, 'message' => __($status)], 422);
        }

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('home')
            : back()->withInput($request->only('email'))
                    ->withErrors(['email' => __($status)]);
    }
}