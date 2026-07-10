/**
 * Admin Module - Admin panel functionality
 */
const Admin = {
    init() {
        // Check if user is admin
        if (!Auth.isAdmin()) {
            window.location.href = 'home.html';
            return;
        }

        this.loadUsers();
        this.loadOrders();
        this.loadSalesReport();
    },

    async loadUsers() {
        try {
            const response = await fetch(`${Auth.getBaseUrl()}/admin/users`, {
                method: 'GET',
                headers: Auth.getHeaders()
            });

            if (!response.ok) throw new Error('Failed to load users');

            const data = await response.json();
            this.renderUsers(data.data);
        } catch (error) {
            console.error('Failed to load users:', error);
        }
    },

    async loadOrders() {
        try {
            const response = await fetch(`${Auth.getBaseUrl()}/admin/orders?per_page=20`, {
                method: 'GET',
                headers: Auth.getHeaders()
            });

            if (!response.ok) throw new Error('Failed to load orders');

            const data = await response.json();
            this.renderOrders(data.data);
        } catch (error) {
            console.error('Failed to load orders:', error);
        }
    },

    async loadSalesReport() {
        try {
            const response = await fetch(`${Auth.getBaseUrl()}/admin/reports/sales`, {
                method: 'GET',
                headers: Auth.getHeaders()
            });

            if (!response.ok) throw new Error('Failed to load sales report');

            const data = await response.json();
            this.renderSalesReport(data.data);
        } catch (error) {
            console.error('Failed to load sales report:', error);
        }
    },

    renderUsers(users) {
        const tbody = document.getElementById('users-tbody');
        if (!tbody) return;

        tbody.innerHTML = '';

        users.forEach(user => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-3 py-3">
                    <span class="fw-bold text-primary">#${user.id}</span>
                </td>
                <td class="px-3 py-3">${user.name}</td>
                <td class="px-3 py-3">${user.email}</td>
                <td class="px-3 py-3">
                    <span class="badge ${user.role === 'admin' ? 'bg-primary' : 'bg-secondary'} rounded-pill px-3 py-2">
                        ${user.role}
                    </span>
                </td>
                <td class="px-3 py-3">${user.orders_count || 0}</td>
                <td class="px-3 py-3 text-secondary">${user.created_at ? new Date(user.created_at).toLocaleDateString() : '-'}</td>
            `;
            tbody.appendChild(row);
        });
    },

    renderOrders(orders) {
        const tbody = document.getElementById('orders-tbody');
        if (!tbody) return;

        tbody.innerHTML = '';

        orders.forEach(order => {
            let statusBadge = '';
            const status = order.status?.toLowerCase() || 'pending';
            if (status === 'delivered' || status === 'completed') {
                statusBadge = `<span class="badge bg-success-subtle text-success rounded-pill px-3 py-2"><i class="fa-solid fa-circle me-1 fs-6 align-middle"></i>Delivered</span>`;
            } else if (status === 'shipped') {
                statusBadge = `<span class="badge bg-info-subtle text-info rounded-pill px-3 py-2"><i class="fa-solid fa-circle me-1 fs-6 align-middle fa-pulse"></i>Shipped</span>`;
            } else if (status === 'cancelled') {
                statusBadge = `<span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2"><i class="fa-solid fa-circle me-1 fs-6 align-middle"></i>Cancelled</span>`;
            } else {
                statusBadge = `<span class="badge bg-light text-dark border rounded-pill px-3 py-2"><i class="fa-solid fa-circle me-1 fs-6 align-middle"></i>Processing</span>`;
            }

            const imageUrl = order.items && order.items[0] && order.items[0].product?.image_url 
                ? order.items[0].product.image_url 
                : 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=150&q=80';

            const row = document.createElement('tr');
            row.style.cursor = 'pointer';
            row.innerHTML = `
                <td class="px-3 py-3">
                    <span class="fw-bold text-primary">#LG-${order.id}-X</span>
                </td>
                <td class="px-3 py-3 text-secondary">${new Date(order.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</td>
                <td class="px-3 py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded border overflow-hidden bg-light" style="width: 40px; height: 40px;">
                            <img class="w-100 h-100 object-fit-cover" src="${imageUrl}" alt="Product" />
                        </div>
                        ${(order.items?.length > 1) ? `
                        <div class="rounded border bg-secondary-subtle text-dark d-flex align-items-center justify-content-center fw-medium" style="width: 40px; height: 40px; margin-left: -10px; z-index: 2; border: 2px solid #fff !important; font-size: 12px;">
                            +${order.items.length - 1}
                        </div>` : ''}
                    </div>
                </td>
                <td class="px-3 py-3">${statusBadge}</td>
                <td class="px-3 py-3 fw-bold">${new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(order.total || order.total_amount || 0)}</td>
                <td class="px-3 py-3 text-end">
                    <div class="d-flex justify-content-end gap-2">
                        <button class="btn btn-sm btn-outline-primary" title="Reorder">
                            <i class="fa-solid fa-arrows-rotate"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" title="Invoice">
                            <i class="fa-solid fa-download"></i>
                        </button>
                        <button class="btn btn-sm btn-primary px-3">Track Order</button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    },

    renderSalesReport(report) {
        // Update stats cards
        const stats = [
            { id: 'total-revenue', value: new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(report.total_revenue || 0) },
            { id: 'total-orders', value: report.total_orders || 0 },
            { id: 'avg-order', value: new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(report.avg_order_value || 0) },
            { id: 'total-users', value: report.total_users || 0 }
        ];

        stats.forEach(s => {
            const el = document.getElementById(s.id);
            if (el) el.textContent = s.value;
        });

        // Render best sellers
        const bestSellersTbody = document.getElementById('best-sellers-tbody');
        if (bestSellersTbody && report.best_sellers) {
            bestSellersTbody.innerHTML = '';
            report.best_sellers.forEach((item, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-3 py-3">${index + 1}</td>
                    <td class="px-3 py-3 fw-bold">${item.name}</td>
                    <td class="px-3 py-3">${item.category || 'N/A'}</td>
                    <td class="px-3 py-3">${item.quantity_sold || 0}</td>
                    <td class="px-3 py-3 fw-bold">${new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(item.total_revenue || 0)}</td>
                `;
                bestSellersTbody.appendChild(row);
            });
        }

        // Render daily breakdown
        const dailyTbody = document.getElementById('daily-breakdown-tbody');
        if (dailyTbody && report.daily_breakdown) {
            dailyTbody.innerHTML = '';
            report.daily_breakdown.forEach(day => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-3 py-3">${new Date(day.date).toLocaleDateString()}</td>
                    <td class="px-3 py-3">${day.orders || 0}</td>
                    <td class="px-3 py-3">${new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(day.revenue || 0)}</td>
                `;
                dailyTbody.appendChild(row);
            });
        }
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    Admin.init();
});