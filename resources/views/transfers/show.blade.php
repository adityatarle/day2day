@extends('layouts.app')

@section('title', 'Transfer #'.$transfer->id)

@section('content')
@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 space-y-6">
    <div class="card p-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold">Transfer Details</h2>
          <p class="text-sm text-gray-500">From {{ $transfer->fromBranch->name ?? '-' }} to {{ $transfer->toBranch->name ?? '-' }}</p>
        </div>
        <span class="badge">{{ ucfirst(str_replace('_',' ', $transfer->status)) }}</span>
      </div>
      <div class="mt-4">
        <table class="data-table">
          <thead>
            <tr><th>Product</th><th>Expected Qty</th></tr>
          </thead>
          <tbody>
            @foreach($transfer->lines as $line)
            <tr>
              <td>{{ $line->product->name ?? ('#'.$line->product_id) }}</td>
              <td>{{ $line->expected_qty }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <div class="card p-6">
      <h3 class="font-semibold mb-4">Shipments</h3>
      @forelse($transfer->shipments as $ship)
        <div class="border rounded-lg p-4 mb-3">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div><span class="text-gray-500">Transporter</span><div>{{ $ship->transporter_name ?? '-' }}</div></div>
            <div><span class="text-gray-500">Vehicle</span><div>{{ $ship->vehicle_no ?? '-' }}</div></div>
            <div><span class="text-gray-500">Net Wt (kg)</span><div>{{ $ship->net_weight_kg ?? '-' }}</div></div>
            <div><span class="text-gray-500">Dispatched</span><div>{{ $ship->dispatch_ts }}</div></div>
          </div>
        </div>
      @empty
        <p class="text-gray-500">No shipments yet.</p>
      @endforelse

      @if(in_array($transfer->status, ['approved','dispatched','in_transit','delivered_pending_confirm']))
      <form class="mt-4" method="POST" action="{{ route('transfers.dispatch', $transfer) }}">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
          <input class="form-input" name="transporter_name" placeholder="Transporter">
          <input class="form-input" name="vehicle_no" placeholder="Vehicle No">
          <input class="form-input" name="lr_no" placeholder="LR No">
          <input class="form-input" name="gross_weight_kg" placeholder="Gross kg" type="number" step="0.01">
          <input class="form-input" name="tare_weight_kg" placeholder="Tare kg" type="number" step="0.01">
          <button class="btn btn-primary" type="submit">Dispatch/Update</button>
        </div>
      </form>
      @endif
    </div>

    <div class="card p-6">
      <h3 class="font-semibold mb-4">Receipts</h3>
      @forelse($transfer->receipts as $rec)
        <div class="border rounded-lg p-4 mb-3">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div><span class="text-gray-500">Arrival</span><div>{{ $rec->arrival_ts }}</div></div>
            <div><span class="text-gray-500">Reweigh Net</span><div>{{ $rec->reweigh_net_kg ?? '-' }}</div></div>
            <div><span class="text-gray-500">Within Tolerance</span><div>{{ $rec->within_tolerance ? 'Yes' : 'No' }}</div></div>
            <div><span class="text-gray-500">Tolerance %</span><div>{{ $rec->tolerance_percent }}</div></div>
          </div>
        </div>
      @empty
        <p class="text-gray-500">No receipts yet.</p>
      @endforelse

      @if(in_array($transfer->status, ['in_transit','delivered_pending_confirm']))
      <form class="mt-4" method="POST" action="{{ route('transfers.receive', $transfer) }}">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
          <input class="form-input" name="reweigh_gross_kg" placeholder="Gross kg" type="number" step="0.01">
          <input class="form-input" name="reweigh_tare_kg" placeholder="Tare kg" type="number" step="0.01">
          <input class="form-input" name="reweigh_net_kg" placeholder="Net kg" type="number" step="0.01">
          <input class="form-input" name="tolerance_percent" placeholder="Tolerance %" type="number" step="0.01" value="1.0">
          <button class="btn btn-success" type="submit">Confirm Receipt</button>
        </div>
      </form>
      @endif
    </div>
  </div>

  <div class="space-y-6">
    <div class="card p-6">
      <h3 class="font-semibold mb-4">Quick Actions</h3>
      <div class="space-y-2">
        @if($transfer->status === 'draft')
        <form method="POST" action="{{ route('transfers.approve', $transfer) }}">@csrf<button class="btn btn-primary w-full">Approve</button></form>
        @endif
        @if($transfer->status === 'in_transit')
        <form method="POST" action="{{ route('transfers.markDelivered', $transfer) }}">@csrf<button class="btn btn-secondary w-full">Mark Reached</button></form>
        @endif
      </div>
    </div>

    <div class="card p-6">
      <h3 class="font-semibold mb-2">Discrepancies</h3>
      @forelse($transfer->discrepancies as $d)
        <div class="border rounded-lg p-3 mb-2">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm">#{{ $d->id }} - {{ ucfirst(str_replace('_',' ', $d->status)) }} ({{ str_replace('_',' ', $d->reason_category) }})</div>
              <div class="text-xs text-gray-500">{{ $d->notes }}</div>
            </div>
            <a class="text-blue-600" href="{{ route('discrepancies.show', $d) }}">Open</a>
          </div>
        </div>
      @empty
        <p class="text-gray-500">No discrepancies.</p>
      @endforelse
    </div>
  </div>
</div>
@endsection

