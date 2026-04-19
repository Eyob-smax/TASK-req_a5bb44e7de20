<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| The SPA is served by the frontend container. This file exists only to
| satisfy Laravel's routing bootstrap. No business logic lives here.
|
*/

Route::get('/', fn () => response()->json(['service' => 'campuslearn-api']));
