<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Models\Currency;
use App\Models\Category;
use App\Models\Brand;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        // ایجاد دسته‌بندی پیش‌فرض
        $category = Category::firstOrCreate([
            'name' => 'دسته‌بندی پیش‌فرض'
        ]);

        // ایجاد برند پیش‌فرض
        $brand = Brand::firstOrCreate([
            'name' => 'برند پیش‌فرض'
        ]);

        // ایجاد چند محصول نمونه
        $products = collect([
            ['name' => 'لپ تاپ ایسوس', 'price' => 25000000, 'type' => 'product'],
            ['name' => 'موس گیمینگ', 'price' => 850000, 'type' => 'product'],
            ['name' => 'کیبورد مکانیکال', 'price' => 1200000, 'type' => 'product'],
            ['name' => 'نصب ویندوز', 'price' => 500000, 'type' => 'service'],
            ['name' => 'آموزش ICDL', 'price' => 2500000, 'type' => 'service'],
        ])->each(function ($product) use ($category, $brand) {
            Product::create([
                'code' => 'P' . rand(1000, 9999),
                'name' => $product['name'],
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'stock' => rand(5, 50),
                'is_active' => true
            ]);
        });

        // ایجاد چند مشتری نمونه
        $customers = collect([
            ['name' => 'علی محمدی', 'mobile' => '09121234567'],
            ['name' => 'مریم احمدی', 'mobile' => '09129876543'],
            ['name' => 'رضا کریمی', 'mobile' => '09123456789'],
        ])->each(function ($customer) {
            Customer::create([
                'first_name' => explode(' ', $customer['name'])[0],
                'last_name' => explode(' ', $customer['name'])[1],
                'mobile' => $customer['mobile']
            ]);
        });

        // ایجاد فروشنده نمونه
        $seller = User::where('email', 'admin@example.com')->first() ?? User::create([
            'name' => 'فروشنده نمونه',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // ایجاد ارز پیش‌فرض
        $currency = Currency::where('code', 'IRR')->first() ?? Currency::create([
            'name' => 'ریال',
            'code' => 'IRR',
            'symbol' => 'ریال'
        ]);

        // ایجاد چند فاکتور نمونه
        $customers = Customer::all();
        $products = Product::all();

        foreach(range(1, 20) as $i) {
            $sale = Sale::create([
                'invoice_number' => 'INV-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'customer_id' => $customers->random()->id,
                'seller_id' => $seller->id,
                'currency_id' => $currency->id,
                'status' => collect(['pending', 'paid', 'completed'])->random(),
                'issued_at' => Carbon::now()->subDays(rand(0, 30)),
            ]);

            // اضافه کردن آیتم‌های فاکتور
            foreach(range(1, rand(1, 3)) as $j) {
                $product = $products->random();
                $quantity = rand(1, 5);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => rand(100000, 1000000), // قیمت تصادفی
                    'total' => $quantity * rand(100000, 1000000)
                ]);
            }

            // محاسبه مجموع
            $total = $sale->saleItems->sum('total');
            $sale->update([
                'total_price' => $total,
                'final_amount' => $total,
                'paid_amount' => $total
            ]);
        }
    }
}
