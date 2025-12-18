<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\City;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerHomeController extends Controller
{
    /**
     * Display the customer-facing home page
     */
    public function index(Request $request)
    {
        // Get user's location if provided
        $latitude = $request->get('lat');
        $longitude = $request->get('lng');
        
        // Get all active branches
        $branches = Branch::where('is_active', true)
            ->with('city')
            ->get();
        
        // Find nearest branch if location is provided
        $nearestBranch = null;
        if ($latitude && $longitude) {
            $nearestBranch = $this->findNearestBranch($latitude, $longitude, $branches);
        }
        
        // Get featured products (active products with stock)
        $featuredProducts = Product::where('is_active', true)
            ->whereHas('branches', function($query) use ($nearestBranch) {
                if ($nearestBranch) {
                    $query->where('branch_id', $nearestBranch->id)
                          ->where('current_stock', '>', 0);
                } else {
                    $query->where('current_stock', '>', 0);
                }
            })
            ->with(['branches' => function($query) use ($nearestBranch) {
                if ($nearestBranch) {
                    $query->where('branch_id', $nearestBranch->id);
                }
            }])
            ->limit(12)
            ->get()
            ->map(function($product) use ($nearestBranch) {
                $branchProduct = $product->branches->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'category' => $product->category,
                    'selling_price' => $branchProduct?->pivot?->selling_price ?? $product->selling_price,
                    'current_stock' => $branchProduct?->pivot?->current_stock ?? 0,
                    'weight_unit' => $product->weight_unit,
                    'image' => $product->image ?? null,
                ];
            });
        
        // Get all cities for location selection
        $cities = City::where('is_active', true)->get();
        
        return view('customer.home', compact('branches', 'nearestBranch', 'featuredProducts', 'cities'));
    }
    
    /**
     * Find the nearest branch based on coordinates
     */
    private function findNearestBranch($latitude, $longitude, $branches)
    {
        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;
        
        foreach ($branches as $branch) {
            if (!$branch->latitude || !$branch->longitude) {
                continue;
            }
            
            $distance = $this->calculateDistance(
                $latitude,
                $longitude,
                $branch->latitude,
                $branch->longitude
            );
            
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $branch;
            }
        }
        
        return $nearest;
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
    
    /**
     * Show store page with products (web view)
     */
    public function showStore(Branch $branch)
    {
        if (!$branch->is_active) {
            abort(404, 'Store not found');
        }

        $branch->load('city');

        // Get products for this branch
        $products = Product::where('is_active', true)
            ->whereHas('branches', function($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                      ->where('current_stock', '>', 0);
            })
            ->with(['branches' => function($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            }])
            ->get()
            ->map(function($product) use ($branch) {
                $branchProduct = $product->branches->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'category' => $product->category,
                    'description' => $product->description,
                    'selling_price' => (float)($branchProduct?->pivot?->selling_price ?? $product->selling_price),
                    'current_stock' => (float)($branchProduct?->pivot?->current_stock ?? 0),
                    'weight_unit' => $product->weight_unit,
                    'image' => $product->image ?? null,
                ];
            });

        // Group products by category
        $productsByCategory = $products->groupBy('category');

        return view('customer.store', compact('branch', 'products', 'productsByCategory'));
    }

    /**
     * Show products page (web view)
     */
    public function showProducts(Branch $branch)
    {
        return $this->showStore($branch);
    }

    /**
     * Get products for a specific branch (API endpoint)
     */
    public function getProducts(Request $request, Branch $branch)
    {
        $products = Product::where('is_active', true)
            ->whereHas('branches', function($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                      ->where('current_stock', '>', 0);
            })
            ->with(['branches' => function($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            }])
            ->get()
            ->map(function($product) use ($branch) {
                $branchProduct = $product->branches->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'category' => $product->category,
                    'description' => $product->description,
                    'selling_price' => (float)($branchProduct?->pivot?->selling_price ?? $product->selling_price),
                    'current_stock' => (float)($branchProduct?->pivot?->current_stock ?? 0),
                    'weight_unit' => $product->weight_unit,
                    'image' => $product->image ?? null,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Show checkout page
     */
    public function showCheckout(Request $request)
    {
        // Get order data from request or session
        $orderData = null;
        
        if ($request->has('order_data')) {
            $orderData = json_decode($request->order_data, true);
            $request->session()->put('order_data', $orderData);
        } else {
            $orderData = $request->session()->get('order_data');
        }
        
        if (!$orderData) {
            return redirect('/')->with('error', 'No items in cart. Please add items to cart first.');
        }

        $branch = Branch::findOrFail($orderData['branch_id']);
        $branch->load('city');

        // Get product details for the items
        $items = collect($orderData['items'])->map(function($item) {
            $product = Product::find($item['product_id']);
            return [
                'product_id' => $item['product_id'],
                'name' => $product->name,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ];
        });

        $subtotal = $items->sum('total_price');
        $total = $subtotal; // No tax or delivery charge for now

        return view('customer.checkout', compact('branch', 'items', 'subtotal', 'total', 'orderData'));
    }

    /**
     * Place order
     */
    public function placeOrder(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string|max:1000',
            'delivery_address' => 'required|string|max:1000',
            'delivery_phone' => 'required|string|max:20',
            'payment_method' => 'required|in:cash,upi,card,cod',
            'notes' => 'nullable|string',
            'delivery_instructions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $branch = Branch::findOrFail($validated['branch_id']);

            // Create or find customer
            $customer = Customer::firstOrCreate(
                ['phone' => $validated['customer_phone']],
                [
                    'name' => $validated['customer_name'],
                    'email' => $validated['customer_email'] ?? null,
                    'address' => $validated['customer_address'] ?? $validated['delivery_address'],
                    'type' => 'retail',
                ]
            );

            // Calculate totals
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                
                // Check stock availability
                $branchProduct = $product->branches()->where('branch_id', $branch->id)->first();
                $currentStock = $branchProduct?->pivot?->current_stock ?? 0;
                
                if ($currentStock < $item['quantity']) {
                    DB::rollBack();
                    return back()->withErrors([
                        'stock' => "Insufficient stock for {$product->name}. Available: {$currentStock} {$product->weight_unit}"
                    ])->withInput();
                }

                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $discountAmount = 0;
            $taxAmount = 0;
            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            // Generate order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'branch_id' => $branch->id,
                'user_id' => null,
                'order_type' => 'online',
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_method'] === 'cod' ? 'pending' : 'paid',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
                'delivery_address' => $validated['delivery_address'],
                'delivery_phone' => $validated['delivery_phone'],
                'delivery_instructions' => $validated['delivery_instructions'] ?? null,
                'order_date' => now(),
            ]);

            // Create order items and update stock
            foreach ($validated['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit' => 'kg',
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);

                // Update stock
                $product = Product::find($item['product_id']);
                if ($product) {
                    DB::table('product_branches')
                        ->where('product_id', $item['product_id'])
                        ->where('branch_id', $branch->id)
                        ->decrement('current_stock', $item['quantity']);
                }
            }

            DB::commit();

            // Clear order data from session
            $request->session()->forget('order_data');

            // Redirect to confirmation page
            return redirect()->route('order.confirm', $order->id)
                ->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to place order: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show order confirmation page
     */
    public function orderConfirmation(Order $order)
    {
        $order->load(['customer', 'branch', 'orderItems.product']);
        
        return view('customer.order-confirm', compact('order'));
    }
}
