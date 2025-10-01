<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorageCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'storage_zone',
        'temperature',
        'humidity',
        'recorded_at',
        'is_within_threshold',
        'alert_message',
        'sensor_data',
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'humidity' => 'decimal:2',
        'recorded_at' => 'datetime',
        'is_within_threshold' => 'boolean',
        'sensor_data' => 'array',
    ];

    /**
     * Get the branch for this storage condition.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope to get alerts (conditions outside threshold).
     */
    public function scopeAlerts($query)
    {
        return $query->where('is_within_threshold', false);
    }

    /**
     * Scope to get conditions by storage zone.
     */
    public function scopeByZone($query, string $zone)
    {
        return $query->where('storage_zone', $zone);
    }

    /**
     * Check if temperature is within acceptable range for storage zone.
     */
    public static function checkTemperatureThreshold(string $zone, float $temperature): array
    {
        $thresholds = static::getThresholds($zone);
        
        $isWithin = $temperature >= $thresholds['temp_min'] 
                   && $temperature <= $thresholds['temp_max'];

        $alert = null;
        if (!$isWithin) {
            if ($temperature < $thresholds['temp_min']) {
                $alert = "Temperature too low ({$temperature}°C). Minimum: {$thresholds['temp_min']}°C";
            } else {
                $alert = "Temperature too high ({$temperature}°C). Maximum: {$thresholds['temp_max']}°C";
            }
        }

        return [
            'is_within' => $isWithin,
            'alert' => $alert,
        ];
    }

    /**
     * Check if humidity is within acceptable range for storage zone.
     */
    public static function checkHumidityThreshold(string $zone, float $humidity): array
    {
        $thresholds = static::getThresholds($zone);
        
        $isWithin = $humidity >= $thresholds['humidity_min'] 
                   && $humidity <= $thresholds['humidity_max'];

        $alert = null;
        if (!$isWithin) {
            if ($humidity < $thresholds['humidity_min']) {
                $alert = "Humidity too low ({$humidity}%). Minimum: {$thresholds['humidity_min']}%";
            } else {
                $alert = "Humidity too high ({$humidity}%). Maximum: {$thresholds['humidity_max']}%";
            }
        }

        return [
            'is_within' => $isWithin,
            'alert' => $alert,
        ];
    }

    /**
     * Get thresholds for storage zone.
     */
    private static function getThresholds(string $zone): array
    {
        // Default thresholds based on common storage requirements
        $defaults = [
            'temp_min' => 0,
            'temp_max' => 25,
            'humidity_min' => 40,
            'humidity_max' => 80,
        ];

        // Zone-specific thresholds
        $zoneThresholds = [
            'cold_storage' => [
                'temp_min' => 0,
                'temp_max' => 4,
                'humidity_min' => 80,
                'humidity_max' => 95,
            ],
            'refrigerator' => [
                'temp_min' => 2,
                'temp_max' => 8,
                'humidity_min' => 80,
                'humidity_max' => 95,
            ],
            'freezer' => [
                'temp_min' => -20,
                'temp_max' => -15,
                'humidity_min' => 0,
                'humidity_max' => 100,
            ],
            'dry_storage' => [
                'temp_min' => 15,
                'temp_max' => 25,
                'humidity_min' => 40,
                'humidity_max' => 60,
            ],
            'ambient' => [
                'temp_min' => 20,
                'temp_max' => 30,
                'humidity_min' => 40,
                'humidity_max' => 70,
            ],
        ];

        // Try to match zone name with predefined thresholds
        foreach ($zoneThresholds as $key => $thresholds) {
            if (stripos($zone, $key) !== false) {
                return $thresholds;
            }
        }

        return $defaults;
    }

    /**
     * Record storage condition from sensor data.
     */
    public static function recordCondition(
        int $branchId,
        string $storageZone,
        float $temperature,
        float $humidity,
        ?array $sensorData = null
    ): self {
        $tempCheck = static::checkTemperatureThreshold($storageZone, $temperature);
        $humidCheck = static::checkHumidityThreshold($storageZone, $humidity);

        $isWithinThreshold = $tempCheck['is_within'] && $humidCheck['is_within'];
        
        $alerts = array_filter([
            $tempCheck['alert'],
            $humidCheck['alert'],
        ]);

        return static::create([
            'branch_id' => $branchId,
            'storage_zone' => $storageZone,
            'temperature' => $temperature,
            'humidity' => $humidity,
            'recorded_at' => now(),
            'is_within_threshold' => $isWithinThreshold,
            'alert_message' => !empty($alerts) ? implode('; ', $alerts) : null,
            'sensor_data' => $sensorData,
        ]);
    }

    /**
     * Get average conditions for a zone over a period.
     */
    public static function getAverageConditions(
        int $branchId,
        string $storageZone,
        int $hours = 24
    ): array {
        $since = now()->subHours($hours);

        $conditions = static::where('branch_id', $branchId)
                           ->where('storage_zone', $storageZone)
                           ->where('recorded_at', '>=', $since)
                           ->get();

        if ($conditions->isEmpty()) {
            return [
                'avg_temperature' => null,
                'avg_humidity' => null,
                'min_temperature' => null,
                'max_temperature' => null,
                'min_humidity' => null,
                'max_humidity' => null,
                'alerts_count' => 0,
            ];
        }

        return [
            'avg_temperature' => round($conditions->avg('temperature'), 2),
            'avg_humidity' => round($conditions->avg('humidity'), 2),
            'min_temperature' => $conditions->min('temperature'),
            'max_temperature' => $conditions->max('temperature'),
            'min_humidity' => $conditions->min('humidity'),
            'max_humidity' => $conditions->max('humidity'),
            'alerts_count' => $conditions->where('is_within_threshold', false)->count(),
        ];
    }
}
