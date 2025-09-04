<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Branch;
use Illuminate\Http\Request;

class PosWebController extends Controller
{
    /**
     * Display the POS interface.
     */
    public function index()
    {
        $user = auth()->user();
        $branch = $user->branch;
        
        if (!$branch) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not assigned to any branch');
        }

        if (!$branch->pos_enabled) {
            return redirect()->route('dashboard')
                ->with('error', 'POS is not enabled for your branch');
        }

        $currentSession = PosSession::where('user_id', $user->id)->active()->first();
        $customers = Customer::active()->get();
        
        return view('pos.index', compact('branch', 'currentSession', 'customers'));
    }

    /**
     * Show session start form.
     */
    public function startSession()
    {
        $user = auth()->user();
        $branch = $user->branch;
        
        if (!$branch || !$branch->pos_enabled) {
            return redirect()->route('dashboard')
                ->with('error', 'POS is not available');
        }

        // Check for existing active session
        $existingSession = PosSession::where('user_id', $user->id)->active()->first();
        
        if ($existingSession) {
            return redirect()->route('pos.index')
                ->with('info', 'You already have an active POS session');
        }

        return view('pos.start-session', compact('branch'));
    }

    /**
     * Process session start.
     */
    public function processStartSession(Request $request)
    {
        $request->validate([
            'terminal_id' => 'required|string',
            'opening_cash' => 'required|numeric|min:0',
        ]);

        $user = auth()->user();
        $branch = $user->branch;

        // Check for existing session on this terminal
        $existingSession = PosSession::where('terminal_id', $request->terminal_id)
            ->where('branch_id', $branch->id)
            ->active()
            ->first();

        if ($existingSession) {
            return back()->withErrors(['terminal_id' => 'This terminal already has an active session']);
        }

        $session = PosSession::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'terminal_id' => $request->terminal_id,
            'opening_cash' => $request->opening_cash,
            'started_at' => now(),
            'status' => 'active',
        ]);

        return redirect()->route('pos.index')
            ->with('success', 'POS session started successfully');
    }

    /**
     * Show session close form.
     */
    public function closeSession()
    {
        $session = PosSession::where('user_id', auth()->id())->active()->first();
        
        if (!$session) {
            return redirect()->route('pos.index')
                ->with('error', 'No active session found');
        }

        $expectedCash = $session->calculateExpectedCash();
        
        return view('pos.close-session', compact('session', 'expectedCash'));
    }

    /**
     * Process session close.
     */
    public function processCloseSession(Request $request)
    {
        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $session = PosSession::where('user_id', auth()->id())->active()->first();
        
        if (!$session) {
            return redirect()->route('pos.index')
                ->with('error', 'No active session found');
        }

        $notes = $request->notes ? [$request->notes] : [];
        $session->closeSession($request->closing_cash, $notes);

        return redirect()->route('pos.index')
            ->with('success', 'POS session closed successfully');
    }

    /**
     * Show sales interface.
     */
    public function sales()
    {
        $session = PosSession::where('user_id', auth()->id())->active()->first();
        
        if (!$session) {
            return redirect()->route('pos.start-session')
                ->with('error', 'Please start a POS session first');
        }

        $cityId = $session->branch->city_id;
        $products = Product::with(['cityPricing' => function($q) use ($cityId) {
            $q->where('city_id', $cityId)->available()->effectiveOn();
        }])->active()->get();

        // Add city-specific pricing
        $products->transform(function($product) use ($cityId) {
            $product->city_price = $product->getCityPrice($cityId);
            $product->is_available_in_city = $product->isAvailableInCity($cityId);
            return $product;
        });

        $customers = Customer::active()->get();
        
        return view('pos.sales', compact('session', 'products', 'customers'));
    }

    /**
     * Show session history.
     */
    public function sessionHistory()
    {
        $sessions = PosSession::with(['user', 'branch.city'])
            ->where('user_id', auth()->id())
            ->orderBy('started_at', 'desc')
            ->paginate(15);

        return view('pos.history', compact('sessions'));
    }
}
