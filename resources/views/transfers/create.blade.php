@extends('layouts.app')

@section('title', 'Create Transfer')

@section('content')
<div class="max-w-5xl mx-auto">
  <form method="POST" action="{{ route('transfers.store') }}" onsubmit="return prepareLines()">
    @csrf

    <div class="card p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="form-label">From Branch</label>
          <select name="from_branch_id" class="form-input" required>
            <option value="">Select</option>
            @foreach($branches as $branch)
              <option value="{{ $branch->id }}">{{ $branch->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="form-label">To Branch</label>
          <select name="to_branch_id" class="form-input" required>
            <option value="">Select</option>
            @foreach($branches as $branch)
              <option value="{{ $branch->id }}">{{ $branch->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="form-label">Notes</label>
          <input type="text" name="notes" class="form-input" placeholder="Optional notes">
        </div>
      </div>
    </div>

    <div class="card p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold">Lines</h3>
        <button type="button" class="btn btn-secondary" onclick="addLine()">Add Line</button>
      </div>
      <div id="lines" class="space-y-4"></div>
    </div>

    <input type="hidden" name="lines" id="lines_json">

    <div class="mt-6 flex justify-end">
      <button type="submit" class="btn btn-primary">Create Transfer</button>
    </div>
  </form>
</div>

<template id="line-template">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
    <div>
      <label class="form-label">Product</label>
      <select class="form-input product-select" required>
        <option value="">Select</option>
        @foreach($products as $p)
          <option value="{{ $p->id }}">{{ $p->name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="form-label">Expected Qty</label>
      <input type="number" step="0.01" min="0.01" class="form-input qty-input" required>
    </div>
    <div>
      <button type="button" class="btn btn-danger" onclick="removeLine(this)">Remove</button>
    </div>
  </div>
</template>

<script>
  function addLine() {
    const tpl = document.getElementById('line-template').content.cloneNode(true);
    document.getElementById('lines').appendChild(tpl);
  }
  function removeLine(btn) {
    const row = btn.closest('.grid');
    row.remove();
  }
  function prepareLines() {
    const rows = document.querySelectorAll('#lines .grid');
    const lines = [];
    rows.forEach(row => {
      const productId = row.querySelector('.product-select').value;
      const qty = row.querySelector('.qty-input').value;
      if (productId && qty) {
        lines.push({ product_id: Number(productId), expected_qty: Number(qty) });
      }
    });
    if (lines.length === 0) {
      alert('Add at least one line');
      return false;
    }
    document.getElementById('lines_json').name = 'lines';
    document.getElementById('lines_json').value = JSON.stringify(lines);
    return true;
  }
</script>
@endsection

