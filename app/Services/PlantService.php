<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PlantService
{
    protected string $apiUrl;
    protected int $timeout;
    protected int $cacheTtl;

    public function __construct()
    {
        $this->apiUrl = config('plants.api_url', 'http://34.136.112.77/api/plantas-disponibles');
        $this->timeout = (int) config('plants.timeout', 4);
        $this->cacheTtl = (int) config('plants.cache_ttl', 300);
    }

    /**
     * Obtiene el listado de plantas disponibles desde la API externa del equipo aliado.
     * Utiliza caché para optimizar la velocidad y tolera fallos de red.
     */
    public function getAvailablePlants(): array
    {
        try {
            return Cache::remember('external_available_plants', $this->cacheTtl, function () {
                $response = Http::timeout($this->timeout)
                    ->acceptJson()
                    ->get($this->apiUrl);

                if ($response->successful()) {
                    $json = $response->json();
                    $plants = $json['data'] ?? [];

                    // Filtrar solo las que tengan stock > 0
                    return array_values(array_filter($plants, fn ($plant) => ($plant['stock'] ?? 0) > 0));
                }

                Log::warning("Plant API returned error status: {$response->status()}");
                return [];
            });
        } catch (Exception $e) {
            Log::error("Failed to connect to external Plants API: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Selecciona una planta aleatoria disponible para regalar.
     */
    public function getRandomGiftPlant(): ?array
    {
        $plants = $this->getAvailablePlants();

        if (empty($plants)) {
            return null;
        }

        return Arr::random($plants);
    }

    /**
     * Intenta notificar la reducción de stock al endpoint de la tienda externa.
     * Si el endpoint no existe o no admite mutación (405), degrada elegantemente.
     */
    public function notifyStockReduction(int $plantId): bool
    {
        try {
            // Se asume una URL de convención REST en caso de que el otro equipo la habilite en el futuro
            $mutationUrl = rtrim(dirname($this->apiUrl), '/')."/plantas/{$plantId}/reducir-stock";
            $response = Http::timeout(2)->post($mutationUrl, ['cantidad' => 1]);

            return $response->successful();
        } catch (Exception) {
            // La API del otro equipo es de solo lectura actualmente
            return false;
        }
    }
}
