<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PokemonController extends Controller
{
    public function show(string $name): JsonResponse
    {
        $normalizedName = mb_strtolower(trim($name));
        $isValid = preg_match('/^[a-z0-9-]{1,50}$/', $normalizedName) === 1;

        if (! $isValid) {
            return response()->json([
                'error' => 'Parametro name invalido. Usa letras, numeros o guion.',
            ], 422);
        }

        $cacheKey = 'pokemon:' . $normalizedName;
        $ttl = max(60, min(300, (int) config('services.pokeapi.cache_ttl_seconds', 180)));

        try {
            $data = Cache::remember($cacheKey, now()->addSeconds($ttl), function () use ($normalizedName) {
                $baseUrl = rtrim((string) config('services.pokeapi.base_url', 'https://pokeapi.co/api/v2'), '/');
                $timeout = (int) config('services.pokeapi.timeout', 5);
                $retryTimes = (int) config('services.pokeapi.retry_times', 2);
                $retrySleep = (int) config('services.pokeapi.retry_sleep_ms', 200);

                try {
                    $response = Http::acceptJson()
                        ->timeout($timeout)
                        ->retry($retryTimes, $retrySleep)
                        ->get($baseUrl . '/pokemon/' . $normalizedName);
                } catch (RequestException $e) {
                    if ($e->response?->status() === 404) {
                        throw new \RuntimeException('POKEMON_NOT_FOUND');
                    }

                    throw new \RuntimeException('UPSTREAM_ERROR', 0, $e);
                }

                if ($response->status() === 404) {
                    throw new \RuntimeException('POKEMON_NOT_FOUND');
                }

                if ($response->failed()) {
                    throw new \RuntimeException('UPSTREAM_ERROR');
                }

                $payload = $response->json();

                return [
                    'name' => $payload['name'] ?? $normalizedName,
                    'height' => $payload['height'] ?? null,
                    'weight' => $payload['weight'] ?? null,
                    'sprite' => $payload['sprites']['front_default'] ?? null,
                    'types' => collect($payload['types'] ?? [])
                        ->map(fn ($item) => $item['type']['name'] ?? null)
                        ->filter()
                        ->values()
                        ->all(),
                ];
            });

            return response()->json($data, 200);
        } catch (ConnectionException $e) {
            report($e);

            return response()->json([
                'error' => 'Servicio temporalmente no disponible.',
            ], 504);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'POKEMON_NOT_FOUND') {
                return response()->json([
                    'error' => 'Pokemon no encontrado',
                ], 404);
            }

            report($e);

            return response()->json([
                'error' => 'Error al consultar el servicio externo.',
            ], 502);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'error' => 'Error interno del servidor.',
            ], 500);
        }
    }
}