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
            'title' => 'Relojes - Panel de administración',
            'watches' => Watch::orderBy('brand')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.watches.create', [
            'title' => 'Nuevo reloj - Panel de administración',
            'watch' => new Watch,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $watch = Watch::create($this->validated($request));

        return redirect()->route('admin.watches.index')
            ->with('success', 'Reloj "'.$watch->name.'" creado correctamente.');
    }

    public function edit(Watch $watch): View
    {
        return view('admin.watches.edit', [
            'title' => 'Editar reloj - Panel de administración',
            'watch' => $watch,
        ]);
    }

    public function update(Request $request, Watch $watch): RedirectResponse
    {
        $watch->update($this->validated($request));

        return redirect()->route('admin.watches.index')
            ->with('success', 'Reloj "'.$watch->name.'" actualizado correctamente.');
    }

    public function destroy(Watch $watch): RedirectResponse
    {
        if ($watch->orderItems()->exists()) {
            return redirect()->route('admin.watches.index')
                ->withErrors(['watch' => 'No se puede eliminar "'.$watch->name.'" porque tiene pedidos asociados.']);
        }

        $watch->delete();

        return redirect()->route('admin.watches.index')
            ->with('success', 'Reloj "'.$watch->name.'" eliminado correctamente.');
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
