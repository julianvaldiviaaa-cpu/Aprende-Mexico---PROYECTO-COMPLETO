<?php

namespace App\Http\Controllers\Identity;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Registration');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = new User($request->safe()->only(['name', 'email', 'password']));
        $user->role = UserRole::User;
        $user->status = 'active';
        $user->save();
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Tu cuenta está lista. ¡Bienvenido a México Aprende!');
    }
}
