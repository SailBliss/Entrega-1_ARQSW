<?php

namespace App\Http\Controllers;

use App\Models\Watch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminWatchController extends Controller
{
    public function index(): View
    {
        return view('admin.watches.index', [
            'title' => __('messages.admin_watches_title'),
            'watches' => Watch::orderBy('brand')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.watches.create', [
            'title' => __('messages.admin_watches_create_title'),
            'watch' => new Watch,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $watch = Watch::create($this->validated($request));

        return redirect()->route('admin.watches.index')
            ->with('success', __('messages.admin_watches_created', ['name' => $watch->name]));
    }

    public function edit(Watch $watch): View
    {
        return view('admin.watches.edit', [
            'title' => __('messages.admin_watches_edit_title'),
            'watch' => $watch,
        ]);
    }

    public function update(Request $request, Watch $watch): RedirectResponse
    {
        $watch->update($this->validated($request));

        return redirect()->route('admin.watches.index')
            ->with('success', __('messages.admin_watches_updated', ['name' => $watch->name]));
    }

    public function destroy(Watch $watch): RedirectResponse
    {
        if ($watch->orderItems()->exists()) {
            return redirect()->route('admin.watches.index')
                ->withErrors(['watch' => __('messages.admin_watches_has_orders', ['name' => $watch->name])]);
        }

        $watch->delete();

        return redirect()->route('admin.watches.index')
            ->with('success', __('messages.admin_watches_deleted', ['name' => $watch->name]));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|integer|min:1',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|string|max:255',
        ]);
    }
}
