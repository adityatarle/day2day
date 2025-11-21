<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CashLedgerEntry;
use App\Models\PosSession;
use Illuminate\Http\Request;

class CashLedgerController extends Controller
{
    /**
     * Show cash ledger for current session.
     */
    public function index()
    {
        $user = auth()->user();
        $session = PosSession::where('user_id', $user->id)->active()->first();

        $entries = CashLedgerEntry::where('branch_id', $user->branch_id)
            ->when($session, fn($q) => $q->where('pos_session_id', $session->id))
            ->orderBy('entry_date', 'desc')
            ->paginate(20);

        $totals = [
            'take' => CashLedgerEntry::where('branch_id', $user->branch_id)
                ->when($session, fn($q) => $q->where('pos_session_id', $session->id))
                ->where('entry_type', 'take')->sum('amount'),
            'give' => CashLedgerEntry::where('branch_id', $user->branch_id)
                ->when($session, fn($q) => $q->where('pos_session_id', $session->id))
                ->where('entry_type', 'give')->sum('amount'),
        ];

        return view('pos.ledger.index', compact('entries', 'session', 'totals'));
    }

    /**
     * Store a new cash ledger entry.
     */
    public function store(Request $request)
    {
        $request->validate([
            'entry_type' => 'required|in:give,take',
            'purpose' => 'nullable|in:food,miscellaneous,etc',
            'amount' => 'required|numeric|min:0.01',
            'counterparty' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $session = PosSession::where('user_id', $user->id)->active()->first();

        $entry = CashLedgerEntry::create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'pos_session_id' => $session?->id,
            'entry_type' => $request->entry_type,
            'purpose' => $request->purpose,
            'amount' => $request->amount,
            'counterparty' => $request->counterparty,
            'reference_number' => $request->reference_number,
            'notes' => $request->notes,
            'entry_date' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'data' => $entry]);
        }

        return back()->with('success', 'Cash entry recorded');
    }
}

