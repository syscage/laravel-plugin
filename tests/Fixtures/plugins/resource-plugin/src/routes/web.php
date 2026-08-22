<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/resource-plugin-test/web', fn () => 'ok-web');
