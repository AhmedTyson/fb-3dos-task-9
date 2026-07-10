/**
 * Admin Module - Admin panel functionality
 * No ES modules - plain JavaScript for browser compatibility
 */
const Admin = {
    chart: null,
    currentChartMode: 'daily',

    init() {
        if (!AdminAuth.isAdmin()) {
            window.location.href = '../frontend/login.html';
            return;
        }

        this.loadUsers();
        this.loadOrders();
        this.loadSalesReport();
        this.loadAdminInfo();
        this.initLogout();
        this.initExportButtons();
        this.initChartModeButtons();
    },

    initChartModeButtons() {
        document.querySelectorAll('[data-chart-mode]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const mode = btn.getAttribute('data-chart-mode');
                if (!mode || mode === this.currentChartMode) return;
                this.currentChartMode = mode;
                // Toggle active class
                document.querySelectorAll('[data-chart-mode]').forEach(b => {
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-outline-secondary');
                });
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-primary');
                this.loadSalesReport();
            });
        });
    },

    initLogout() {
        document.querySelectorAll('.admin-logout-btn, #adminLogoutBtn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                AdminAuth.logout();
            });
        });
    },

    async loadAdminInfo() {
        const user = await AdminAuth.getUser();
        if (user) {
            const nameEl = document.querySelector('.user-profile h6');
            const emailEl = document.querySelector('.user-profile small');
            if (nameEl) nameEl.textContent = user.name || 'Admin';
            if (emailEl) emailEl.textContent = user.email || '';
        }
    },

    async loadUsers() {
        try {
            const response = await fetch(`${AdminAuth.getBaseUrl()}/admin/users`, {
                method: 'GET',
                headers: AdminAuth.getHeaders()
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
            const response = await fetch(`${AdminAuth.getBaseUrl()}/admin/orders?per_page=20`, {
                method: 'GET',
                headers: AdminAuth.getHeaders()
            });
            if (!response.ok) throw new Error('Failed to load orders');
            const data = await response.json();
            const orders = data?.data?.items || [];
            this.renderOrders(orders);
            this.renderPendingShipments(orders);
            this.renderRecentActivity(orders);
        } catch (error) {
            console.error('Failed to load orders:', error);
        }
    },

    async loadSalesReport() {
        try {
            const url = `${AdminAuth.getBaseUrl()}/admin/reports/sales?mode=${this.currentChartMode}`;
            const response = await fetch(url, {
                method: 'GET',
                headers: AdminAuth.getHeaders()
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
                <td class="px-3 py-3"><span class="fw-bold text-primary">#${user.id}</span></td>
                <td class="px-3 py-3">${user.name}</td>
                <td class="px-3 py-3">${user.email}</td>
                <td class="px-3 py-3">
                    <span class="badge ${user.role === 'admin' ? 'bg-primary' : 'bg-secondary'} rounded-pill px-3 py-2">${user.role}</span>
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
            const status = (order.status || 'pending').toLowerCase();
            const badgeMap = {
                'delivered': 'bg-success-subtle text-success',
                'completed': 'bg-success-subtle text-success',
                'shipped':   'bg-info-subtle text-info',
                'cancelled': 'bg-danger-subtle text-danger',
            };
            const badgeClass = badgeMap[status] || 'bg-warning-subtle text-warning';
            const badgeLabel = status === 'pending' ? 'Pending' :
                               status === 'approved' ? 'Approved' :
                               status.charAt(0).toUpperCase() + status.slice(1);
            const statusBadge = `<span class="badge ${badgeClass} rounded-pill px-3 py-2"><i class="fa-solid fa-circle me-1 fs-6 align-middle ${status === 'shipped' ? 'fa-pulse' : ''}"></i>${badgeLabel}</span>`;

            const firstItem = order.items && order.items[0];
            const imgSrc = firstItem?.product?.image_url || firstItem?.product?.thumbnail || '';
            const imageUrl = imgSrc
                ? (imgSrc.startsWith('http') ? imgSrc : (imgSrc.startsWith('/') ? imgSrc : '/' + imgSrc))
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
                <td class="px-3 py-3 text-end"></td>
            `;
            tbody.appendChild(row);
        });
    },

    renderPendingShipments(orders) {
        const tbody = document.getElementById('pendingShipmentsTbody');
        if (!tbody) return;
        const pendingStatuses = ['pending', 'approved', 'processing', 'shipped'];
        const pending = orders.filter(o => pendingStatuses.includes((o.status || '').toLowerCase()));
        if (pending.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">All orders processed</td></tr>';
            return;
        }
        tbody.innerHTML = '';
        pending.slice(0, 5).forEach(order => {
            const row = document.createElement('tr');
            const customerName = order.user?.name || order.customer_name || 'N/A';
            const dest = order.shipping_address;
            const destination = typeof dest === 'string' ? dest : (dest?.city ? `${dest.city}, ${dest.country || ''}` : 'N/A');
            const itemCount = order.items_count || order.items?.length || 0;
            row.innerHTML = `
                <td><span class="fw-bold text-primary">#LG-${order.id}-X</span></td>
                <td>${customerName}</td>
                <td class="text-muted">${destination.substring(0, 30)}</td>
                <td>${itemCount} item${itemCount !== 1 ? 's' : ''}</td>
                <td>${order.status || 'Processing'}</td>
                <td class="text-end"><button class="btn btn-sm btn-outline-primary download-pdf-btn" data-order-id="${order.id}" title="Download invoice PDF"><i class="fa-solid fa-file-pdf me-1"></i>PDF</button></td>
            `;
            tbody.appendChild(row);
        });
        // Attach PDF download via event delegation on tbody (once)
        if (!tbody.dataset.pdfAttached) {
            tbody.dataset.pdfAttached = '1';
            tbody.addEventListener('click', (e) => {
                const btn = e.target.closest('.download-pdf-btn');
                if (!btn) return;
                const orderId = btn.getAttribute('data-order-id');
                if (!orderId) return;
                this.downloadPendingPdf(orderId, btn);
            });
        }
    },

    async downloadPendingPdf(orderId, btn) {
        btn.disabled = true;
        const orig = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
        try {
            const res = await fetch(`${AdminAuth.getBaseUrl()}/admin/orders/${orderId}/print-file`, {
                headers: AdminAuth.getHeaders()
            });
            if (!res.ok) {
                const errBody = await res.json().catch(() => ({}));
                throw new Error(errBody.message || `HTTP ${res.status}`);
            }
            const blob = await res.blob();
            const blobUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = blobUrl;
            a.download = `order-${orderId}.pdf`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(blobUrl);
        } catch (err) {
            console.error('PDF download failed:', err);
            alert('PDF download failed: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    },

    initExportButtons() {
        const pdfBtn = document.getElementById('exportPdfBtn');
        const excelBtn = document.getElementById('exportExcelBtn');
        if (pdfBtn) pdfBtn.addEventListener('click', (e) => { e.preventDefault(); this.downloadReport('pdf'); });
        if (excelBtn) excelBtn.addEventListener('click', (e) => { e.preventDefault(); this.downloadReport('xlsx'); });
    },

    async downloadReport(format) {
        const from = document.getElementById('reportFromDate')?.value || '';
        const to = document.getElementById('reportToDate')?.value || '';
        const params = new URLSearchParams({ format, from, to });
        const url = `${AdminAuth.getBaseUrl()}/admin/reports/sales?${params}`;
        try {
            const res = await fetch(url, { headers: AdminAuth.getHeaders() });
            if (!res.ok) {
                const errBody = await res.json().catch(() => ({}));
                throw new Error(errBody.message || `HTTP ${res.status}`);
            }
            const blob = await res.blob();
            const ext = format === 'pdf' ? 'pdf' : 'xlsx';
            const blobUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = blobUrl;
            a.download = `sales-report.${ext}`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(blobUrl);
        } catch (err) {
            console.error('Report download failed:', err);
            alert('Download failed: ' + err.message);
        }
    },

    renderRecentActivity(orders) {
        const container = document.getElementById('recentActivityContainer');
        if (!container) return;
        if (!orders || orders.length === 0) {
            container.innerHTML = '<div class="text-muted small">No recent activity.</div>';
            return;
        }
        container.innerHTML = '';
        orders.slice(0, 5).forEach(order => {
            const name = order.user?.name || order.customer_name || 'Customer';
            const status = order.status || 'Processing';
            const date = order.created_at ? new Date(order.created_at).toLocaleDateString() : '';
            const div = document.createElement('div');
            div.className = 'd-flex align-items-center gap-2';
            div.innerHTML = `
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">
                    <i class="fa-solid fa-cart-shopping" style="font-size: 12px;"></i>
                </div>
                <div class="small flex-grow-1">
                    <strong>${name}</strong> placed order <span class="text-primary fw-medium">#LG-${order.id}-X</span>
                    <div class="text-muted" style="font-size: 11px;">${date} &middot; ${status}</div>
                </div>
            `;
            container.appendChild(div);
        });
    },

    renderChart(report) {
        const canvas = document.getElementById('salesChart');
        if (!canvas || !report.daily_breakdown || report.daily_breakdown.length === 0) return;
        const labels = report.daily_breakdown.map(d => d.date?.substring(5) || '');
        const revenue = report.daily_breakdown.map(d => d.revenue || 0);
        const orders = report.daily_breakdown.map(d => d.order_count || d.orders || 0);
        if (this.chart) this.chart.destroy();
        this.chart = new Chart(canvas, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label: 'Revenue (EGP)', data: revenue, borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,0.1)', yAxisID: 'y', tension: 0.3, fill: true },
                    { label: 'Order Count', data: orders, borderColor: '#198754', backgroundColor: 'rgba(25,135,84,0.1)', yAxisID: 'y1', tension: 0.3, fill: true }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false } },
                scales: {
                    y: { type: 'linear', position: 'left', beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    y1: { type: 'linear', position: 'right', beginAtZero: true, grid: { display: false } }
                }
            }
        });
    },

    renderSalesReport(report) {
        // Update KPI cards (HTML IDs: kpi-total-orders, kpi-total-sales, kpi-total-customers)
        const kpiEl = (id) => document.getElementById(id);
        if (kpiEl('kpi-total-orders'))   kpiEl('kpi-total-orders').textContent   = report.total_orders || 0;
        if (kpiEl('kpi-total-sales'))    kpiEl('kpi-total-sales').textContent    = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(report.total_revenue || 0);
        if (kpiEl('kpi-total-customers')) kpiEl('kpi-total-customers').textContent = report.total_users || 0;

        // Render sales chart
        this.renderChart(report);

        // Render best sellers as card grid
        const container = document.getElementById('bestSellersContainer');
        if (container && report.best_sellers && report.best_sellers.length > 0) {
            container.innerHTML = '';
            report.best_sellers.forEach(item => {
                const imgUrl = (item.images && Array.isArray(item.images) && item.images[0])
                    ? (item.images[0].startsWith('http') ? item.images[0] : '/storage/' + item.images[0].replace('/storage/', ''))
                    : 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=150&q=80';
                const card = document.createElement('div');
                card.className = 'col';
                card.innerHTML = `
                    <div class="card h-100 border-0 shadow-sm text-center p-3" style="border-radius: 12px;">
                        <img src="${imgUrl}" class="card-img-top mb-2" style="height: 100px; object-fit: contain;" alt="${item.name || ''}" />
                        <div class="card-body p-0">
                            <h6 class="fw-bold mb-1 text-truncate">${item.name || ''}</h6>
                            <small class="text-muted">${item.units_sold || 0} sold</small>
                            <div class="fw-bold text-primary mt-1">${new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(item.total_revenue || 0)}</div>
                        </div>
                    </div>`;
                container.appendChild(card);
            });
        }
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    Admin.init();
});