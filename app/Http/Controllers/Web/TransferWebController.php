<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Transfer;
use App\Services\TransferService;
use Illuminate\Http\Request;

class TransferWebController extends Controller
{
    public function __construct(private TransferService $service = new TransferService()) {}

    public function index()
    {
        $transfers = Transfer::with(['fromBranch', 'toBranch'])
            ->latest()
            ->paginate(20);
        return view('transfers.index', compact('transfers'));
    }

    public function create()
    {
        $branches = Branch::active()->get();
        $products = Product::active()->get();
        return view('transfers.create', compact('branches', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'to_subbranch_id' => 'nullable|exists:branches,id',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.expected_qty' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        $transfer = $this->service->createTransfer(
            $request->from_branch_id,
            $request->to_branch_id,
            $request->to_subbranch_id,
            $request->lines,
            $request->notes
        );

        return redirect()->route('transfers.show', $transfer)->with('success', 'Transfer created');
    }

    public function show(Transfer $transfer)
    {
        $transfer->load(['lines.product', 'shipments', 'receipts', 'discrepancies.lines']);
        return view('transfers.show', compact('transfer'));
    }

    public function approve(Transfer $transfer)
    {
        $this->service->approveTransfer($transfer);
        return redirect()->route('transfers.show', $transfer)->with('success', 'Transfer approved');
    }

    public function dispatch(Transfer $transfer, Request $request)
    {
        $request->validate([
            'transporter_name' => 'nullable|string',
            'vehicle_no' => 'nullable|string',
            'lr_no' => 'nullable|string',
            'seal_no' => 'nullable|string',
            'gross_weight_kg' => 'nullable|numeric|min:0',
            'tare_weight_kg' => 'nullable|numeric|min:0',
            'net_weight_kg' => 'nullable|numeric|min:0',
            'dispatch_ts' => 'nullable|date',
        ]);

        $this->service->dispatchTransfer($transfer, $request->all());
        return redirect()->route('transfers.show', $transfer)->with('success', 'Transfer dispatched');
    }

    public function markDelivered(Transfer $transfer)
    {
        $this->service->markDelivered($transfer);
        return redirect()->route('transfers.show', $transfer)->with('success', 'Marked as reached');
    }

    public function receive(Transfer $transfer, Request $request)
    {
        $request->validate([
            'arrival_ts' => 'nullable|date',
            'reweigh_gross_kg' => 'nullable|numeric|min:0',
            'reweigh_tare_kg' => 'nullable|numeric|min:0',
            'reweigh_net_kg' => 'nullable|numeric|min:0',
            'tolerance_percent' => 'nullable|numeric|min:0|max:100',
            'lines' => 'nullable|array',
            'lines.*.received_qty' => 'nullable|numeric|min:0',
        ]);

        $this->service->receiveTransfer($transfer, $request->all(), $request->tolerance_percent ?? 1.0);
        return redirect()->route('transfers.show', $transfer)->with('success', 'Transfer received');
    }
}

