@extends('layouts.app')

@section('title', 'Branch Staff')

@section('content')
<div class="p-6">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Branch Staff</h1>
            <p class="text-gray-600 mt-1">Manage cashiers and delivery staff for {{ $branch->name }}</p>
        </div>
        <a href="{{ route('branch.staff.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">Add Staff</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($staff as $member)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                            <div class="text-sm text-gray-500">{{ $member->phone }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $member->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full @if(optional($member->role)->name === 'cashier') bg-green-100 text-green-800 @elseif(optional($member->role)->name === 'delivery_boy') bg-blue-100 text-blue-800 @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst(optional($member->role)->name ?? 'N/A') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($member->is_active)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('branch.staff.show', $member) }}" class="text-blue-600 hover:text-blue-900">View</a>
                            <a href="{{ route('branch.staff.edit', $member) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            <button data-id="{{ $member->id }}" class="toggle-status text-yellow-600 hover:text-yellow-800">Toggle Status</button>
                            <form action="{{ route('branch.staff.destroy', $member) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Delete this staff member?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">No staff found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t">
            {{ $staff->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-status').forEach(function(button) {
        button.addEventListener('click', async function() {
            const userId = this.getAttribute('data-id');
            try {
                const response = await fetch(`{{ url('/branch/staff') }}/${userId}/toggle-status`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to update status');
                }
            } catch (e) {
                alert('Failed to update status');
            }
        });
    });
});
</script>
@endsection

