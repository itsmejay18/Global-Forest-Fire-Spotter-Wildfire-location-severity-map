<?php

use App\Http\Controllers\Api\WildfireIncidentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/incidents', [WildfireIncidentController::class, 'index']);
