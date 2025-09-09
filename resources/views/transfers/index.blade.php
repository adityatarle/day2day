@extends('layouts.app')

@section('title', 'Transfers')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-semibold">Transfers</h2>
    <a href="{{ route('transfers.create') }}" class="btn btn-primary">Create Transfer</a>
  </div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="table-container">
  <table class="data-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>From</th>
        <th>To</th>
        <th>Status</th>
        <th>Created</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    @forelse($transfers as $transfer)
      <tr>
        <td>#{{ $transfer->id }}</td>
        <td>{{ $transfer->fromBranch->name ?? '-' }}</td>
        <td>{{ $transfer->toBranch->name ?? '-' }}</td>
        <td><span class="badge">{{ ucfirst(str_replace('_',' ', $transfer->status)) }}</span></td>
        <td>{{ $transfer->created_at->format('Y-m-d H:i') }}</td>
        <td>
          <a class="text-blue-600" href="{{ route('transfers.show', $transfer) }}">View</a>
        </td>
      </tr>
    @empty
      <tr><td colspan="6" class="text-center text-gray-500">No transfers</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $transfers->links() }}</div>
@endsection

