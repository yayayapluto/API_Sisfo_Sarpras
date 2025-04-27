<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Rack;
use App\Observers\CategoryObserver;
use App\Observers\ItemObserver;
use App\Observers\RackObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Category::observe(CategoryObserver::class);
        Rack::observe(RackObserver::class);
        Item::observe(ItemObserver::class);
    }
}
