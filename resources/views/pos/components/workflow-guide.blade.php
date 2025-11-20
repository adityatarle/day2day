<!-- POS Workflow Guide Component -->
<div class="cashier-card rounded-xl p-6 mb-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900">POS Workflow Guide</h2>
        <button onclick="toggleWorkflowGuide()" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-chevron-up" id="workflow-chevron"></i>
        </button>
    </div>
    
    <div id="workflow-content" class="space-y-6">
        <!-- Step 1: Start Session -->
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <span class="text-green-600 font-bold text-sm">1</span>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-900 mb-2">Start Your Session</h3>
                <p class="text-gray-600 text-sm mb-3">Begin your workday by starting a new POS session with your opening cash amount.</p>
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="flex items-center space-x-2 text-sm">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        <span class="text-gray-700">Enter the exact amount of cash in your register drawer</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Process Sales -->
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 font-bold text-sm">2</span>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-900 mb-2">Process Sales</h3>
                <p class="text-gray-600 text-sm mb-3">Use the POS terminal to scan products, add items to cart, and complete transactions.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="flex items-center space-x-2 text-sm">
                            <i class="fas fa-shopping-cart text-green-500"></i>
                            <span class="text-gray-700">Add products to cart</span>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="flex items-center space-x-2 text-sm">
                            <i class="fas fa-credit-card text-blue-500"></i>
                            <span class="text-gray-700">Select payment method</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Monitor Session -->
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                    <span class="text-purple-600 font-bold text-sm">3</span>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-900 mb-2">Monitor Your Session</h3>
                <p class="text-gray-600 text-sm mb-3">Keep track of your sales, transactions, and session performance throughout the day.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="flex items-center space-x-2 text-sm">
                            <i class="fas fa-chart-line text-green-500"></i>
                            <span class="text-gray-700">Total Sales</span>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="flex items-center space-x-2 text-sm">
                            <i class="fas fa-receipt text-blue-500"></i>
                            <span class="text-gray-700">Transaction Count</span>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="flex items-center space-x-2 text-sm">
                            <i class="fas fa-clock text-purple-500"></i>
                            <span class="text-gray-700">Session Duration</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Close Session -->
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                    <span class="text-red-600 font-bold text-sm">4</span>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-900 mb-2">Close Your Session</h3>
                <p class="text-gray-600 text-sm mb-3">At the end of your shift, count your cash and close the session to complete your workday.</p>
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="flex items-center space-x-2 text-sm">
                        <i class="fas fa-calculator text-orange-500"></i>
                        <span class="text-gray-700">Count actual cash in drawer and enter the amount</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Tips -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="font-semibold text-blue-900 mb-2">
                <i class="fas fa-lightbulb mr-2"></i>Quick Tips
            </h4>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• Always count your opening cash carefully</li>
                <li>• Keep your session active throughout your shift</li>
                <li>• Use the quick sale feature for fast transactions</li>
                <li>• Check your session stats regularly</li>
                <li>• Close your session at the end of your shift</li>
            </ul>
        </div>
    </div>
</div>

<script>
function toggleWorkflowGuide() {
    const content = document.getElementById('workflow-content');
    const chevron = document.getElementById('workflow-chevron');
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        chevron.className = 'fas fa-chevron-up';
    } else {
        content.style.display = 'none';
        chevron.className = 'fas fa-chevron-down';
    }
}
</script>



