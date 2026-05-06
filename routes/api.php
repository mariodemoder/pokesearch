<?php

use App\Http\Controllers\PokemonController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:pokemon-api')->group(function () {
	Route::get('/pokemon/{name}', [PokemonController::class, 'show']);
});