<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'forecast_date',
        'forecasted_demand',
        'actual_demand',
        'forecast_accuracy',
        'forecast_method',
        'calculation_data',
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'forecasted_demand' => 'decimal:2',
        'actual_demand' => 'decimal:2',
        'forecast_accuracy' => 'decimal:2',
        'calculation_data' => 'array',
    ];

    /**
     * Get the product for this forecast.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the branch for this forecast.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Calculate forecast using moving average method.
     */
    public static function calculateMovingAverage(int $productId, int $branchId, int $days = 7): float
    {
        $startDate = now()->subDays($days);
        
        $totalSales = OrderItem::whereHas('order', function ($query) use ($branchId, $startDate) {
            $query->where('branch_id', $branchId)
                  ->where('status', 'completed')
                  ->where('created_at', '>=', $startDate);
        })
        ->where('product_id', $productId)
        ->sum('quantity');

        return $totalSales / $days;
    }

    /**
     * Calculate forecast using weighted moving average (recent data has more weight).
     */
    public static function calculateWeightedAverage(int $productId, int $branchId, int $days = 7): float
    {
        $salesData = [];
        
        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->toDateString();
            
            $daySales = OrderItem::whereHas('order', function ($query) use ($branchId, $date) {
                $query->where('branch_id', $branchId)
                      ->where('status', 'completed')
                      ->whereDate('created_at', $date);
            })
            ->where('product_id', $productId)
            ->sum('quantity');
            
            $salesData[] = [
                'date' => $date,
                'sales' => $daySales,
                'weight' => $days - $i, // Recent days have higher weight
            ];
        }

        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($salesData as $data) {
            $weightedSum += $data['sales'] * $data['weight'];
            $totalWeight += $data['weight'];
        }

        return $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
    }

    /**
     * Generate forecast for a specific date.
     */
    public static function generateForecast(
        int $productId, 
        int $branchId, 
        string $forecastDate, 
        string $method = 'weighted_average'
    ): self {
        $forecastedDemand = match($method) {
            'moving_average' => static::calculateMovingAverage($productId, $branchId, 7),
            'weighted_average' => static::calculateWeightedAverage($productId, $branchId, 7),
            default => static::calculateWeightedAverage($productId, $branchId, 7),
        };

        // Apply seasonal adjustments
        $product = Product::find($productId);
        if ($product) {
            $seasonalFactor = SeasonalAdjustment::getSeasonalFactor($productId, $product->category);
            $forecastedDemand *= $seasonalFactor;
        }

        return static::updateOrCreate(
            [
                'product_id' => $productId,
                'branch_id' => $branchId,
                'forecast_date' => $forecastDate,
            ],
            [
                'forecasted_demand' => $forecastedDemand,
                'forecast_method' => $method,
                'calculation_data' => [
                    'base_forecast' => $forecastedDemand,
                    'seasonal_factor' => $seasonalFactor ?? 1.0,
                    'calculated_at' => now()->toDateTimeString(),
                ],
            ]
        );
    }

    /**
     * Update actual demand and calculate accuracy.
     */
    public function updateActualDemand(float $actualDemand): void
    {
        $this->actual_demand = $actualDemand;
        
        if ($this->forecasted_demand > 0) {
            $error = abs($this->forecasted_demand - $actualDemand);
            $this->forecast_accuracy = 100 - (($error / $this->forecasted_demand) * 100);
        }
        
        $this->save();
    }

    /**
     * Get forecast accuracy statistics for a product.
     */
    public static function getAccuracyStats(int $productId, int $branchId, int $days = 30): array
    {
        $forecasts = static::where('product_id', $productId)
                          ->where('branch_id', $branchId)
                          ->whereNotNull('actual_demand')
                          ->whereNotNull('forecast_accuracy')
                          ->where('forecast_date', '>=', now()->subDays($days))
                          ->get();

        if ($forecasts->isEmpty()) {
            return [
                'average_accuracy' => 0,
                'total_forecasts' => 0,
                'successful_forecasts' => 0,
            ];
        }

        return [
            'average_accuracy' => round($forecasts->avg('forecast_accuracy'), 2),
            'total_forecasts' => $forecasts->count(),
            'successful_forecasts' => $forecasts->where('forecast_accuracy', '>=', 80)->count(),
        ];
    }
}
