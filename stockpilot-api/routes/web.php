<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['name' => 'StockPilot API', 'version' => 'v1']);
});
