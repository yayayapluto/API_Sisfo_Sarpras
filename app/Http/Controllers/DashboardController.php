<?php

namespace App\Http\Controllers;

use App\Custom\Formatter;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Item;
use App\Models\Rack;
use App\Models\Returning;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function general()
    {
        $userCount = User::count();
        $rackCount = Rack::count();
        $categoryCount = Category::count();
        $itemCount = Item::count();
        $borrowingCount = Borrowing::count();
        $returningCount = Returning::count();

        return Formatter::apiResponse(200, "Data retrieved", [
            "userCount" => $userCount,
            "rackCount" => $rackCount,
            "categoryCount" => $categoryCount,
            "itemCount" => $itemCount,
            "borrowingCount" => $borrowingCount,
            "returningCount" => $returningCount,
        ]);
    }

    public function user()
    {
        return Formatter::apiResponse(200, "Data retrieved", [
            'recentUserCount' => [
                'today' => User::whereDate('created_at', Carbon::today())->get(),
                'thisWeek' => User::whereBetween('created_at', [Carbon::now()->subDays(7), Carbon::now()])->get(),
                'thisMonth' => User::whereBetween('created_at', [Carbon::now()->subMonth(), Carbon::now()])->get()
            ],
            'lastActiveUsers' => User::orderByDesc("last_login_at")->take(10)->get(),
            'totalBorrowingRecords' => User::withCount('borrowings')->orderByDesc('borrowings_count')->get(),
            'usersByRole' => [
                "user" => User::where('role', 'user')->get(),
                "admin" => User::where('role', 'admin')->get()
            ]
        ]);
    }

    public function rack()
    {
        return Formatter::apiResponse(200, "Data retrieved", [
            'totalRacks' => Rack::count(),
            'averageCapacity' => Rack::avg('capacity'),
            'rackList' => Rack::with(['rackItems.item:id,name'])->get(['id', 'code', 'name', 'capacity'])
        ]);
    }

    public function category()
    {
        return Formatter::apiResponse(200, "Data retrieved", [
            'totalCategories' => Category::count(),
            'topCategories' => Category::withCount('items')
                ->orderByDesc('items_count')
                ->take(5)
                ->get(),
            'categoryList' => Category::with(['items' => function($query) {
                $query->select('items.id', 'items.name');
            }])->get(['id', 'slug', 'name'])
        ]);
    }

    public function item()
    {
        return Formatter::apiResponse(200, "Data retrieved", [
            'totalItems' => Item::count(),
            'lowStockItems' => Item::where('stock', '<', 10)->get(),
            'itemsByCategory' => Category::withCount('items')->get(),
            'topBorrowedItems' => Item::withCount('borrowings')
                ->orderByDesc('borrowings_count')
                ->take(5)
                ->get()
        ]);
    }

    public function borrowing()
    {
        return Formatter::apiResponse(200, "Data retrieved", [
            'borrowingStats' => [
                'total' => Borrowing::count(),
                'pending' => Borrowing::where('status', 'pending')->count(),
                'approved' => Borrowing::where('status', 'approved')->count(),
                'overdue' => Borrowing::where('due', '<', Carbon::now())->count()
            ],
            'recentBorrowings' => Borrowing::with(['user:id,username', 'item:id,name'])
                ->orderByDesc('created_at')
                ->take(10)
                ->get(),
            'topBorrowers' => User::withCount('borrowings')
                ->orderByDesc('borrowings_count')
                ->take(5)
                ->get()
        ]);
    }

    public function returning()
    {
        return Formatter::apiResponse(200, "Data retrieved", [
            'returnStats' => [
                'totalReturns' => Returning::count(),
                'averageReturnQuantity' => Returning::avg('returned_quantity')
            ],
            'recentReturns' => Returning::with(['borrow.item:id,name', 'handler:id,username'])
                ->orderByDesc('created_at')
                ->take(10)
                ->get(),
            'topHandlers' => User::whereHas('handler')
                ->withCount('handler')
                ->orderByDesc('handler_count')
                ->take(5)
                ->get()
        ]);
    }
}
