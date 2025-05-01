<?php

namespace App\Providers;

use App\Models\Attachment;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemAttachment;
use App\Models\ItemCategory;
use App\Models\Rack;
use App\Models\RackItem;
use App\Models\Returning;
use App\Models\User;
use App\Observers\AttachmentObserver;
use App\Observers\BorrowingObserver;
use App\Observers\CategoryObserver;
use App\Observers\ItemAttachmentObserver;
use App\Observers\ItemCategoryObserver;
use App\Observers\ItemObserver;
use App\Observers\RackItemObserver;
use App\Observers\RackObserver;
use App\Observers\ReturningObserver;
use App\Observers\UserObserver;
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
        User::observe(UserObserver::class);
        Category::observe(CategoryObserver::class);
        Rack::observe(RackObserver::class);
        Item::observe(ItemObserver::class);
        Borrowing::observe(BorrowingObserver::class);
        Returning::observe(ReturningObserver::class);
        ItemCategory::observe(ItemCategoryObserver::class);
        RackItem::observe(RackItemObserver::class);
        Attachment::observe(AttachmentObserver::class);
        ItemAttachment::observe(ItemAttachmentObserver::class);
    }
}
