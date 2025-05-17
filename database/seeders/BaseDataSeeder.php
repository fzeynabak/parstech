<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;
use App\Models\Category;
use App\Models\Brand;

class BaseDataSeeder extends Seeder
{
    public function run()
    {
        // ایجاد ارز پیش‌فرض
        Currency::firstOrCreate(
            ['code' => 'IRR'],
            [
                'name' => 'ریال',
                'code' => 'IRR',
                'symbol' => 'ریال'
            ]
        );

        // ایجاد دسته‌بندی پیش‌فرض
        Category::firstOrCreate(
            ['name' => 'دسته‌بندی پیش‌فرض'],
            [
                'name' => 'دسته‌بندی پیش‌فرض',
                'description' => 'دسته‌بندی پیش‌فرض سیستم'
            ]
        );

        // ایجاد برند پیش‌فرض
        Brand::firstOrCreate(
            ['name' => 'برند پیش‌فرض'],
            [
                'name' => 'برند پیش‌فرض',
                'description' => 'برند پیش‌فرض سیستم'
            ]
        );
    }
}
