@extends('layouts.app')

@section('title', 'Discrepancy #'.$discrepancy->id)

@section('content')
@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card p-6 mb-6">
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-lg font-semibold">Discrepancy Details</h2>
      <p class="text-sm text-gray-500">Transfer #{{ $discrepancy->transfer_id }}</p>
    </div>
    <span class="badge">{{ ucfirst(str_replace('_',' ', $discrepancy->status)) }}</span>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 text-sm">
    <div><span class="text-gray-500">Reason</span><div>{{ str_replace('_',' ', $discrepancy->reason_category) }}</div></div>
    <div><span class="text-gray-500">Raised</span><div>{{ $discrepancy->created_at->format('Y-m-d H:i') }}</div></div>
    <div><span class="text-gray-500">Resolved</span><div>{{ $discrepancy->resolved_at ? $discrepancy->resolved_at->format('Y-m-d H:i') : '-' }}</div></div>
  </div>
</div>

<div class="card p-6">
  <h3 class="font-semibold mb-4">Lines</h3>
  <div class="table-container">
    <table class="data-table">
      <thead>
        <tr><th>Product</th><th>Qty Δ</th><th>Wt Δ (kg)</th><th>Disposition</th><th>Notes</th></tr>
      </thead>
      <tbody>
        @foreach($discrepancy->lines as $line)
        <tr>
          <td>{{ $line->product->name ?? ('#'.$line->product_id) }}</td>
          <td>{{ $line->qty_delta ?? '-' }}</td>
          <td>{{ $line->weight_delta_kg ?? '-' }}</td>
          <td>{{ ucfirst($line->disposition) }}</td>
          <td>{{ $line->notes }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  @if(in_array($discrepancy->status, ['open','under_review','reopened']))
  <form class="mt-4" method="POST" action="{{ route('discrepancies.resolve', $discrepancy) }}">
    @csrf
    <div class="flex items-center space-x-3">
      <select name="disposition" class="form-input" required>
        <option value="adjust">Adjust</option>
        <option value="scrap">Scrap</option>
      </select>
      <button type="submit" class="btn btn-primary">Resolve</button>
    </div>
  </form>
  @endif
</div>
@endsection

