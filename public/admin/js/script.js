document.addEventListener("DOMContentLoaded", function () {

    const API_BASE = window.location.origin + "/api";

    // --- SECURE FETCH WRAPPER ---
    async function adminFetch(endpoint, options = {}) {
        const token = localStorage.getItem("admin_token");
        if (!token) {
            window.location.href = "login.html";
            return;
        }

        const defaultHeaders = {
            "Authorization": `Bearer ${token}`,
            "Accept": "application/json",
            "ngrok-skip-browser-warning": "true"
        };

        if (!(options.body instanceof FormData)) {
            defaultHeaders["Content-Type"] = "application/json";
        }

        options.headers = { ...defaultHeaders, ...options.headers };

        try {
            const response = await fetch(`${API_BASE}${endpoint}`, options);
            if (response.status === 401 || response.status === 403) {
                localStorage.removeItem("admin_token");
                window.location.href = "login.html";
                return;
            }
            return response;
        } catch (error) {
            console.error("Fetch Error:", error);
            throw error;
        }
    }

    const currencyFmt = new Intl.NumberFormat('en-EG', { style: 'currency', currency: 'EGP' });
    const numberFmt = new Intl.NumberFormat('en-US');

    // Unwrap cache middleware wrapper { message, source, data: actual }
    function unwrap(resp) {
        if (resp && typeof resp === 'object' && 'data' in resp && ('items' in resp.data || 'data' in resp.data || 'total_revenue' in resp.data)) {
            return resp.data;
        }
        return resp;
    }

    function timeAgo(dateStr) {
        const now = new Date();
        const then = new Date(dateStr);
        const sec = Math.floor((now - then) / 1000);
        if (sec < 60) return 'just now';
        const min = Math.floor(sec / 60);
        if (min < 60) return min + ' min ago';
        const hr = Math.floor(min / 60);
        if (hr < 24) return hr + 'h ago';
        const days = Math.floor(hr / 24);
        return days + 'd ago';
    }

    function getWeekNumber(date) {
        const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
        d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay() || 7));
        const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
        const weekNo = Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
        return d.getUTCFullYear() + '-W' + String(weekNo).padStart(2, '0');
    }

    // --- PHASE 4-5: LOAD KPIs ---
    async function loadDashboardKPIs() {
        const salesEl = document.getElementById("kpi-total-sales");
        const ordersEl = document.getElementById("kpi-total-orders");
        const customersEl = document.getElementById("kpi-total-customers");

        // Fetch sales report separately
        try {
            const reportRes = await adminFetch("/admin/reports/sales?format=json");
            if (reportRes && reportRes.ok) {
                const reportData = await reportRes.json();
                const d = unwrap(reportData);
                if (d) {
                    if (salesEl) salesEl.innerText = currencyFmt.format(d.total_revenue || 0);
                    if (ordersEl) ordersEl.innerText = numberFmt.format(d.total_orders || 0);
                    if (d.daily_breakdown) window._dailyData = d.daily_breakdown;
                    if (d.best_sellers) window._bestSellers = d.best_sellers;
                } else {
                    if (salesEl) salesEl.innerText = "0";
                    if (ordersEl) ordersEl.innerText = "0";
                }
            } else {
                if (salesEl) salesEl.innerText = "0";
                if (ordersEl) ordersEl.innerText = "0";
            }
        } catch (e) {
            console.error("Sales report fetch failed:", e);
            if (salesEl) salesEl.innerText = "0";
            if (ordersEl) ordersEl.innerText = "0";
        }

        // Fetch customers separately
        try {
            const usersRes = await adminFetch("/admin/users?page=1&per_page=1");
            if (usersRes && usersRes.ok) {
                let totalCustomers = usersRes.headers.get("X-Pagination-Total-Count");
                if (!totalCustomers) {
                    const body = await usersRes.clone().json().catch(() => ({}));
                    const unwrapped = unwrap(body);
                    totalCustomers = unwrapped?.pagination?.total || unwrapped?.meta?.total || body?.data?.pagination?.total || body?.data?.meta?.total || "0";
                }
                if (customersEl) customersEl.innerText = numberFmt.format(parseInt(totalCustomers));
            } else {
                if (customersEl) customersEl.innerText = "0";
            }
        } catch (e) {
            console.error("Users fetch failed:", e);
            if (customersEl) customersEl.innerText = "0";
        }
    }

    // --- PHASE 8: BEST SELLERS ---
    function renderBestSellers(products) {
        const container = document.getElementById('bestSellersContainer');
        if (!container) return;
        if (!products || products.length === 0) {
            container.innerHTML = '<div class="col-12 text-center text-muted py-4">No sales data yet</div>';
            return;
        }
        const emojis = ['🏆', '🥈', '🥉', '📦', '📦', '📦', '📦', '📦', '📦', '📦'];
        const bgClasses = ['p-bg-1','p-bg-2','p-bg-3','p-bg-4','p-bg-5','p-bg-1','p-bg-2','p-bg-3','p-bg-4','p-bg-5'];
        const maxSold = products[0].units_sold || 1;
        container.innerHTML = products.slice(0, 10).map((p, i) => {
            const pct = ((p.units_sold || 0) / maxSold * 100).toFixed(0);
            const rawImg = (p.images && p.images[0]) ? p.images[0] : null;
            // Strip leading /storage/ or storage/ to prevent double path
            const cleanPath = rawImg ? rawImg.replace(/^\/?storage\//, '') : null;
            const imgSrc = cleanPath ? `/api/storage/${cleanPath}` : null;
            const fallbackEl = emojis[i] || '📦';
            return `<div class="col product-item text-center">
                <div class="product-img mb-2 d-flex align-items-center justify-content-center ${bgClasses[i % bgClasses.length]} position-relative loading">
                    ${imgSrc ? `<img src="${imgSrc}" alt="${p.name}" loading="lazy" onerror="this.closest('.product-img').innerHTML='${fallbackEl}';this.closest('.product-img').classList.remove('loading')" onload="this.closest('.product-img').classList.remove('loading')" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">` : fallbackEl}
                    <span class="position-absolute top-0 start-0 badge bg-dark bg-opacity-75 rounded-circle" style="font-size:0.6rem;width:22px;height:22px;display:flex;align-items:center;justify-content:center">${i + 1}</span>
                </div>
                <h6 class="m-0 text-truncate" style="font-size:0.8rem">${p.name}</h6>
                <small class="text-muted d-block" style="font-size:0.75rem">${numberFmt.format(p.units_sold || 0)} sold</small>
                <div class="progress mt-1" style="height:4px"><div class="progress-bar" style="width:${pct}%"></div></div>
                <span class="text-primary fw-bold" style="font-size:0.75rem">${currencyFmt.format(p.total_revenue || 0)}</span>
            </div>`;
        }).join('');
    }

    // --- PHASE 9: RECENT ACTIVITY ---
    async function loadRecentActivity() {
        const container = document.getElementById('recentActivityContainer');
        if (!container) return;
        try {
            const [ordersRes, usersRes, productsRes] = await Promise.all([
                adminFetch("/admin/orders?per_page=1"),
                adminFetch("/admin/users?per_page=1"),
                fetch(`${API_BASE}/products?per_page=1&sort=newest`, { headers: { "ngrok-skip-browser-warning": "true", "Accept": "application/json" } })
            ]);

            let items = [];

            if (ordersRes && ordersRes.ok) {
                const d = unwrap(await ordersRes.clone().json().catch(() => ({})));
                const data = d.items || d.data || [];
                if (data.length) {
                    const o = data[0];
                    items.push({ type: 'order', text: `Order #${o.id} placed by <b>${o.customer_name || o.customer_email || 'Customer'}</b>`, time: o.created_at, icon: 'fa-cart-plus', color: 'primary' });
                }
            }
            if (usersRes && usersRes.ok) {
                const d = unwrap(await usersRes.clone().json().catch(() => ({})));
                const data = d.data || d.items || [];
                if (data.length) {
                    const u = data[0];
                    items.push({ type: 'user', text: `New customer registered: <b>${u.name || 'Unknown'}</b>`, time: u.created_at, icon: 'fa-user-plus', color: 'warning' });
                }
            }
            if (productsRes && productsRes.ok) {
                const d = await productsRes.clone().json().catch(() => ({}));
                const payload = d.data || d;
                const data = payload.data || payload.items || [];
                if (data.length) {
                    const p = data[0];
                    items.push({ type: 'product', text: `Product added: <b>${p.name || 'Unknown'}</b>`, time: p.created_at, icon: 'fa-box-open', color: 'success' });
                }
            }

            items.sort((a, b) => new Date(b.time) - new Date(a.time));

            if (items.length === 0) {
                container.innerHTML = '<div class="text-muted text-center py-3">No recent activity</div>';
                return;
            }

            container.innerHTML = items.slice(0, 5).map(item =>
                `<div class="d-flex gap-3 activity-item">
                    <div class="activity-dot bg-${item.color}-subtle text-${item.color}"><i class="fa-solid ${item.icon}"></i></div>
                    <div>
                        <p class="m-0 text-muted" style="font-size:0.85rem">${item.text}</p>
                        <small class="text-muted" style="font-size:0.75rem">${timeAgo(item.time)}</small>
                    </div>
                </div>`
            ).join('');
        } catch (e) {
            container.innerHTML = '<div class="text-muted text-center py-3">Failed to load activity</div>';
        }
    }

    // --- PHASE 10: PENDING SHIPMENTS ---
    async function loadPendingShipments() {
        const tbody = document.getElementById('pendingShipmentsTbody');
        if (!tbody) return;
        try {
            const res = await adminFetch("/admin/orders?status=approved&per_page=5");
            if (!res || !res.ok) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Failed to load</td></tr>';
                return;
            }
            const d = unwrap(await res.json());
            const orders = d.items || d.data || [];

            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">No pending shipments</td></tr>';
                return;
            }

            tbody.innerHTML = orders.map(o => {
                const addr = o.shipping_address || {};
                const city = addr.city || addr.address || '';
                const itemsCount = o.items_count || o.items?.length || 'N/A';
                return `<tr>
                    <td class="fw-bold">#${o.id}</td>
                    <td>${o.customer_name || o.customer_email || 'N/A'}</td>
                    <td>${city}</td>
                    <td>${itemsCount} item(s)</td>
                    <td><span class="status-badge status-processing">${o.status || 'Pending'}</span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary print-order-btn" data-order-id="${o.id}" title="Print Invoice">
                            <i class="fa-solid fa-print"></i>
                        </button>
                    </td>
                </tr>`;
            }).join('');

            // Attach print handlers
            tbody.querySelectorAll('.print-order-btn').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const orderId = this.dataset.orderId;
                    try {
                        const printRes = await adminFetch(`/admin/orders/${orderId}/print-file`);
                        if (printRes && printRes.ok) {
                            const blob = await printRes.blob();
                            const url = window.URL.createObjectURL(blob);
                            window.open(url, '_blank');
                            setTimeout(() => window.URL.revokeObjectURL(url), 60000);
                        } else {
                            alert('Failed to generate print file');
                        }
                    } catch (e) {
                        alert('Network error printing order');
                    }
                });
            });
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Error loading data</td></tr>';
        }
    }

    // --- PHASE 11: SALES VS ORDERS CHART ---
    let salesChartInstance = null;
    let currentChartMode = 'daily';

    function renderChart(dailyData, mode) {
        const canvas = document.getElementById('salesChart');
        if (!canvas) return;
        if (!dailyData || dailyData.length === 0) return;

        mode = mode || 'daily';

        let labels, revenueData, orderData;

        if (mode === 'daily') {
            // Last 7 days
            const last7 = [];
            const today = new Date();
            for (let i = 6; i >= 0; i--) {
                const d = new Date(today);
                d.setDate(d.getDate() - i);
                const key = d.toISOString().slice(0, 10);
                const found = dailyData.find(x => x.date === key);
                last7.push({
                    date: key,
                    revenue: found ? parseFloat(found.revenue) : 0,
                    order_count: found ? parseInt(found.order_count) : 0
                });
            }
            labels = last7.map(d => d.date.slice(5));
            revenueData = last7.map(d => d.revenue);
            orderData = last7.map(d => d.order_count);
        } else if (mode === 'weekly') {
            // Last 4 ISO weeks, fill missing with 0
            const today = new Date();
            const last4 = [];
            for (let i = 3; i >= 0; i--) {
                const d = new Date(today);
                d.setDate(d.getDate() - i * 7);
                const wk = getWeekNumber(d);
                let rev = 0, ord = 0;
                dailyData.forEach(x => {
                    const xd = new Date(x.date + 'T00:00:00');
                    if (getWeekNumber(xd) === wk) {
                        rev += parseFloat(x.revenue) || 0;
                        ord += parseInt(x.order_count) || 0;
                    }
                });
                last4.push({ week: wk, revenue: rev, order_count: ord });
            }
            labels = last4.map(d => d.week);
            revenueData = last4.map(d => d.revenue);
            orderData = last4.map(d => d.order_count);
        } else if (mode === 'monthly') {
            // Last 6 months, fill missing with 0
            const today = new Date();
            const last6 = [];
            for (let i = 5; i >= 0; i--) {
                const d = new Date(today.getFullYear(), today.getMonth() - i, 1);
                const key = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
                let rev = 0, ord = 0;
                dailyData.forEach(x => {
                    if (x.date.slice(0, 7) === key) {
                        rev += parseFloat(x.revenue) || 0;
                        ord += parseInt(x.order_count) || 0;
                    }
                });
                last6.push({ month: key, revenue: rev, order_count: ord });
            }
            labels = last6.map(d => d.month);
            revenueData = last6.map(d => d.revenue);
            orderData = last6.map(d => d.order_count);
        }

        if (salesChartInstance) salesChartInstance.destroy();

        const ctx = canvas.getContext('2d');
        salesChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (EGP)',
                    data: revenueData,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.1)',
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y'
                }, {
                    label: 'Orders',
                    data: orderData,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25,135,84,0.1)',
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        ticks: { callback: v => currencyFmt.format(v) }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    // Chart mode toggle handlers
    document.querySelectorAll('[data-chart-mode]').forEach(btn => {
        btn.addEventListener('click', function () {
            const mode = this.dataset.chartMode;
            if (mode === currentChartMode || !window._dailyData) return;
            currentChartMode = mode;
            document.querySelectorAll('[data-chart-mode]').forEach(b => {
                b.classList.toggle('btn-primary', b === this);
                b.classList.toggle('btn-outline-secondary', b !== this);
            });
            renderChart(window._dailyData, mode);
        });
    });

    // --- PHASE 6: REPORT EXPORT ---
    const fromDateInput = document.getElementById('reportFromDate');
    const toDateInput = document.getElementById('reportToDate');
    const exportPdfBtn = document.getElementById('exportPdfBtn');
    const exportExcelBtn = document.getElementById('exportExcelBtn');

    async function downloadSalesReport(format) {
        const fromDate = fromDateInput?.value;
        const toDate = toDateInput?.value;

        if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
            alert("Validation Error: 'From Date' cannot be after 'To Date'.");
            return;
        }

        const params = new URLSearchParams();
        if (fromDate) params.set('from', fromDate);
        if (toDate) params.set('to', toDate);
        params.set('format', format);

        try {
            const response = await adminFetch(`/admin/reports/sales?${params.toString()}`);
            if (!response) return;

            if (response.ok) {
                const blob = await response.blob();
                const blobUrl = window.URL.createObjectURL(blob);
                const downloadAnchor = document.createElement('a');
                downloadAnchor.href = blobUrl;
                downloadAnchor.download = `sales-report-${fromDate || 'all'}-to-${toDate || 'all'}.${format}`;
                document.body.appendChild(downloadAnchor);
                downloadAnchor.click();
                document.body.removeChild(downloadAnchor);
                window.URL.revokeObjectURL(blobUrl);
            } else if (response.status === 422) {
                const errResult = await response.json();
                alert(`Validation Failed (422): ${errResult.message}`);
            } else if (response.status === 403) {
                alert("Access Denied (403): You do not have Admin privileges.");
            } else {
                alert(`Server Error (${response.status}): Failed to generate report.`);
            }
        } catch (error) {
            console.error("Report Export Error:", error);
            alert("Network Error: Could not connect to the server.");
        }
    }

    if (exportPdfBtn) {
        exportPdfBtn.addEventListener('click', function(e) {
            e.preventDefault();
            downloadSalesReport('pdf');
        });
    }
    if (exportExcelBtn) {
        exportExcelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            downloadSalesReport('xlsx');
        });
    }

    // --- INIT DASHBOARD ---
    if (document.getElementById("kpi-total-sales")) {
        loadDashboardKPIs().then(() => {
            // After KPIs load, render Best Sellers + Chart from cached data
            if (window._bestSellers && window._bestSellers.length > 0) renderBestSellers(window._bestSellers);
            if (window._dailyData && window._dailyData.length > 0) renderChart(window._dailyData, currentChartMode);
        });
        loadRecentActivity();
        loadPendingShipments();
    }

    // --- SIDEBAR TOGGLE ---
    const sidebar = document.getElementById('sidebarMenu');
    const toggleBtn = document.getElementById('menuToggleBtn');
    const closeBtn = document.getElementById('closeSidebarBtn');
    const overlay = document.getElementById('sidebarOverlay');
    function openSidebar() {
        if (sidebar) sidebar.classList.add('show');
        if (overlay) overlay.classList.add('show');
    }
    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('show');
        if (overlay) overlay.classList.remove('show');
    }
    if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // --- MINI BAR ANIMATION ---
    setTimeout(() => {
        document.querySelectorAll('.mini-bar').forEach(bar => {
            const targetHeight = bar.getAttribute('data-height');
            if (targetHeight) bar.style.height = targetHeight;
        });
    }, 500);
});
