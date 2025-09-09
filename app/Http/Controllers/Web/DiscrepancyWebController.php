<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Discrepancy;
use App\Models\Transfer;
use App\Services\TransferService;
use Illuminate\Http\Request;

class DiscrepancyWebController extends Controller
{
    public function __construct(private TransferService $service = new TransferService()) {}

    public function index()
    {
        $discrepancies = Discrepancy::with(['transfer.fromBranch', 'transfer.toBranch'])
            ->latest()
            ->paginate(20);
        return view('discrepancies.index', compact('discrepancies'));
    }

    public function show(Discrepancy $discrepancy)
    {
        $discrepancy->load(['lines.product', 'transfer']);
        return view('discrepancies.show', compact('discrepancy'));
    }

    public function resolve(Discrepancy $discrepancy, Request $request)
    {
        $request->validate([
            'disposition' => 'required|in:adjust,scrap',
        ]);
        $this->service->resolveDiscrepancy($discrepancy, $request->disposition);
        return redirect()->route('discrepancies.show', $discrepancy)->with('success', 'Discrepancy resolved');
    }
}

