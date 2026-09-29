<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('id', 'desc')->paginate(15);

        return view('admin.users.index', [
            'title' => __('messages.admin_users_title'),
            'subtitle' => __('messages.admin_users_subtitle'),
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'title' => __('messages.admin_users_create_title'),
            'subtitle' => __('messages.admin_users_create_subtitle'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'is_admin' => 'nullable|boolean',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];

        if (Schema::hasColumn('users', 'is_admin')) {
            $userData['is_admin'] = $request->boolean('is_admin');
        }

        User::create($userData);

        return redirect()->route('admin.users.index')
            ->with('success', __('messages.admin_users_created'));
    }

    public function show(User $user): RedirectResponse
    {
        return redirect()->route('admin.users.edit', $user);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'title' => __('messages.admin_users_edit_title'),
            'subtitle' => __('messages.admin_users_edit_subtitle', ['name' => $user->name]),
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'is_admin' => 'nullable|boolean',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        if (Schema::hasColumn('users', 'is_admin')) {
            $user->is_admin = $request->boolean('is_admin');
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', __('messages.admin_users_updated'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user() && $request->user()->id === $user->id) {
            return back()->withErrors(['user' => __('messages.admin_users_cannot_delete_self')]);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', __('messages.admin_users_deleted'));
    }
}
