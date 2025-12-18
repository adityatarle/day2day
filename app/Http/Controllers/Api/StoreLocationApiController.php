<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class StoreLocationApiController extends Controller
{
    /**
     * Get nearest stores based on coordinates
     */
    public function nearest(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0|max:50', // radius in kilometers
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $request->radius ?? 10; // Default 10km radius

        // Get all active branches with coordinates
        $branches = Branch::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('city')
            ->get();

        // Calculate distance for each branch
        $branchesWithDistance = $branches->map(function($branch) use ($latitude, $longitude) {
            $distance = $this->calculateDistance(
                $latitude,
                $longitude,
                $branch->latitude,
                $branch->longitude
            );
            
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
                'address' => $branch->address,
                'phone' => $branch->phone,
                'email' => $branch->email,
                'latitude' => (float) $branch->latitude,
                'longitude' => (float) $branch->longitude,
                'city' => $branch->city ? [
                    'id' => $branch->city->id,
                    'name' => $branch->city->name,
                ] : null,
                'distance' => round($distance, 2), // Distance in kilometers
                'pos_enabled' => (bool) $branch->pos_enabled,
            ];
        })
        ->filter(function($branch) use ($radius) {
            return $branch['distance'] <= $radius;
        })
        ->sortBy('distance')
        ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'nearest_store' => $branchesWithDistance->first(),
                'nearby_stores' => $branchesWithDistance->take(5),
                'all_stores' => $branchesWithDistance,
            ],
        ]);
    }

    /**
     * Get all stores
     */
    public function index(Request $request)
    {
        $cityId = $request->get('city_id');

        $query = Branch::where('is_active', true)
            ->with('city');

        if ($cityId) {
            $query->where('city_id', $cityId);
        }

        $branches = $query->get()->map(function($branch) {
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
                'address' => $branch->address,
                'phone' => $branch->phone,
                'email' => $branch->email,
                'latitude' => $branch->latitude ? (float) $branch->latitude : null,
                'longitude' => $branch->longitude ? (float) $branch->longitude : null,
                'city' => $branch->city ? [
                    'id' => $branch->city->id,
                    'name' => $branch->city->name,
                ] : null,
                'pos_enabled' => (bool) $branch->pos_enabled,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $branches,
        ]);
    }

    /**
     * Get store by ID
     */
    public function show(Branch $branch)
    {
        if (!$branch->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found or inactive'
            ], 404);
        }

        $branch->load('city');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
                'address' => $branch->address,
                'phone' => $branch->phone,
                'email' => $branch->email,
                'latitude' => $branch->latitude ? (float) $branch->latitude : null,
                'longitude' => $branch->longitude ? (float) $branch->longitude : null,
                'city' => $branch->city ? [
                    'id' => $branch->city->id,
                    'name' => $branch->city->name,
                ] : null,
                'pos_enabled' => (bool) $branch->pos_enabled,
                'operating_hours' => $branch->operating_hours,
            ],
        ]);
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in kilometers
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
}
