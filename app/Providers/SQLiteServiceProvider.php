<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SQLiteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $path = env('DB_DATABASE', database_path('database.sqlite'));
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!file_exists($path)) {
            file_put_contents($path, '');
        }
    }

    public function register() {}
}
