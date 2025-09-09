<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Services\TransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransferController extends Controller
{
    public function __construct(private TransferService $service = new TransferService()) {}

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'to_subbranch_id' => 'nullable|exists:branches,id',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.expected_qty' => 'required|numeric|min:0.01',
            'lines.*.batch_number' => 'nullable|string',
            'lines.*.expected_weight_kg' => 'nullable|numeric|min:0',
            'lines.*.expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $transfer = $this->service->createTransfer(
            $request->from_branch_id,
            $request->to_branch_id,
            $request->to_subbranch_id,
            $request->lines,
            $request->notes
        );

        return response()->json(['status' => 'success', 'data' => $transfer], 201);
    }

    public function approve(Transfer $transfer)
    {
        $transfer = $this->service->approveTransfer($transfer);
        return response()->json(['status' => 'success', 'data' => $transfer]);
    }

    public function dispatch(Transfer $transfer, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transporter_name' => 'nullable|string',
            'vehicle_no' => 'nullable|string',
            'lr_no' => 'nullable|string',
            'seal_no' => 'nullable|string',
            'gross_weight_kg' => 'nullable|numeric|min:0',
            'tare_weight_kg' => 'nullable|numeric|min:0',
            'net_weight_kg' => 'nullable|numeric|min:0',
            'dispatch_ts' => 'nullable|date',
            'documents' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $shipment = $this->service->dispatchTransfer($transfer, $request->all());
        return response()->json(['status' => 'success', 'data' => $shipment]);
    }

    public function markDelivered(Transfer $transfer)
    {
        $transfer = $this->service->markDelivered($transfer);
        return response()->json(['status' => 'success', 'data' => $transfer]);
    }

    public function receive(Transfer $transfer, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'arrival_ts' => 'nullable|date',
            'reweigh_gross_kg' => 'nullable|numeric|min:0',
            'reweigh_tare_kg' => 'nullable|numeric|min:0',
            'reweigh_net_kg' => 'nullable|numeric|min:0',
            'tolerance_percent' => 'nullable|numeric|min:0|max:100',
            'lines' => 'nullable|array',
            'lines.*.received_qty' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $receipt = $this->service->receiveTransfer($transfer, $request->all(), $request->tolerance_percent ?? 1.0);
        return response()->json(['status' => 'success', 'data' => $receipt]);
    }
}

