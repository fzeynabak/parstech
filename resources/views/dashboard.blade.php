@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endsection

@section('content')
<div class="dashboard-container">
    <!-- آمار کلی -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">فروش امروز</span>
                <div class="stat-icon sales">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($todaySales) }} تومان</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>۱۲٪ نسبت به دیروز</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">محصولات فعال</span>
                <div class="stat-icon products">
                    <i class="fas fa-box"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($activeProducts) }}</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>۵ محصول جدید</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">مشتریان</span>
                <div class="stat-icon customers">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($totalCustomers) }}</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>۳ مشتری جدید</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">درآمد ماه</span>
                <div class="stat-icon revenue">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($monthlyRevenue) }} تومان</div>
            <div class="stat-change negative">
                <i class="fas fa-arrow-down"></i>
                <span>۸٪ نسبت به ماه قبل</span>
            </div>
        </div>
    </div>

    <!-- نمودارها -->
    <div class="charts-grid">
        <!-- نمودار فروش -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">آمار فروش</h3>
                <div class="chart-actions">
                    <button class="chart-action-btn" onclick="updateSalesChart('week')">هفته</button>
                    <button class="chart-action-btn" onclick="updateSalesChart('month')">ماه</button>
                    <button class="chart-action-btn" onclick="updateSalesChart('year')">سال</button>
                </div>
            </div>
            <div class="chart-container" id="salesChart"></div>
        </div>

        <!-- نمودار محصولات پرفروش -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">محصولات پرفروش</h3>
            </div>
            <div class="chart-container" id="topProductsChart"></div>
        </div>

        <!-- نمودار وضعیت سفارشات -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">وضعیت سفارشات</h3>
            </div>
            <div class="chart-container" id="ordersStatusChart"></div>
        </div>

        <!-- نمودار درآمد ماهانه -->
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">درآمد ماهانه</h3>
            </div>
            <div class="chart-container" id="monthlyRevenueChart"></div>
        </div>
    </div>

    <!-- فعالیت‌های اخیر -->
    <div class="recent-activity">
        <div class="activity-header">
            <h3 class="chart-title">فعالیت‌های اخیر</h3>
            <button class="chart-action-btn">مشاهده همه</button>
        </div>
        <ul class="activity-list">
            @foreach($recentActivities as $activity)
            <li class="activity-item">
                <div class="activity-icon" style="background: {{ $activity->getIconColor() }}">
                    <i class="fas {{ $activity->getIcon() }}"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">{{ $activity->description }}</div>
                    <div class="activity-time">{{ $activity->created_at->diffForHumans() }}</div>
                </div>
            </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // تنظیمات مشترک برای همه نمودارها
    const chartDefaults = {
        chart: {
            fontFamily: 'IRANSans, Tahoma, sans-serif',
            toolbar: {
                show: false
            },
            zoom: {
                enabled: false
            }
        },
        colors: ['#2563eb', '#16a34a', '#eab308', '#dc2626', '#8b5cf6'],
        stroke: {
            curve: 'smooth',
            width: 3
        },
        xaxis: {
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px'
                },
                formatter: function(value) {
                    return new Intl.NumberFormat('fa-IR').format(value);
                }
            }
        },
        tooltip: {
            theme: 'light',
            x: {
                show: true
            },
            y: {
                formatter: function(value) {
                    return new Intl.NumberFormat('fa-IR').format(value) + ' تومان';
                }
            }
        },
        grid: {
            borderColor: '#e2e8f0',
            strokeDashArray: 4
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            labels: {
                colors: '#1e293b'
            }
        }
    };

    // نمودار فروش
    const salesChart = new ApexCharts(document.querySelector("#salesChart"), {
        ...chartDefaults,
        chart: {
            ...chartDefaults.chart,
            type: 'area',
            height: 350
        },
        series: [{
            name: 'فروش',
            data: {!! json_encode($salesData) !!}
        }],
        xaxis: {
            ...chartDefaults.xaxis,
            categories: {!! json_encode($salesLabels) !!}
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.2,
                stops: [0, 90, 100]
            }
        }
    });
    salesChart.render();

    // نمودار محصولات پرفروش
    const topProductsChart = new ApexCharts(document.querySelector("#topProductsChart"), {
        ...chartDefaults,
        chart: {
            ...chartDefaults.chart,
            type: 'bar',
            height: 350
        },
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 4,
                dataLabels: {
                    position: 'top'
                }
            }
        },
        series: [{
            name: 'تعداد فروش',
            data: {!! json_encode($topProductsData) !!}
        }],
        xaxis: {
            ...chartDefaults.xaxis,
            categories: {!! json_encode($topProductsLabels) !!}
        }
    });
    topProductsChart.render();

    // نمودار وضعیت سفارشات
    const ordersStatusChart = new ApexCharts(document.querySelector("#ordersStatusChart"), {
        ...chartDefaults,
        chart: {
            ...chartDefaults.chart,
            type: 'donut',
            height: 350
        },
        series: {!! json_encode($orderStatusData) !!},
        labels: {!! json_encode($orderStatusLabels) !!},
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    });
    ordersStatusChart.render();

    // نمودار درآمد ماهانه
    const monthlyRevenueChart = new ApexCharts(document.querySelector("#monthlyRevenueChart"), {
        ...chartDefaults,
        chart: {
            ...chartDefaults.chart,
            type: 'line',
            height: 350
        },
        series: [{
            name: 'درآمد',
            data: {!! json_encode($monthlyRevenueData) !!}
        }],
        xaxis: {
            ...chartDefaults.xaxis,
            categories: {!! json_encode($monthlyRevenueLabels) !!}
        },
        markers: {
            size: 5
        }
    });
    monthlyRevenueChart.render();
});

// تابع به‌روزرسانی نمودار فروش
function updateSalesChart(period) {
    fetch(`/api/sales-data/${period}`)
        .then(response => response.json())
        .then(data => {
            salesChart.updateOptions({
                series: [{
                    data: data.values
                }],
                xaxis: {
                    categories: data.labels
                }
            });
        });
}
</script>
@endsection
