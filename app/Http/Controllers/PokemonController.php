<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class PokemonController extends Controller
{
    public function show(string $name): JsonResponse
    {
        return response()->json([
            'name' => strtolower($name),
            'source' => 'laravel-api-proxy-phase-1',
            'message' => 'Endpoint API activo',
        ]);
    }
}