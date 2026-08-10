<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| L'API n'est pas exposée dans cette version ; les routes sont gérées
| exclusivement par l'interface web (routes/web.php).
|
*/

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});
