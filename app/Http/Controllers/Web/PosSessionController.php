<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PosSession;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PosSessionController extends Controller
{
    /**
     * Display POS sessions for management.
     */
    public function index()
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return redirect()->route('dashboard')->with('error', 'No branch assigned to your account.');
        }

        // Get sessions based on user role
        $sessions = $this->getSessionsForUser($user);
        
        return view('pos.sessions.index', compact('sessions'));
    }

    /**
     * Show form to create new POS session.
     */
    public function create()
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return redirect()->route('dashboard')->with('error', 'No branch assigned to your account.');
        }

        // Check if user already has active session
        $activeSession = $user->currentPosSession();
        if ($activeSession) {
            return redirect()->route('pos.sessions.show', $activeSession)
                ->with('info', 'You already have an active POS session.');
        }

        return view('pos.sessions.create');
    }

    /**
     * Store a new POS session.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return response()->json(['error' => 'No branch assigned to your account.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'opening_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if user already has active session
        $activeSession = $user->currentPosSession();
        if ($activeSession) {
            return response()->json(['error' => 'You already have an active POS session.'], 400);
        }

        $session = PosSession::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'started_at' => now(),
            'opening_balance' => $request->opening_balance,
            'current_balance' => $request->opening_balance,
            'status' => 'active',
            'notes' => $request->notes,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'POS session started successfully.',
                'session' => $session
            ]);
        }

        return redirect()->route('pos.sessions.show', $session)
            ->with('success', 'POS session started successfully.');
    }

    /**
     * Show POS session details.
     */
    public function show(PosSession $posSession)
    {
        $user = auth()->user();

        // Check permissions
        if (!$this->canAccessSession($user, $posSession)) {
            abort(403, 'You do not have permission to access this POS session.');
        }

        $posSession->load([
            'user',
            'branch',
            'orders' => function($query) {
                $query->with(['customer', 'orderItems.product'])->latest();
            }
        ]);

        $sessionStats = [
            'total_orders' => $posSession->orders()->count(),
            'completed_orders' => $posSession->orders()->where('status', 'completed')->count(),
            'pending_orders' => $posSession->orders()->where('status', 'pending')->count(),
            'cancelled_orders' => $posSession->orders()->where('status', 'cancelled')->count(),
            'total_sales' => $posSession->orders()->where('status', 'completed')->sum('total_amount'),
            'cash_sales' => $posSession->orders()
                ->where('status', 'completed')
                ->where('payment_method', 'cash')
                ->sum('total_amount'),
            'card_sales' => $posSession->orders()
                ->where('status', 'completed')
                ->where('payment_method', 'card')
                ->sum('total_amount'),
            'duration' => $posSession->ended_at ? 
                $posSession->started_at->diffForHumans($posSession->ended_at, true) :
                $posSession->started_at->diffForHumans(),
        ];

        return view('pos.sessions.show', compact('posSession', 'sessionStats'));
    }

    /**
     * Close POS session.
     */
    public function close(Request $request, PosSession $posSession)
    {
        $user = auth()->user();

        // Check permissions
        if (!$this->canManageSession($user, $posSession)) {
            return response()->json(['error' => 'You do not have permission to close this session.'], 403);
        }

        if ($posSession->status !== 'active') {
            return response()->json(['error' => 'Session is not active.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'closing_balance' => 'required|numeric|min:0',
            'closing_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Calculate expected closing balance
        $cashSales = $posSession->orders()
            ->where('status', 'completed')
            ->where('payment_method', 'cash')
            ->sum('total_amount');
        
        $expectedBalance = $posSession->opening_balance + $cashSales;

        $posSession->update([
            'ended_at' => now(),
            'closing_balance' => $request->closing_balance,
            'expected_closing_balance' => $expectedBalance,
            'variance' => $request->closing_balance - $expectedBalance,
            'closing_notes' => $request->closing_notes,
            'status' => 'closed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'POS session closed successfully.',
            'variance' => $posSession->variance
        ]);
    }

    /**
     * Get session performance data.
     */
    public function getPerformanceData(PosSession $posSession)
    {
        $user = auth()->user();

        if (!$this->canAccessSession($user, $posSession)) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $hourlyData = $posSession->orders()
            ->where('status', 'completed')
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as orders, SUM(total_amount) as sales')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $paymentMethodData = $posSession->orders()
            ->where('status', 'completed')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'hourly_performance' => $hourlyData,
            'payment_methods' => $paymentMethodData,
        ]);
    }

    /**
     * Get sessions for user based on their role.
     */
    private function getSessionsForUser(User $user)
    {
        $query = PosSession::with(['user', 'branch']);

        if ($user->isSuperAdmin()) {
            // Super admin can see all sessions
            $query = $query->latest();
        } elseif ($user->isBranchManager()) {
            // Branch manager can see sessions for their branch
            $query = $query->where('branch_id', $user->branch_id)->latest();
        } else {
            // Cashiers can only see their own sessions
            $query = $query->where('user_id', $user->id)->latest();
        }

        return $query->paginate(20);
    }

    /**
     * Check if user can access a session.
     */
    private function canAccessSession(User $user, PosSession $session): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isBranchManager()) {
            return $user->branch_id === $session->branch_id;
        }

        return $user->id === $session->user_id;
    }

    /**
     * Check if user can manage (close/modify) a session.
     */
    private function canManageSession(User $user, PosSession $session): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isBranchManager()) {
            return $user->branch_id === $session->branch_id;
        }

        return $user->id === $session->user_id;
    }

    /**
     * Handle current session - redirect to active session or create new one.
     */
    public function current()
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return redirect()->route('dashboard')->with('error', 'No branch assigned to your account.');
        }

        // Check if user has an active session
        $activeSession = $user->currentPosSession();
        
        if ($activeSession) {
            // Redirect to the active session
            return redirect()->route('pos.sessions.show', $activeSession);
        } else {
            // No active session, redirect to create new one
            return redirect()->route('pos.sessions.create')
                ->with('info', 'You don\'t have an active POS session. Please start a new session.');
        }
    }

    /**
     * Get active sessions for branch management.
     */
    public function getActiveSessions()
    {
        $user = auth()->user();
        $branch = $user->branch;

        if (!$branch) {
            return response()->json(['error' => 'No branch assigned.'], 400);
        }

        $activeSessions = PosSession::where('status', 'active')
            ->where('branch_id', $branch->id)
            ->with(['user'])
            ->get()
            ->map(function($session) {
                return [
                    'id' => $session->id,
                    'user_name' => $session->user->name,
                    'started_at' => $session->started_at,
                    'duration' => $session->started_at->diffForHumans(),
                    'opening_balance' => $session->opening_balance,
                    'current_sales' => $session->orders()->where('status', 'completed')->sum('total_amount'),
                ];
            });

        return response()->json($activeSessions);
    }
}