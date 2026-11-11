<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class PreferenceController extends Controller
{
    public function update(Request $request, string $preference): RedirectResponse
    {
        $allowed = match ($preference) {
            'locale' => ['es', 'en'],
            'currency' => ['COP', 'USD'],
        };

        $data = $request->validate(['value' => ['required', 'string', Rule::in($allowed)]]);

        return back()->withCookie(cookie($preference, $data['value'], 525600));
    }
}
