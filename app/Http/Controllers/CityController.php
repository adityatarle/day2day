<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\CityProductPricing;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    /**
     * Display a listing of cities.
     */
    public function index(): JsonResponse
    {
        $cities = City::with(['branches' => function($q) {
            $q->active();
        }])->active()->get();

        return response()->json([
            'success' => true,
            'data' => $cities,
            'message' => 'Cities retrieved successfully'
        ]);
    }

    /**
     * Store a newly created city.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:cities,code',
            'delivery_charge' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $city = City::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $city,
            'message' => 'City created successfully'
        ], 201);
    }

    /**
     * Display the specified city.
     */
    public function show($id): JsonResponse
    {
        $city = City::with(['branches', 'productPricing.product'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $city,
            'message' => 'City retrieved successfully'
        ]);
    }

    /**
     * Update the specified city.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $city = City::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:10|unique:cities,code,' . $city->id,
            'delivery_charge' => 'sometimes|required|numeric|min:0',
            'tax_rate' => 'sometimes|required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $city->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $city,
            'message' => 'City updated successfully'
        ]);
    }

    /**
     * Remove the specified city.
     */
    public function destroy($id): JsonResponse
    {
        $city = City::findOrFail($id);
        
        // Check if city has branches
        if ($city->branches()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete city with existing branches'
            ], 400);
        }

        $city->delete();

        return response()->json([
            'success' => true,
            'message' => 'City deleted successfully'
        ]);
    }

    /**
     * Set product pricing for a city.
     */
    public function setProductPricing(Request $request, $cityId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'selling_price' => 'required|numeric|min:0',
            'mrp' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_available' => 'boolean',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after:effective_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $city = City::findOrFail($cityId);

        // Check for existing pricing for the same period
        $existingPricing = CityProductPricing::where('city_id', $cityId)
            ->where('product_id', $request->product_id)
            ->where('effective_from', $request->effective_from)
            ->first();

        if ($existingPricing) {
            $existingPricing->update($request->all());
            $pricing = $existingPricing;
        } else {
            $pricing = CityProductPricing::create([
                'city_id' => $cityId,
                'product_id' => $request->product_id,
                'selling_price' => $request->selling_price,
                'mrp' => $request->mrp,
                'discount_percentage' => $request->discount_percentage ?? 0,
                'is_available' => $request->is_available ?? true,
                'effective_from' => $request->effective_from,
                'effective_until' => $request->effective_until,
            ]);
        }

        $pricing->load(['city', 'product']);

        return response()->json([
            'success' => true,
            'data' => $pricing,
            'message' => 'Product pricing set successfully'
        ]);
    }

    /**
     * Get product pricing for a city.
     */
    public function getProductPricing($cityId, Request $request): JsonResponse
    {
        $query = CityProductPricing::with(['product', 'city'])
            ->where('city_id', $cityId);

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('is_available')) {
            $query->where('is_available', $request->boolean('is_available'));
        }

        // Get current effective pricing
        if ($request->boolean('current_only', false)) {
            $query->effectiveOn();
        }

        $pricing = $query->orderBy('effective_from', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $pricing,
            'message' => 'City product pricing retrieved successfully'
        ]);
    }
}
