<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('resource-plugin:closure-demo', function () {
    $this->line(Inspiring::quote());
})->purpose('A closure-based console command contributed by the resource plugin fixture.');
