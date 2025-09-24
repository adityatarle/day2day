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

    // Notifications dropdown, fetch, and polling
    const notifBtn = document.getElementById('notifications-button');
    const notifDropdown = document.getElementById('notifications-dropdown');
    const notifList = document.getElementById('notifications-list');
    const notifBadge = document.getElementById('notifications-badge');
    const notifMarkAll = document.getElementById('notifications-mark-all');

    if (notifBtn && notifDropdown && notifList && notifBadge) {
        let lastFetchedIds = new Set();

        function toggleDropdown() {
            notifDropdown.classList.toggle('hidden');
        }

        function closeDropdownOnOutsideClick(event) {
            if (!notifDropdown.contains(event.target) && !notifBtn.contains(event.target)) {
                notifDropdown.classList.add('hidden');
            }
        }

        function renderNotifications(data) {
            const { notifications, unread_count } = data;
            notifList.innerHTML = '';

            if (!notifications || notifications.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'p-4 text-sm text-gray-500';
                empty.textContent = 'No notifications';
                notifList.appendChild(empty);
            } else {
                notifications.forEach(n => {
                    const isUnread = !n.read_at;
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = `w-full text-left px-4 py-3 flex items-start gap-3 hover:bg-gray-50 ${isUnread ? 'bg-blue-50' : ''}`;
                    const icon = document.createElement('i');
                    icon.className = 'fas fa-bell text-gray-500 mt-0.5';
                    const content = document.createElement('div');
                    const title = document.createElement('div');
                    title.className = 'font-medium text-sm text-gray-800';
                    title.textContent = n.title || 'Notification';
                    const message = document.createElement('div');
                    message.className = 'text-sm text-gray-600';
                    message.textContent = n.message || '';
                    const meta = document.createElement('div');
                    meta.className = 'text-xs text-gray-400 mt-1';
                    meta.textContent = new Date(n.created_at).toLocaleString();
                    content.appendChild(title);
                    content.appendChild(message);
                    content.appendChild(meta);
                    item.appendChild(icon);
                    item.appendChild(content);

                    item.addEventListener('click', async () => {
                        try {
                            await window.axios.post(`/api/notifications/${n.id}/read`);
                            item.classList.remove('bg-blue-50');
                            fetchNotifications();
                        } catch (e) {
                            // ignore
                        }
                    });

                    notifList.appendChild(item);
                });
            }

            if (unread_count > 0) {
                notifBadge.classList.remove('hidden');
            } else {
                notifBadge.classList.add('hidden');
            }
        }

        async function fetchNotifications() {
            try {
                const res = await window.axios.get('/api/notifications?limit=10');
                const data = res.data || {};
                // Detect new notifications since last fetch
                const currentIds = new Set((data.notifications || []).map(n => n.id));
                const newIds = [...currentIds].filter(id => !lastFetchedIds.has(id));
                if (lastFetchedIds.size > 0 && newIds.length > 0) {
                    FoodCompany.showNotification('You have new notifications', 'info');
                }
                lastFetchedIds = currentIds;
                renderNotifications(data);
            } catch (e) {
                // silently fail
            }
        }

        async function markAllRead() {
            try {
                await window.axios.post('/api/notifications/mark-all-read');
                fetchNotifications();
            } catch (e) {
                // ignore
            }
        }

        notifBtn.addEventListener('click', toggleDropdown);
        document.addEventListener('click', closeDropdownOnOutsideClick);
        if (notifMarkAll) {
            notifMarkAll.addEventListener('click', markAllRead);
        }

        // Initial fetch and polling every 20s
        fetchNotifications();
        setInterval(fetchNotifications, 20000);
    }
});

// Export for use in other modules
export default FoodCompany;
