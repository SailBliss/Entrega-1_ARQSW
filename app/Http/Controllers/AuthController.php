<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register', ['title' => __('messages.auth_register_title'), 'subtitle' => __('messages.auth_register_subtitle')]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home.index')->with('success', __('messages.auth_registered_success'));
    }

    public function showLogin(): View
    {
        return view('auth.login', ['title' => __('messages.auth_login_title'), 'subtitle' => __('messages.auth_login_subtitle')]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => __('messages.auth_failed')])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home.index'))->with('success', __('messages.auth_logged_in'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home.index')->with('success', __('messages.auth_logged_out'));
    }
}
