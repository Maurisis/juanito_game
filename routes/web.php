<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController; // Obligatorio para que encuentre tu código

// Ruta para ver la pantalla del juego (esta ya te funciona)
Route::get('/juanito-game', [GameController::class, 'index']);

// Ruta para recibir los datos de JavaScript y guardarlos (esta es la que falta)
Route::post('/juanito-game/save', [GameController::class, 'storeResult']);