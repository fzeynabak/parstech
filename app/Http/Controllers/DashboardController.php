<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class DashboardController extends Controller
{
    public function index()
    {
        // آمار کلی
        $todaySales = Sale::whereDate('created_at', Carbon::today())->sum('final_amount');
        $activeProducts = Product::where('is_active', true)->count();
        $totalCustomers = Customer::count();
        $monthlyRevenue = Sale::whereMonth('created_at', Carbon::now()->month)
                             ->whereYear('created_at', Carbon::now()->year)
                             ->sum('final_amount');

        // داده‌های نمودار فروش
        $salesData = Sale::selectRaw('DATE(created_at) as date, SUM(final_amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->limit(30)
            ->get()
            ->map(function ($sale) {
                return [
                    'x' => Jalalian::fromDateTime($sale->date)->format('Y/m/d'),
                    'y' => $sale->total
                ];
            });

        // محصولات پرفروش
        $topProducts = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_quantity'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        $topProductsData = $topProducts->pluck('total_quantity');
        $topProductsLabels = $topProducts->pluck('name');

        // وضعیت سفارشات
        $orderStatus = Sale::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $orderStatusData = $orderStatus->pluck('total');
        $orderStatusLabels = $orderStatus->pluck('status')->map(function ($status) {
            return match ($status) {
                'pending' => 'در انتظار پرداخت',
                'paid' => 'پرداخت شده',
                'canceled' => 'لغو شده',
                'completed' => 'تکمیل شده',
                default => $status
            };
        });

        // درآمد ماهانه
        $monthlyRevenueData = [];
        $monthlyRevenueLabels = [];

        // ساخت آرایه ماه‌های سال شمسی
        $jalaliMonths = [
            'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
            'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
        ];

        foreach (range(1, 12) as $month) {
            $monthlyRevenueLabels[] = $jalaliMonths[$month - 1];
            $date = Carbon::now()->month($month);

            $revenue = Sale::whereMonth('created_at', $date->month)
                          ->whereYear('created_at', $date->year)
                          ->sum('final_amount');

            $monthlyRevenueData[] = $revenue;
        }

        // فعالیت‌های اخیر
        $recentActivities = Activity::with('user')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'todaySales',
            'activeProducts',
            'totalCustomers',
            'monthlyRevenue',
            'salesData',
            'topProductsData',
            'topProductsLabels',
            'orderStatusData',
            'orderStatusLabels',
            'monthlyRevenueData',
            'monthlyRevenueLabels',
            'recentActivities'
        ));
    }

    public function getSalesData($period)
    {
        $data = match ($period) {
            'week' => $this->getWeeklyData(),
            'month' => $this->getMonthlyData(),
            'year' => $this->getYearlyData(),
            default => $this->getWeeklyData()
        };

        return response()->json($data);
    }

    private function getWeeklyData()
    {
        $sales = Sale::selectRaw('DATE(created_at) as date, SUM(final_amount) as total')
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $sales->map(function($sale) {
                return Jalalian::fromDateTime($sale->date)->format('l');
            }),
            'values' => $sales->pluck('total')
        ];
    }

    private function getMonthlyData()
    {
        $sales = Sale::selectRaw('DATE(created_at) as date, SUM(final_amount) as total')
            ->whereMonth('created_at', Carbon::now()->month)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $sales->map(function($sale) {
                return Jalalian::fromDateTime($sale->date)->format('d F');
            }),
            'values' => $sales->pluck('total')
        ];
    }

    private function getYearlyData()
    {
        $jalaliMonths = [
            'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
            'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
        ];

        $sales = Sale::selectRaw('MONTH(created_at) as month, SUM(final_amount) as total')
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthlyData = collect(range(1, 12))->map(function($month) use ($sales, $jalaliMonths) {
            $monthSale = $sales->firstWhere('month', $month);
            return [
                'label' => $jalaliMonths[$month - 1],
                'value' => $monthSale ? $monthSale->total : 0
            ];
        });

        return [
            'labels' => $monthlyData->pluck('label'),
            'values' => $monthlyData->pluck('value')
        ];
    }
}
