<?php
class ChartGenerator {
    private $db;
    
    public function __construct($db = null) {
        $this->db = $db;
    }
    
    public function generateIncomeExpenseChart($data) {
        return [
            'type' => 'line',
            'data' => [
                'labels' => $data['labels'],
                'datasets' => [
                    [
                        'label' => 'درآمد',
                        'data' => $data['income'],
                        'borderColor' => '#28a745',
                        'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
                        'tension' => 0.4,
                        'fill' => true
                    ],
                    [
                        'label' => 'هزینه',
                        'data' => $data['expense'],
                        'borderColor' => '#dc3545',
                        'backgroundColor' => 'rgba(220, 53, 69, 0.1)',
                        'tension' => 0.4,
                        'fill' => true
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'position' => 'top'
                    ]
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'ticks' => [
                            'callback' => "function(value) { return new Intl.NumberFormat('fa-IR').format(value) + ' تومان'; }"
                        ]
                    ]
                ]
            ]
        ];
    }
    
    public function generateIncomeDistributionChart($data) {
        return [
            'type' => 'doughnut',
            'data' => [
                'labels' => array_column($data, 'label'),
                'datasets' => [[
                    'data' => array_column($data, 'value'),
                    'backgroundColor' => [
                        '#28a745',
                        '#17a2b8',
                        '#ffc107',
                        '#dc3545',
                        '#6c757d'
                    ]
                ]]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom',
                        'rtl' => true
                    ],
                    'tooltip' => [
                        'callbacks' => [
                            'label' => "function(context) {
                                return context.label + ': ' + 
                                new Intl.NumberFormat('fa-IR').format(context.raw) + ' تومان';
                            }"
                        ]
                    ]
                ]
            ]
        ];
    }

    public function generateCustomerChart($period = 'month') {
        try {
            $dates = $this->getDateRangeForPeriod($period);
            
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as count
                FROM customers
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY DATE(created_at)
                ORDER BY date
            ");
            $stmt->execute([$dates['start'], $dates['end']]);
            
            $labels = [];
            $data = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $labels[] = $this->formatDate($row['date'], 'j F');
                $data[] = $row['count'];
            }
            
            return [
                'type' => 'bar',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [[
                        'label' => 'مشتریان جدید',
                        'data' => $data,
                        'backgroundColor' => 'rgba(0, 123, 255, 0.5)',
                        'borderColor' => 'rgb(0, 123, 255)',
                        'borderWidth' => 1
                    ]]
                ],
                'options' => [
                    'responsive' => true,
                    'maintainAspectRatio' => false,
                    'scales' => [
                        'y' => [
                            'beginAtZero' => true,
                            'ticks' => [
                                'stepSize' => 1
                            ]
                        ]
                    ]
                ]
            ];
        } catch (Exception $e) {
            error_log("خطا در ایجاد نمودار مشتریان: " . $e->getMessage());
            return null;
        }
    }
    
    private function getDateRangeForPeriod($period) {
        switch ($period) {
            case 'week':
                return [
                    'start' => date('Y-m-d', strtotime('-6 days')),
                    'end' => date('Y-m-d')
                ];
            case 'month':
                return [
                    'start' => date('Y-m-01'),
                    'end' => date('Y-m-d')
                ];
            case 'year':
                return [
                    'start' => date('Y-01-01'),
                    'end' => date('Y-m-d')
                ];
            default:
                return [
                    'start' => date('Y-m-01'),
                    'end' => date('Y-m-d')
                ];
        }
    }
    
    private function formatDate($date, $format = 'Y/m/d') {
        return date($format, strtotime($date));
    }
}
?>