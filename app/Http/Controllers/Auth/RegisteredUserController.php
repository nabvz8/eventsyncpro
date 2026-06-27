<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Contracts\Services\AuthServiceInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    protected $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required|string|alpha_dash|max:50|unique:users,username',
            'full_name' => 'required|string|max:100',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = $this->authService->register([
            'username' => $request->username,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        event(new Registered($user));

        return redirect('/onboarding');
    }
}
