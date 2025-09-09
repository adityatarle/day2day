<div class="space-y-6">
    <!-- POS Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <div class="flex items-center">
                <div class="bg-blue-100 p-2 rounded-lg">
                    <i class="fas fa-cash-register text-blue-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-blue-600">Active Sessions</p>
                    <p class="text-2xl font-bold text-blue-900">{{ $posData['active_sessions'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <div class="flex items-center">
                <div class="bg-green-100 p-2 rounded-lg">
                    <i class="fas fa-chart-line text-green-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-600">Today's Sales</p>
                    <p class="text-2xl font-bold text-green-900">₹{{ number_format($posData['today_sales'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <div class="flex items-center">
                <div class="bg-purple-100 p-2 rounded-lg">
                    <i class="fas fa-clock text-purple-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-purple-600">Today Sessions</p>
                    <p class="text-2xl font-bold text-purple-900">{{ $posData['today_sessions'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
            <div class="flex items-center">
                <div class="bg-orange-100 p-2 rounded-lg">
                    <i class="fas fa-history text-orange-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-orange-600">Total Sessions</p>
                    <p class="text-2xl font-bold text-orange-900">{{ $posData['total_sessions'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent POS Sessions -->
    <div class="bg-white rounded-lg border border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900">Recent POS Sessions</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cashier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($posData['sessions'] as $session)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $session->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $session->user->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $session->started_at ? $session->started_at->format('M d, Y H:i') : 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $session->ended_at ? $session->ended_at->format('M d, Y H:i') : 'Active' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                {{ $session->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($session->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ₹{{ number_format($session->orders->sum('total_amount'), 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No POS sessions found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- POS Configuration -->
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">POS Configuration</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-sm font-medium text-gray-600">Terminal ID</label>
                <p class="text-gray-900 font-medium">{{ $branch->pos_terminal_id ?? 'Not configured' }}</p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-600">POS Status</label>
                <p class="text-gray-900 font-medium">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                        {{ $branch->pos_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $branch->pos_enabled ? 'Enabled' : 'Disabled' }}
                    </span>
                </p>
            </div>
        </div>
        
        @if($branch->pos_enabled)
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="flex space-x-4">
                <button onclick="managePosSettings()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-cog mr-2"></i>Manage Settings
                </button>
                <button onclick="viewPosReports()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-chart-bar mr-2"></i>View Reports
                </button>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function managePosSettings() {
    alert('POS settings management functionality');
}

function viewPosReports() {
    alert('POS reports functionality');
}
</script>