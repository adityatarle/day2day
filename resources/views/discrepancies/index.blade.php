@extends('layouts.app')

@section('title', 'Discrepancies')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-semibold">Discrepancies</h2>
  </div>

<div class="table-container">
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Transfer</th>
        <th>Reason</th>
        <th>Status</th>
        <th>Raised</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    @forelse($discrepancies as $d)
      <tr>
        <td>#{{ $d->id }}</td>
        <td>#{{ $d->transfer_id }} ({{ $d->transfer->fromBranch->name ?? '-' }} → {{ $d->transfer->toBranch->name ?? '-' }})</td>
        <td>{{ str_replace('_',' ', $d->reason_category) }}</td>
        <td><span class="badge">{{ ucfirst(str_replace('_',' ', $d->status)) }}</span></td>
        <td>{{ $d->created_at->format('Y-m-d H:i') }}</td>
        <td><a href="{{ route('discrepancies.show', $d) }}" class="text-blue-600">View</a></td>
      </tr>
    @empty
      <tr><td colspan="6" class="text-center text-gray-500">No discrepancies</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $discrepancies->links() }}</div>
@endsection

