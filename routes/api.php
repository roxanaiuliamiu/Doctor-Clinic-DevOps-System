<?php

use Illuminate\Support\Facades\Route;

Route::get('/metrics', function () {
    $metrics = [];
    $metrics[] = '# HELP app_up Laravel app is up';
    $metrics[] = '# TYPE app_up gauge';
    $metrics[] = 'app_up 1';

    return response(implode("\n", $metrics) . "\n", 200)
        ->header('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
});