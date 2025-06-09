<?php

namespace App\Providers;

use App\Import\Contracts\ParserInterface;
use App\Import\Parsers\XlsxParser;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ParserInterface::class, XlsxParser::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
