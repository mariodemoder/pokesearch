<?php

namespace Tests\Feature;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PokemonApiTest extends TestCase
{
    public function test_get_pokemon_returns_200_with_mapped_fields(): void
    {
        Cache::flush();

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/pikachu' => Http::response([
                'name' => 'pikachu',
                'height' => 4,
                'weight' => 60,
                'sprites' => [
                    'front_default' => 'https://img.example/pikachu.png',
                ],
                'types' => [
                    ['type' => ['name' => 'electric']],
                ],
                'ignored_field' => 'ignored',
            ], 200),
        ]);

        $response = $this->getJson('/api/pokemon/pikachu');

        $response->assertOk()->assertExactJson([
            'name' => 'pikachu',
            'height' => 4,
            'weight' => 60,
            'sprite' => 'https://img.example/pikachu.png',
            'types' => ['electric'],
        ]);
    }

    public function test_get_pokemon_not_found_returns_404_with_consistent_message(): void
    {
        Cache::flush();

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/noexiste123' => Http::response([], 404),
        ]);

        $response = $this->getJson('/api/pokemon/noexiste123');

        $response->assertStatus(404)->assertExactJson([
            'error' => 'Pokemon no encontrado',
        ]);
    }

    public function test_upstream_error_returns_502(): void
    {
        Cache::flush();

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/raichu' => Http::response([], 500),
        ]);

        $response = $this->getJson('/api/pokemon/raichu');

        $response->assertStatus(502)->assertExactJson([
            'error' => 'Error al consultar el servicio externo.',
        ]);
    }

    public function test_non_404_upstream_error_returns_502(): void
    {
        Cache::flush();

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/charizard' => Http::response([
                'error' => 'upstream failed',
            ], 500),
        ]);

        $response = $this->getJson('/api/pokemon/charizard');

        $response->assertStatus(502)->assertExactJson([
            'error' => 'Error al consultar el servicio externo.',
        ]);
    }

    public function test_repeated_request_within_ttl_uses_cache_and_avoids_second_upstream_call(): void
    {
        Cache::flush();

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/bulbasaur' => Http::response([
                'name' => 'bulbasaur',
                'height' => 7,
                'weight' => 69,
                'sprites' => [
                    'front_default' => 'https://img.example/bulbasaur.png',
                ],
                'types' => [
                    ['type' => ['name' => 'grass']],
                    ['type' => ['name' => 'poison']],
                ],
            ], 200),
        ]);

        $first = $this->getJson('/api/pokemon/bulbasaur');
        $second = $this->getJson('/api/pokemon/bulbasaur');

        $first->assertOk();
        $second->assertOk();

        Http::assertSentCount(1);
    }

    public function test_rate_limit_returns_429_when_limit_is_exceeded(): void
    {
        RateLimiter::for('pokemon-api', function (Request $request) {
            return Limit::perMinute(1)->by($request->ip());
        });

        Cache::flush();

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/squirtle' => Http::response([
                'name' => 'squirtle',
                'height' => 5,
                'weight' => 90,
                'sprites' => [
                    'front_default' => 'https://img.example/squirtle.png',
                ],
                'types' => [
                    ['type' => ['name' => 'water']],
                ],
            ], 200),
        ]);

        $first = $this->getJson('/api/pokemon/squirtle');
        $second = $this->getJson('/api/pokemon/squirtle');

        $first->assertOk();
        $second->assertStatus(429);
    }
}
