import './bootstrap';

// Common utility functions for the food company application
window.FoodCompany = {
    // Format currency
    formatCurrency: function(amount) {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR'
        }).format(amount);
    },

    // Format date
    formatDate: function(date) {
        return new Date(date).toLocaleDateString('en-IN', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },

    // Show notification
    showNotification: function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            type === 'warning' ? 'bg-yellow-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    },

    // Confirm action
    confirmAction: function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    },

    // Debounce function
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Search functionality
    search: function(query, items, searchFields) {
        if (!query) return items;
        
        const searchTerm = query.toLowerCase();
        return items.filter(item => {
            return searchFields.some(field => {
                const value = item[field];
                return value && value.toString().toLowerCase().includes(searchTerm);
            });
        });
    },

    // Pagination helper
    paginate: function(items, page, perPage) {
        const start = (page - 1) * perPage;
        const end = start + perPage;
        return {
            data: items.slice(start, end),
            currentPage: page,
            lastPage: Math.ceil(items.length / perPage),
            total: items.length,
            perPage: perPage
        };
    }
};

// Initialize common functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add loading states to forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⟳</span>Processing...';
            }
        });
    });

    // Add confirmation to delete buttons
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Are you sure you want to delete this item?';
            if (!FoodCompany.confirmAction(message)) {
                e.preventDefault();
            }
        });
    });

    // Add search functionality to search inputs
    const searchInputs = document.querySelectorAll('[data-search]');
    searchInputs.forEach(input => {
        const targetSelector = input.dataset.search;
        const targetContainer = document.querySelector(targetSelector);
        
        if (targetContainer) {
            const debouncedSearch = FoodCompany.debounce(function(query) {
                const items = targetContainer.querySelectorAll('[data-searchable]');
                const searchableItems = Array.from(items).map(item => ({
                    element: item,
                    text: item.dataset.searchable
                }));
                
                const results = FoodCompany.search(query, searchableItems, ['text']);
                
                // Hide all items first
                items.forEach(item => item.style.display = 'none');
                
                // Show only matching items
                results.forEach(result => {
                    result.element.style.display = 'block';
                });
            }, 300);
            
            input.addEventListener('input', function() {
                debouncedSearch(this.value);
            });
        }
    });

    // Initialize tooltips
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'absolute z-50 px-2 py-1 text-sm text-white bg-gray-900 rounded shadow-lg';
            tooltip.textContent = this.dataset.tooltip;
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
            
            this._tooltip = tooltip;
        });
        
        element.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
                this._tooltip = null;
            }
        });
    });
});

// Export for use in other modules
export default FoodCompany;
