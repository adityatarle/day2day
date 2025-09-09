<?php

namespace App\Http\Controllers;

use App\Models\Discrepancy;
use App\Models\DiscrepancyLine;
use App\Services\TransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DiscrepancyController extends Controller
{
    public function __construct(private TransferService $service = new TransferService()) {}

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transfer_id' => 'required|exists:transfers,id',
            'reason_category' => 'required|in:weight_diff,damaged,spoiled,expired,short,excess,mispick,other',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.qty_delta' => 'nullable|numeric',
            'lines.*.weight_delta_kg' => 'nullable|numeric',
            'lines.*.disposition' => 'required|in:adjust,return,scrap,quarantine,replace',
            'lines.*.notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $discrepancy = Discrepancy::create([
            'transfer_id' => $request->transfer_id,
            'status' => 'open',
            'reason_category' => $request->reason_category,
            'notes' => $request->notes,
            'raised_by' => auth()->id(),
        ]);

        foreach ($request->lines as $line) {
            DiscrepancyLine::create([
                'discrepancy_id' => $discrepancy->id,
                'product_id' => $line['product_id'],
                'qty_delta' => $line['qty_delta'] ?? null,
                'weight_delta_kg' => $line['weight_delta_kg'] ?? null,
                'disposition' => $line['disposition'],
                'notes' => $line['notes'] ?? null,
            ]);
        }

        return response()->json(['status' => 'success', 'data' => $discrepancy->load('lines')], 201);
    }

    public function resolve(Discrepancy $discrepancy, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'disposition' => 'required|in:adjust,scrap',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $resolved = $this->service->resolveDiscrepancy($discrepancy, $request->disposition);
        return response()->json(['status' => 'success', 'data' => $resolved]);
    }
}

